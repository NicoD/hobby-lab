# Domain — ColorLab

## Objective

Model a miniature paint catalogue that allows:

- reusing existing references;
- creating custom references;
- searching and aggregating data easily;
- staying simple for a first version.

---

## Catalog — subdomain

Reference data describing paint products. Shared across users.

### Brand

The paint manufacturer.

Examples: Vallejo, Citadel, Army Painter, AK Interactive

**Identity:** handle (slug derived from name, unique per system).

### Range

A product line belonging to a Brand. **Range is not a standalone aggregate — it is a value object embedded in the Brand aggregate.** Ranges are managed through the Brand they belong to.

Examples: Game Color, Model Color, Base, Contrast, Speedpaint

**Identity:** handle (slug derived from name, unique within a Brand).

### PaintType

The technical behavior or usage of the paint. Distinct from color.

Examples: Standard, Metallic, Wash, Glaze, Ink, Contrast, Technical

**Identity:** handle (slug derived from name, unique per system).

### Color

The color family. The commercial name of a paint is not necessarily its color.

Examples:

| Commercial name  | Color  |
|------------------|--------|
| Crimson          | Red    |
| Goblin Green     | Green  |
| Nuln Oil         | Black  |
| Agrax Earthshade | Brown  |

**Identity:** handle (slug derived from name, unique per system).

### Paint

A catalogue entry describing a paint product. Defined by Brand, Range, PaintType, Name, and optionally Color.

```
Brand     = Vallejo
Range     = Game Color
PaintType = Standard
Name      = Crimson
Color     = Red (optional)
```

**Identity:** handle (slug derived from `{brandHandle} {name}`, unique per system, collisions incremented automatically).

---

## Stash — subdomain

A user's personal reserve of physical paints. References Catalog entries.

### Paint

A physical paint owned by a user. References a Catalog Paint and adds personal ownership data.

```
Catalog Paint = Vallejo Game Color Crimson
OwnedBy       = User42
PurchasedAt   = 2026-02-15 (optional)
```

**Identity:** UUID. No handle.

---

## Model summary

```
Catalog:
  Brand          ← owns Range (embedded, not standalone)
  PaintType
  Color
  Paint          → Brand, Range, PaintType, Color (optional)

Stash:
  Paint          → Catalog/Paint (by handle)
                 → OwnedBy (User), PurchasedAt (optional)
```
