# ADR-001 — Monorepo structure and naming conventions

**Status**: Accepted

## Context

The project is a DDD + microservices learning sandbox. It contains several applications of different natures (frontend, business backend, identity service) and one infrastructure configuration (gateway).

The initial `apps/<stack>` structure (e.g. `apps/react`, `apps/symfony`) named folders by **technology**, which creates confusion when changing tech or adding services.

## Decision

Name each folder by its **responsibility**, not its technology.

```
apps/
  frontend/    ← React        (role: user interface)
  backend/     ← Symfony      (role: business domains)
  user/        ← NestJS       (role: identity domain)
infra/
  gateway/     ← Traefik      (role: routing and security)
```

The gateway lives in `infra/` rather than `apps/` because it is not a developed application — it is infrastructure configuration with no business code, no tests, and no build step.

## Naming rule

> A folder name under `apps/` describes what the service **exposes to the rest of the system**, not the technology implementing it.

| Bad | Good | Why |
|---|---|---|
| `apps/react` | `apps/frontend` | technology is an implementation detail |
| `apps/symfony` | `apps/backend` | same |
| `apps/node` | `apps/user` | names the business responsibility |

## Consequences

- Rename `apps/react` → `apps/frontend` and `apps/symfony` → `apps/backend`
- Create `apps/user/` for the NestJS service
- Create `infra/gateway/` for the Traefik configuration
- Makefile and docker-compose.yml must reflect these names
