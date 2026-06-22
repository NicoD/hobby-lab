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
    paint/          ← feature: manage paints
    brand/          ← future feature: manage brands
  User/             ← feature group
    login/          ← feature: authentication
    profile/        ← feature: user profile
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
