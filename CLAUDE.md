# hobby-lab — Agent instructions

## Project purpose

This project is a sandbox whose goal is to train. All choices must meet industry standards in terms of architecture, security, and good practices.

It may include over-engineered or overly complex choices if this aligns with the developer's decision to train on a specific topic.

**Why:** Learning-driven project — correctness and best practices always apply, but complexity is not a reason to reject a design if it serves a training purpose.

**How to apply:** Never suggest simplifying or removing complexity solely on grounds of "this is overkill for the project size." If a choice meets industry standards and the developer wants it for training purposes, support it. Do not propose alternatives to validated architectural choices without strong justification.

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

Never modify CLAUDE.md files or `docs/*.md` unless the user explicitly requests it via `/update-instructions`.

## Key rules (do not deviate without explicit user approval)

- Folder names reflect **responsibility**, not technology (`frontend`, `backend`, `user`)
- The domain inside `apps/user` is named `identity`, not `user`
- `apps/backend` never processes a JWT — it reads `X-User-Id` and `X-User-Roles` headers only
- No direct cross-domain imports — Domain Events only
- PII stays exclusively in `apps/user`
- Bounded Contexts are a **strategic concept only** — they are documented, never materialized as folders
- `docker-compose.yml` (base file) publishes no ports except the `gateway` service — any other port needed for local dev goes in `docker-compose.override.yml`, never the base file
