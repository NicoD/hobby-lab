# ADR-010 — Outbox Pattern implementation

**Status**: Accepted

## Context

ADR-007 establishes the Outbox Pattern as the delivery mechanism for all Integration Events. It defines the intent and the flow, but not the implementation details: table schema, worker strategy, retry behaviour, or broker topology.

This ADR completes ADR-007 with those implementation decisions.

A placeholder already exists in `TransactionManager` to write Integration Events to the outbox within the same DB transaction as the aggregate change. RabbitMQ is not yet installed.

## Decisions

### 1. outbox_events table schema

```sql
CREATE TABLE outbox_events (
    id              UUID        PRIMARY KEY,
    event_type      VARCHAR     NOT NULL,            -- routing key: "colorlab.brand.created"
    payload         JSONB       NOT NULL,
    occurred_at     TIMESTAMPTZ NOT NULL,
    status          VARCHAR     NOT NULL DEFAULT 'pending'
                        CHECK (status IN ('pending', 'sent', 'failed')),
    attempt         SMALLINT    NOT NULL DEFAULT 0,
    next_retry_at   TIMESTAMPTZ,
    last_error      TEXT,
    sent_at         TIMESTAMPTZ,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX ON outbox_events (status, next_retry_at)
    WHERE status = 'pending';
```

`global_id` and any ownership identifier are part of each event's domain identity and belong inside `payload`. The worker treats the payload as opaque — it only needs `event_type` for routing and the delivery mechanic columns above.

### 2. Write path — TransactionManager

The Application layer writes to `outbox_events` **in the same Doctrine transaction** as the aggregate change. No event is written outside a transaction.

```
Command Handler
  → opens transaction
  → aggregate.doSomething() → raises DomainEvent
  → Application EventListener reacts → builds IntegrationEvent
  → TransactionManager.appendEvent(integrationEvent)   ← writes to outbox_events
  → commits transaction
```

If the commit fails, the outbox row is rolled back with it. The outbox is the single source of truth for "what needs to be published".

### 3. Worker — PostgreSQL LISTEN/NOTIFY with timeout fallback

The worker is a long-lived Symfony Console command (`bin/console app:outbox:process`) running as a dedicated Docker service.

**Wake-up strategy: LISTEN/NOTIFY + 30s timeout**

The worker blocks on a PostgreSQL LISTEN call with a 30-second timeout. It can be woken up in two ways:

- **Immediate**: the `TransactionManager` sends a `pg_notify` after each successful commit
- **Fallback**: the 30-second timeout fires regardless, catching any event missed while the worker was down

Both paths execute the same processing function — one loop, two wake-up triggers:

```php
while (true) {
    $pdo->pgsqlGetNotify(PDO::FETCH_ASSOC, 30_000); // blocks up to 30s
    $this->processOutbox();
}
```

**Notification from TransactionManager (not a DB trigger)**

The `TransactionManager` sends the notification from PHP after flush, keeping the logic in application code:

```php
// After $entityManager->flush()
$connection->executeStatement("SELECT pg_notify('outbox_new_event', '')");
```

The notify is sent as a standalone query **after** the transaction commits, not inside it. There is no risk of waking the worker for a rolled-back write: if the transaction rolls back, the PHP code path that sends the notify is never reached. If the process crashes between commit and notify, the 30-second timeout fallback ensures the worker will still pick up the event.

**Concurrent worker safety — SKIP LOCKED**

When fetching pending rows, the worker uses `SELECT FOR UPDATE SKIP LOCKED`:

```sql
SELECT * FROM outbox_events
WHERE status = 'pending'
ORDER BY created_at
LIMIT 10
FOR UPDATE SKIP LOCKED
```

`SKIP LOCKED` causes any row already locked by another worker to be silently skipped. Two concurrent workers (e.g. during a rolling deploy) will never process the same event simultaneously. This is the standard PostgreSQL mechanism for queue-like workloads.

The worker only picks rows where `next_retry_at IS NULL OR next_retry_at <= now()`.

For each pending row, the worker:
1. Publishes to the RabbitMQ Topic Exchange (`domain.events`)
2. On success → marks `status = 'sent'`, sets `sent_at = now()`
3. On failure → increments `attempt`, sets `last_error`, sets `next_retry_at` with exponential backoff (`30s * 2^attempt`), keeps `status = 'pending'`

**Considered alternatives**

| Strategy | Latency | Complexity | Rejected because |
|---|---|---|---|
| Cron / Symfony Scheduler | depends on interval | low | interval is a hard floor on latency; scheduler overhead for what is a tight loop |
| Sleep loop daemon | depends on sleep | low | wastes CPU polling when idle; same latency problem as cron |
| `kernel.terminate` listener | sub-ms | low | no retry, no delivery guarantee, silent on daemon-generated events |
| **LISTEN/NOTIFY + timeout** | **sub-second** | **low-medium** | **chosen** |

