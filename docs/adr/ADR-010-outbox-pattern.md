# ADR-010 — Outbox Pattern implementation

**Status**: Accepted

## Context

ADR-007 establishes the Outbox Pattern as the delivery mechanism for all Integration Events. It defines the intent and the flow, but not the implementation details: table schema, worker strategy, retry behaviour, or broker topology.

This ADR completes ADR-007 with those implementation decisions.

A placeholder already exists in `TransactionManager` to write events to the outbox within the same DB transaction as the aggregate change. RabbitMQ is not yet installed.

## Decisions

### 1. Domain event vs integration event — two-phase processing

The outbox stores two distinct payloads:

| Phase | Name | Content | When produced |
|---|---|---|---|
| Write | **Domain payload** | Serialized domain event state | Inside the DB transaction |
| Resolve | **Integration payload** | Routing type + consumer-facing data | Worker, outside transaction |

**Why not resolve the integration event before writing to the outbox?**

The naive approach is to call the mapper before the transaction and write the integration event directly. This is simple but creates coupling: the integration mapping logic must run inside the transaction, which risks making the transaction heavy (joins, read-model queries) and ties the broker's concerns to the DB write path.

**The two-phase approach separates these concerns:**

1. **Write phase** (inside transaction): `TransactionManager` stores the raw domain event class and domain payload. This is always cheap — it mirrors what the domain event already carries.
2. **Resolve phase** (worker, outside transaction): the worker calls `IntegrationEventResolverInterface::resolve()` to produce the integration event, then stores it alongside the domain payload. This mapping can be arbitrarily complex without affecting commit latency.
3. **Publish phase** (worker, outside transaction): the worker publishes the already-resolved integration payload to the broker.

If publishing fails, retries replay from the resolved integration payload — no re-resolution needed.

**`DomainEvent::$payload` — abstract property hook**

Every domain event exposes its state through an abstract `payload` hook (PHP 8.4):

```php
abstract class DomainEvent
{
    abstract public array $payload { get; }
}
```

Each concrete event implements the hook, returning a flat `array<string, scalar|null>` — the canonical serialization of the event's state. This is what the outbox stores as `domain_payload`.

### 2. `outbox_events` table schema

```sql
CREATE TABLE outbox_events (
    id                  UUID        PRIMARY KEY,
    domain_event_class  VARCHAR     NOT NULL,   -- FQCN, e.g. "App\ColorLab\Brand\Event\BrandCreatedEvent"
    domain_payload      JSONB       NOT NULL,   -- serialized domain state at the moment of the write
    integration_type    VARCHAR,                -- routing key resolved by worker: "colorlab.brand.created"
    integration_payload JSONB,                  -- consumer-facing payload resolved by worker
    occurred_at         TIMESTAMPTZ NOT NULL,
    mapped_at           TIMESTAMPTZ,            -- when the worker resolved the integration event
    status              VARCHAR     NOT NULL DEFAULT 'pending'
                            CHECK (status IN ('pending', 'mapped', 'sent', 'failed')),
    attempt             SMALLINT    NOT NULL DEFAULT 0,
    next_retry_at       TIMESTAMPTZ,
    last_error          TEXT,
    sent_at             TIMESTAMPTZ,
    created_at          TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX outbox_events_processable_idx ON outbox_events (status, next_retry_at)
    WHERE status IN ('pending', 'mapped');
```

**Status lifecycle:**

```
pending → mapped → sent
   ↘          ↘
   failed     failed    (after 5 attempts at any phase)
```

`domain_event_class` records the FQCN of the domain event — both for routing and for debugging (replaying, inspecting, filtering by CLI).

### 3. Write path — TransactionManager

The Application layer writes to `outbox_events` **in the same Doctrine transaction** as the aggregate change. No event is written outside a transaction.

```
Command Handler
  → opens transaction
  → aggregate.doSomething() → raises DomainEvent
  → TransactionManager.execute() collects domain events
  → outbox.record(...$events)          ← writes domain_event_class + domain_payload
  → commits transaction
```

