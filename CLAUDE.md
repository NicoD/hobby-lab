# hobby-lab — Agent instructions

## Language

All documentation, ADRs, comments, and commit messages must be written in **English**.

## Architecture reference

Before any structural work: read `docs/architecture.md` for the overview, then check `docs/adr/` for relevant decisions. For domain concepts and ubiquitous language, read `docs/domain.md`.

## Task completion

After implementing a **complete task** (full feature, bug fix, refactor — not a snippet or a partial answer), always run the relevant validate skill:

- `apps/backend` → `/backend-validate`
- `apps/user` → `/user-validate`
- `apps/frontend` → `/frontend-validate`

## Instruction files — read-only

Never modify CLAUDE.md files, `docs/*.md`, or `.claude/memory/` files unless the user explicitly requests it via `/update-instructions`.

## Key rules (do not deviate without explicit user approval)

- Folder names reflect **responsibility**, not technology (`frontend`, `backend`, `user`)
- The domain inside `apps/user` is named `identity`, not `user`
- `apps/backend` never processes a JWT — it reads `X-User-Id` and `X-User-Roles` headers only
- No direct cross-domain imports — Domain Events only
- PII stays exclusively in `apps/user`
- Bounded Contexts are a **strategic concept only** — they are documented, never materialized as folders
