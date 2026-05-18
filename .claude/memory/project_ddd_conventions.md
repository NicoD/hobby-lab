---
name: project-ddd-conventions
description: DDD conventions for recipe-lab — domains, aggregates, layers, anti-patterns
metadata:
  type: project
---

DDD conventions validated with the user. See [[project-architecture]] for global context.

**Why:** Prevent domain pollution and keep boundaries coherent over time.

**How to apply:** Apply these rules systematically when scaffolding or adding code.

## Core principle

Bounded Contexts are a **strategic concept only** — documented in ADRs and context maps, never materialized as folders. Domains are what gets materialized in code.

## Rules

- Domains named by concept (PascalCase in Symfony, camelCase in NestJS)
- `domain/` folders are flat by default — subfolder per aggregate only when 5+ related classes
- No direct cross-domain imports — communication via Domain Events only
- Deptrac configured before the first domain in Symfony (violation = CI fail)
- NestJS modules are closed by default (`@Module({ exports: [] })`)
- PII only in `apps/user` — never in `apps/backend`
- `apps/backend` never validates a JWT — it reads X-User-Id and X-User-Roles from headers

## Signal to extract a domain into a new service

- Domain needs independent deployment
- Different team takes ownership
- Significantly different scaling requirements

Never extract in anticipation.

## Layers

domain/ → pure domain objects (no framework dependency)
application/ → commands, queries, handlers, DTOs
infrastructure/ → implementations (Doctrine, Prisma, adapters)
interface/ → controllers, listeners
