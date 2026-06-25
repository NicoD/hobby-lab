# Frontend — Agent instructions (React / Vite)

## Architecture reference

Read `docs/architecture.md` (relative to this file) before any structural work on the frontend.

## Routing and import naming (ColorLab)

Library routes: `/color-lab/<feature>` — Catalog routes: `/color-lab/catalog/<feature>`

In `main.jsx`, catalog imports are prefixed `ColorLabCatalog<Entity>`:

```js
import ColorLabPaint          from '…/paint/pages/Paint'           // library
import ColorLabCatalogBrands  from '…/catalog/brands/pages/Brands' // catalog
```

## Component and hook placement

- `shared/` — used by more than one feature, no business logic, never imports from features.
- `features/` — business logic, never imports from other features.
- Co-locate by default: a hook used only by one component lives in that component's folder.
- Promote to `shared/` only when a second consumer appears — not speculatively.

## Component folder convention

A component with co-located logic uses a folder:

```
FormAddPaint/
  index.jsx
  useFormAddPaint.js
  usePaintReferences.js
```

## Hook conventions

- One hook per entity or concern — do not aggregate unrelated queries into one hook.
- Hooks that mix a query and its related mutation for the same entity stay together.
- Pass external state as an argument rather than reading it from context inside the hook.

## Code rules

- No comments unless the **why** is non-obvious — never describe what the code does.
- No `useEffect` for derived state — compute inline or use `useMemo`.
- Hooks are named `use<Entity>` or `use<Action>` — never abbreviate.
- No barrel files (`index.js` re-exporting siblings) — import the file directly.

## Styling

Tailwind utility classes only — no custom CSS files beyond `index.css`.
