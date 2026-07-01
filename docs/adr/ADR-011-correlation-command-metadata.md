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

### 4. Domain and Application layers stay pure

`correlationId` is a **pure infrastructure concern**. It is handled exclusively in the Infrastructure layer:

- The Domain layer never sees it.
- The Application layer (`Command`, `CommandHandler`, `TransactionManager`, `Outbox` interface) never sees it.
- Infrastructure adapters (`OutboxAdapter`, HTTP listeners, Monolog processors) read and propagate it.

---

## Backend (`apps/backend`) — implementation specifics

### Outbox

ADR-010 `outbox_events` table is extended with one column:

| Column | Type | Description |
|---|---|---|
| `correlation_id` | `UUID NOT NULL` | Propagated to all Integration Events produced by the same action |

Written by `OutboxAdapter` at record time: it reads `CorrelationContext::get()` and passes the value to `OutboxMessage`. If no context is set (e.g. a CLI command without explicit setup), a random UUID is generated as fallback.

The `correlation_id` flows into the published AMQP message as the native `correlation_id` property during the publish phase.

### CorrelationContext

`CorrelationContext` is a singleton Infrastructure service — a simple mutable holder for the current correlationId. It is set and cleared at each entry point:

| Entry point | Behaviour |
|---|---|
| HTTP request (`kernel.request`) | reads `X-Correlation-Id` header or generates a UUID; stores it; sets the header if absent |
| HTTP response (`kernel.response`) | echoes `X-Correlation-Id` back in the response header |
| HTTP terminate (`kernel.terminate`) | clears the context |

A Monolog processor reads from `CorrelationContext` and injects `correlationId` into every log record. It is auto-registered by Symfony's autoconfigure via `ProcessorInterface`.

The `OutboxAdapter` (Infrastructure) injects `CorrelationContext` directly — no interface indirection is needed since both reside in the Infrastructure layer.

### Outbox worker

The outbox worker reads `correlation_id` from each processed row and passes it to the `IntegrationEvent`. It propagates as the AMQP `correlation_id` message property. The worker does **not** update the global `CorrelationContext` — its logs are not correlated per row.

## Consequences

- A single user action is traceable across all the events it produces, regardless of which service or domain emitted them.
- Logs across all services are correlatable without additional tooling — the `correlationId` is sufficient.
- The Domain and Application layers are completely free of `correlationId` — it is invisible to Commands, CommandHandlers, and domain logic.
- Services that receive HTTP requests must forward `X-Correlation-Id` on all outbound calls to preserve the chain.
- Entry points outside HTTP (jobs, CLI) must explicitly generate a `correlationId` at invocation time.
- The outbox worker logs are not correlated per row; full correlation in the worker context is deferred to a future iteration.
