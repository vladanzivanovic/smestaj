# Migrate `parameters.yml` to `.env` for Dev/Prod Parity

## Why

Production has no Docker, so the environment-variable injection that `docker-compose.yml` provides locally (notably `MAILER_DSN`, pointing at Mailcatcher) does not exist on the prod host. The app needs a portable, Docker-independent configuration mechanism so the same parameters can be supplied on prod without rewriting config files per environment.

## What

Replace `app/config/parameters.yml` as the source of runtime configuration with a `.env`-based mechanism, applied consistently across dev (Docker) and prod (no Docker).

Concrete deliverables:

1. A committed `.env.dist` template at the repository root listing **every** variable the application needs, with placeholder (non-secret) values and brief inline comments where useful.
2. A `.gitignore` rule covering the real `.env` and `.env.local` (and any other environment-specific dotenv files that should not be committed).
3. All configuration files, services, and any other runtime references that previously read from `parameters.yml` updated to read from environment variables (typically via Symfony's `%env(...)%` parameter syntax).
4. `app/config/parameters.yml` (and `parameters.yml.dist`, if present) removed from runtime use. The planner decides whether the files are deleted outright or emptied/retained as a no-op shim — but nothing the app loads at runtime may depend on their values.
5. `docker-compose.yml` updated so the local stack loads variables from a dotenv file (e.g. `env_file: .env.local`) instead of (or in addition to) inline `environment:` entries, so dev and prod use the same mechanism.
6. Documentation (in the repo, location at planner's discretion) describing how to create `.env` / `.env.local` from `.env.dist` on a fresh prod host and locally.

## Constraints

### Must

- Cover **every** parameter currently defined in `app/config/parameters.yml` (mailer DSN, database DSN/credentials, app secret, and any others present) — not just the mailer DSN.
- Use environment variables as the single source of truth for configuration. Symfony config files should reference them via `%env(...)%` (or the equivalent for the project's actual Symfony version — see Open Questions).
- Provide a committed `.env.dist` with placeholders and no real secrets. Real `.env` / `.env.local` files are gitignored and provisioned manually per host.
- Preserve current local developer UX: after the change, running the existing dev workflow must still result in a working app — DB connects, Mailcatcher receives mail, app boots — without the developer needing extra manual steps beyond copying `.env.dist` to `.env.local` once.
- Preserve current prod behavior expectations: the app must boot and send mail using the `MAILER_DSN` supplied via `.env` on the prod host.

### Must Not

- Commit real secrets (DB passwords, app secret, mailer credentials, OAuth keys, Sentry DSN, etc.) to the repository.
- Leave any runtime code path still reading values out of `parameters.yml`.
- Introduce a configuration mechanism that diverges between dev and prod (e.g. dotenv in prod but inline `environment:` in dev). Both environments must use the same loading path.
- Modify unrelated code, business logic, or templates.

### Out of Scope

- Refactoring service definitions, bundles, or business logic beyond what is strictly required to swap the parameter source.
- Introducing a secrets vault (Symfony Secrets, HashiCorp Vault, etc.).
- Changing the prod deployment process itself, beyond documenting how `.env` is placed on the host.
- Adjusting CI/CD pipelines (unless they currently rely on `parameters.yml` — in which case minimal updates are allowed).

## Current State

- Local dev runs in Docker (`smestaj-app`, MariaDB on :9501, Mailcatcher on :9503/:9504, app on :9505). `docker-compose.yml` injects environment variables — at minimum `MAILER_DSN` — into the app container.
- Production runs directly on a host (no Docker). It currently has no equivalent mechanism for those env vars, which is the immediate pain point.
- Configuration today lives in `app/config/parameters.yml` (per `AGENTS.md`). Other relevant config files mentioned in `AGENTS.md`: `app/config/config.yml`, `security.yml`, `routing.yml`, `services.yml`, `doctrine_extension.yml`, `liip_params.yml`, `nelmio_security.yaml`.
- The exact Symfony major version is unverified in this spec (see Open Questions); `composer.json` is the source of truth and the planner will check it in Phase 1.
- The planner will produce the implementation plan at `/Users/vlada/Sites/smestaj/IMPLEMENTATION_PLAN.md`.

## Validation

End-to-end checks once all tasks complete:

- **Local dev (Docker):** starting the stack and using the app results in a working DB connection and Mailcatcher receiving outbound mail, with configuration sourced from `.env.local` loaded via docker-compose's `env_file`. No regressions vs. today's developer experience.
- **Production (no Docker):** after placing a populated `.env` on the prod host, the app boots and successfully sends mail via the configured `MAILER_DSN`. DB connects using credentials from `.env`.
- **Codebase audit:** a grep for `parameters.yml` (and for any parameter keys it used to define) shows no remaining runtime references; all such lookups go through `%env(...)%` or the Symfony equivalent.
- **Template completeness:** `.env.dist` lists every variable the app requires; a fresh checkout + `cp .env.dist .env.local` + filling in real values is sufficient to boot the app locally and on prod.
- **Secrets hygiene:** `git status` on a configured host shows `.env` / `.env.local` as ignored; no real secret values are present in the committed `.env.dist`.

---

## Open Questions / Assumptions

The following items were not answered by the user and are recorded as working assumptions. The user may override any of them before Phase 1 begins, or the planner may surface them again once it has inspected the codebase.

1. **Dev/prod parity mechanism (assumption).** Local development will move to a gitignored `.env.local` consumed by docker-compose via `env_file:`, replacing inline `environment:` entries, so dev and prod load configuration the same way. Override if you want dev to keep its current inline `environment:` style.
2. **Loader (assumption).** Use `symfony/dotenv` (the standard Symfony approach) to load `.env` files into the PHP process. If you prefer a non-PHP loader (Apache `SetEnv`, nginx `fastcgi_param`, systemd `EnvironmentFile`, shell sourcing), say so and the planner will adapt.
3. **Production environment details (unknown).** Web server (Apache vs. nginx+PHP-FPM), how PHP currently receives env vars on prod, and whether any `.env*` files already exist on the prod host are unknown. The planner should ask the user (or be given access to inspect) before finalizing the prod-side instructions.
4. **Symfony version (to verify).** `AGENTS.md` is internally inconsistent (mentions both Symfony 4.x and 7.x). The planner must treat `composer.json` as the source of truth and adapt the approach accordingly: `%env()%` parameter syntax availability, dotenv autoloading location (`config/bootstrap.php` for Symfony 4 Flex layouts vs. `public/index.php` / `Runtime` component for modern Symfony), and whether `symfony/dotenv` is already installed.
5. **Disposition of `parameters.yml` files.** Whether to delete `app/config/parameters.yml` and `parameters.yml.dist` outright or retain them as empty/no-op shims is left to the planner, with the hard requirement that no runtime code path reads from them after the change.
