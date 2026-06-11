# Sandbox

A minimal client/server sandbox project with a **Symfony 8** backend (PHP 8.4) and a **React + Vite** frontend, fully containerized with Docker.

## Structure

```
.
├── apps/
│   ├── symfony/        # Symfony 8 skeleton — PHP 8.4
│   │   └── Dockerfile
│   └── react/          # React + Vite skeleton
│       └── Dockerfile
├── docker-compose.yml
├── Makefile
└── README.md
```

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
| Gateway (Traefik) | http://localhost:8000  | Single entry point — all traffic |
| Traefik dashboard | http://localhost:8080  | Sandbox only                     |
| Symfony (direct)  | http://localhost:8001  | Dev only — bypasses gateway      |
| React (Vite dev)  | http://localhost:5173  | Dev only — bypasses gateway      |

The two containers run on the same Docker network (`sandbox`).  

## Commands

| Command                       | Description                              |
|-------------------------------|------------------------------------------|
| `make install`                | Install dependencies and clear caches    |
| `make up`                     | Start all containers (detached)          |
| `make down`                   | Stop and remove containers               |
| `make restart`                | Restart all containers                   |
| `make build`                  | Rebuild Docker images from scratch       |
| `make logs`                   | Stream logs from all containers          |
| `make shell-symfony`          | Open a shell in the Symfony container    |
| `make shell-react`            | Open a shell in the React container      |
| `make composer cmd="<cmd>"`   | Run a Composer command                   |
| `make npm cmd="<cmd>"`        | Run an npm command                       |

### Examples

```bash
# Add a Symfony package
make composer cmd="require symfony/orm-pack"

# Add an npm package
make npm cmd="install axios"
```
