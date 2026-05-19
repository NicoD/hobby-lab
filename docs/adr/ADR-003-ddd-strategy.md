# ADR-003 — DDD strategy and domains

**Status**: Accepted

## Context

The project uses Domain-Driven Design. It is necessary to define how DDD concepts materialize in code and folder structure, and at which level the notion of Bounded Context is handled.

## Decision: Bounded Context is a strategic concept only

Bounded Contexts are **not materialized in the folder structure**. They are a strategic design and organizational tool, documented in ADRs and context maps, but absent from the codebase.

The reason: a Bounded Context can naturally span multiple services (e.g. a "Customer" BC could encompass the `identity` domain in `apps/user` and a `profile` domain in `apps/backend`). Forcing it into a folder would either misrepresent reality or create artificial constraints.

**What is materialized in code: domains.**

| Concept | Nature | Materialized by |
|---|---|---|
| Bounded Context | Strategic | Service boundary or documentation |
| Domain | Tactical | Folder under `src/` |
| Aggregate | Tactical | File under `domain/` |
| Entity / Value Object | Tactical | File |

## Folder organization

### Structure

Each domain is a folder under `src/`. Layers are nested inside.

```
apps/user/src/
  identity/            ← domain
    domain/
    application/
    infrastructure/
    interface/

apps/backend/src/
  ColorLab/             ← domain (PascalCase, PHP convention)
    Domain/
    Application/
    Infrastructure/
    UI/
      Http/
  Order/               ← domain
    Domain/
    Application/
    Infrastructure/
    UI/
      Http/
```

### Layer conventions

**domain/** (or **Domain/**): pure domain objects — no framework dependency
- Aggregates, Entities, Value Objects
- Repository interfaces
- Domain Events, Domain Services

**application/** (or **Application/**): use-case orchestration
- Commands + Handlers, Queries + Handlers, DTOs

**infrastructure/** (or **Infrastructure/**): technical implementations
- Repository implementations, external adapters, framework config

**interface/** (NestJS) / **UI/Http/** (Symfony): entry points
- API Controllers, Event listeners

### Files inside domain/

`domain/` stays **flat** by default. A file per aggregate, no subfolder for a single class.

```
domain/
  User.ts            ← aggregate
  Profile.ts         ← value object
  Role.ts            ← value object
  UserRepository.ts  ← interface
```

Add a subfolder per aggregate only when it generates 5+ related classes (events, snapshots, etc.).

## Domain isolation enforcement

Domains must not import from each other directly. Violations must **break the CI build**.

- **Symfony**: Deptrac configured before the first domain is written
- **NestJS**: closed modules (`@Module({ exports: [] })`)

Inter-domain communication goes through **Domain Events** (Symfony Messenger or NestJS EventEmitter).

## Signal to extract a domain into a new service

Extract when:
- The domain needs an independent deployment cycle
- A different team takes ownership
- The domain has significantly different scaling requirements

Do not extract in anticipation — only when the need is concrete.
