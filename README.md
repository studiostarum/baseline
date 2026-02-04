# Baseline

Internal Laravel + Vue starter kit with **admin CMS** (users, roles, settings), **Stripe billing** (Cashier), and **i18n** (locales and translations).

## Quick start

```bash
composer run setup
composer run setup:full
```

Or after `composer run setup`, run `php artisan baseline:install` to seed roles and optionally assign the first admin by email.

To choose which features to include (admin, billing, languages), run:

```bash
php artisan baseline:configure
```

Use the interactive menu: **space** to toggle an option, **enter** to confirm. The command updates `.env` and clears config.

**Client projects:** When handing off to a client, run `php artisan baseline:strip` after configuring. It removes the configure command, locks the current feature flags into config, and strips `BASELINE_*` from `.env` so clients cannot re-enable features.

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
