# Baseline

Internal Laravel + Vue starter kit with **admin CMS** (users, roles, settings), **Stripe billing** (Cashier), and **i18n** (locales and translations). Stack: Laravel 12, Inertia (Vue 3), Tailwind CSS 4, Wayfinder.

**Requirements:** PHP 8.2+, Node.js, Composer.

## Quick start

```bash
./setup
```

Or `composer run setup`. Use `./setup` to silence PHP/Composer deprecation notices on the console.

The setup script installs PHP deps, then shows **feature selection** (admin, billing, languages — **space** to toggle, **enter** to confirm). After confirm, it runs key generation, migrations, Wayfinder, npm install & build, and seeds roles when admin is enabled.

To seed roles and optionally assign the first admin:

```bash
composer run setup:full
```

Or after `composer run setup`: `php artisan db:seed --class=RolesAndPermissionsSeeder --force` then `php artisan baseline:install` to assign an admin by email.

**Feature flags:** Edit `BASELINE_ADMIN`, `BASELINE_BILLING`, and `BASELINE_LOCALES` in `.env`, then `php artisan config:clear`. Non-interactive (e.g. CI): set `BASELINE_SETUP_NON_INTERACTIVE=1` before setup.

**Client handoff:** Run `php artisan baseline:strip` to lock feature flags into config and remove `BASELINE_*` from `.env`.

## First admin user

[docs/admin-panel.md](docs/admin-panel.md) — seeding roles and assigning a role (tinker or `php artisan baseline:install`).

## Documentation

- [docs/README.md](docs/README.md) — index of all docs  
- [docs/admin-panel.md](docs/admin-panel.md) — admin & roles  
- [docs/billing.md](docs/billing.md) — Stripe / Cashier  
- [docs/languages.md](docs/languages.md) — i18n  
- [docs/contact-form-email.md](docs/contact-form-email.md) — contact form  
- [docs/production-admin-setup.md](docs/production-admin-setup.md) — production admin  
- [docs/vercel-deployment.md](docs/vercel-deployment.md) — Vercel deploy

## Develop

```bash
composer run dev
```

Starts PHP server, queue, Pail (logs), and Vite. For Stripe: set `STRIPE_*` and `STRIPE_WEBHOOK_SECRET` in `.env` and run `stripe listen` (e.g. `npm run start`). See [docs/billing.md](docs/billing.md).

## Lint

```bash
composer run lint        # PHP (Pint)
composer run test:lint   # Pint check only
npm run lint             # ESLint
npm run format           # Prettier
```

## Test

```bash
composer run test
```

Or `php artisan test`.
