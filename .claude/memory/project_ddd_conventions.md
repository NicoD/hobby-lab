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

## Rules

- Bounded Contexts: strategic concept only — never materialized as folders
- Domains named by concept: PascalCase (Symfony), camelCase (NestJS)
- `domain/` flat by default — subfolder only when 5+ related classes
- No cross-domain imports — Domain Events only
- PII only in `apps/user`; `apps/backend` reads X-User-Id / X-User-Roles, never a JWT
- Deptrac configured before first domain (violation = CI fail)
- NestJS modules closed by default (`@Module({ exports: [] })`)
- Never extract a domain in anticipation

## Entity construction

Constructor = creation factory (generates IDs, enforces invariants). Doctrine reconstitutes via reflection — no `from()` factory needed on entities.

`Brand` uses handle as primary key (no `BrandId`). UUID-identity entities use `SomeId::create()`.

## Value Objects

Default: `new MyVo($value)` directly. Static factory only when semantically meaningful (`create()`, `inCents()`) — never `from()` or `of()`.

## Doctrine types

Each VO mapped by Doctrine gets a type in `Infrastructure/Doctrine/Type/`. The type calls `new MyVo($value)` — the VO exposes nothing for this.
