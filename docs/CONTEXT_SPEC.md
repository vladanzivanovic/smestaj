# Resolve Symfony 8.1 Deprecations in dev.log

## Why

After the recent upgrade to Symfony 8.1.0 / PHP 8.4.21, `dev.log` is emitting Symfony 8.1 deprecation warnings on normal app usage. These need to be cleared now while the upgrade context is fresh, so the log stays useful for spotting real issues and so the app is ready for the eventual Symfony 9.0 jump.

## What

A full sweep that eliminates every `Since symfony/*` 8.1 deprecation entry currently emitted by the running app. The two deprecations the user named are illustrative examples, not the full scope:

1. `Since symfony/framework-bundle 8.1: Setting the "framework.profiler.collect_serializer_data" configuration option is deprecated. It will be removed in version 9.0.`
2. `Since symfony/dependency-injection 8.1: Relying solely on the name of parameter "$productEditRequestParser" of "__construct()" to match a named autowiring alias is deprecated; use the "#[Target]" attribute.`

The planner is responsible for discovering all other Symfony 8.1 deprecations currently in `dev.log` and addressing each one with the mechanical fix recommended by Symfony's upgrade guide.

## Constraints

### Must

- Address every Symfony-origin 8.1 deprecation currently emitted by the app (the two named ones plus any others discovered in `dev.log`).
- For the `framework.profiler.collect_serializer_data` deprecation: remove the offending configuration key from wherever it is currently set.
- For parameter-name-based autowiring deprecations (e.g. `$productEditRequestParser`): use the `#[Symfony\Component\DependencyInjection\Attribute\Target]` attribute on the constructor argument. This is the Symfony-recommended fix and matches the project's "imported class names, explicit DI" convention.
- Follow the project's import convention: every class referenced (including `Target`) must have a `use` statement; never use FQCN inline.
- All work runs inside the `smestaj-app` Docker container, per `AGENTS.md`.

### Must Not

- Do not introduce new runtime dependencies. A `composer require` of an already-present Symfony component subpackage to access `#[Target]` is acceptable if needed, but no new third-party packages.
- Do not modify code unrelated to clearing a Symfony 8.1 deprecation.
- Do not rename constructor parameters as the primary fix for the named-autowiring deprecation. Renaming is the fallback only when `#[Target]` is impractical at a given call site (and any such case must be justified).
- Do not silence deprecations via log filters / channel config — fix the source.

### Out of Scope

- Non-Symfony deprecations already present before this task: Sass `@import` deprecation warnings, Doctrine Migrations 2→3 namespace split warnings, and any other library-origin notices not prefixed `Since symfony/*`.
- The `DoctrineBundle::registerCommands()` deprecation noted in the prior QA report: this is assumed to be an upstream issue inside `doctrine/doctrine-bundle` itself, not application code. The planner may either bump `doctrine/doctrine-bundle` to a version that uses `#[AsCommand]`, or explicitly document it as "upstream — wait for fix". This is a recorded risk, not a required deliverable.
- Symfony 9.0 forward-compat work beyond clearing 8.1 deprecations.
- Refactoring DI bindings, services.yml structure, or controller wiring beyond what each individual fix requires.

## Current State

- The app was just upgraded to Symfony 8.1.0 / PHP 8.4.21 in the previous task.
- `dev.log` now contains Symfony 8.1 deprecation entries on normal page loads. The two confirmed examples are quoted above; the full inventory is for the planner to enumerate.
- Project conventions live in `/Users/vlada/Sites/smestaj/AGENTS.md` (Docker-only execution, explicit `use` imports, constructor injection, etc.).
- Symfony config files conventionally live under `app/config/` (e.g. `config.yml`, `config_dev.yml`) — the planner will locate the exact file holding `framework.profiler.collect_serializer_data`.
- The class consuming `$productEditRequestParser` exists somewhere in `AdminBundle` (parser naming aligns with `AdminBundle/Parser/Product/`); the planner will locate it.

## Validation

End-to-end verification after all fixes are applied:

- Inside the `smestaj-app` container, clear cache: `php bin/console cache:clear --env=dev`.
- Truncate or rotate `dev.log` so the post-fix log is clean.
- Exercise representative flows against `http://localhost:9505`:
  - Public home page loads (HTTP 200).
  - Admin login page loads and a successful login (HTTP 200 / expected redirect).
  - At least one authenticated admin flow that touches the previously-deprecated `productEditRequestParser` path.
- Inspect `dev.log`: zero entries matching `Since symfony/*`. Specifically, the two named deprecations must be absent.
- The QA skill's standard checks pass: PHPUnit green, lint green, frontend build (`npm run dev`) green, no new 500s on smoke-tested routes.
- No unrelated regressions — pre-existing non-Symfony deprecations (Sass `@import`, Doctrine Migrations 2→3) may still appear and are acceptable.
- Manual check: confirm that the constructor argument previously named `$productEditRequestParser` now carries a `#[Target('...')]` attribute with a `use` statement for `Symfony\Component\DependencyInjection\Attribute\Target` at the top of the file.
