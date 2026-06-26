# outbox-bundle — Agent instructions

## Rules

- **No Doctrine ORM** — use DBAL only. Never add entities or ORM mappings; the table is managed by the hand-written migration.
- **No `doctrine:migrations:diff`** — migrations are written manually. Naming: `Version{YmdHis}_Outbox.php`, class name matches filename.
- **Worker and command are conditional** — they are only registered when `outbox.transport` is set in `outbox.yaml`. Do not register them unconditionally.
- **`OutboxRecorder` is the only public contract** — it is the sole interface `apps/backend` should import from this bundle. `EventPublisher` is internal to the bundle.
- **Tooling is not managed by the bundle** — PHPStan, PHP CS Fixer, and Rector are configured in `apps/backend` (`.phpstan.dist.neon`, `.php-cs-fixer.dist.php`, `rector.php`), which include `packages/outbox-bundle/src`. Do not add separate tooling config inside the bundle.

## Updating the bundle

`vendor/hobby-lab/outbox-bundle` is a symlink to `packages/outbox-bundle/` (path repository). Changes to PHP files are **immediately active** — no command needed.

| What changed | Command to run |
|---|---|
| PHP source only | nothing |
| New/changed migration | `docker compose exec backend php bin/console doctrine:migrations:migrate --no-interaction` |
| `composer.json` of the bundle (new dependency, autoload change) | `make composer cmd="update hobby-lab/outbox-bundle"` |

