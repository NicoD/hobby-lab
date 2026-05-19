# ADR-006 — Authentication and authorization flow through the API gateway

**Status**: Accepted

## Context

The system is split into two backend services:
- `apps/user` — owns authentication and identity (JWT issuance, validation, PII)
- `apps/backend` — owns business domains (ColorLab, etc.)

Two questions must be answered:
1. Who validates the JWT?
2. How does `apps/backend` know who the caller is?

## Decision

### Authentication: gateway only

The API gateway (Traefik) is the **single point of JWT validation**. It uses the ForwardAuth pattern to delegate validation to `apps/user /validate`.

Three alternatives were considered:

| Option | Description | Rejected because |
|---|---|---|
| Gateway validates, passes JWT | Gateway checks JWT, backend still decodes it | Backend still coupled to JWT format and RS256 key |
| **Gateway validates, injects headers** ✓ | Gateway checks JWT, injects `X-User-Id` / `X-User-Roles` | — chosen |
| Each service validates its JWT | Every service has its own JWT library and key | Validation logic duplicated across all services |

**Chosen option**: the gateway validates the JWT, then injects `X-User-Id` (JWT `sub` claim) and `X-User-Roles` (JWT `roles` claim) as HTTP headers into every downstream request. `apps/backend` never sees a JWT.

### Network isolation: the trust foundation

The header-injection model is only secure if `apps/backend` is unreachable except through the gateway. The trust model is:

```
Internet
    │
    ▼
Traefik :80        ← only public port
    │   ForwardAuth → apps/user /validate (401 blocks request)
    ▼
apps/backend       ← internal Docker network only, no public port
```

In Docker Compose, `apps/backend` and `apps/user` expose **no `ports`** to the host. They are reachable only within the `sandbox` Docker network. Because Traefik is the only service with a public port, it is physically impossible for an external caller to forge `X-User-Id`.

A `docker-compose.override.yml` (gitignored) re-exposes ports for local development.

### Symfony Security: defense-in-depth

Even with network isolation, `apps/backend` enforces authentication at the application layer via a custom Symfony Security authenticator (`GatewayAuthenticator`):

- If `X-User-Id` is present → a `GatewayUser` is built and authenticated
- If `X-User-Id` is absent → `401 Unauthorized` (protects against direct calls in dev)

This adds a second line of defense and produces a meaningful error instead of a crash when headers are missing.

```
Request
    │
    ├── X-User-Id present? ──No──► 401 Unauthorized
    │
    └── Yes → GatewayUser(userId, roles) created
                    │
                    ▼
              Symfony Voters (fine-grained authz)
              e.g. "can this user edit this Brand?"
```

### Authorization: Symfony Voters in the domain layer

Authentication answers "who are you?". Authorization answers "what can you do?".

Fine-grained authorization (e.g. ownership checks — "only the owner can delete this Brand") lives in **Symfony Voters**, in the Application layer of each domain. Voters receive the `GatewayUser` from the Security token and the domain aggregate, and decide whether to grant access.

The gateway has no knowledge of domain aggregates and no authorization logic.

## Consequences

- `apps/user` exposes `GET /validate` (ForwardAuth endpoint) and `GET /auth/jwks` (RS256 public key)
- `apps/backend` depends on `symfony/security-bundle`
- `apps/backend` reads `X-User-Id` as a `UserId` value object (from `Shared/Domain/ValueObject/UserId`)
- PII never leaves `apps/user` — `apps/backend` only stores the user's UUID as a foreign reference
- Future fine-grained authorization rules are implemented as Voters, not in the gateway

## Production considerations

In production, replace Traefik ForwardAuth with **Kong** and its native JWT plugin (local RS256 validation, zero network call per request). The downstream behavior — headers injected, `apps/backend` reads them — stays identical.

For additional defense-in-depth on the internal network, add a shared secret header (`X-Gateway-Token: HMAC-SHA256(userId + timestamp, secret)`) that `apps/backend` verifies before trusting `X-User-Id`. This protects against header injection from other services inside the same network.
