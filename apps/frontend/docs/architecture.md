# Frontend — Architecture

## Folder structure

```
apps/frontend/src/
  features/               ← one folder per feature group (PascalCase)
    ColorLab/
      layout/
        ColorLabLayout.tsx  ← layout scoped to this feature group
      paint/                ← one folder per feature
        components/
          FormAddPaint/   ← component folder (index.tsx + co-located hooks)
            index.tsx
            useFormAddPaint.ts
            usePaintReferences.ts
        pages/
          Paint.tsx
    User/                 ← feature group: user-facing auth & profile
      login/
        pages/
          Login.tsx
      profile/
        pages/
          Profile.tsx
    MiniLab/              ← feature group with a single page
      pages/
        MiniLab.tsx
  shared/                 ← cross-feature building blocks
    components/           ← generic UI components (Combobox, Modal, ListWrapper, …)
    context/              ← React contexts (AuthContext)
    hooks/                ← reusable hooks (useApiFetch, useModal, …)
    lib/                  ← plain utilities (apiFetch)
  layout/                 ← app shell layout (Layout.tsx)
  main.tsx
  index.css
```

## ColorLab — Library vs Catalog

**Library** — user's personal collection (`paint/`, `brush/`). Features live directly under `ColorLab/`.

**Catalog** — reference data (brands, colors, paint references). Features live under `ColorLab/catalog/`.
