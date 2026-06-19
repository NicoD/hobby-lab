# Domain — ColorLab

## Objective

Model a miniature paint catalogue that allows:

- reusing existing references;
- creating custom references;
- searching and aggregating data easily;
- staying simple for a first version.

---

## Brand

The paint manufacturer. Owned by a user.

Examples: Vallejo, Citadel, Army Painter, AK Interactive

**Identity:** handle (slug derived from name, unique per system).

---

## Range

A product line belonging to a Brand. **Range is not a standalone aggregate — it is an entity within the Brand aggregate.** Ranges are managed through the Brand they belong to.

Examples: Game Color, Model Color, Base, Contrast, Speedpaint

**Identity:** handle (slug derived from name, unique within a Brand).

---

## PaintType

The technical behavior or usage of the paint. Distinct from color. Owned by a user.

Examples: Standard, Metallic, Wash, Glaze, Ink, Contrast, Technical

**Identity:** handle (slug derived from name, unique per system).

---

## Color

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

## PaintReference

A catalogue entry for a paint. The core of the catalogue. Owned by a user.

Defined by: Brand, Range, PaintType, Name, and optionally Color.

```
Brand     = Vallejo
Range     = Game Color
PaintType = Standard
Name      = Crimson
Color     = Red (optional)
```

```
Brand     = Citadel
Range     = Shade
PaintType = Wash
Name      = Nuln Oil
Color     = Black (optional)
```

**Identity:** UUID (primary key). The handle is derived from `{brandHandle} {name}` and is unique per system (collisions incremented automatically).

---

## Paint

A paint owned by a user. References a PaintReference and adds ownership data.

```
PaintReference = Vallejo Game Color Crimson
OwnedBy        = User42
PurchasedAt    = 2026-02-15 (optional)
```

**Identity:** UUID. No handle.

---

## Model summary

```
Brand                          → ownedBy (User)
Range          → Brand         (entity within Brand aggregate, not standalone)
PaintType                      → ownedBy (User)
Color                          → ownedBy (User)

PaintReference → Brand, Range, PaintType, Color (optional)
               → ownedBy (User)
Paint          → PaintReference
               → ownedBy (User), purchasedAt (optional date)
```
