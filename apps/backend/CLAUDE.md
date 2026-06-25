# Backend — Agent instructions (Symfony / PHP)

## Architecture reference

Read `docs/architecture.md` (relative to this file) before any structural work on the backend.

## Layer rules

Refuse the following without explicit user approval:
- Application depending on Symfony/Doctrine concretes → define an interface in Application, implement it in Infrastructure.
- Domain depending on Doctrine annotations or Symfony types. (Known exception: `#[ORM\*]` on entities — accepted pragmatic tradeoff, do not extend.)
- Cross-domain imports — Domain Events only.

## PHP attributes

Always add `#[\Override]` on every method that overrides a parent or implements an interface.

Exceptions:
- `__construct` — PHP rejects it with a fatal error, never add it there.
- Trait methods are validated at use-site — still add it for documentation value.

- `#[\SensitiveParameter]` (PHP 8.2) — on any parameter holding a secret, token, or password.
- `#[Deprecated]` (PHP 8.4) — instead of `@deprecated` docblocks.

## Global helpers (`src/functions.php`)

- `wrap(?scalar $value, class-string $class): ?object` — Application layer only; instantiates a nullable VO from a nullable scalar. Target class must have exactly one typed constructor parameter.
- `str_cast(mixed $value): ?string` — anywhere; casts any scalar or `Stringable` to string, returns `null` otherwise.

## Aggregate root

- Private constructor — pure property assignment, no IO, no generation.
- Static `create()` factory — only public entry point for construction; raises domain events via `raiseDomainEvent()`.
- Implements `AggregateRoot` (marker interface extending `DomainEventHolder`).
- `DomainEventTrait` provides event collection; use it in every aggregate root.

## Bus conventions

Controllers communicate with Application exclusively via `CommandBus` and `QueryBus` (`Shared\Application\Bus`) — never inject handlers directly.

- `CommandBus::handle()` — write operations; the handler must return an object (value object, aggregate id…).
- `QueryBus::handle()` — read operations; the handler may return any type (`mixed`).
- Every handler must carry `#[AsMessageHandler]` — Symfony 8 autoconfigure does not detect handlers by `__invoke` signature alone.
- List query handlers that ignore their query object must name the parameter `$_query`.

## Code rules

- Deptrac must be configured before writing the first class in a new domain.
- `TransactionManager::execute()` is the only way to flush and dispatch events — never call `EntityManager::flush()` directly from a handler.
- Domain events dispatched after DB commit — never inside the transaction.
- Domain event subclasses declare typed VO properties only — no `array $payload`.
- Value objects use `new MyVo($value)` directly — no `from()` / `of()` static factories.
- VOs persisted by Doctrine get a custom type in `Infrastructure/Doctrine/Type/`, extending `AbstractHandleType` or `AbstractUuidType`.
- PHPStan array types: use `list<T>` for sequential arrays (default), `array<K, V>` only when keys are meaningful. Never use `T[]`.
- Authorization — coarse-grained (route access): Symfony Firewall. Fine-grained (aggregate action): Symfony Voters in `Application/Security/` of the relevant domain.
