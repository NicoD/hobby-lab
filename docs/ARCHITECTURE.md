# Architecture — Recipe Lab

> Reference document for the agent. All decisions are detailed in `docs/adr/`.

## Overview

Monorepo with 3 applications and 1 infrastructure configuration, following a **DDD + microservices** approach.

```
recipe-lab/
├── apps/
│   ├── frontend/      ← React (user interface)
│   ├── backend/       ← Symfony (business domains)
│   └── user/          ← NestJS (identity domain)
├── infra/
│   └── gateway/       ← Traefik configuration
├── docs/
│   └── adr/           ← Architecture Decision Records
├── docker-compose.yml
└── Makefile
```

## Applications

| App | Tech | Role | Type |
|---|---|---|---|
| `frontend` | React | End-user interface | Presentation layer |
| `backend` | Symfony | Business domains (ColorLab, Order...) | Multi-domain DDD |
| `user` | NestJS | Identity domain (auth + profile) | Microservice |
| `gateway` | Traefik | Routing, JWT validation | Infrastructure |

## Request flow

```
Browser (React)
      │
      ▼
   Traefik (gateway)
      │
      ├─ /auth/*  ──────────────────────────→  apps/user  (NestJS)
      │                                              ↓
      │                                        issues RS256-signed JWT
      │
      └─ /api/*   ──→  ForwardAuth ──→  apps/user (/validate)
                              │ 200 OK + injected headers
                              ▼
                         apps/backend  (Symfony)
                              ↓
                         X-User-Id, X-User-Roles available
```

## Contracts between services

### JWT (issued by `apps/user`)

```json
{
  "sub": "user-uuid",
  "email": "user@example.com",
  "roles": ["ROLE_USER"],
  "iat": 1700000000,
  "exp": 1700000900
}
```

- Algorithm: **RS256** (asymmetric key)
- Access token lifetime: **15 minutes**
- Public key exposed by `apps/user` via `GET /auth/jwks`
- Symfony validates locally — no network call to `apps/user` per business request

### Headers injected by Traefik → Symfony

| Header | Value | Source |
|---|---|---|
| `X-User-Id` | User UUID | JWT `sub` claim |
| `X-User-Roles` | Comma-separated roles | JWT `roles` claim |

### Endpoints exposed by `apps/user`

| Method | Route | Description |
|---|---|---|
| `POST` | `/auth/register` | Creates a new user account |
| `POST` | `/auth/login` | Authentication, returns access + refresh token |
| `POST` | `/auth/refresh` | Renews the access token *(stubbed)* |
| `POST` | `/auth/logout` | Invalidates the refresh token *(stubbed)* |
| `GET` | `/auth/jwks` | RS256 public key (JWKS format) *(stubbed)* |
| `GET` | `/validate` | ForwardAuth endpoint for Traefik |

## Related ADRs

- [ADR-001](adr/ADR-001-monorepo-structure.md) — Monorepo structure and naming conventions
- [ADR-002](adr/ADR-002-api-gateway.md) — API Gateway with Traefik and ForwardAuth
- [ADR-003](adr/ADR-003-ddd-strategy.md) — DDD strategy and domains
- [ADR-004](adr/ADR-004-user-identity-service.md) — user/identity service (NestJS)
- [ADR-005](adr/ADR-005-backend-symfony.md) — Symfony multi-domain backend
