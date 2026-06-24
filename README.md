# Recipe Lab

A miniature paint management application built with **Symfony 8** (PHP 8.4), **React + Vite**, and **NestJS**, fully containerized with Docker.

## Requirements

- [Docker](https://docs.docker.com/get-docker/) with the Compose plugin

## Getting started

```bash
# 1. Install dependencies
make install

# 2. Start all containers
make up
```

| Service           | URL                    | Note                             |
|-------------------|------------------------|----------------------------------|
| App               | http://localhost:8000  | Single entry point — all traffic |
| Traefik dashboard | http://localhost:8080  | Dev only                         |
| Backend (direct)  | http://localhost:8001  | Dev only — bypasses gateway      |
| Frontend (Vite)   | http://localhost:5173  | Dev only — bypasses gateway      |

## Commands

Run `make help` for the full list. Key shortcuts:

| Command                       | Description                              |
|-------------------------------|------------------------------------------|
| `make install`                | Install all dependencies                 |
| `make up`                     | Start all containers (detached)          |
| `make down`                   | Stop and remove containers               |
| `make shell-backend`          | Open a shell in the backend container    |
| `make shell-frontend`         | Open a shell in the frontend container   |
| `make shell-user`             | Open a shell in the user container       |
| `make composer cmd="<cmd>"`   | Run a Composer command in backend        |
| `make npm cmd="<cmd>"`        | Run an npm command in frontend           |
| `make backend-lint`           | PHP CS Fixer + Rector + Deptrac (dry-run)|
| `make backend-analyse`        | PHPStan static analysis (level max)      |
| `make backend-test`           | Run the PHP test suite                   |
