PWD := $(shell pwd)

.PHONY: help install backend-install frontend-install user-install media-install \
        up down restart logs \
        shell-backend shell-frontend shell-user composer npm npx-user \
        backend-test backend-test-setup backend-lint backend-lint-fix backend-deptrac backend-analyse backend-refacto backend-watch \
        frontend-test frontend-lint frontend-lint-fix frontend-watch \
        user-test user-lint user-lint-fix user-watch \
        user-account-create user-migrate user-migrate-reset user-prisma \
		media-test media-lint media-lint-fix media-watch \
        media-migrate media-migrate-reset media-prisma \
        gateway-logs backend-logs

help:
	@echo ""
	@echo "Usage:"
	@echo "  make install              Install all dependencies"
	@echo "  make up                   Start all containers"
	@echo "  make down                 Stop all containers"
	@echo "  make restart              Restart all containers"
	@echo "  make logs                 Stream logs from all containers"
	@echo ""
	@echo "── Shells ────────────────────────────────────────────────────────────────────"
	@echo "  make shell-backend        Open a shell in the backend container"
	@echo "  make shell-frontend       Open a shell in the frontend container"
	@echo "  make shell-user           Open a shell in the user container"
	@echo "  make shell-media          Open a shell in the media container"	
	@echo ""
	@echo "── Shortcuts ─────────────────────────────────────────────────────────────────"
	@echo "  make composer cmd=...     Run a composer command in backend"
	@echo "  make npm cmd=...          Run an npm command in frontend"
	@echo "  make npx-user cmd=...     Run an npx command in user (e.g. make npx-user cmd='prisma studio')"
	@echo ""
	@echo "── Backend ───────────────────────────────────────────────────────────────────"
	@echo "  make backend-test         Set up test DB and run the test suite"
	@echo "  make backend-test-setup   Create test DB and run pending migrations (idempotent)"
	@echo "  make backend-lint         Check coding style and architecture (PHP CS Fixer + Rector + Deptrac, dry-run)"
	@echo "  make backend-lint-fix     Auto-fix coding style issues (PHP CS Fixer + Rector) and check architecture (Deptrac)"
	@echo "  make backend-deptrac      Check architecture layer boundaries (Deptrac)"
	@echo "  make backend-refacto      Apply Rector refactoring rules (interactive)"
	@echo "  make backend-analyse      Run PHPStan static analysis (level max)"
	@echo "  make backend-watch        Re-run tests on every PHP file change (Ctrl+C to stop)"
	@echo "  make backend-logs         Stream backend logs (stderr)"
	@echo ""
	@echo "── Frontend ──────────────────────────────────────────────────────────────────"
	@echo "  make frontend-test        Run the Jest test suite"
	@echo "  make frontend-lint        Check coding style (ESLint, dry-run)"
	@echo "  make frontend-lint-fix    Auto-fix coding style issues"
	@echo "  make frontend-watch       Re-run tests on every JS file change (Ctrl+C to stop)"
	@echo ""
	@echo "── User ───────────────────────────────────────────────────────────────────────"
	@echo "  make user-test            Run the Jest test suite"
	@echo "  make user-lint            Check coding style (ESLint, dry-run)"
	@echo "  make user-lint-fix        Auto-fix coding style issues"
	@echo "  make user-watch           Re-run tests on every TS file change (Ctrl+C to stop)"
	@echo "  make user-account-create          Create a user account interactively"
	@echo "  make user-migrate         Run pending Prisma migrations"
	@echo "  make user-migrate-reset   Reset the database and re-run all migrations (destructive)"
	@echo "  make user-prisma cmd=...  Run any Prisma CLI command (e.g. make user-prisma cmd='studio')"
	@echo ""
	@echo "── Media ───────────────────────────────────────────────────────────────────────"
	@echo "  make media-test            Run the Jest test suite"
	@echo "  make media-lint            Check coding style (ESLint, dry-run)"
	@echo "  make media-lint-fix        Auto-fix coding style issues"
	@echo "  make media-watch           Re-run tests on every TS file change (Ctrl+C to stop)"
	@echo "  make media-migrate         Run pending Prisma migrations"
	@echo "  make media-migrate-reset   Reset the database and re-run all migrations (destructive)"
	@echo "  make media-prisma cmd=...  Run any Prisma CLI command (e.g. make user-prisma cmd='studio')"
	@echo ""
	@echo "── Gateway (Traefik) ─────────────────────────────────────────────────────────"
	@echo "  make gateway-logs         Stream Traefik logs"
	@echo "  Dashboard:                http://localhost:8080"
	@echo ""