The Symfony Scheduler was specifically considered as a lighter alternative to a sleep loop — it avoids the manual sleep management and integrates with the framework. It was ruled out for the same reason as cron: the polling interval is a ceiling on throughput, and there is no mechanism to wake it early from application code.

### 4. RabbitMQ topology

All Integration Events are published to a single **Topic Exchange**: `domain.events`.

Routing key pattern: `{domain}.{entity}.{action}` (e.g. `colorlab.brand.created`).

Each consumer declares its own **durable queue** bound to the exchange:

| Consumer | Binding key | Receives |
|---|---|---|
| History service | `#` | all events |
| Notification service | `colorlab.brand.*` | all Brand events |

The publisher is unaware of consumers. Adding a consumer requires no change to `apps/backend`.

### 5. Retry and failure handling

- The worker retries `pending` events where `next_retry_at IS NULL OR next_retry_at <= now()`.
- Each failure sets `next_retry_at = now() + interval '30 seconds' * 2^attempt` (exponential backoff: 30s, 1m, 2m, 4m, 8m…).
- After **5 failed attempts**, the row is moved to `status = 'failed'` and excluded from polling.
- Failed rows trigger a manual alert (log + monitoring). No automatic dead-letter queue at this stage.
- Retrying a `failed` row requires a manual status reset to `pending` and clearing `next_retry_at`.

### 7. Code organisation — internal Symfony bundle

The outbox pattern is pure technical infrastructure. It does not belong to any bounded context and does not fit the hexagonal organisation enforced inside `apps/backend/src/`. Placing it there would force an artificial mapping onto a structure designed for domain code.

**Decision: the outbox is extracted as an internal Symfony bundle, living in the monorepo under `packages/`.**

```
hobby-lab/
  apps/
    backend/
  packages/
    outbox-bundle/
      src/
        Entity/
          OutboxEvent.php
        Migrations/
          Version_CreateOutboxEvents.php
        Worker/
          OutboxWorker.php
          ProcessOutboxCommand.php
        Port/
          OutboxPort.php              ← interface consumed by apps/backend
        Adapter/
          DoctrineOutboxAdapter.php   ← implements OutboxPort
        DependencyInjection/
          OutboxExtension.php         ← registers entity mappings, migration path, services
      composer.json
```

`apps/backend` declares a `path` repository in its `composer.json` and requires the bundle like any Composer dependency:

```json
"repositories": [
  { "type": "path", "url": "../../packages/outbox-bundle" }
],
"require": {
  "hobby-lab/outbox-bundle": "*"
}
```

**Doctrine migrations**

The bundle's `OutboxExtension` registers its own migration path via `prepend`:

```php
$container->prependExtensionConfig('doctrine_migrations', [
    'migrations_paths' => [
        'HobbyLab\OutboxBundle\Migrations' => __DIR__.'/../Migrations',
    ],
]);
```

`doctrine:migrations:migrate` run from `apps/backend` picks up both the app's migrations and the bundle's migrations. Tracking is shared in the same `doctrine_migration_versions` table, with no conflict because namespaces differ.

**Integration point in apps/backend**

`apps/backend` depends only on `OutboxPort` (the interface). The `TransactionManager` receives it by injection. The bundle wires the concrete adapter automatically via its extension.

```
apps/backend/
  Application layer → injects OutboxPort
  ↓
  (bundle DI wires to)
  ↓
  DoctrineOutboxAdapter → outbox_events table
```

**Why not place it in `Shared/`**

`Shared` inside `apps/backend` already contains cross-domain code. Adding technical infrastructure there conflates two different concerns: shared domain concepts and shared plumbing. The bundle boundary enforces a cleaner separation — the outbox has an explicit public API (`OutboxPort`) and its internals are fully encapsulated.

**Why not a separate repository**

The bundle will evolve in lockstep with `apps/backend` during initial development. A separate repository would introduce version coordination overhead without the reuse benefit that justifies it. Extraction to a standalone repository remains straightforward if the bundle is ever needed in another project.

### 8. At-least-once delivery

The outbox guarantees **at-least-once** delivery. Consumers must be idempotent: receiving the same event twice must produce the same result.

Each Integration Event carries a stable `id` (UUID). Consumers use this id to detect and discard duplicates if needed.

## Consequences

- Every aggregate mutation that crosses a domain boundary is durable — no event is lost on broker unavailability or process crash.
- The worker is a simple polling loop with no framework magic — straightforward to debug and monitor.
- Consumers must handle duplicate delivery. This is documented as a contract, not a limitation.
- `status = 'failed'` rows require manual intervention. Monitoring must alert on their presence.
- When RabbitMQ is unavailable, rows accumulate in `pending` and are published when the broker recovers — no data loss.
