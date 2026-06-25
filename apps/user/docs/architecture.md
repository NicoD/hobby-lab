# User — Architecture

## Folder structure

```
apps/user/src/
  identity/               ← domain: PII, authentication, account management
    application/
      commands/
      queries/
    domain/
    infrastructure/
    interface/
    identity.module.ts
  cli/                    ← CLI entry points (e.g. account creation)
  app.module.ts
  main.ts
```

## Stack

NestJS (TypeScript) — Prisma ORM — PostgreSQL.

## API Gateway contract

`apps/user` is the **only** service that validates JWTs. On successful authentication it responds to the Traefik ForwardAuth request so the gateway can inject `X-User-Id` and `X-User-Roles` into downstream requests.
