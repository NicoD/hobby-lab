# Frontend — Agent instructions (React / Vite / TypeScript)

## Architecture reference

Read `docs/architecture.md` (relative to this file) before any structural work on the frontend.

## Language

All source files must be TypeScript: `.ts` for logic, `.tsx` for components. Tooling config files (`eslint.config.js`, `vite.config.ts`, `jest.config.cjs`) are excluded from this rule.

## Routing and import naming (ColorLab)

Library routes: `/color-lab/<feature>` — Catalog routes: `/color-lab/catalog/<feature>`

In `main.tsx`, catalog imports are prefixed `ColorLabCatalog<Entity>`:

```ts
import ColorLabPaint from '…/paint/pages/Paint'; // library
import ColorLabCatalogBrands from '…/catalog/brands/pages/Brands'; // catalog
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
  index.tsx
  useFormAddPaint.ts
  usePaintReferences.ts
```

## Hook conventions

- One hook per entity or concern — do not aggregate unrelated queries into one hook.
- Hooks that mix a query and its related mutation for the same entity stay together.
- Pass external state as an argument rather than reading it from context inside the hook.

## TypeScript patterns

- `import { type X }` for type-only imports.
- Use `ListResponse<T>` (exported from `useApiFetch`) for paginated endpoints: `apiFetch<ListResponse<Brand>>(url)`.
- Feature types: declare only the fields consumed. Business types go in `feature/types.ts`. Use `ListResponse<T>` for paginated responses — no need for a dedicated response type alias.
- `void` operator for fire-and-forget promises in JSX handlers: `onClick={() => { void handleClick() }}`.
- React props use `undefined` for absent values, not `null`. Use `null` only at API or state boundaries.

## Code rules

- No comments unless the **why** is non-obvious — never describe what the code does.
- No `useEffect` for derived state — compute inline or use `useMemo`.
- Hooks are named `use<Entity>` or `use<Action>` — never abbreviate.
- No barrel files (`index.ts` re-exporting siblings) — import the file directly.

## Styling

Tailwind utility classes only — no custom CSS files beyond `index.css`.
