# Frontend — Architecture

## Folder structure

```
apps/frontend/src/
  features/               ← one folder per feature group (PascalCase)
    ColorLab/
      layout/
        ColorLabLayout.jsx  ← layout scoped to this feature group
      paint/                ← one folder per feature
        components/
          FormAddPaint/   ← component folder (index.jsx + co-located hooks)
            index.jsx
            useFormAddPaint.js
            usePaintReferences.js
        pages/
          Paint.jsx
    User/                 ← feature group: user-facing auth & profile
      login/
        pages/
          Login.jsx
      profile/
        pages/
          Profile.jsx
    MiniLab/              ← feature group with a single page
      pages/
        MiniLab.jsx
  shared/                 ← cross-feature building blocks
    components/           ← generic UI components (Combobox, Modal, ListWrapper, …)
    context/              ← React contexts (AuthContext)
    hooks/                ← reusable hooks (useApiFetch, useModal, …)
    lib/                  ← plain utilities (apiFetch)
  layout/                 ← app shell layout (Layout.jsx)
  main.jsx
  index.css
```

## Two-level feature hierarchy

`features/<Group>/<feature>/` — feature groups are product namespaces (PascalCase) that
gather related features; features are individual product capabilities (lowercase). Names reflect
the UI — they do not need to match backend naming conventions.

```
features/
  ColorLab/         ← feature group
    paint/          ← library feature: manage paints
    catalog/        ← catalog sub-group (see below)
      brands/
      colors/
  User/             ← feature group
    login/          ← feature: authentication
    profile/        ← feature: user profile
```

## ColorLab — Library vs Catalog

ColorLab features split into two concepts:

**Library** — the user's personal collection. Things they *own*: their paints, their brushes. Data is user-scoped and relational. Library features live directly under `ColorLab/` (e.g. `paint/`, `brush/`). They are the primary purpose of the app and sit at the top of the sidebar.

**Catalog** — the reference data that describes library items: brands, colors, paint references. The user administers catalog entries (add, edit, delete) but does not "own" them the way they own library items. Catalog features live under `ColorLab/catalog/` and share a generic table+filter+pagination layout (`catalog/shared/`). They sit at the bottom of the sidebar with a secondary visual treatment.

A paint in the Library references a brand from the Catalog. The Catalog has no meaning without the Library — it exists to enrich it.

### Routing and import naming

Library routes sit directly under the feature group — no prefix:

```
/color-lab/paint
/color-lab/brush
```

Catalog routes are always prefixed with `catalog/`:

```
/color-lab/catalog/brands
/color-lab/catalog/colors
/color-lab/catalog/references
```

In `main.jsx`, catalog page imports are named `ColorLab**Catalog**<Entity>` to make the distinction visible at a glance:

```js
import ColorLabPaint          from '…/paint/pages/Paint'           // library
import ColorLabCatalogBrands  from '…/catalog/brands/pages/Brands' // catalog
import ColorLabCatalogColors  from '…/catalog/colors/pages/Colors' // catalog
```

There is no top-level `pages/` folder. Every page belongs to a feature. When a feature group
has a single page with no sub-features (e.g. `MiniLab`), that page lives directly at
`features/<Group>/pages/` without an intermediate feature folder.

A feature-group layout (e.g. sidebar nav) lives in `features/<Group>/layout/`,
mirroring the root `layout/` convention, since it is shared by all features of that group.

## shared/ vs features/

| Criterion | `shared/` | `features/<Group>/<feature>/` |
|---|---|---|
| Used by more than one feature | yes | no |
| Contains business logic | no | yes |
| Can depend on other features | no | no |

`shared/` contains **no business logic** — only generic UI and technical utilities.
Features never import from each other — cross-feature communication goes through shared state or events.

## Co-location principle

Code lives as close as possible to where it is used.

- A hook used only by one component → same folder as the component.
- A hook used by multiple components in one feature → `features/<Group>/<feature>/hooks/`.
- A hook used across features → `shared/hooks/`.

Do not pre-emptively move code to `shared/`. Move it up when the second consumer appears.

## Component folder convention

A component with co-located logic uses a folder instead of a single file:

```
FormAddPaint/
  index.jsx           ← the component (default export)
  useFormAddPaint.js  ← local data/mutation logic
  usePaintReferences.js
```

A component with no co-located hooks stays as a single file (e.g. `Combobox.jsx`).

## Hook conventions

- One hook per entity or concern — do not aggregate unrelated queries into one hook.
- Hooks that mix a query and its related mutation for the same entity stay together
  (e.g. `usePaintReferences` exposes both `paintReferences` and `createPaintReference`).
- Pass external state as an argument rather than reading it from context inside the hook
  when the caller already owns that state (keeps the hook's contract explicit).
