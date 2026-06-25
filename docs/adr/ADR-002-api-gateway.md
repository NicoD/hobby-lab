# ADR-002 — API Gateway with Traefik and ForwardAuth

**Status**: Accepted

## Context

The microservices architecture requires a single entry point that:
- Routes requests to the correct service
- Validates JWTs without each service doing it independently
- Injects the user identity as headers

Two distinct responsibilities must be separated:
- **Authentication** (who are you?) → gateway
- **Authorization** (what can you do?) → business domain

## Decision

Use **Traefik** as the API Gateway via the **ForwardAuth** pattern.

### Local (Docker Compose)

```
Traefik                                                  :8000
  ├── /api/auth/*        → strip /api → apps/user (no ForwardAuth)
  ├── /api/color-lab/*   → strip /api → ForwardAuth → apps/backend
  └── /*                 → apps/frontend (Vite dev server :5173)
```

**Traefik is the single entry point for all traffic** — including the React frontend. The Vite dev server is not exposed directly; the browser always goes through Traefik on port 8000. This matches the production topology where a static file server sits behind the same gateway.

All API routes are prefixed with `/api` in the browser to avoid conflicts with frontend routes (e.g. the React route `/auth` and the API route `/auth/login` would otherwise collide). Traefik strips the `/api` prefix via the `strip-api-prefix` middleware before forwarding — backend services are completely unaware of this prefix.

In development, Vite's built-in proxy is not used. Traefik handles all routing before requests reach Vite.

Each domain in `apps/backend` gets its own router in Traefik. After prefix stripping, Symfony receives the full domain path (e.g. `/color-lab/brands`) and owns the routing internally. Routes in Symfony controllers include the domain prefix (`#[Route('/color-lab/brands')]`).

To add a new domain: add a router in `routers.yml` with the `forward-auth` middleware.

Traefik injects the following headers into the request to Symfony:
- `X-User-Id`: JWT `sub` claim
- `X-User-Roles`: JWT `roles` claim (comma-separated)

### Production (reference)

Replace ForwardAuth with **Kong** and its native JWT plugin (local RS256 validation, no network call). The logic stays identical, latency disappears.

| Context | Solution | Trade-off |
|---|---|---|
| Local | Traefik + ForwardAuth | Simple, 1 network call per request |
| Production | Kong + JWT plugin | Zero latency, more configuration |
| Cloud | AWS API Gateway / Cloudflare | Zero ops, variable cost |

## What the gateway does NOT do

- It does **not** handle authorization (what the user is allowed to do)
- It has no knowledge of domain aggregates
- It contains no business logic

Fine-grained authorization (e.g. "an author can only edit their own recipes") remains in the Symfony domain via **Voters**.

## Consequences

- `infra/gateway/` contains `traefik.yml` (static config) and `dynamic/` (middlewares, rules)
- `apps/user` exposes `GET /validate` for ForwardAuth
- `apps/user` exposes `GET /auth/jwks` for the RS256 public key
- `apps/backend` reads `X-User-Id` and `X-User-Roles` from headers — it never processes a JWT directly