The domain event's `payload` hook is the sole source of serialized state. Mapping to an integration event happens in the worker, not here.

### 4. Worker — two-phase loop with PostgreSQL LISTEN/NOTIFY

The worker is a long-lived Symfony Console command (`bin/console outbox:process`) running as a dedicated Docker service.

**Wake-up strategy: LISTEN/NOTIFY + 30s timeout**

```php
while (true) {
    $pdo->pgsqlGetNotify(PDO::FETCH_ASSOC, 30_000); // blocks up to 30s
    $this->process();
}
```

The worker blocks on a PostgreSQL LISTEN call with a 30-second timeout:

- **Immediate**: the `TransactionManager` sends `pg_notify('outbox_new_event', '')` after each successful commit.
- **Fallback**: the 30-second timeout fires regardless, catching any event missed while the worker was down.

The notify is sent after the transaction commits. If the process crashes between commit and notify, the timeout fallback ensures the event is still picked up.

**Phase 1 — resolve (`resolvePending`)**

```
SELECT pending FOR UPDATE SKIP LOCKED LIMIT 1
  → IntegrationEventResolverInterface::resolve(domainEventClass, domainPayload)
  → UPDATE status='mapped', integration_type, integration_payload, mapped_at
  → COMMIT
```

If `resolve()` returns `null` (no mapper registered for this event class), the row is silently moved to `sent` — the event does not need to be published.

**Phase 2 — publish (`publishMapped`)**

```
SELECT mapped FOR UPDATE SKIP LOCKED LIMIT 1
  → EventPublisher::publish(IntegrationEvent)
  → UPDATE status='sent', sent_at
  → COMMIT
```

Each phase runs in its own transaction. A failure in Phase 2 retries from the already-resolved `integration_payload` without re-running the mapper.

**Concurrent worker safety — SKIP LOCKED**

`SELECT FOR UPDATE SKIP LOCKED` ensures two concurrent workers (e.g. during a rolling deploy) never process the same row simultaneously. This is the standard PostgreSQL mechanism for queue-like workloads.

**Considered alternatives for worker strategy**

| Strategy | Latency | Complexity | Rejected because |
|---|---|---|---|
| Cron / Symfony Scheduler | depends on interval | low | interval is a hard floor on latency; no early wake-up from application code |
| Sleep loop daemon | depends on sleep | low | wastes CPU polling when idle |
| `kernel.terminate` listener | sub-ms | low | no retry, no delivery guarantee |
| **LISTEN/NOTIFY + timeout** | **sub-second** | **low-medium** | **chosen** |

### 5. IntegrationEventResolverInterface — bundle/application boundary

The bundle defines:

```php
interface IntegrationEventResolverInterface
{
    /** @param class-string $domainEventClass */
    public function resolve(string $domainEventClass, array $domainPayload): ?array;
}
```

`apps/backend` provides the implementation (`IntegrationEventTranslator`) which iterates tagged `IntegrationEventMapper` services. Each domain registers its own mapper:

```php
#[AutoconfigureTag('app.outbox.integration_event_mapper')]
interface IntegrationEventMapper
{
    public function supports(string $domainEventClass): bool;
    public function map(array $domainPayload): array;
}
```

The bundle worker only knows `IntegrationEventResolverInterface` — it is unaware of individual mappers or domain event classes.

### 6. RabbitMQ topology

All Integration Events are published to a single **Topic Exchange**: `domain.events`.

Routing key pattern: `{domain}.{entity}.{action}` (e.g. `colorlab.brand.created`).

Each consumer declares its own **durable queue** bound to the exchange:

| Consumer | Binding key | Receives |
|---|---|---|
| History service | `#` | all events |
| Notification service | `colorlab.brand.*` | all Brand events |

The publisher is unaware of consumers. Adding a consumer requires no change to `apps/backend`.

### 7. Retry and failure handling

