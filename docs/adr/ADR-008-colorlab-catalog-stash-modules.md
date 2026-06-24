# ADR-008 — ColorLab module separation: Catalog, Stash, and Recipe

**Status**: Accepted

## Context

`ColorLab` is the bounded context responsible for paint management. Its initial implementation treated all entities (`Brand`, `Color`, `PaintType`, `Range`, `PaintReference`, `Paint`) as a single flat domain under `src/ColorLab/`.

As the project scope was refined, two structurally distinct concerns emerged:

- **Catalog** — reference data describing paint products. Entities exist independently of any user. A user may contribute private entries, but the concept is a product catalogue, not a personal collection.
- **Stash** — a user's personal reserve of physical items (paints, accessories, tools). Each entry records the physical possession of a product and points to a Catalog entry by identity. The term "stash" is the established word in the miniature painting community for one's personal collection of paints and materials.

This ADR covers the separation of `Catalog` and `Stash`. A third concern — user-created color recipes — is not implemented yet and out of scope here. It is used as an illustrative example where relevant.

The distinction between Catalog and Stash is not merely a UI concern. The two modules have different invariants, different lifecycles, and different ownership semantics:

| | Catalog | Stash |
|---|---|---|
| Ownership field | `userId` — creator of the entry | `ownedBy` — physical possessor of the item |
| Visibility | public entries + user-private entries | always private to one user |
| Lifecycle | stable reference data | changes with user behaviour |
| Write invariants | product integrity (name, brand, etc.) | possession integrity (reference must exist) |

The same term "Paint" is valid in both modules. The module namespace disambiguates: `Catalog\Paint` is a product reference; `Stash\Paint` is a possessed paint. No prefix is needed in either module — the context carries the meaning.

This ADR supersedes the implicit flat organisation of `ColorLab` and defines the two-module structure, naming conventions, isolation rules, API contract, and migration scope.

## Decision

### Module structure

`ColorLab` is reorganised into two active modules (`Catalog` and `Stash`) with a placeholder for the future `Recipe` module.

```
apps/backend/src/ColorLab/
├── Catalog/
│   ├── Domain/
│   │   ├── Brand/
│   │   │   ├── Brand.php
│   │   │   ├── BrandHandle.php
│   │   │   ├── BrandRepository.php
│   │   │   └── Event/
│   │   │       └── BrandCreatedEvent.php
│   │   ├── Color/
│   │   │   ├── Color.php
│   │   │   ├── ColorHandle.php
│   │   │   ├── ColorRepository.php
│   │   │   └── Event/
│   │   │       └── ColorCreatedEvent.php
│   │   ├── PaintType/
│   │   │   ├── PaintType.php
│   │   │   ├── PaintTypeHandle.php
│   │   │   ├── PaintTypeRepository.php
│   │   │   └── Event/
│   │   │       └── PaintTypeCreatedEvent.php
│   │   ├── Range/
│   │   │   ├── Range.php
│   │   │   ├── RangeHandle.php
│   │   │   ├── RangeRepository.php
│   │   │   └── Event/
│   │   │       └── RangeCreatedEvent.php
│   │   └── Paint/
│   │       ├── Paint.php               ← was PaintReference
│   │       ├── PaintHandle.php         ← was PaintReferenceHandle
│   │       ├── PaintRepository.php     ← was PaintReferenceRepository
│   │       └── Event/
│   │           └── PaintCreatedEvent.php ← was PaintReferenceCreatedEvent
│   ├── Application/
│   │   ├── Brand/
│   │   ├── Color/
│   │   ├── PaintType/
│   │   ├── Range/
│   │   └── Paint/
│   ├── Infrastructure/
│   │   └── Doctrine/
│   │       ├── Repository/
│   │       └── Type/
│   └── UI/
│       └── Http/
│           ├── BrandController.php
│           ├── ColorController.php
│           ├── PaintTypeController.php
│           └── PaintController.php     ← was PaintReferenceController
│
└── Stash/
    ├── Domain/
    │   └── Paint/
    │       ├── Paint.php               ← was Paint (root level)
    │       ├── PaintId.php
    │       ├── PaintRepository.php
    │       └── Event/
    │           └── PaintCreatedEvent.php
    ├── Application/
    │   └── Paint/
    ├── Infrastructure/
    │   └── Doctrine/
    │       ├── Repository/
    │       └── Type/
    └── UI/
        └── Http/
            └── PaintController.php
```

