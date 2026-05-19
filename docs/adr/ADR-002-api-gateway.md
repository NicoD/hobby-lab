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

### Sandbox (Docker Compose)

```
Traefik
  ├── /auth/*        → apps/user directly (no ForwardAuth)
  ├── /color-lab/*   → ForwardAuth → stripPrefix(/color-lab) → apps/backend
  └── /mini-lab/*    → ForwardAuth → stripPrefix(/mini-lab)  → apps/backend  (example)
```

Each domain in `apps/backend` gets its own router in Traefik. This makes the bounded-context separation visible at the URL level (`/color-lab/brands`) while keeping Symfony routes clean (`/brands` — the prefix is stripped by a `stripPrefix` middleware before the request reaches Symfony).

To add a new domain: add a `strip-{domain}` middleware in `middlewares.yml` and a matching router in `routers.yml`.

Traefik injects the following headers into the request to Symfony:
- `X-User-Id`: JWT `sub` claim
- `X-User-Roles`: JWT `roles` claim (comma-separated)

### Production (reference)

Replace ForwardAuth with **Kong** and its native JWT plugin (local RS256 validation, no network call). The logic stays identical, latency disappears.

| Context | Solution | Trade-off |
|---|---|---|
| Sandbox | Traefik + ForwardAuth | Simple, 1 network call per request |
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
