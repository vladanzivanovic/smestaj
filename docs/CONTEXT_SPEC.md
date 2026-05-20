# Fix `docker compose build` failure in `lamp` service (Node.js upgrade to 16.x)

## Why

`docker compose build` currently fails at the Node.js installation step in the `lamp` service Dockerfile. The legacy NodeSource `setup_14.x` script no longer ships a valid signing key, causing apt to reject the repository (`NO_PUBKEY 1655A0AB68576280`) and the build to abort. Node.js 14 is also EOL. Without a working build the local development stack cannot be (re)created.

## What

A green `docker compose build` for the existing stack, with Node.js **16.x** installed in the `lamp` image via a currently-supported NodeSource installation method, and the existing front-end workflow (`npm run dev` / `npm run watch` against the existing `webpack.config.js`) still functional.

Concrete deliverable:
- The `lamp` image builds end-to-end with no apt GPG warnings and no deprecation-induced 60-second stalls.
- Inside the built container, `node -v` reports a `v16.x` version and `npm -v` works.
- `npm run dev` succeeds against the unchanged `webpack.config.js` and `package.json`.

## Constraints

### Must

- Target Node.js major version **16.x** (user-chosen, explicit).
- Use a currently-supported NodeSource installation pattern that does not rely on the deprecated `setup_<ver>.x` bash pipe. The expected modern approach is the keyring + signed `sources.list` pattern (e.g. `/usr/share/keyrings/nodesource.gpg` plus `deb [signed-by=...] https://deb.nodesource.com/node_16.x focal main`). Final mechanics are at the planner's discretion as long as apt accepts the repository without GPG warnings.
- Work on both **arm64** (Apple Silicon, the user's host) and **amd64** architectures.
- Preserve the rest of the `lamp` image: PHP 7.4, Apache, Composer, and any other tooling already installed must continue to work unchanged.
- Preserve all host port mappings: site `9500`, MariaDB `9501`, phpMyAdmin `9502`, Mailcatcher `9503` (SMTP) / `9504` (UI).
- Preserve the existing `composer develop` bootstrap flow.

### Must Not

- Must not modify the `mysql`, `phpmyadmin`, or `mailcatcher` services in `docker-compose.yml`.
- Must not modify `package.json`, `composer.json`, `webpack.config.js`, or any PHP / Symfony / Twig / JS application code.
- Must not upgrade webpack or any npm/composer dependency as part of this fix.
- Must not change PHP version, Symfony version, or Apache configuration.
- Must not introduce new top-level services or change container names.

### Out of Scope

- Upgrading Node.js beyond 16.x (e.g. to Node 18 LTS) — tracked as a follow-up (see Open Questions).
- Upgrading webpack, Babel, or any front-end dependency.
- Any application-level change (controllers, entities, templates, assets).
- Refactoring unrelated parts of the Dockerfile.
- CI / deployment pipeline changes.

## Current State

- `docker compose build` fails at Dockerfile step 32/45: `RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -`.
- apt error observed:
  - `W: GPG error: https://deb.nodesource.com/node_14.x focal InRelease: NO_PUBKEY 1655A0AB68576280`
  - `E: The repository 'https://deb.nodesource.com/node_14.x focal InRelease' is not signed.`
- The `setup_14.x` script additionally prints a deprecation warning and forces a 60-second wait before failing.
- Host architecture: Apple Silicon (arm64). The failing build was pulling arm64 packages.
- Affected service: `lamp` (PHP/Apache) only. Other services (`mysql`, `phpmyadmin`, `mailcatcher`) are not implicated.
- The exact Dockerfile path and surrounding instructions are for the planner to identify; this spec deliberately does not enumerate file paths beyond what the user/coordinator referenced.

## Validation

End-to-end verification, all run via Docker (per project hard rule):

1. `docker compose build` exits with status `0` and produces no apt GPG warnings during the Node install step.
2. `docker compose up -d` brings the full stack up; all four services (`lamp`, `mysql`, `phpmyadmin`, `mailcatcher`) report healthy / running.
3. `docker compose exec lamp node -v` prints a version matching `v16.<minor>.<patch>`.
4. `docker compose exec lamp npm -v` prints a version without error.
5. `docker compose exec lamp npm run dev` completes successfully against the unchanged `webpack.config.js`.
6. The site responds on `http://localhost:9500` (basic smoke check — no application changes expected).
7. Build succeeds on both arm64 (primary, user's host) and amd64 (must not be regressed).

## Open Questions / Assumptions

- **Node.js 16 is EOL** (end-of-life since September 2023). The user explicitly chose 16.x for this ticket. Recorded as a known risk; **not** a blocker for this work. Recommendation: open a follow-up ticket to move to Node 18 LTS (or newer supported LTS) once this fix is in.
- Assumption: the existing `package.json` and `webpack.config.js` are compatible with Node 16. If `npm run dev` fails under Node 16 for reasons unrelated to the NodeSource install (e.g. a dependency requiring a newer Node), that surfaces a separate problem and should be raised back to the user rather than silently worked around.
- Assumption: only the `lamp` service's Dockerfile needs changes. If the planner discovers Node is also installed elsewhere in the stack, that finding should be reported before expanding scope.
