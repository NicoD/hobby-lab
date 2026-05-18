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
        │   ├── Profile.ts         ← value object (PII)
        │   ├── Role.ts            ← value object
        │   ├── Token.ts           ← entity (refresh tokens)
        │   └── UserRepository.ts  ← interface
        ├── application/
        │   ├── commands/
        │   │   ├── RegisterUser.ts
        │   │   ├── AuthenticateUser.ts
        │   │   └── UpdateProfile.ts
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

### Sandbox: simplified authentication

To validate the architecture without implementing a full system, credentials are stored in the database (a single test user). The goal is to validate the JWT flow and ForwardAuth, not a complete registration system.

### Exposed endpoints

| Method | Route | Auth required | Description |
|---|---|---|---|
| `POST` | `/auth/login` | No | Returns access token (JWT) + refresh token |
| `POST` | `/auth/refresh` | Refresh token | Renews the access token |
| `POST` | `/auth/logout` | Access token | Invalidates the refresh token |
| `GET` | `/auth/jwks` | No | RS256 public key in JWKS format |
| `GET` | `/validate` | No (internal) | ForwardAuth endpoint for Traefik |

### GDPR consequences

Isolating this service is an advantage: the "right to erasure" materializes as deleting a single `User` aggregate. No personal data must leak into `apps/backend`.
