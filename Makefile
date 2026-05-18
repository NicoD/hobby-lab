PWD := $(shell pwd)

.PHONY: help install backend-install frontend-install user-install \
        up down restart logs build \
        shell-backend shell-frontend shell-user composer npm npx-user \
        backend-test backend-lint backend-lint-fix backend-watch \
        frontend-test frontend-lint frontend-lint-fix frontend-watch \
        user-test user-lint user-lint-fix user-watch \
        user-migrate user-migrate-reset user-prisma \
        gateway-logs

help:
	@echo ""
	@echo "Usage:"
	@echo "  make install              Install all dependencies"
	@echo "  make up                   Start all containers"
	@echo "  make down                 Stop all containers"
	@echo "  make restart              Restart all containers"
	@echo "  make build                Rebuild Docker images"
	@echo "  make logs                 Stream logs from all containers"
	@echo ""
	@echo "── Shells ────────────────────────────────────────────────────────────────────"
	@echo "  make shell-backend        Open a shell in the backend container"
	@echo "  make shell-frontend       Open a shell in the frontend container"
	@echo "  make shell-user           Open a shell in the user container"
	@echo ""
	@echo "── Shortcuts ─────────────────────────────────────────────────────────────────"
	@echo "  make composer cmd=...     Run a composer command in backend"
	@echo "  make npm cmd=...          Run an npm command in frontend"
	@echo "  make npx-user cmd=...     Run an npx command in user (e.g. make npx-user cmd='prisma studio')"
	@echo ""
	@echo "── Backend ───────────────────────────────────────────────────────────────────"
	@echo "  make backend-test         Run the PHPUnit test suite"
	@echo "  make backend-lint         Check coding style (PHP CS Fixer, dry-run)"
	@echo "  make backend-lint-fix     Auto-fix coding style issues"
	@echo "  make backend-watch        Re-run tests on every PHP file change (Ctrl+C to stop)"
	@echo ""
	@echo "── Frontend ──────────────────────────────────────────────────────────────────"
	@echo "  make frontend-test        Run the Jest test suite"
	@echo "  make frontend-lint        Check coding style (ESLint, dry-run)"
	@echo "  make frontend-lint-fix    Auto-fix coding style issues"
	@echo "  make frontend-watch       Re-run tests on every JS file change (Ctrl+C to stop)"
	@echo ""
	@echo "── User (NestJS) ─────────────────────────────────────────────────────────────"
	@echo "  make user-test            Run the Jest test suite"
	@echo "  make user-lint            Check coding style (ESLint, dry-run)"
	@echo "  make user-lint-fix        Auto-fix coding style issues"
	@echo "  make user-watch           Re-run tests on every TS file change (Ctrl+C to stop)"
	@echo "  make user-migrate         Run pending Prisma migrations"
	@echo "  make user-migrate-reset   Reset the database and re-run all migrations (destructive)"
	@echo "  make user-prisma cmd=...  Run any Prisma CLI command (e.g. make user-prisma cmd='studio')"
	@echo ""
	@echo "── Gateway (Traefik) ─────────────────────────────────────────────────────────"
	@echo "  make gateway-logs         Stream Traefik logs"
	@echo "  Dashboard:                http://localhost:8080"
	@echo ""

# ── Installation ──────────────────────────────────────────────────────────────

install: backend-install frontend-install user-install

backend-install:
	docker compose run --rm backend composer install
	docker compose run --rm backend php bin/console cache:clear

frontend-install:
	docker compose run --rm frontend npm install

user-install:
	docker compose run --rm user npm install
	docker compose run --rm user npx prisma generate

# ── Docker Compose ─────────────────────────────────────────────────────────────

up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

build:
	docker compose build --no-cache

logs:
	docker compose logs -f

# ── Shells ─────────────────────────────────────────────────────────────────────

shell-backend:
	docker compose exec backend sh

shell-frontend:
	docker compose exec frontend sh

shell-user:
	docker compose exec user sh

# ── Shortcuts ─────────────────────────────────────────────────────────────────

composer:
	docker compose exec backend composer $(cmd)

npm:
	docker compose exec frontend npm $(cmd)

npx-user:
	docker compose exec user npx $(cmd)

# ── Backend QA ─────────────────────────────────────────────────────────────────

backend-test:
	docker compose exec backend php vendor/bin/phpunit

backend-lint:
	docker compose exec backend php vendor/bin/php-cs-fixer check --diff --ansi

backend-lint-fix:
	docker compose exec backend php vendor/bin/php-cs-fixer fix --ansi

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

# ── User — Prisma ───────────────────────────────────────────────────────────────

user-migrate:
	docker compose exec user npx prisma migrate dev

user-migrate-reset:
	docker compose exec user npx prisma migrate reset

user-prisma:
	docker compose exec user npx prisma $(cmd)

# ── Gateway ────────────────────────────────────────────────────────────────────

gateway-logs:
	docker compose logs -f gateway
