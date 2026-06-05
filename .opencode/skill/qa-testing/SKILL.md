---
name: qa-testing
description: Full QA validation protocol for smestaj. Use whenever work needs to be verified — running unit tests, syntax checks, lint, frontend builds, or end-to-end curl validation against the running stack. Triggers on phrases like "run tests", "validate", "verify it works", "QA this", "check the endpoint", "phpunit", "curl the site", or when finishing any implementation that touches routes, entities, migrations, Twig templates, or frontend assets. Everything runs inside Docker — never on the host.
---

# QA Testing Protocol — smestaj

You validate changes against the real, running stack. You do not "look at the code and conclude it works" — you execute tests and hit endpoints. A task is not done until this protocol is green.

Announce at the top of your response: `[QA] Running validation`.

---

## 0. Docker-only — non-negotiable

Every command below runs through `docker exec smestaj-app <cmd>` (or `docker exec smestaj-mysql <cmd>` for DB-client commands like `mysqldump` / `mariadb`). Never on the host. No `php`, `composer`, `npm`, `npx`, `node`, `mariadb`, `mysql`, or `curl` on the host machine. If a dependency is missing inside a container, install it **inside the container**; remove it after use unless the project needs it persistently.

When an existing service does not provide a needed tool, ephemeral containers are allowed:

```bash
docker run --rm <image> <cmd>
```

Cleanup is mandatory: `--rm` on the container, and `docker rmi <image>` for any image pulled solely for the task.

### Services

- `smestaj-app` — PHP 8.4+ + Apache + Node/npm (project root mounted at `/var/www/html`). Spec-canonical exec target for all PHP / Composer / npm / `bin/console` commands.
- `smestaj-mysql` — MariaDB 10, root password `vlada123!!!`, database `smestaj`. Use this container directly for DB-client commands (`mysqldump`, `mariadb`, `mysql`).
- `mailcatcher` — SMTP on `9503`, UI on `http://localhost:9504`.

---

## 1. Static checks

Run these **on every change**, in this order. Stop at the first failure and report it.

### 1.1 PHP syntax check on touched files

```bash
docker exec smestaj-app php -l src/SiteBundle/Path/To/File.php
```

### 1.2 PHP code style (if php-cs-fixer is available)

```bash
docker exec smestaj-app php vendor/bin/php-cs-fixer fix --dry-run --diff
```

If `php-cs-fixer` is not installed, skip this step and note it in the report — do not install it just to satisfy QA.

---

## 2. Unit + integration tests

### 2.1 PHPUnit

```bash
docker exec smestaj-app ./bin/simple-phpunit
```

Configuration: `phpunit.xml.dist` at project root. Bootstrap: `app/autoload.php`.

Capture: total tests, assertions, failures, errors, skipped. Any failure or error = fail. Skipped tests must be accounted for — don't accept silent skips on paths you just touched.

### 2.2 Scoped runs during iteration

While debugging a specific failure:

```bash
docker exec smestaj-app ./bin/simple-phpunit --filter <TestClassName>
docker exec smestaj-app ./bin/simple-phpunit tests/SiteBundle/Path/To/SpecificTest.php
```

Always re-run the **full** suite before declaring green.

---

## 3. Cache + Symfony sanity

```bash
docker exec smestaj-app php bin/console cache:clear --no-warmup
docker exec smestaj-app php bin/console cache:warmup
```

A cache failure usually means a wiring/config error. Treat it as fail.

Optional drill-downs (useful when investigating a failure, not required for green):

```bash
docker exec smestaj-app php bin/console debug:router
docker exec smestaj-app php bin/console debug:container <service-id>
docker exec smestaj-app php bin/console debug:autowiring
```

---

## 4. Frontend build (if frontend was touched)

```bash
docker exec smestaj-app npm run dev
```

Build failures = fail. Treat warnings about unresolved imports or missing assets as failures too.

For production-mode verification (only when explicitly requested):

```bash
docker exec smestaj-app npm run build
```

---

## 5. JS routes / translations re-dump

If any route, locale, or JS-visible translation was added/changed/removed during Phase 2, verify both dumps ran:

```bash
docker exec smestaj-app php bin/console fos:js-routing:dump
docker exec smestaj-app php bin/console bazinga:js-translation:dump
```

These regenerate `web/js/fos_js_routes.json` (FOSJsRouting) and the Bazinga translation files respectively. If the executor forgot them, run them and note the omission in the report — but `qa` is still allowed to call them (they are build steps, not source edits).

---

## 6. End-to-end validation (curl, inside the container)

**Never run `curl` on the host.** Use PHP's curl functions via `docker exec smestaj-app php -r "..."`.

### 6.1 Port reference

| Where | URL |
|---|---|
| Site from **inside** the `smestaj-app` container (preferred for validation) | `http://localhost` |
| Site from host (manual checks only) | `http://localhost:9505` |
| Mailcatcher (email inspection) | `http://localhost:9504` |
| DB from host | `127.0.0.1:9501` |
| DB from inside containers | `mysql:3306` |

