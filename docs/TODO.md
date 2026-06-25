# TODO

---

## High priority

### ~~CI/CD pipeline~~ ✅
~~Set up GitHub Actions with the following steps: lint, test, build, docker push.~~

### Symfony Voters
Implement fine-grained authorization on at least one resource (Brand or Paint).
Example: only the owner can modify/delete their Brand.
The architecture already supports Voters in the Application layer — ADR-003.

### NestJS tests
No tests observed in `apps/user`.
Add e2e tests for the complete auth flow:
- register, login, refresh (including revoked token case), logout
- `/validate` endpoint

---

## Medium priority

### Security headers
Configure a Traefik middleware to add security headers:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Strict-Transport-Security`
- `Content-Security-Policy`

Target file: `infra/gateway/dynamic/`

### React tests
Add unit tests for critical hooks:
- `useApiFetch` (auto-refresh on 401, retry, logout)
- `AuthContext` (login, logout, restore session)

### Structured logging
- Symfony: configure Monolog with JSON output and correlation ID
- NestJS: integrate NestJS Logger with JSON format

### OpenAPI / Swagger
Document the Symfony backend endpoints and the user service.
Options: NelmioApiDocBundle (Symfony), @nestjs/swagger (NestJS).

---

## Low priority

### CLAUDE.md for apps/user
~~Add a CLAUDE.md in `apps/user/` on a par with `apps/backend/` and `apps/frontend/`.~~

### Deployment scenarios
Add a `docker-compose.prod.yml` or deployment documentation to demonstrate thinking about a non-dev environment.

---

## Tooling & Workflow

### Conventional commits
~~Integrate the commit convention (https://www.conventionalcommits.org):~~
~~- Install `commitlint` + `husky` at the monorepo root~~
~~- Config: `feat`, `fix`, `chore`, `docs`, `test`, `refactor`, `ci`~~
~~- Add a `make commit-lint` script to the Makefile~~
~~- Document in the README~~

### Git in the Claude workflow
Configure the Claude workflow to follow conventional commits:
- Update the root `CLAUDE.md` with the commit format rule
- Add the required git permissions in `.claude/settings.local.json`
- Expected format example: `feat(color-lab): add paint reference use case`

---

## Before publishing on GitHub

- [x] README: verify the project starts with `make install && make up`
- [ ] README: add the Claude Code note (already written)
- [ ] README: add GitHub topics (symfony, nestjs, react, ddd, microservices, docker)
- [ ] Check git history: no committed secrets (`git log --all -S "PRIVATE KEY"`)
- [ ] Complete and up-to-date `.env.example` for each service
- [ ] Set older GitHub projects to private
