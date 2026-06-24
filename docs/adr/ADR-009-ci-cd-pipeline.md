# ADR-009 — CI/CD pipeline: GitHub Actions, Dependabot, versioning

**Status**: Accepted

## Context

The project needed an automated CI/CD pipeline covering dependency updates, commit quality, per-service validation, and release management. Several tools and patterns were evaluated before converging on the current setup.

## Decisions

### Dependabot

Dependabot is activated via `.github/dependabot.yml`. It monitors all three apps and covers three ecosystems per service: the package manager (`composer`, `npm`), the Docker base image, and GitHub Actions themselves.

Schedule is **weekly on Monday** — daily updates generate too much noise on an active development project.

### Commit validation — commitlint only, no Semantic PR app

Two tools were considered:

- **commitlint** (`wagoid/commitlint-github-action`) — validates individual commit messages on each PR
- **Semantic Pull Requests** (GitHub App + `semantic.yaml`) — validates the PR title

The choice depends on the merge strategy:
- Squash merge → PR title becomes the commit on `main` → Semantic PR is the relevant control point
- Rebase merge → individual commits land on `main` as-is → commitlint is the relevant control point

This project uses **rebase merge** (local squash before push when needed). Semantic PR is therefore irrelevant. `commitlint` is the only tool used.

The `wagoid/commitlint-github-action` action was replaced by a direct `npx commitlint` call via `actions/setup-node` to avoid a dependency on a third-party action running Node 20, over which we have no control.

### CODEOWNERS — not set up

CODEOWNERS assigns automatic reviewers per file path. It has no value and will be introduced if the project becomes multi-contributor.

### Release management — semantic-release, manual trigger

Releases are handled by `semantic-release` via a `workflow_dispatch` workflow (`global-release-proposal`). The trigger is **manual** with a `dry_run` flag so the developer controls when a version is published and can preview the outcome first.

`semantic-release` is run via `npx` with explicit plugin flags — no root `package.json` is needed. Plugin list: `commit-analyzer`, `release-notes-generator`, `github`, `conventional-changelog-conventionalcommits`.

Release configuration is in `.releaserc.json` at the root. It targets `main` only and publishes GitHub Releases without npm publication.

### Per-service CI — Docker Compose, no reviewdog

Each service (`backend`, `frontend`, `user`) has a dedicated workflow (`project-<service>-ci.yaml`) triggered on pull requests that touch `apps/<service>/**`.

**Reviewdog was not adopted.** It provides inline PR annotations for linter output, which is valuable for large teams reviewing many PRs. For a small team, reading CI logs is sufficient. The added complexity (Docker-based tooling, Dependabot-specific branches for the `GITHUB_TOKEN`) is not justified at this stage.

Each workflow runs two **parallel jobs**: `lint` and `test`. Separating them surfaces the failure type immediately without waiting for the other job to finish.

CI execution goes through **Docker Compose** (`docker-compose.ci.yaml`) rather than native runners (`setup-php`, `setup-node`). This mirrors the local development environment and handles PostgreSQL dependencies without a separate service container configuration per workflow.

Key implementation choices in `docker-compose.ci.yaml`:
- Source code is **volume-mounted** from the GitHub Actions workspace (Dockerfiles are dev-only and do not `COPY` code)
- Databases use **`tmpfs`** instead of named volumes — state is ephemeral, teardown requires no `--volumes` flag
- **Healthchecks** on both Postgres services enable `docker compose up -d --wait` — no manual polling loop
- The vendor/node_modules directories are **cached on the host** via `actions/cache` and picked up through the volume mount, avoiding a full reinstall on every run

`COMPOSE_PROJECT_NAME` is scoped per job (`run_id_run_number_job`) to prevent container name collisions on self-hosted runners.

## Consequences

- `.github/dependabot.yml` — monitors composer, npm, docker, github-actions weekly
- `.github/workflows/global-commitlint.yaml` — blocks merge on non-conventional commit messages
- `.github/workflows/global-release-proposal.yaml` — manual semantic-release with dry-run preview
- `.github/workflows/project-{backend,frontend,user}-ci.yaml` — lint + test per service on PR
- `docker-compose.ci.yaml` — CI-specific Compose file, not used locally
- `.releaserc.json` — semantic-release config at monorepo root
- No `CODEOWNERS`, no `semantic.yaml`, no reviewdog
