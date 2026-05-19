# Local development

## Ports

| URL | Service | Notes |
|---|---|---|
| `http://localhost:8000` | Traefik (API gateway) | All normal traffic goes here — ForwardAuth active |
| `http://localhost:8001` | Symfony direct | Bypass Traefik — set `X-User-Id` / `X-User-Roles` headers manually |
| `http://localhost:3000` | NestJS direct | Bypass Traefik — identity service |
| `http://localhost:8080` | Traefik dashboard | Inspect routers, middlewares, services |
| `http://localhost:5173` | React frontend | Vite dev server |
| `http://localhost:5432` | PostgreSQL (identity) | `identity / identity` |
| `http://localhost:5433` | PostgreSQL (backend) | `backend / backend` |

Direct ports (`8001`, `3000`) are defined in `docker-compose.override.yml` and not committed to CI.

---

## Testing the auth flow with Postman

### 1 — Register

```
POST http://localhost:8000/auth/register
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "motdepasse123"
}
```

Expected: `201 Created`

### 2 — Login

```
POST http://localhost:8000/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "motdepasse123"
}
```

Expected: `200 OK`

```json
{
  "accessToken": "eyJhbGci...",
  "refreshToken": "..."
}
```

Copy the `accessToken`.

### 3 — Call a protected endpoint

```
GET http://localhost:8000/color-lab/brands
Authorization: Bearer eyJhbGci...
```

Traefik forwards the request to `apps/user /validate`, injects `X-User-Id` / `X-User-Roles` on success, then forwards to Symfony.

### Rejected request (no token)

```
GET http://localhost:8000/color-lab/brands
```

Expected: `401 Unauthorized` — blocked by Traefik before reaching Symfony.

### Debug: call `/validate` directly

```
GET http://localhost:8000/validate
Authorization: Bearer eyJhbGci...
```

`200` → token valid. `401` → token invalid or expired. Useful to isolate whether the problem is the token or Symfony.

---

## Bypass mode (port 8001)

Direct access to Symfony without Traefik. Useful to test a controller without needing a valid JWT.
Set the headers manually:

```
GET http://localhost:8001/color-lab/brands
X-User-Id: 550e8400-e29b-41d4-a716-446655440000
X-User-Roles: ROLE_USER
```

`GatewayAuthenticator` reads these headers directly and builds a `GatewayUser` without any JWT validation.

---

## Unmatched routes

Any path that does not start with `/auth` or a known domain prefix (e.g. `/color-lab`) returns a **Traefik 404** — the request never reaches Symfony.