### Entity and field renaming

| Before | After | Notes |
|---|---|---|
| `ColorLab\Domain\Model\PaintReference` | `ColorLab\Catalog\Domain\Paint\Paint` | Renamed |
| `ColorLab\Domain\Model\PaintReferenceHandle` | `ColorLab\Catalog\Domain\Paint\PaintHandle` | Renamed |
| `ColorLab\Domain\Repository\PaintReferenceRepository` | `ColorLab\Catalog\Domain\Paint\PaintRepository` | Renamed |
| `ColorLab\Domain\Event\PaintReferenceCreatedEvent` | `ColorLab\Catalog\Domain\Paint\Event\PaintCreatedEvent` | Renamed |
| `ColorLab\Domain\Model\Paint` | `ColorLab\Stash\Domain\Paint\Paint` | Moved |
| `ColorLab\Domain\Model\PaintId` | `ColorLab\Stash\Domain\Paint\PaintId` | Moved |
| `PaintReference::$ownedBy` (UserId) | `Catalog\Paint::$userId` (UserId) | Creator of the entry |
| `Paint::$ownedBy` (UserId) | `Stash\Paint::$ownedBy` (UserId) | Physical possessor — unchanged |
| `Paint::$paintReference` (PaintReferenceHandle) | `Stash\Paint::$paintHandle` (PaintHandle) | Renamed to match new VO |

All other entities (`Brand`, `Color`, `PaintType`, `Range`) move into `Catalog/Domain/` unchanged except for namespace.

### Database tables

| Table (before) | Table (after) |
|---|---|
| `paint_references` | `catalog_paints` |
| `paints` | `stash_paints` |
| `brands` | `catalog_brands` |
| `colors` | `catalog_colors` |
| `paint_types` | `catalog_paint_types` |

A Doctrine migration renames all five tables. No data migration — rename only.

### Doctrine types

All custom Doctrine column types (`BrandHandleType`, `ColorHandleType`, `PaintReferenceHandleType`, etc.) move into `Catalog\Infrastructure\Doctrine\Type\`. The type named `paint_reference_handle` is renamed to `paint_handle` in the Doctrine type registry and in all column annotations.

`PaintIdType` moves into `Stash\Infrastructure\Doctrine\Type\`.

### API routes

The route prefix changes from `/color-lab/` to `/color-lab/catalog/` or `/color-lab/stash/` depending on the module.

| Before | After |
|---|---|
| `GET /color-lab/brands` | `GET /color-lab/catalog/brands` |
| `POST /color-lab/brands` | `POST /color-lab/catalog/brands` |
| `GET /color-lab/brands/{handle}` | `GET /color-lab/catalog/brands/{handle}` |
| `GET /color-lab/colors` | `GET /color-lab/catalog/colors` |
| `POST /color-lab/colors` | `POST /color-lab/catalog/colors` |
| `GET /color-lab/colors/{handle}` | `GET /color-lab/catalog/colors/{handle}` |
| `GET /color-lab/paint-types` | `GET /color-lab/catalog/paint-types` |
| `POST /color-lab/paint-types` | `POST /color-lab/catalog/paint-types` |
| `GET /color-lab/paint-types/{handle}` | `GET /color-lab/catalog/paint-types/{handle}` |
| `GET /color-lab/paint-references` | `GET /color-lab/catalog/paints` |
| `POST /color-lab/paint-references` | `POST /color-lab/catalog/paints` |
| `GET /color-lab/paint-references/{handle}` | `GET /color-lab/catalog/paints/{handle}` |
| `GET /color-lab/paints` | `GET /color-lab/stash/paints` |
| `POST /color-lab/paints` | `POST /color-lab/stash/paints` |
| `GET /color-lab/paints/{id}` | `GET /color-lab/stash/paints/{id}` |

### Module isolation rules

**Write side — strict.**
The `Stash` module must never import a domain class from `Catalog`. Cross-module references use identity value objects only.

```php
// Allowed — Stash references Catalog by identity VO
use App\ColorLab\Catalog\Domain\Paint\PaintHandle;

class Paint {
    private PaintHandle $paintHandle; // ✅
}

// Forbidden — Stash imports a Catalog aggregate
use App\ColorLab\Catalog\Domain\Paint\Paint as CatalogPaint;

