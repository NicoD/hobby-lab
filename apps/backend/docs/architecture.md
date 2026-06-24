# Backend — Architecture

## Folder structure

```
apps/backend/src/
  ColorLab/               ← business domain (PascalCase)
    Catalog/              ← module: reference data (paint products)
      Domain/
        Brand/            ← aggregate root + handle + repository + events
          Range/          ← value object embedded in Brand (not an aggregate — lives here, not at Domain root)
        Color/
        PaintType/
        Paint/            ← renamed from PaintReference
      Application/
        {Aggregate}/
          Command/
          Query/
          ReadModel/
      Infrastructure/
        Doctrine/
          Repository/
          Type/           ← catalog_* prefixed types
      UI/
        Http/             ← JSON controllers at /color-lab/catalog/*
    Stash/                ← module: user's physical paint collection
      Domain/
        Paint/            ← aggregate root + PaintId + repository + events
      Application/
        Paint/
          Command/
          Query/
          ReadModel/
      Infrastructure/
        Doctrine/
          Repository/
          Type/           ← stash_* prefixed types
      UI/
        Http/             ← JSON controllers at /color-lab/stash/*
  Identity/               ← integration contract with apps/user (Anti-Corruption Layer)
    UserId.php            ← UUID received from X-User-Id header; not owned by this backend
  Shared/                 ← technical cross-domain building blocks (no business logic)
    Domain/               ← organized by technical role (no aggregates here)
      Model/              ← AbstractHandle, AbstractUuid, AggregateRoot, AggregateRootId
      Event/              ← DomainEvent, DomainEventTrait, DomainEventHolder, DomainEventId
      Exception/          ← DomainException
      Service/            ← HandleGenerator, HandleGeneratorFactory, Slugifier
    Application/
      Bus/                ← CommandBus, QueryBus, Command, Query interfaces
      Query/              ← PaginatedResult, Pagination, SortOrder
      Service/            ← TransactionManager, TransactionBoundary, DomainEventDispatcher
    Infrastructure/
      Doctrine/           ← DoctrineTransactionBoundary
      Doctrine/Type/      ← AbstractHandleType, AbstractUuidType, UserIdType
      Event/              ← SymfonyDomainEventDispatcher, LoggingDomainEventDispatcher
      Messenger/          ← MessengerBus (CommandBus + QueryBus impl)
      Security/           ← GatewayAuthenticator, GatewayUser
      Testing/            ← CollectingEventDispatcher
```

### Domain folder convention — aggregate-centric

The `Domain/` folder is organized around **aggregates**, not around DDD building block types. Each aggregate root has its own subfolder containing everything that belongs to it: its handle (identity VO), its repository interface, its domain events, and any non-root entities or value objects it owns.

```
Domain/
  Brand/          ← aggregate
    Range/        ← entity owned by Brand → nested under Brand, not at root level
    Brand.php
    BrandHandle.php
    BrandRepository.php
    Event/
      BrandCreatedEvent.php
```

A value object or entity that is only reachable through a specific aggregate root **must** live inside that aggregate's folder, not at the `Domain/` root. The folder structure must reflect aggregate ownership.

**Exception — `Shared/`:** `Shared` contains no aggregates, only technical building blocks. Its `Domain/` subfolder is therefore organized by technical role (`Model/`, `Event/`, `Exception/`, `Service/`). This is the only place in the codebase where type-based organization is acceptable.

## Layers

```
Domain/         → pure PHP, zero framework dependency
Application/    → commands, queries, handlers; depends only on Domain
Infrastructure/ → Doctrine, Symfony adapters; depends on Application + Domain
UI/             → controllers, event listeners; depends on Application
```

### Layer violations — refuse without explicit approval

- Application depending on any Symfony/Doctrine concrete (`EntityManagerInterface`, `EventDispatcherInterface`, …) → define an interface in Application, implement it in Infrastructure.
- Domain depending on Doctrine annotations or Symfony types. (Known exception: `#[ORM\*]` on entities — accepted pragmatic tradeoff, do not extend.)
- Cross-domain imports — Domain Events only.

## Module isolation (ColorLab)

**Write side — strict.** `Stash` must never import a domain class from `Catalog`. The only cross-module reference allowed is the `PaintHandle` identity VO.

**Read side — free JOIN.** Query handlers in `Stash` may join `catalog_*` tables directly in their infrastructure implementation. No shared Application read layer.

Enforced by Deptrac: `ColorLab.Stash.*` layers list `ColorLab.Catalog.Domain` as an allowed dependency for the VO import only.

## Domain isolation (Deptrac)

See `deptrac.yaml`. A cross-domain import breaks the CI build.

## API routes

| Module | Prefix |
|---|---|
| Catalog | `/color-lab/catalog/{brands,colors,paint-types,paints}` |
| Stash | `/color-lab/stash/paints` |

## Doctrine types

All types are module-scoped to avoid name collisions:

| Type name | Module | PHP class |
|---|---|---|
| `catalog_brand_handle` | Catalog | `BrandHandle` |
| `catalog_color_handle` | Catalog | `ColorHandle` |
| `catalog_paint_type_handle` | Catalog | `PaintTypeHandle` |
| `catalog_range_handle` | Catalog | `RangeHandle` |
| `catalog_range_collection` | Catalog | `Range[]` JSON |
| `catalog_paint_handle` | Catalog | `PaintHandle` |
| `stash_paint_id` | Stash | `PaintId` (UUID) |
| `user_id` | Identity | `UserId` (UUID) |

## User identity

`apps/backend` never validates a JWT. It only reads two headers injected by the API gateway:

- `X-User-Id` — UUID of the authenticated user
- `X-User-Roles` — comma-separated roles

`GatewayAuthenticator` reads these headers and populates the Symfony Security token.

## Authorization

**Coarse-grained** (access to a route) → Symfony Firewall, based on `X-User-Roles`.

**Fine-grained** (action on a specific aggregate) → Symfony Voters, placed in `Application/Security/` of the relevant domain.

## Aggregate root pattern

- Private constructor — pure property assignment, no IO, no generation.
- Static `create()` factory — the only public entry point for construction. Raises domain events via `raiseDomainEvent()`.
- Implements `AggregateRoot` (marker interface extending `DomainEventHolder`).
- `DomainEventTrait` provides event collection; use it in every aggregate root.

## Domain events

- `DomainEvent` base class auto-generates `$eventId` and `$occurredAt`. Subclasses declare typed VO properties — no generic payload arrays.
- Events are raised inside `create()` / mutation methods, collected by `DomainEventTrait`, and dispatched **after** DB commit via `TransactionManager`. Never dispatch inside the transaction.

## Transaction boundary

`TransactionManager` (interface in Application) wraps a closure returning the aggregate(s). `DoctrineTransactionManager` (Infrastructure) collects events, flushes, commits, then dispatches. Command handlers never touch `EntityManagerInterface` directly.

## Value objects

- `new MyVo($value)` by default. Static factory only when semantically meaningful (`create()`, `inCents()`).
- Each VO persisted by Doctrine gets a custom type in `Infrastructure/Doctrine/Type/`, extending `AbstractHandleType` or `AbstractUuidType`.

## Tests

See `docs/testing.md`.
