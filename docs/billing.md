# Billing (Stripe)

Baseline uses [Laravel Cashier](https://laravel.com/docs/billing) for Stripe subscriptions and billing.

## Environment variables

Set these in `.env`:

| Variable | Description |
|----------|-------------|
| `STRIPE_KEY` | Stripe publishable key |
| `STRIPE_SECRET` | Stripe secret key |
| `STRIPE_WEBHOOK_SECRET` | Webhook signing secret (from `stripe listen` locally, or Stripe Dashboard in production) |
| `STRIPE_PRICE_ID` | Stripe Price ID for **monthly** checkout (create in Stripe Dashboard → Products) |
| `STRIPE_PRICE_ID_ANNUAL` | Optional. Stripe Price ID for **yearly** checkout. If unset, only the monthly plan is offered. |
| `CASHIER_CURRENCY` | e.g. `usd` |
| `CASHIER_CURRENCY_LOCALE` | e.g. `en` |

## Local webhooks

To receive Stripe webhooks locally (e.g. subscription updated, payment succeeded):

```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

Use the webhook signing secret it prints as `STRIPE_WEBHOOK_SECRET` in `.env`.

The `npm run start` script (if configured) runs the Laravel dev server, Vite, and `stripe listen` together.

## Price IDs

Create a product and one or two recurring prices in the [Stripe Dashboard](https://dashboard.stripe.com/products) (e.g. monthly and yearly). Set the **monthly** price ID as `STRIPE_PRICE_ID` in `.env`; it is used when users choose the monthly plan or when no yearly option is configured. Optionally set a **yearly** price ID as `STRIPE_PRICE_ID_ANNUAL` to offer a monthly/yearly toggle on the settings billing page and home pricing section.
