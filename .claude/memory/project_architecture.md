---
name: project-architecture
description: Overall architecture of the hobby-lab monorepo — DDD microservices with React, Symfony, NestJS and Traefik
metadata:
  type: project
---

Monorepo learning project for DDD + microservices.

**Why:** Testing a microservices architecture with isolated domains, particularly for auth/identity.

**How to apply:** Always reference `docs/ARCHITECTURE.md` and `docs/adr/` for decisions. Do not propose alternatives to validated choices without strong justification.

## Structure

```
apps/frontend/   ← React
apps/backend/    ← Symfony (multi-domain DDD)
apps/user/       ← NestJS (identity domain)
infra/gateway/   ← Traefik config
docs/adr/        ← Architecture Decision Records
```

## Request flow

React → Traefik → ForwardAuth (apps/user /validate) → Symfony
React → Traefik → apps/user (routes /auth/*)

## Key decisions

- Folders named by responsibility, not technology (`frontend`, `backend`, `user`)
- Bounded Contexts are a strategic concept only — documented, never materialized as folders
- Domains are materialized as folders under `src/` (e.g. `src/identity/`, `src/ColorLab/`)
- `apps/user` contains a domain named `identity` (not `user`) to avoid redundancy
- JWT RS256 — Symfony reads X-User-Id and X-User-Roles injected by Traefik, never touches the JWT directly
- Domain isolation enforced by Deptrac (Symfony) and closed modules (NestJS) — violation = CI fail
- Fine-grained authorization via Symfony Voters in the Application layer of each domain
- Inter-domain communication via Domain Events (Symfony Messenger), never direct cross-namespace imports

## Backend stack

Symfony + Doctrine ORM + PostgreSQL (`doctrine/doctrine-bundle`, `doctrine/orm`). Container: `postgres:16-alpine` on host port 5433.

## User service stack

NestJS + Prisma + PostgreSQL + argon2 + RS256 JWT

## ADRs

- ADR-001: monorepo structure
- ADR-002: Traefik API Gateway + ForwardAuth
- ADR-003: DDD strategy and domains
- ADR-004: user/identity NestJS service
- ADR-005: Symfony multi-domain backend
