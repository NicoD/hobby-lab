# ADR-005 — Symfony multi-domain backend

**Status**: Accepted

## Context

`apps/backend` implements the business domains of the project. Multiple domains coexist in the same Symfony service because they share common infrastructure (database, deployment) and are under the same team's responsibility.

Storage uses Doctrine ORM + PostgreSQL. The backend database is distinct from the identity database used by `apps/user`.

## Decision

### Domain structure

Each domain is a PHP namespace under `src/`. A domain cannot directly import from another domain.

```
apps/backend/src/
  ColorLab/         ← business domain (PascalCase)
    Domain/
    Application/
    Infrastructure/
    UI/
      Http/
  Shared/           ← cross-domain technical building blocks (no business logic)
    Domain/
    Application/
    Infrastructure/
```

Domains are named in PascalCase. Layer folders also use PascalCase.

### Layer conventions

**Domain/**: pure domain objects — no dependency on Symfony or Doctrine
- Aggregates, entities, value objects
- Repository interfaces, domain events, domain services

**Application/**: use-case orchestration
- Commands + Handlers, Queries + Handlers, read models

**Infrastructure/**: technical implementations
- Doctrine repositories, event dispatcher adapters, Symfony configuration

**UI/Http/**: entry points
- JSON API controllers, event listeners

### User identity and authorization

See ADR-006 — it covers JWT validation, header injection, `GatewayAuthenticator`, and Voter placement.

### Domain isolation: Deptrac

Deptrac is configured from day one. A cross-domain import breaks the CI build.

```yaml
# deptrac.yaml
layers:
  - name: ColorLab
    collectors:
      - { type: directory, value: src/ColorLab }
  - name: Shared
    collectors:
      - { type: directory, value: src/Shared }

ruleset:
  ColorLab:
    - Shared   # business domains may depend on Shared
  Shared: []   # Shared never depends on a business domain
```

### Inter-domain communication

Domains do not call each other directly. See ADR-007 for the full event strategy (Domain Events vs. Integration Events, Outbox pattern).

### Shared namespace

`App\Shared` holds cross-domain technical building blocks: abstract base classes (`AbstractUuid`, `AbstractHandle`), cross-cutting value objects (`UserId`), abstract Doctrine types, and security adapters. Business domains may depend on `Shared`. `Shared` must never depend on a business domain.

For the current detailed structure of all namespaces, see `apps/backend/docs/architecture.md`.

## Consequences

- Each domain is independently testable — Domain/ has no Symfony dependency.
- A domain can be extracted into a separate service without rewriting its core logic.
- Deptrac must be installed and configured before the first domain class is written.
- `apps/backend` stores no personal data (PII) — that stays in `apps/user`.
