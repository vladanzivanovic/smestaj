# Agents Guide

## Project Overview

This is **Smestaj** (.checkout) — a Symfony 4.3+ marketplace web application for accommodation/property rental listings. Serbian language ("Smestaj" = Accommodation).

## Tech Stack

- **Backend**: PHP 8.4+, Symfony 7.2+, Doctrine ORM 3.6
- **Frontend**: jQuery 3.6, Bootstrap 3/4, SASS/SCSS, Webpack Encore
- **Database**: MySQL 5.7+ / MariaDB 10
- **Dev Environment**: Docker (Apache+PHP on :9505, MariaDB on :9501, Mailcatcher on :9503/:9504)

## Architecture

The app uses a **multi-bundle** Symfony structure with service-oriented architecture:

### Bundles

| Bundle | Purpose | Location |
|--------|---------|----------|
| **SiteBundle** | Public-facing site (listings, reservations, user profiles) | `src/SiteBundle/` |
| **AdminBundle** | Admin dashboard (product/user management) | `src/AdminBundle/` |
| **LogBundle** | Internal log viewer | `src/LogBundle/` |

### Layer Structure (SiteBundle)

```
Controller/          → Route handlers (including Api/ subdirectories for JSON endpoints)
Entity/              → Doctrine ORM entities (25 domain models)
Repository/          → Custom Doctrine repositories
Services/            → Business logic (organized by domain, e.g. Services/Ads/)
Handler/             → Request/operation handlers (AdsHandler, UserHandler, etc.)
EventListeners/      → Side-effect processing (emails, file uploads, sitemap)
Helper/              → Utility classes
Twig/                → Custom Twig extensions
Validators/          → Custom validation constraints
Resources/views/     → Twig templates
Resources/public/    → Frontend assets (JS, SASS, components)
Resources/config/    → Bundle config and routing
```

### AdminBundle Layer Structure

```
Controller/          → Admin route handlers (Products/, User/)
Parser/              → Request parsers (DataTable, Product, User)
Formatter/           → Response formatters
Handler/             → Auth handlers (login, entry point, access denied)
Resources/views/     → Admin Twig templates (Pages/, Components/, Macros/)
```

## Key Entities

- `User` — User accounts (bcrypt passwords, roles, OAuth)
- `Ads` — Property/accommodation listings
- `Reservation` / `UserReservation` — Booking system
- `Review` / `Stars` — Ratings
- `Category` — Listing categories
- `City` — Geographic locations
- `Tag` / `Adshastags` — Tagging system
- `AdsAdditionalInfo` — Extended property details
- `Media` — Uploaded images

## Configuration

- **App config**: `app/config/` (config.yml, security.yml, routing.yml, services.yml, parameters.yml)
- **Parameters**: `app/config/parameters.yml` (DB, mail, app-specific)
- **Doctrine extensions**: `app/config/doctrine_extension.yml` (Gedmo timestampable)
- **Image processing**: `app/config/liip_params.yml`
- **Security headers**: `app/config/nelmio_security.yaml`

## Frontend

### Build System

Webpack Encore with entry points:
- `js/admin/app` → AdminBundle JS
- `js/site/app` → SiteBundle JS
- `css/site/app` → Main site styles
- `css/site/user_profile` → User dashboard styles
- `css/admin/app` → Admin styles

Output goes to `web/build/`.

### Frontend JS Architecture

```
js/
├── Controller/      → Page/feature controllers
├── Handler/         → Event handlers
├── Mapper/          → Model/response mapping
├── Services/        → Reusable utilities
├── Validation/      → Form validation
├── Constants/       → Global constants
├── Filters/         → Data filtering
└── Components/      → Reusable UI components
```

### CSS

SASS source in `Resources/public/sass/` organized by `Pages/` and `Moduls/`.

## CRITICAL: All Commands Must Run Inside Docker

**Running any command directly on the host machine is FORBIDDEN.** Every command — PHP, Composer, npm, Symfony console, tests — must be executed inside the `smestaj-app` Docker container. The only exception is `docker compose` itself, which runs on the host to manage containers.

### Ensure Docker Is Running

Before executing any command inside the container, first verify that Docker containers are running:

```bash
docker ps --filter name=smestaj --format '{{.Names}} {{.Status}}'
```

If containers are not running or not listed, start them with:

```bash
docker compose up -d --build
```

### Executing Commands

```bash
# Prefix ALL commands with:
docker exec smestaj-app <command>

# Or open an interactive shell:
docker exec -it smestaj-app bash
```

### Local App URL

The app runs locally inside Docker and is exposed on port **9505**. When accessing the application via HTTP (e.g. for testing, curl, or browser), always use the port:

```
http://localhost:9505
```

Never use `http://localhost` without the port — it will not reach the app.

## Commands

All commands below must be run inside the `smestaj-app` container:

```bash
# Development
docker exec smestaj-app composer develop          # Full dev setup
docker exec smestaj-app npm run dev               # Dev build
docker exec smestaj-app npm run watch             # Watch mode
docker exec smestaj-app npm run dev-server        # Dev server with HMR

# Production
docker exec smestaj-app composer deploy           # Full production deploy
docker exec smestaj-app npm run build             # Production build

# Symfony
docker exec smestaj-app php bin/console                                    # Symfony CLI
docker exec smestaj-app php bin/console doctrine:migrations:migrate        # Run migrations
docker exec smestaj-app php bin/console assets:install                     # Install bundle assets
docker exec smestaj-app php bin/console fos:js-routing:dump                # Generate JS routes
docker exec smestaj-app php bin/console bazinga:js-translation:dump        # Generate JS translations

# Docker (only command that runs on host)
docker compose up -d      # Start dev environment

# Tests
docker exec smestaj-app ./vendor/bin/phpunit      # Run tests
```

## Conventions

- **Naming**: Doctrine underscore naming strategy (camelCase properties → snake_case columns)
- **DI**: Constructor injection, autowiring enabled in services.yml
- **Locale**: Primary locale is Serbian (`rs`), translations in YAML
- **Security**: Role-based (ROLE_ADMIN, ROLE_USER, ROLE_ADVANCED_USER), OAuth via Facebook/Google
- **Passwords**: Bcrypt with cost 4
- **Images**: Liip Imagine for dynamic filtering/resizing (thumbnails 400x400, categories 600x300, sliders 1920x1080)
- **Serialization**: JMS Serializer with annotations for API responses
- **Migrations**: Doctrine migrations in `app/DoctrineMigrations/`
- **Templates**: Twig with global base in `app/Resources/views/`, bundle-specific in each bundle's `Resources/views/`
- **Imports**: Every class used in a file MUST be declared with a `use` statement at the top. Always reference classes by their short name in code, never by FQCN. No dynamic class names or string-based service retrieval.

## External Integrations

- **OAuth**: HWI OAuth Bundle (Facebook, Google)
- **Email**: SwiftMailer (Mailcatcher in dev)
- **Error Tracking**: Sentry.io
- **SEO**: Presta Sitemap Bundle
- **Image Processing**: Liip Imagine Bundle

## Testing

PHPUnit with bootstrap via `app/autoload.php`. Tests in `tests/` directory, organized by bundle. Currently minimal test coverage.
