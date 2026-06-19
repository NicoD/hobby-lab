# recipe-lab — Agent instructions

## Language

All documentation, ADRs, comments, and commit messages must be written in **English**.

## Memory

Project memory is versioned in `.claude/memory/`. Read it at the start of any conversation to recover architectural context.

- `.claude/memory/MEMORY.md` — index
- `.claude/memory/project_architecture.md` — overall architecture, stack, key decisions

When learning something new about the project (decisions, conventions, context), update the relevant memory file and keep the index in sync.

## Architecture reference

All architectural decisions are documented in `docs/adr/`. Before implementing anything, check whether an ADR covers the topic.

- `docs/ARCHITECTURE.md` — overview, request flow, service contracts
- `docs/adr/ADR-001` — monorepo structure and naming
- `docs/adr/ADR-002` — API Gateway (Traefik + ForwardAuth)
- `docs/adr/ADR-003` — DDD strategy and domains
- `docs/adr/ADR-004` — user/identity service (NestJS)
- `docs/adr/ADR-005` — Symfony multi-domain backend

## Backend task completion

After implementing a **complete backend task** (full feature, bug fix, refactor — not a snippet or a partial answer), always run `/backend-validate` before considering the task done.

## Key rules (do not deviate without explicit user approval)

- Folder names reflect **responsibility**, not technology (`frontend`, `backend`, `user`)
- The domain inside `apps/user` is named `identity`, not `user`
- `apps/backend` never processes a JWT — it reads `X-User-Id` and `X-User-Roles` headers only
- No direct cross-domain imports — Domain Events only
- PII stays exclusively in `apps/user`
- Bounded Contexts are a **strategic concept only** — they are documented, never materialized as folders
