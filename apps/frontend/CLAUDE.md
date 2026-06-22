# Frontend — Agent instructions (React / Vite)

## Architecture reference

Read `docs/architecture.md` (relative to this file) before any structural work on the frontend.

## Code rules

- No comments unless the **why** is non-obvious — never describe what the code does.
- No `useEffect` for derived state — compute inline or use `useMemo`.
- Hooks are named `use<Entity>` or `use<Action>` — never abbreviate.
- No barrel files (`index.js` re-exporting siblings) — import the file directly.

## Component and hook placement

- **Co-locate by default**: a hook used only by one component lives in that component's folder.
- **Promote when shared**: move to `shared/` only when a second consumer appears — not speculatively.
- See `docs/architecture.md` for the full folder convention.

## Styling

Tailwind utility classes only — no custom CSS files beyond `index.css`.
