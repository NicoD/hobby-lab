# ADR-004 — user/identity service (NestJS)

**Status**: Accepted

## Context

Authentication and user data form a naturally isolated domain: its own model (User, Token, Session), independent deployment cycle, and no other service is allowed to modify its data directly.

This service implements IAM (Identity and Access Management): the domain responsible for proving and managing who a user is in the system.

## Decision

### Responsibility

`apps/user` is the only service allowed to:
- Store user credentials (email, password hash)
- Issue and revoke JWTs
- Manage personal data (PII): name, preferences, avatar
- Manage user roles

### Tech stack

| Component | Choice | Reason |
|---|---|---|
| Framework | **NestJS** | DDD-friendly (modules, DI, native CQRS), patterns close to Symfony |
| ORM | **Prisma** | TypeScript-first, clean migrations |
| Database | **PostgreSQL** | User + PII storage |
| JWT | `@nestjs/jwt` | RS256 (asymmetric) |
| Hashing | **argon2** | Superior to bcrypt in security |
| Validation | `class-validator` | DTO validation |

### Internal structure

```
apps/user/
└── src/
    └── identity/                  ← domain (NestJS module)
        ├── domain/
        │   ├── User.ts            ← root aggregate
        │   ├── Role.ts            ← value object
        │   ├── Token.ts           ← entity (refresh tokens)
        │   └── UserRepository.ts  ← interface
        ├── application/
        │   ├── commands/
        │   │   ├── RegisterUser.ts
        │   │   └── AuthenticateUser.ts
        │   └── queries/
        │       └── GetUserById.ts
        ├── infrastructure/
        │   ├── UserPrismaRepository.ts
        │   └── JwtService.ts
        └── interface/
            └── AuthController.ts
```

**Why `identity` and not `user`?** The service is called `user` (what it exposes to the system). The domain is called `identity` (the precise concept: authentication + profile + roles). This avoids the redundancy `apps/user/src/user/`.

### NestJS ↔ Symfony mapping

| Symfony | NestJS |
|---|---|
| Bundle / Module | `@Module()` |
| Service | `@Injectable()` |
| Controller | `@Controller()` |
| Repository | Native repository pattern |
| DI Container | Built-in DI container |

### Sandbox: registration and authentication

Registration is fully implemented (`POST /auth/register`). The goal is to validate the JWT flow and ForwardAuth with real users created via the CLI (`npm run cli:create-user`) or the API.

Refresh token rotation and logout (`/auth/refresh`, `/auth/logout`) are stubbed — the `Token` entity and the Prisma schema are in place but the persistence logic is not yet wired.

### Exposed endpoints

| Method | Route | Auth required | Status | Description |
|---|---|---|---|---|
| `POST` | `/auth/register` | No | Implemented | Creates a new user account |
| `POST` | `/auth/login` | No | Implemented | Returns access token (JWT, 15 min) + refresh token |
| `POST` | `/auth/refresh` | Refresh token | Stubbed | Renews the access token |
| `POST` | `/auth/logout` | Access token | Stubbed | Invalidates the refresh token |
| `GET` | `/auth/jwks` | No | Stubbed | RS256 public key in JWKS format |
| `GET` | `/validate` | No (internal) | Implemented | ForwardAuth endpoint for Traefik |

### GDPR consequences

Isolating this service is an advantage: the "right to erasure" materializes as deleting a single `User` aggregate. No personal data must leak into `apps/backend`.
