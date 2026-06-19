# Domain — ColorLab

## Objective

Model a miniature paint catalogue that allows:

- reusing existing references;
- creating custom references;
- searching and aggregating data easily;
- staying simple for a first version.

---

## Brand

The paint manufacturer.

Examples: Vallejo, Citadel, Army Painter, AK Interactive

---

## Range

A product line belonging to a Brand.

Examples: Game Color, Model Color, Base, Contrast, Speedpaint

---

## PaintType

The technical behavior or usage of the paint. Distinct from color.

Examples: Standard, Metallic, Wash, Glaze, Ink, Contrast, Technical

---

## Color

The color family. The commercial name of a paint is not necessarily its color.

Examples:

| Commercial name  | Color  |
|------------------|--------|
| Crimson          | Red    |
| Goblin Green     | Green  |
| Nuln Oil         | Black  |
| Agrax Earthshade | Brown  |

---

## PaintReference

A catalogue entry for a paint. The core of the catalogue.

Defined by: Brand, Range, PaintType, Name, and optionally Color.

```
Brand     = Vallejo
Range     = Game Color
PaintType = Standard
Name      = Crimson
Color     = Red
```

```
Brand     = Citadel
Range     = Shade
PaintType = Wash
Name      = Nuln Oil
Color     = Black
```

---

## Paint

A paint owned by a user. References a PaintReference and adds ownership data.

```
PaintReference = Vallejo Game Color Crimson
OwnedBy        = User42
PurchasedAt    = 2026-02-15
```

---

## Model summary

```
Brand
Range          → Brand
PaintType
Color

PaintReference → Brand, Range, PaintType, Color
Paint          → PaintReference, OwnedBy, PurchasedAt
```

