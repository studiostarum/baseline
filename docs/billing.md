# Billing (Stripe)

Baseline uses [Laravel Cashier](https://laravel.com/docs/billing) for Stripe subscriptions and billing.

## Environment variables

Set these in `.env`:

| Variable | Description |
|----------|-------------|
| `STRIPE_KEY` | Stripe publishable key |
| `STRIPE_SECRET` | Stripe secret key |
| `STRIPE_WEBHOOK_SECRET` | Webhook signing secret (from `stripe listen` locally, or Stripe Dashboard in production) |
| `STRIPE_PRICE_ID` | Stripe Price ID used for checkout (create in Stripe Dashboard → Products) |
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

Create a product and price in the [Stripe Dashboard](https://dashboard.stripe.com/products). Set the price’s ID (e.g. `price_xxx`) as `STRIPE_PRICE_ID` in `.env`. It is used when users hit “Subscribe” on the settings billing page for checkout.
