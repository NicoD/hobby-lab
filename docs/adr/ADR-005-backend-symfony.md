# ADR-005 — Symfony multi-domain backend

**Status**: Accepted

## Context

`apps/backend` implements the business domains of the project. Multiple domains coexist in the same Symfony service because they share common infrastructure (database, deployment) and are under the same team's responsibility.

## Decision

### Domain structure

Each domain is a **PHP namespace** under `src/`. A domain cannot directly import from another domain.

```
apps/backend/src/
  Kitchen/         ← kitchen domain
    Domain/
    Application/
    Infrastructure/
    Interface/
  Order/           ← order domain
    Domain/
    Application/
    Infrastructure/
    Interface/
  Catalog/         ← catalog domain (example)
    Domain/
    Application/
    Infrastructure/
    Interface/
```

### Naming convention

Domains are named in **PascalCase** (PHP/Symfony convention). Layer folders also use PascalCase.

### Layer conventions

**Domain/**: pure domain objects only (no direct dependency on Symfony or Doctrine)
- Aggregates, Entities, Value Objects
- Repository interfaces
- Domain Events
- Domain Services

**Application/**: use-case orchestration
- Commands + Handlers
- Queries + Handlers
- DTOs

**Infrastructure/**: technical implementations
- Doctrine repositories
- External adapters
- Symfony configuration (services.yaml, doctrine mappings)

**Interface/**: entry points
- API Controllers (JSON)
- Event listeners

### User identity

`apps/backend` never validates a JWT. It only receives:
- `X-User-Id` (UUID, injected by Traefik)
- `X-User-Roles` (roles, injected by Traefik)

These headers are injected into Symfony's `Security Token` via a custom `TokenAuthenticator`.

### Authorization: two levels

**Coarse-grained** (access to a route) → Symfony Firewall, based on `X-User-Roles`

**Fine-grained** (action on a specific aggregate) → Symfony Voters in the Application layer

```php
// Application/Security/RecipeVoter.php (inside Kitchen/)
class RecipeVoter extends Voter
{
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match($attribute) {
            'EDIT'    => $subject->isOwnedBy($token->getUserId()),
            'ARCHIVE' => $token->hasRole('ROLE_ADMIN'),
        };
    }
}
```

### Domain isolation: Deptrac

Deptrac is configured from day one to forbid cross-domain imports. A violation breaks the CI build.

```yaml
# deptrac.yaml (example)
layers:
  - name: Kitchen
    collectors:
      - { type: directory, value: src/Kitchen }
  - name: Order
    collectors:
      - { type: directory, value: src/Order }

ruleset:
  Kitchen: []    # Kitchen imports no other domain
  Order:   []
```

### Inter-domain communication

Domains do not call each other directly. Communication goes through **Domain Events** (Symfony Messenger event bus):

```
Kitchen dispatches RecipePublished
  → Order listens to RecipePublished (if needed)
```

Direct cross-namespace calls are never allowed.

## Consequences

- Each domain is independently testable
- A domain can be extracted into a separate service if needed (Domain/ has no Symfony dependency)
- Deptrac must be installed and configured before writing the first domain
- `apps/backend` stores no personal data (PII) — that remains in `apps/user`
