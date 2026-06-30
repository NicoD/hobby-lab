# ADR-011 — Correlation ID

**Status**: Accepted

## Context

A single user action can produce multiple events emitted at different times and from different services or domains:

```
BrandMetadataUpdated  ──┐
                        ├── same user action
MediaUploaded         ──┘
```

Consumers that build history or audit logs have no way to group these events as belonging to the same user action. Logs across services are equally impossible to correlate without a shared identifier.

## Decisions

### 1. Every execution unit carries a correlationId

A `correlationId` (UUID) is generated once per user action and propagated through the entire chain it triggers — across services, domains, and async boundaries.

**Origin rules:**

- **HTTP request** → generated at the request boundary, carried in the `X-Correlation-Id` header for outbound calls
- **Event consumer** → inherited from the incoming message envelope, not regenerated
- **Scheduled job / CLI** → generated at invocation time

This allows tracing a full chain of reactions under a single id:

```
HTTP request → correlationId: "abc"
  → BrandUpdated          [correlationId: "abc"]
    → CreateHistoryEntry  [correlationId: "abc"]  ← inherited
      → HistoryCreated    [correlationId: "abc"]  ← inherited
```

### 2. Transport propagation

| Transport | Propagation mechanism |
|---|---|
| HTTP | `X-Correlation-Id` request/response header |
| AMQP (RabbitMQ) | Native `correlation_id` message property |

In both cases the `correlationId` travels in the envelope, not in the payload. The payload stays free of infrastructure concerns.

### 3. correlationId in logs

Every log record across all services must include the `correlationId` of the current execution unit. This makes the full trace recoverable from logs alone, without additional tooling.

Each service maintains a `CorrelationContext` — a global context populated at each entry point and read by the logging infrastructure:

| Entry point | Populates context from |
|---|---|
| HTTP request | `X-Correlation-Id` header (or generates if absent) |
| AMQP consumer | AMQP `correlation_id` envelope property |
| Background worker | correlationId of the item being processed |

### 4. Domain Events stay pure

Domain Events carry no `correlationId`. It is an infrastructure concern injected at the application boundary, not inside the domain.

---

## Backend (`apps/backend`) — implementation specifics

### Outbox

ADR-010 `outbox_events` table is extended with one column:

| Column | Type | Description |
|---|---|---|
| `correlation_id` | `UUID NOT NULL` | Propagated to all Integration Events produced by the same action |

Written during the write phase (inside the DB transaction), alongside `domain_event_class` and `domain_payload`. It flows into the published AMQP message as the native `correlation_id` property during the publish phase.

### CorrelationContext

A global `CorrelationContext` service is set and cleared at each entry point:

- **HTTP** → `kernel.request` / `kernel.terminate`
- **AMQP consumer** → before/after message handling
- **Outbox worker** → start/end of each iteration

A Monolog processor reads from `CorrelationContext` and injects `correlationId` into every log record.

### Command propagation

`correlationId` travels through the call stack explicitly: the Command carries it, the `CommandHandler` passes it to `TransactionManager`, which writes it to the outbox.

## Consequences

- A single user action is traceable across all the events it produces, regardless of which service or domain emitted them.
- Logs across all services are correlatable without additional tooling — the `correlationId` is sufficient.
- Domain Events remain free of infrastructure concerns.
- Services that receive HTTP requests must forward `X-Correlation-Id` on all outbound calls to preserve the chain.
- Entry points outside HTTP (jobs, CLI) must explicitly generate a `correlationId` at invocation time.
