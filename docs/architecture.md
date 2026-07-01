# Architecture — hobby-lab

## Overview

Monorepo with 3 applications and 1 infrastructure configuration.

```
hobby-lab/
├── apps/
│   ├── frontend/      ← React (user interface)
│   ├── backend/       ← Symfony (business domains)
│   └── user/          ← NestJS (identity domain)
├── infra/
│   └── gateway/       ← Traefik configuration
├── docs/
└── docker-compose.yml
```

| App | Tech | Role |
|---|---|---|
| `frontend` | React + TypeScript | End-user interface |
| `backend` | Symfony | Business domains (DDD) |
| `user` | NestJS | Identity domain (auth + profile) |
| `gateway` | Traefik | Routing, ForwardAuth |

## Request flow

```
Browser
   │
   ▼
Traefik :8000 (single entry point)
   │
   ├─ PathPrefix(/)              ──────────────────────────→  frontend
   │
   ├─ PathPrefix(/api/auth)      ── strip /api ────────────→  user :3000
   │                                                              ↓
   │                                                        issues RS256 JWT
   │
   ├─ PathPrefix(/api/color-lab) ── strip /api ─→  ForwardAuth → user (/validate)
   └─ PathPrefix(/api/mini-lab)  ── strip /api ─→  ForwardAuth → user (/validate)
                                                         │ 200 OK + injected headers
                                                         ▼
                                                    backend :8000
```

All API calls are prefixed with `/api` in the browser. Traefik strips `/api` before forwarding.

## Service contracts

### JWT (issued by `apps/user`)

- Algorithm: **RS256**
- Access token lifetime: **15 minutes**
- Public key: `GET /auth/jwks`

```json
{ "sub": "user-uuid", "email": "user@example.com", "roles": ["ROLE_USER"] }
```

### Headers injected by Traefik → `apps/backend`

| Header | Source |
|---|---|
| `X-User-Id` | JWT `sub` claim |
| `X-User-Roles` | JWT `roles` claim |

### Endpoints — `apps/user`

| Method | Route | Description |
|---|---|---|
| `POST` | `/auth/register` | Creates a new user account |
| `POST` | `/auth/login` | Returns access + refresh token |
| `POST` | `/auth/refresh` | Renews the access token |
| `POST` | `/auth/logout` | Invalidates the refresh token |
| `GET` | `/auth/jwks` | RS256 public key (JWKS format) |
| `GET` | `/validate` | ForwardAuth endpoint for Traefik |

## Correlation ID

Every HTTP request and AMQP message carries a `correlationId` (UUID) that identifies the chain of reactions it triggers — across services, domains, and async boundaries. It is a **pure infrastructure concern**: the Domain and Application layers are unaffected. See ADR-011.

| Where | How |
|---|---|
| HTTP | `X-Correlation-Id` request/response header |
| AMQP | Native `correlation_id` message property |

## Inter-service security

Internal REST communication between services is secured at the **network level only**. Services are not exposed outside the Docker network. No application-level authentication is implemented between internal services.

This is an explicit, documented assumption — not an oversight. It will be revisited if the threat model changes (e.g., services exposed across trust boundaries).

## ADRs

See `docs/adr/` for all architectural decisions.
