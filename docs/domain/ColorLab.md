# Domain — ColorLab

## Objective

Model a miniature paint catalogue that allows:

- reusing existing references;
- creating custom references;
- searching and aggregating data easily;
- staying simple for a first version.

---

## Module structure (ADR-008)

`ColorLab` is divided into two modules:

| Module | Responsibility | Tables |
|---|---|---|
| **Catalog** | Reference data describing paint products | `catalog_brands`, `catalog_colors`, `catalog_paint_types`, `catalog_paints` |
| **Stash** | User's physical paint reserve | `stash_paints` |

`Stash` references `Catalog` only through the `PaintHandle` identity VO (write-side isolation). The read side may JOIN catalog tables directly.

---

## Catalog module

### Brand

The paint manufacturer. Owned by a user.

Examples: Vallejo, Citadel, Army Painter, AK Interactive

**Identity:** handle (slug derived from name, unique per system).

---

### Range

A product line belonging to a Brand. **Range is not a standalone aggregate — it is a value object embedded in the Brand aggregate.** Ranges are managed through the Brand they belong to.

Examples: Game Color, Model Color, Base, Contrast, Speedpaint

**Identity:** handle (slug derived from name, unique within a Brand).

---

### PaintType

The technical behavior or usage of the paint. Distinct from color. Owned by a user.

Examples: Standard, Metallic, Wash, Glaze, Ink, Contrast, Technical

**Identity:** handle (slug derived from name, unique per system).

---

### Color

The color family. The commercial name of a paint is not necessarily its color. Owned by a user.

Examples:

| Commercial name  | Color  |
|------------------|--------|
| Crimson          | Red    |
| Goblin Green     | Green  |
| Nuln Oil         | Black  |
| Agrax Earthshade | Brown  |

**Identity:** handle (slug derived from name, unique per system).

---

### Paint (formerly PaintReference)

A catalogue entry for a paint. The core of the catalogue. Owned by a user (the one who created the entry).

Defined by: Brand, Range, PaintType, Name, and optionally Color.

```
Brand     = Vallejo
Range     = Game Color
PaintType = Standard
Name      = Crimson
Color     = Red (optional)
```

**Identity:** handle (slug derived from `{brandHandle} {name}`, unique per system, collisions incremented automatically).

---

## Stash module

### Paint

A physical paint owned by a user. References a Catalog `PaintHandle` and adds personal ownership data.

```
PaintHandle  = vallejo-game-color-crimson   ← references Catalog/Paint
OwnedBy      = User42
PurchasedAt  = 2026-02-15 (optional)
```

**Identity:** UUID. No handle.

---

## Model summary

```
Catalog:
  Brand                           → userId (creator)
  Range          → Brand          (value object embedded in Brand, not standalone)
  PaintType                       → userId (creator)
  Color                           → userId (creator)
  Paint          → Brand, Range, PaintType, Color (optional)
                 → userId (creator)

Stash:
  Paint          → Catalog/PaintHandle (identity VO only)
                 → ownedBy (User), purchasedAt (optional date)
```
