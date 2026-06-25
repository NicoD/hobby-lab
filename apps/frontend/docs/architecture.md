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

## ColorLab — Library vs Catalog

**Library** — user's personal collection (`paint/`, `brush/`). Features live directly under `ColorLab/`.

**Catalog** — reference data (brands, colors, paint references). Features live under `ColorLab/catalog/`.