### 6.2 GET

```bash
docker exec smestaj-app php -r "
\$c = curl_init('http://localhost/<route>');
curl_setopt(\$c, CURLOPT_RETURNTRANSFER, true);
curl_setopt(\$c, CURLOPT_FOLLOWLOCATION, true);
\$body = curl_exec(\$c);
\$code = curl_getinfo(\$c, CURLINFO_HTTP_CODE);
echo \"HTTP \$code\n\".substr(\$body, 0, 800).\"\n\";
"
```

### 6.3 Authenticated session flow (form-login)

smestaj uses session-based auth. Capture the session cookie on login, replay it on the protected route:

```bash
docker exec smestaj-app php -r "
\$jar = tempnam(sys_get_temp_dir(), 'cj');

// Login POST
\$c = curl_init('http://localhost/login_check');
curl_setopt_array(\$c, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => http_build_query(['_username' => 'user@example.com', '_password' => 'secret']),
  CURLOPT_COOKIEJAR => \$jar,
  CURLOPT_COOKIEFILE => \$jar,
]);
curl_exec(\$c);

// Protected GET
\$c = curl_init('http://localhost/<protected-route>');
curl_setopt_array(\$c, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_COOKIEJAR => \$jar,
  CURLOPT_COOKIEFILE => \$jar,
]);
\$body = curl_exec(\$c);
\$code = curl_getinfo(\$c, CURLINFO_HTTP_CODE);
echo \"HTTP \$code\n\".substr(\$body, 0, 800).\"\n\";
unlink(\$jar);
"
```

Adapt the login URL and field names (`_username` / `_password`) to whatever `security.yaml` declares.

### 6.4 POST / PUT / DELETE

Same pattern: set `CURLOPT_CUSTOMREQUEST` or `CURLOPT_POST => true`, `CURLOPT_POSTFIELDS => http_build_query([...])` (or JSON-encoded body with `Content-Type: application/json`), and reuse the cookie jar for authenticated calls.

### 6.5 What to check

For every route the change touches:

- **HTTP status** matches the contract (200 on success; 302 on redirect; 400/401/403/404/422/500 on the right failure paths).
- **Response body** shape matches the documented contract (HTML markers for pages; JSON shape for API endpoints).
- **Side effects** — DB row created? Email queued to Mailcatcher? File written under `web/uploads/`? Verify with a follow-up SQL query or a visit to `http://localhost:9504`.
- **Auth boundary** — same protected route without session returns 302 to login (or 401/403 for API routes).

---

## 7. Database safety (when migrations are involved)

### 7.1 Backup — mandatory before every migration on the dev DB

```bash
docker exec smestaj-mysql sh -c "mysqldump -u root -p'vlada123!!!' smestaj" > backup_$(date +%Y%m%d_%H%M%S).sql
```

### 7.2 Status check before migrating

```bash
docker exec smestaj-app php bin/console doctrine:migrations:status
```

If "executed migrations = 0" **and** the DB is non-empty → **STOP**. This is a tracking-table mismatch and running `migrate` will destroy data. Report it; do not proceed.

### 7.3 Migrate

```bash
docker exec smestaj-app php bin/console doctrine:migrations:migrate --no-interaction
```

Re-run phpunit afterwards — schema changes often break fixtures.

### 7.4 Read-only inspection

```bash
docker exec smestaj-mysql mariadb -u root -p'vlada123!!!' smestaj -e "SELECT COUNT(*) FROM ad;"
```

---

## 8. Reporting

Line-report each step as you go:

```
[QA] php -l (3 files): OK
[QA] php-cs-fixer --dry-run: OK (or SKIPPED — tool unavailable)
[QA] phpunit: PASS (N tests, 0 failures, 0 errors)
[QA] cache:clear + warmup: OK
[QA] npm run dev: OK
[QA] curl GET /listing/123: 200
[QA] curl GET /admin/dashboard (no session): 302 → /login
[QA] curl GET /admin/dashboard (authenticated): 200
```

Final line, one of:

- `[QA] ALL GREEN` — every check above passed.
- `[QA] FAILURES: <short list>` — stop, do not claim success, do not attempt deep fixes. Trivial typos in test setup are fair game; anything else goes back to the author.

---

## 9. Hard rules

- **No host commands.** Ever. Not even for a quick sanity check.
- **No `curl` binary on host** — use PHP's curl functions inside the `smestaj-app` container.
- **No installing deps to make a test pass** — if a test needs a missing dep, that's a blocker, report it.
- **No editing source files to force green** — verify, don't fix.
- **No skipping the full suite** — scoped runs are for iteration only; green requires the full `phpunit` run.
- **No "looks right to me"** — if it wasn't executed, it wasn't tested.
- **Ephemeral containers must be cleaned up** (`--rm` + `docker rmi`).
- **Do not delete `IMPLEMENTATION_PLAN.md`** — that's Phase 4 (reviewer).
