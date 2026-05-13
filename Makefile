PWD := $(shell pwd)

.PHONY: help install symfony-install react-install up down restart logs build \
        shell-symfony shell-react composer npm \
        symfony-test symfony-lint symfony-lint-fix symfony-watch \
        react-test react-lint react-lint-fix react-watch

help:
	@echo ""
	@echo "Usage:"
	@echo "  make install            Install Symfony and React skeletons (first run)"
	@echo "  make up                 Start all containers"
	@echo "  make down               Stop all containers"
	@echo "  make restart            Restart all containers"
	@echo "  make build              Rebuild Docker images"
	@echo "  make logs               Stream logs from all containers"
	@echo "  make shell-symfony      Open a shell in the Symfony container"
	@echo "  make shell-react        Open a shell in the React container"
	@echo "  make composer cmd=...   Run a composer command (e.g. make composer cmd='require symfony/orm-pack')"
	@echo "  make npm cmd=...        Run an npm command    (e.g. make npm cmd='install axios')"
	@echo ""
	@echo "── Symfony ───────────────────────────────────────────────────────────────────"
	@echo "  make symfony-test       Run the PHPUnit test suite"
	@echo "  make symfony-lint       Check coding style (PHP CS Fixer, dry-run)"
	@echo "  make symfony-lint-fix   Auto-fix coding style issues"
	@echo "  make symfony-watch      Re-run tests on every PHP file change (Ctrl+C to stop)"
	@echo ""
	@echo "── React ─────────────────────────────────────────────────────────────────────"
	@echo "  make react-test         Run the Jest test suite"
	@echo "  make react-lint         Check coding style (ESLint, dry-run)"
	@echo "  make react-lint-fix     Auto-fix coding style issues"
	@echo "  make react-watch        Re-run tests on every JS file change (Ctrl+C to stop)"
	@echo ""

# ── Installation ──────────────────────────────────────────────────────────────

install: symfony-install react-install

symfony-install:
	@if [ ! -f apps/symfony/composer.json ]; then \
		echo ">>> Installing Symfony skeleton..."; \
		docker run --rm \
			--user $(shell id -u):$(shell id -g) \
			-v $(PWD)/apps/symfony:/app \
			-w /app \
			composer:latest create-project symfony/skeleton . --no-interaction; \
		echo ">>> Symfony skeleton ready."; \
	else \
		echo ">>> Symfony already installed, skipping."; \
	fi

react-install:
	@if [ ! -f apps/react/package.json ]; then \
		echo ">>> Installing React/Vite skeleton..."; \
		docker run --rm \
			--user $(shell id -u):$(shell id -g) \
			-e npm_config_cache=/tmp/npm-cache \
			-v $(PWD)/apps:/apps \
			-w /apps \
			node:lts-alpine sh -c "npx --yes create-vite@latest react --template react"; \
		echo ">>> React skeleton ready."; \
	else \
		echo ">>> React already installed, skipping."; \
	fi

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

shell-symfony:
	docker compose exec symfony sh

shell-react:
	docker compose exec react sh

# ── Shortcuts ─────────────────────────────────────────────────────────────────

composer:
	docker compose exec symfony composer $(cmd)

npm:
	docker compose exec react npm $(cmd)

# ── Symfony QA ─────────────────────────────────────────────────────────────────

symfony-test:
	docker compose exec symfony php vendor/bin/phpunit

symfony-lint:
	docker compose exec symfony php vendor/bin/php-cs-fixer check --diff --ansi

symfony-lint-fix:
	docker compose exec symfony php vendor/bin/php-cs-fixer fix --ansi

symfony-watch:
	docker compose exec -it symfony sh -c "find src tests -name '*.php' | entr -c php vendor/bin/phpunit"

# ── React QA ───────────────────────────────────────────────────────────────────

react-test:
	docker compose exec react npm test

react-lint:
	docker compose exec react npm run lint

react-lint-fix:
	docker compose exec react npm run lint:fix

react-watch:
	docker compose exec -it react npm run test:watch
