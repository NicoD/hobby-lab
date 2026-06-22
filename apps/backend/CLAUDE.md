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

## Global helpers (`src/functions.php`)

### `wrap(?scalar $value, class-string $class): ?object`

Use in the **Application layer** to instantiate a nullable value object from a nullable scalar, replacing the verbose ternary:

```php
// before
null !== $command->brandHandle ? new BrandHandle($command->brandHandle) : null

// after
wrap($command->brandHandle, BrandHandle::class)
```

Constraints:
- The target class must have exactly one typed constructor parameter.
- Do not use outside the Application layer — Domain constructs its own VOs directly, Infrastructure maps from Doctrine types.

### `str_cast(mixed $value): ?string`

Casts any scalar or `Stringable` to `string`, returns `null` for anything else (objects without `__toString`, arrays, resources…).

Use anywhere you need to extract an optional string from a value whose type is not guaranteed:

```php
// before
$brand instanceof BrandHandle ? (string) $brand : null

// after
str_cast($brand)
```

Typical use: passing a nullable VO as a string argument (e.g. `HandleGenerator::generate()` prefix).

## Code rules

- Deptrac must be configured before writing the first class in a new domain.
- `TransactionManager::execute()` is the only way to flush and dispatch events — never call `EntityManager::flush()` directly from a handler.
- PHPStan array types: use `list<T>` for sequential arrays (default), `array<K, V>` only when keys are meaningful. Never use `T[]`.
- Domain event subclasses declare typed VO properties only — no `array $payload`.
- Value objects use `new MyVo($value)` directly — no `from()` / `of()` static factories.
