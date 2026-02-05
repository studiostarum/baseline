# Baseline

Internal Laravel + Vue starter kit with **admin CMS** (users, roles, settings), **Stripe billing** (Cashier), and **i18n** (locales and translations).

## Quick start

```bash
./setup
```

Or `composer run setup`. Use `./setup` to silence PHP/Composer deprecation notices on the console.

This runs the combined Baseline script: it installs PHP deps, then shows the **feature selection** (admin, billing, languages — **space** to toggle, **enter** to confirm). After you confirm, it installs the rest (key, migrations, wayfinder, npm, build) and seeds roles when admin is enabled. Then seed roles and optionally assign the first admin:

```bash
composer run setup:full
```

Or after `composer run setup`, run `php artisan db:seed --class=RolesAndPermissionsSeeder --force` then `php artisan baseline:install` to assign an admin by email.

To change features later: edit `BASELINE_ADMIN`, `BASELINE_BILLING`, and `BASELINE_LOCALES` in `.env`, then run `php artisan config:clear`. For non-interactive installs (e.g. CI), set `BASELINE_SETUP_NON_INTERACTIVE=1` before running setup.

**Client projects:** When handing off to a client, run `php artisan baseline:strip`. It locks the current feature flags into config and strips `BASELINE_*` from `.env` so clients cannot re-enable features.

## First admin user

See [docs/admin-panel.md](docs/admin-panel.md) for seeding roles and assigning a role to a user (e.g. via tinker or `php artisan baseline:install`).

## Documentation

- [docs/README.md](docs/README.md) — Index of all Baseline docs

## Develop

```bash
composer run dev
```

For billing (Stripe): set `STRIPE_*` and `STRIPE_WEBHOOK_SECRET` in `.env`, and run `stripe listen` when developing (e.g. `npm run start` runs Stripe listen with the dev servers). See [docs/billing.md](docs/billing.md).

## Test

```bash
composer run test
```

Or `php artisan test`.
