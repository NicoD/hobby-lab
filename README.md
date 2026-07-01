# HobbyLab

A miniature paint management application — **work in progress**, used as a playground to apply production-grade backend practices.

Built with **Symfony**, **NestJS**, and **React**, containerized and routed through a Traefik gateway.

> Developed with [Claude Code](https://claude.com/claude-code) as an AI pair programmer (following sdd workflow).
> **All** architectural decisions and technical direction are driven and validated by the author.
> Code might have been generated either by the author or by Claude.

> Architectural decisions are documented incrementally in [`docs/adr/`](docs/adr/)

---

## What this project demonstrates

- **DDD** on the Symfony backend: aggregate roots, value objects, command/query bus, layered architecture enforced by Deptrac
- **Outbox pattern** as a standalone Symfony bundle, ensuring reliable event delivery without losing events on crash
- **Bounded context isolation** - identity (NestJS) and business domains (Symfony) are fully decoupled; the backend never processes a JWT
- **Authentication via gateway** - Traefik ForwardAuth delegates auth to NestJS and injects `X-User-Id` / `X-User-Roles` headers
- **Integration tests** that validate REST APIs and domain event dispatch
- **PHPStan at max level** + Rector + Deptrac in CI

---

## Architecture

```
Browser → Traefik :8000
            ├─ /           →  React frontend
            ├─ /api/auth   →  NestJS (identity)
            └─ /api/*      →  NestJS (ForwardAuth) → Symfony backend
```

| App | Stack |
|---|---|
| `frontend` | React + TypeScript |
| `backend` | Symfony + PHP |
| `user` | NestJS |
| `gateway` | Traefik |

---

## Getting started

**Requirements:** Docker with the Compose plugin

```bash
# Copy and fill in environment files
cp apps/user/.env.example apps/user/.env

make install   # install dependencies
make up        # start all containers
make user-account-create  # create a user account
```

| Service | URL | Note |
|---|---|---|
| App | http://localhost:8000 | Single entry point |
| Traefik dashboard | http://localhost:8080 | Dev only |
| Backend (direct) | http://localhost:8001 | Bypasses gateway |
| Frontend (Vite) | http://localhost:5173 | Bypasses gateway |

```bash
make backend-test     # PHP test suite
make backend-analyse  # PHPStan (level max)
make backend-lint     # CS Fixer + Rector + Deptrac
```

Run `make help` for the full list of commands.