- Each phase (`pending → mapped`, `mapped → sent`) has its own retry counter.
- Each failure increments `attempt` and sets `next_retry_at = now() + 30s * 2^attempt` (backoff: 30s, 1m, 2m, 4m, 8m…).
- After **5 failed attempts**, the row moves to `status = 'failed'` and is excluded from polling.
- Failed rows require a manual status reset to either `pending` or `mapped` to be retried.
- Failed rows trigger monitoring alerts. No automatic dead-letter queue at this stage.

### 8. PostgreSQL hard requirement

This bundle uses PostgreSQL-specific features that have no portable equivalent:

- `LISTEN/NOTIFY` — wake-up mechanism for the worker
- `pgsqlGetNotify()` — PHP PDO extension, PostgreSQL only
- `SELECT FOR UPDATE SKIP LOCKED` — concurrent worker safety
- `JSONB` — payload column type
- `TIMESTAMPTZ` — timezone-aware timestamps

The bundle enforces the requirement at boot with a runtime guard in `OutboxWorker`:

```php
if (!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
    throw new \RuntimeException('OutboxBundle requires PostgreSQL — LISTEN/NOTIFY and SKIP LOCKED are not portable.');
}
```

### 9. Code organisation — internal Symfony bundle

The outbox pattern is pure technical infrastructure. It does not belong to any bounded context and does not fit the hexagonal organisation enforced inside `apps/backend/src/`.

**Decision: extracted as an internal Symfony bundle under `apps/backend/packages/`.**

```
apps/backend/
  packages/
    outbox-bundle/
      migrations/
        Version20260626000000CreateOutboxEvents.php
        Version20260629000000_OutboxTwoPhase.php   ← renames columns, adds mapped status
      src/
        OutboxBundle.php
        OutboxMessage.php        ← id, domainEventClass, domainPayload, occurredAt
        OutboxRecorder.php       ← interface consumed by apps/backend
        Adapter/
          DoctrineOutboxAdapter.php
        Publisher/
          IntegrationEvent.php
          IntegrationEventResolverInterface.php
          EventPublisher.php
          MessengerEventPublisher.php
        Worker/
          OutboxWorker.php
        CLI/
          ListOutboxEventsCommand.php   ← outbox:events (--status, --type, --limit, --watch)
          ShowOutboxEventCommand.php    ← outbox:event <uuid>
          ProcessOutboxCommand.php
        DependencyInjection/
          OutboxExtension.php
  src/
    Shared/
      Application/Service/
        Outbox.php                      ← record(DomainEvent ...$events): void
        IntegrationEventMapper.php      ← supports() + map()
        IntegrationEventTranslator.php  ← implements IntegrationEventResolverInterface
      Infrastructure/Outbox/
        OutboxAdapter.php               ← implements Outbox, creates OutboxMessage from DomainEvent
```

`OutboxRecorder` is the **only** public contract the bundle exposes to `apps/backend`. Everything else is internal.

**Doctrine migrations — hand-written, not generated**

The bundle uses DBAL directly (no ORM entity). The `outbox_events` table is therefore invisible to `doctrine:migrations:diff`. The bundle ships hand-written migrations registered via `OutboxExtension::prepend`.

### 10. At-least-once delivery

The outbox guarantees **at-least-once** delivery. Consumers must be idempotent: receiving the same event twice must produce the same result.

Each Integration Event carries a stable `id` (the domain event UUID). Consumers use this id to detect and discard duplicates.

## Consequences

- Every aggregate mutation that crosses a domain boundary is durable — no event is lost on broker unavailability or process crash.
- Domain payload and integration payload are both stored — full audit trail and ability to replay from either stage.
- The mapping step can be arbitrarily complex without affecting commit latency.
- A failed publish retries from the resolved integration payload — no re-mapping needed.
- Consumers must handle duplicate delivery. This is documented as a contract, not a limitation.
- `status = 'failed'` rows require manual intervention. Monitoring must alert on their presence.
- When RabbitMQ is unavailable, rows accumulate as `mapped` and are published when the broker recovers — no data loss.
- **PostgreSQL is a hard requirement.** The bundle will throw at boot on any other engine.