# ── Installation ──────────────────────────────────────────────────────────────

install: backend-install frontend-install user-install media-install

backend-install:
	docker compose build backend
	docker compose run --rm backend composer install
	docker compose run --rm backend php bin/console cache:clear

frontend-install:
	docker compose build frontend
	docker compose run --rm frontend npm install

user-install:
	docker compose build user
	docker compose run --rm user npm install

media-install:
	docker compose build media
	docker compose run --rm media npm install

# ── Docker Compose ─────────────────────────────────────────────────────────────

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart


logs:
	docker compose logs -f

# ── Shells ─────────────────────────────────────────────────────────────────────

shell-backend:
	docker compose exec backend sh

shell-frontend:
	docker compose exec frontend sh

shell-user:
	docker compose exec user sh

shell-media:
	docker compose exec media sh	

# ── Shortcuts ─────────────────────────────────────────────────────────────────

composer:
	docker compose exec backend composer $(cmd)

npm:
	docker compose exec frontend npm $(cmd)

npx-user:
	docker compose exec user npx $(cmd)

# ── Backend QA ─────────────────────────────────────────────────────────────────

backend-test-setup:
	docker compose exec backend php bin/console doctrine:database:create --env=test --if-not-exists
	docker compose exec backend php bin/console doctrine:migrations:migrate --env=test --no-interaction

backend-test: backend-test-setup
	docker compose exec backend php vendor/bin/phpunit

backend-lint:
	docker compose exec backend php vendor/bin/php-cs-fixer check --diff --ansi
	docker compose exec backend php vendor/bin/rector --dry-run --ansi
	docker compose exec backend php vendor/bin/deptrac analyse --ansi

backend-lint-fix:
	docker compose exec backend php vendor/bin/php-cs-fixer fix --ansi
	docker compose exec backend php vendor/bin/rector --ansi
	docker compose exec backend php vendor/bin/deptrac analyse --ansi

backend-deptrac:
	docker compose exec backend php vendor/bin/deptrac analyse --ansi

backend-refacto:
	docker compose exec backend php vendor/bin/rector --ansi

backend-analyse:
	docker compose exec backend php vendor/bin/phpstan analyse --memory-limit=256M

backend-watch:
	docker compose exec -it backend sh -c "find src tests -name '*.php' | entr -c php vendor/bin/phpunit"

# ── Frontend QA ────────────────────────────────────────────────────────────────

frontend-test:
	docker compose exec frontend npm test

frontend-lint:
	docker compose exec frontend npm run lint

frontend-lint-fix:
	docker compose exec frontend npm run lint:fix

frontend-watch:
	docker compose exec -it frontend npm run test:watch

# ── User QA ────────────────────────────────────────────────────────────────────

user-test:
	docker compose exec user npm test

user-lint:
	docker compose exec user npm run lint

user-lint-fix:
	docker compose exec user npm run lint:fix

user-watch:
	docker compose exec -it user sh -c "find src -name '*.ts' | entr -c npm test"

user-account-create:
	docker compose exec -it user npm run cli:create-user

# ── User — Prisma ───────────────────────────────────────────────────────────────

user-migrate:
	docker compose exec user npx prisma migrate dev

user-migrate-reset:
	docker compose exec user npx prisma migrate reset

user-prisma:
	docker compose exec user npx prisma $(cmd)

# ── Media QA ────────────────────────────────────────────────────────────────────

media-test:
	docker compose exec media sh -c "npm test && npm run test:e2e"

media-lint:
	docker compose exec media npm run lint

media-lint-fix:
	docker compose exec media npm run lint:fix

media-watch:
	docker compose exec -it media sh -c "find src -name '*.ts' | entr -c npm test"


# ── Media — Prisma ───────────────────────────────────────────────────────────────

media-migrate:
	docker compose exec media npx prisma migrate dev

media-migrate-reset:
	docker compose exec media npx prisma migrate reset

media-prisma:
	docker compose exec media npx prisma $(cmd)

# ── Gateway ────────────────────────────────────────────────────────────────────

gateway-logs:
	docker compose logs -f gateway

backend-logs:
	docker compose logs -f backend
