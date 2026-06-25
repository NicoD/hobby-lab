# Local development

## Ports

| URL | Service | Notes |
|---|---|---|
| `http://localhost:8000` | Traefik (API gateway) | All normal traffic — ForwardAuth active |
| `http://localhost:8001` | Symfony direct | Bypass Traefik — set headers manually |
| `http://localhost:3000` | NestJS direct | Bypass Traefik — identity service |
| `http://localhost:8080` | Traefik dashboard | Inspect routers, middlewares, services |
| `http://localhost:5173` | React frontend | Vite dev server |
| `http://localhost:5432` | PostgreSQL (identity) | `identity / identity` |
| `http://localhost:5433` | PostgreSQL (backend) | `backend / backend` |

Direct ports (`8001`, `3000`) are defined in `docker-compose.override.yml` — not committed to CI.

## Bypass mode (port 8001)

Direct access to Symfony without Traefik. Useful to test a controller without a valid JWT — set headers manually:

```
GET http://localhost:8001/color-lab/catalog/brands
X-User-Id: 550e8400-e29b-41d4-a716-446655440000
X-User-Roles: ROLE_USER
```

`GatewayAuthenticator` reads these headers directly and builds a `GatewayUser` without JWT validation.

## Unmatched routes

Any path that does not start with `/auth` or a known domain prefix returns a **Traefik 404** — the request never reaches Symfony.
