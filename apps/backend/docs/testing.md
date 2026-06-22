# Backend — Testing conventions

## Structure

```
tests/
  Unit/                     ← unit tests, no framework, no DB
  Integration/              ← API-level tests, full Symfony kernel + DB
    ColorLab/
  bootstrap.php
  doctrine_object_manager.php
```

## Naming

Unit test files mirror `src/` using the filename as the directory name, with individual test files named after what they test:

```
src/functions.php  →  tests/Unit/functions/WrapTest.php
                       tests/Unit/functions/OtherFunctionTest.php
```

If a source file only ever needs one test file, a flat file is acceptable:
```
src/SomeClass.php  →  tests/Unit/SomeClassTest.php
```

Integration test files follow the domain arborescence under `tests/Integration/`.

## Namespaces

| Suite | Namespace | Directory |
|---|---|---|
| Unit | `Test\Unit\…` | `tests/Unit/` |
| Integration | `Test\Integration\…` | `tests/Integration/` |

`ApiTestCase` (`Test\Integration\ApiTestCase`) is the base class for all integration tests.

## PHPUnit suites

```bash
vendor/bin/phpunit --testsuite Unit          # fast, no infra needed
vendor/bin/phpunit --testsuite Integration   # requires a running DB
```
