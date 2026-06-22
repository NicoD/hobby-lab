# Backend — Architecture

## Folder structure

```
apps/backend/src/
  ColorLab/               ← business domain (PascalCase)
    Domain/
      Model/              ← aggregates, entities, value objects (siblings, no sub-namespace)
      Repository/         ← repository interfaces
      Event/              ← domain events
      Service/            ← domain services
    Application/
      {Aggregate}/
        Command/
        Query/
        ReadModel/
    Infrastructure/
      Doctrine/
        Repository/
        Type/
    UI/
      Http/               ← JSON controllers
  Shared/                 ← technical cross-domain building blocks (no business logic)
    Domain/
      Model/              ← abstract base classes (AbstractUuid, AbstractHandle) and cross-cutting VOs (UserId, AggregateRoot, …)
      Event/              ← DomainEvent base, DomainEventTrait, DomainEventHolder
    Application/
      Service/            ← interfaces used across handlers (TransactionManager, DomainEventDispatcher)
    Infrastructure/
      Doctrine/           ← DoctrineTransactionManager, AbstractHandleType, AbstractUuidType
      Doctrine/Type/      ← abstract Doctrine custom types
      Event/              ← SymfonyDomainEventDispatcher
      Security/           ← GatewayAuthenticator, GatewayUser
```

## Layers

```
Domain/         → pure PHP, zero framework dependency
Application/    → commands, queries, handlers; depends only on Domain
Infrastructure/ → Doctrine, Symfony adapters; depends on Application + Domain
UI/             → controllers, event listeners; depends on Application
```

### Layer violations — refuse without explicit approval

- Application depending on any Symfony/Doctrine concrete (`EntityManagerInterface`, `EventDispatcherInterface`, …) → define an interface in Application, implement it in Infrastructure.
- Domain depending on Doctrine annotations or Symfony types. (Known exception: `#[ORM\*]` on entities — accepted pragmatic tradeoff, do not extend.)
- Cross-domain imports — Domain Events only.

## Domain isolation (Deptrac)

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

> Add `Shared` as a Deptrac layer when a second domain is introduced.

## User identity

`apps/backend` never validates a JWT. It only reads two headers injected by the API gateway:

- `X-User-Id` — UUID of the authenticated user
- `X-User-Roles` — comma-separated roles

`GatewayAuthenticator` reads these headers and populates the Symfony Security token.

## Authorization

**Coarse-grained** (access to a route) → Symfony Firewall, based on `X-User-Roles`.

**Fine-grained** (action on a specific aggregate) → Symfony Voters, placed in `Application/Security/` of the relevant domain.

```php
// ColorLab/Application/Security/BrandVoter.php
class BrandVoter extends Voter
{
    #[\Override]
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        return match($attribute) {
            'EDIT' => $subject->isOwnedBy($token->getUserIdentifier()),
            default => false,
        };
    }
}
```

## Aggregate root pattern

- Private constructor — pure property assignment, no IO, no generation.
- Static `create()` factory — the only public entry point for construction. Raises domain events via `raiseDomainEvent()`.
- Implements `AggregateRoot` (marker interface extending `DomainEventHolder`).
- `DomainEventTrait` provides event collection; use it in every aggregate root.

## Domain events

- `DomainEvent` base class auto-generates `$eventId` and `$occurredAt`. Subclasses declare typed VO properties — no generic payload arrays.
- Events are raised inside `create()` / mutation methods, collected by `DomainEventTrait`, and dispatched **after** DB commit via `TransactionManager`. Never dispatch inside the transaction.

## Transaction boundary

`TransactionManager` (interface in Application) wraps a closure returning the aggregate(s). `DoctrineTransactionManager` (Infrastructure) collects events, flushes, commits, then dispatches. Command handlers never touch `EntityManagerInterface` directly.

## Value objects

- `new MyVo($value)` by default. Static factory only when semantically meaningful (`create()`, `inCents()`).
- Each VO persisted by Doctrine gets a custom type in `Infrastructure/Doctrine/Type/`, extending `AbstractHandleType` or `AbstractUuidType`.

## Tests

See `docs/testing.md`.
