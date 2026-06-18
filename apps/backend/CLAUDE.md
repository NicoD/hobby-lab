# Backend — Agent instructions (Symfony / PHP)

## Architecture reference

Read `docs/architecture.md` (relative to this file) before any structural work on the backend.

## PHP attributes

Always add `#[\Override]` on every method that overrides a parent or implements an interface.

Exceptions:
- `__construct` — PHP rejects it with a fatal error, never add it there.
- Trait methods are validated at use-site — still add it for documentation value.

Other attributes to apply where relevant:
- `#[\SensitiveParameter]` (PHP 8.2) — on any parameter holding a secret, token, or password.
- `#[Deprecated]` (PHP 8.4) — instead of `@deprecated` docblocks.

## Code rules

- Deptrac must be configured before writing the first class in a new domain.
- `TransactionManager::execute()` is the only way to flush and dispatch events — never call `EntityManager::flush()` directly from a handler.
- PHPStan array types: use `list<T>` for sequential arrays (default), `array<K, V>` only when keys are meaningful. Never use `T[]`.
- Domain event subclasses declare typed VO properties only — no `array $payload`.
- Value objects use `new MyVo($value)` directly — no `from()` / `of()` static factories.
