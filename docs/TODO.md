# TODO

---

## Priorité haute

### CI/CD pipeline
Mettre en place GitHub Actions avec les étapes : lint, test, build, docker push.
C'est le point le plus visible manquant pour un portfolio lead dev.

Étapes suggérées :
- `backend` : PHPStan + Deptrac + PHPUnit
- `user` : ESLint + Jest
- `frontend` : ESLint + build Vite
- Docker build de chaque service

### Symfony Voters
Implémenter l'autorisation fine-grained sur au moins une ressource (Brand ou Paint).
Exemple : seul le propriétaire peut modifier/supprimer sa Brand.
L'architecture supporte déjà les Voters dans la couche Application — ADR-003.

### Tests NestJS
Aucun test observé dans `apps/user`.
Ajouter des tests e2e pour le flow auth complet :
- register, login, refresh (dont le cas token révoqué), logout
- endpoint /validate

---

## Priorité moyenne

### Security headers
Configurer un middleware Traefik pour ajouter les headers de sécurité :
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Strict-Transport-Security`
- `Content-Security-Policy`

Fichier cible : `infra/gateway/dynamic/`

### Tests React
Ajouter des tests unitaires sur les hooks critiques :
- `useApiFetch` (auto-refresh sur 401, retry, logout)
- `AuthContext` (login, logout, restore session)

### Logging structuré
- Symfony : configurer Monolog en sortie JSON avec correlation ID
- NestJS : intégrer le Logger NestJS avec format JSON

### OpenAPI / Swagger
Documenter les endpoints du backend Symfony et du service user.
Options : NelmioApiDocBundle (Symfony), @nestjs/swagger (NestJS).

---

## Priorité basse

### CLAUDE.md apps/user
Ajouter un CLAUDE.md dans `apps/user/` au même titre que `apps/backend/` et `apps/frontend/`.

### Scénarios de déploiement
Ajouter un `docker-compose.prod.yml` ou une documentation de déploiement pour démontrer la réflexion sur un environnement non-dev.

---

## Tooling & Workflow

### Conventional commits
Intégrer la convention de commits (https://www.conventionalcommits.org) :
- Installer `commitlint` + `husky` à la racine du monorepo
- Config : `feat`, `fix`, `chore`, `docs`, `test`, `refactor`, `ci`
- Ajouter un script `make commit-lint` dans le Makefile
- Documenter dans le README

### Git dans le workflow Claude
Configurer le workflow Claude pour respecter les conventional commits :
- Mettre à jour `CLAUDE.md` racine avec la règle de format de commit
- Ajouter dans `.claude/settings.local.json` les permissions git nécessaires
- Exemple de format attendu : `feat(color-lab): add paint reference use case`

---

## Avant publication GitHub

- [ ] README : vérifier que le projet se lance avec `make install && make up`
- [ ] README : ajouter la note Claude Code (rédigée)
- [ ] README : ajouter topics GitHub (symfony, nestjs, react, ddd, microservices, docker)
- [ ] Vérifier l'historique git : aucun secret commité (`git log --all -S "PRIVATE KEY"`)
- [ ] `.env.example` complet et à jour pour chaque service
- [ ] Passer les anciens projets GitHub en privé
