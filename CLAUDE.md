# CLAUDE.md

## Project Context

Read `AGENTS.md` for full project documentation including architecture, tech stack, conventions, commands, and directory structure.

## Quick Reference

- **Framework**: Symfony 7.2+ (PHP 8.4+)
- **Frontend**: jQuery 3.6 + Bootstrap 3/4 + Webpack Encore
- **Database**: MariaDB 10 via Doctrine ORM
- **Bundles**: SiteBundle (public), AdminBundle (admin), LogBundle (logs)

## CRITICAL: All Commands Must Run Inside Docker

**Running any command directly on the host machine is FORBIDDEN.** All commands — PHP, Composer, npm, tests, Symfony console, etc. — must be executed inside the `smestaj-app` Docker container.

Before running any command, verify containers are running (`docker ps --filter name=smestaj`). If not running, start with:

```bash
docker compose up -d --build
```

```bash
# Prefix ALL commands with:
docker exec smestaj-app <command>

# Or open an interactive shell:
docker exec -it smestaj-app bash
```

**Local App URL**: `http://localhost:9505` — always include the port when accessing the app.

## Working With This Codebase

- Always check `AGENTS.md` before making architectural decisions
- Entities are in `src/SiteBundle/Entity/` — use Doctrine annotations
- Services follow domain organization under `src/SiteBundle/Services/`
- Frontend JS uses a Controller/Handler/Mapper/Services pattern in `src/SiteBundle/Resources/public/js/`
- Templates are Twig — global base in `app/Resources/views/`, bundle-specific in each bundle
- Config lives in `app/config/` — parameters in `parameters.yml`
- Locale is Serbian (`rs`) — keep translations in YAML format

## Commands

All commands must be run inside the `smestaj-app` container:

```bash
docker exec smestaj-app npm run dev          # Dev build
docker exec smestaj-app npm run watch        # Watch mode
docker exec smestaj-app npm run build        # Production build
docker exec smestaj-app composer develop     # Full dev setup
docker exec smestaj-app composer deploy      # Full production deploy
docker exec smestaj-app ./vendor/bin/phpunit # Run tests
```
