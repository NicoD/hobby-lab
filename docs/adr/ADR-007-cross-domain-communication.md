# ADR-007 — Cross-domain communication strategy

**Status**: Accepted

## Context

ADR-005 establishes that domains in `apps/backend` communicate through Domain Events (Symfony Messenger) and never via direct cross-namespace imports. It does not address:

- The distinction between Domain Events and Integration Events
- How communication works when the consumer is in a separate service (`apps/history`, `apps/user`, etc.)
- Delivery guarantees and event persistence
- The transaction boundary problem (in-process dispatch after commit)

This ADR supersedes the inter-domain communication section of ADR-005 and extends it to cover all scenarios.

## Decisions

### 1. Domain Events are internal to their domain

A Domain Event is raised by an aggregate and consumed **only within the same domain**. It is a pure PHP object with no serialization concern and no cross-domain contract.

```
ColorLab aggregate raises BrandCreated
  → ColorLab Application listener reacts (same domain)
```

Domain Events never cross a domain boundary directly. They are not published to a message broker.

### 2. Integration Events are the cross-domain contract

When a domain needs to notify another domain (whether in the same app or a separate service), the Application layer listens to the Domain Event and produces an **Integration Event**.

```
ColorLab aggregate raises BrandCreated (Domain Event)
  → ColorLab\Application\EventListener\OnBrandCreated
  → writes IntegrationEvent to the Outbox
```

The Integration Event is the stable, versioned contract. Consumers depend on it, never on the Domain Event.

### 3. Generic envelope

Integration Events use a single envelope structure. There is no dedicated class per event type — only `eventType` and `payload` vary.

```php
class IntegrationEvent
{
    public string $eventType;   // "ColorLab.Brand.Created"
    public string $globalId;    // "//ColorLab/Brand/{uuid}"
    public string $ownedBy;     // userId (entity owner)
    public array  $payload;     // serialized domain event data
    public string $occurredAt;  // ISO 8601
}
```

The `eventType` field maps to the RabbitMQ routing key (see §5).

### 4. Outbox Pattern for all cross-domain communication

The Outbox Pattern applies to **all** Integration Events, including communication between two domains on the same infrastructure.

**Why not in-process dispatch for same-infra consumers?**

- After a transaction commits, an in-process dispatch has no persistence. A failure (broker unavailable, process crash) loses the event permanently with no retry path.
- The Outbox provides a durable record of every event that was requested to be published, along with its delivery status (`pending` / `sent` / `failed`).
- A single Outbox implementation per app (`apps/backend`) covers all domains. The overhead of routing a same-infra event through the broker is negligible compared to the uniformity gained.

**The flow:**

```
1. Aggregate changes + Integration Event written to outbox_events
   → single DB transaction (atomic)

2. Outbox worker polls outbox_events WHERE status = 'pending'
   → publishes to RabbitMQ Exchange
   → marks status = 'sent'

3. Consumer receives event from its own queue
```

A single Outbox service handles all domains in `apps/backend`. Each app that needs outbox behavior (`apps/user` if applicable) implements its own.

### 5. RabbitMQ topology: Topic Exchange

All Integration Events are published to a single **Topic Exchange** (`domain.events`).

The routing key follows the pattern: `{domain}.{entity}.{action}`

```
colorlab.brand.created
colorlab.brand.deleted
colorlab.range.assigned
```

Each consuming application declares its own **durable queue** bound to the exchange with a binding key:

| Consumer | Binding key | Receives |
|---|---|---|
| `apps/history` | `#` | all events |
| A notification service | `colorlab.brand.*` | all Brand events |
| A search indexer | `colorlab.#` | all ColorLab events |

The publisher (`apps/backend`) is unaware of how many consumers exist. Adding a new consumer requires no change to the publisher.

## Consequences

- Every aggregate change that needs to notify another domain produces an Integration Event, written in the same DB transaction
- Domain Events remain pure and free of serialization concerns
- A single code path handles all cross-domain communication regardless of consumer location
- The `eventType` and `payload` schema of an Integration Event is a **breaking change** once published — versioning must be considered before modifying it
- `apps/user` is currently out of scope for this outbox implementation; events from the identity domain are not published to the broker unless explicitly decided
