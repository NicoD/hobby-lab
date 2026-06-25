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
        Paint/
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

`Domain/` is organized around aggregates, not DDD building block types.

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

- VOs and entities reachable only through an aggregate **must** live in that aggregate's folder.
- Exception — `Shared/`: no aggregates, so organized by technical role (`Model/`, `Event/`, …). The only place type-based organization is acceptable.

## Layers

```
Domain/         → pure PHP, zero framework dependency
Application/    → commands, queries, handlers; depends only on Domain
Infrastructure/ → Doctrine, Symfony adapters; depends on Application + Domain
UI/             → controllers, event listeners; depends on Application
```

## Module isolation (ColorLab)

**Write side — strict.** `Stash` must never import a domain class from `Catalog`. Only `PaintHandle` identity VO is allowed.

**Read side — free JOIN.** Query handlers in `Stash` may join `catalog_*` tables directly in their infrastructure implementation.

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

## Tests

See `docs/testing.md`.