class Paint {
    private CatalogPaint $catalogPaint; // ❌
}
```

**Read side — free JOIN.**
Query handlers in `Stash` may join Catalog tables directly in their infrastructure implementation. This is the standard CQS approach: the read side has no domain invariants to protect.

A `Stash` query handler for `GET /color-lab/stash/paints` may JOIN `catalog_paints`, `catalog_brands`, etc. to build an enriched read model. The join lives in the Doctrine read repository, not in the domain.

This approach preserves CQS symmetry: Commands and Queries are both organised at module level. There is no shared Application query layer at BC level.

### Deptrac configuration

The Deptrac ruleset is extended to enforce module isolation within `ColorLab`.

```yaml
layers:
  - name: ColorLab.Catalog
    collectors:
      - { type: directory, value: src/ColorLab/Catalog }
  - name: ColorLab.Stash
    collectors:
      - { type: directory, value: src/ColorLab/Stash }
  - name: Shared
    collectors:
      - { type: directory, value: src/Shared }

ruleset:
  ColorLab.Catalog:
    - Shared
  ColorLab.Stash:
    - Shared
    # Stash may import Catalog identity VOs (PaintHandle etc.)
    # This is handled via an allow-list on specific namespaces if Deptrac supports it,
    # or by placing shared identity VOs in Shared if cross-import becomes noisy.
  Shared: []
```

Note: if Deptrac's allow-list granularity is insufficient to permit only VO imports from Catalog into Stash, identity VOs that are referenced across modules (`PaintHandle`) are promoted to `Shared\Domain\ColorLab\` to satisfy the ruleset without relaxing the entire layer boundary.

### Frontend

Frontend file structure is updated to reflect the new module organisation.

| Before | After |
|---|---|
| `features/ColorLab/catalog/brands/` | unchanged |
| `features/ColorLab/catalog/colors/` | unchanged |
| `features/ColorLab/catalog/references/` | `features/ColorLab/catalog/paints/` |
| `features/ColorLab/paint/` | `features/ColorLab/stash/paints/` |

All API calls are updated to the new routes. Route names in the frontend router are updated accordingly.

## Migration scope

The following areas require changes. Each item is independently executable.

### Backend

1. Create `Catalog/` and `Stash/` folder trees under `src/ColorLab/`
2. Move and rename domain classes per the entity table above
3. Update all namespaces (`App\ColorLab\...` → `App\ColorLab\Catalog\...` / `App\ColorLab\Stash\...`)
4. Rename `PaintReference` → `Paint` in Catalog (class, handle, repository, event)
5. Rename field `PaintReference::$ownedBy` → `$userId`
6. Rename field `Paint::$paintReference` → `$paintHandle` (type `PaintHandle`)
7. Update Doctrine type registry: `paint_reference_handle` → `paint_handle`
8. Update all Application layer classes (commands, queries, handlers, read models, DTOs)
9. Update all Infrastructure layer classes (repositories, Doctrine types)
10. Update all UI/Http controllers and their route annotations
11. **Database reset strategy** — the project is pre-production; existing migrations are deleted and a single fresh migration is generated from the final schema. No table-rename migration is needed.
12. Update Deptrac configuration and verify no violations
13. Update `apps/backend/docs/architecture.md`

### API

14. Update route prefixes in all controllers (see route table above)

### Frontend

15. Rename `features/ColorLab/catalog/references/` → `features/ColorLab/catalog/paints/`
16. Rename `features/ColorLab/paint/` → `features/ColorLab/stash/paints/`
17. Update all API calls to new routes
18. Update React Router route definitions

### Documentation

19. Update `docs/ARCHITECTURE.md` to mention the two-module structure (Catalog, Stash)
20. Update `apps/backend/docs/architecture.md` with new namespace map
21. Update `.claude/memory/project_architecture.md`

## Consequences

- `Catalog` and `Stash` are independently testable modules with explicit domain boundaries.
- The API route structure matches the domain model — routes are self-documenting.
- The `ownedBy` / `userId` naming distinction eliminates ambiguity when discussing ownership across modules.
- `Stash` is designed to grow beyond paints: accessories and tools follow the same pattern (Stash aggregate referencing a Catalog entry by identity VO).
- The future `Recipe` module has a clear home and a clear relationship to `Catalog` — recipes reference Catalog entries by identity, never importing Catalog aggregates.
- A future extraction of either module into a separate service is a mechanical operation: extract the folder, wrap it in a new Symfony app, replace internal VO references with API calls.
- The Deptrac ruleset must be re-verified after migration. The Stash → Catalog VO import rule requires attention (see note above).
