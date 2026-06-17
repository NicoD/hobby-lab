---
name: project-ddd-conventions
description: DDD conventions for recipe-lab — domains, aggregates, layers, anti-patterns
metadata:
  type: project
---

## Layers

```
domain/         → pure domain objects, no framework dependency
application/    → commands, queries, handlers, read models
infrastructure/ → Doctrine, adapters
UI/             → controllers, listeners
```

## Domain/ internal organisation

```
Domain/
  Model/        → entities, aggregate roots, value objects
  Repository/   → repository interfaces
  Event/        → domain events (when introduced)
  Service/      → domain services (when introduced)
```

All entities, aggregate roots, and value objects live under `Domain/Model/` — no sub-namespace inside `Model/` (VOs and entities are siblings).

## Rules

- Bounded Contexts: strategic concept only — never materialized as folders
- Domains named by concept: PascalCase (Symfony), camelCase (NestJS)
- Sub-folders in `Domain/` follow the fixed structure above — never free-form
- No cross-domain imports — Domain Events only
- PII only in `apps/user`; `apps/backend` reads X-User-Id / X-User-Roles, never a JWT
- Deptrac configured before first domain (violation = CI fail)
- NestJS modules closed by default (`@Module({ exports: [] })`)
- Never extract a domain in anticipation

## Entity construction

Private constructor = pure instantiation (no IO, no generation). Doctrine reconstitutes via `newInstanceWithoutConstructor()` — constructor is bypassed.

Static `create()` = named factory for production code. Generates IDs/handles, may receive domain services via parameters. Callers use `Entity::create(...)`, never `new Entity(...)`.

Handle uniqueness: aggregate-scoped uniqueness enforced by the aggregate root. Global uniqueness enforced by a Domain Service before `create()` + DB `UNIQUE` constraint as safety net.

## Value Objects

Default: `new MyVo($value)` directly. Static factory only when semantically meaningful (`create()`, `inCents()`) — never `from()` or `of()`.

## Doctrine types

Each VO mapped by Doctrine gets a type in `Infrastructure/Doctrine/Type/`. The type calls `new MyVo($value)` — the VO exposes nothing for this.
