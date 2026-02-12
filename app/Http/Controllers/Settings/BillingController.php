<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\AuthenticationException;

class BillingController extends Controller
{
    /**
     * Show the user's billing settings page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $stripeConfigured = $this->isStripeConfigured();
        $error = null;

        $subscription = null;
        $subscriptionData = null;
        $paymentMethod = null;
        $invoices = [];

        if ($stripeConfigured) {
            try {
                $subscription = $user->subscription('default');

                if ($subscription) {
                    $stripePriceId = $subscription->stripe_price
                        ?? $subscription->items->first()?->stripe_price;
                    $planType = $this->planTypeFromPriceId($stripePriceId);

                    $subscriptionData = [
                        'name' => $subscription->type,
                        'status' => $subscription->stripe_status,
                        'planType' => $planType,
                        'onTrial' => $subscription->onTrial(),
                        'trialEndsAt' => $subscription->trial_ends_at?->toIso8601String(),
                        'endsAt' => $subscription->ends_at?->toIso8601String(),
                        'canceledAt' => $subscription->canceled() ? $subscription->ends_at?->toIso8601String() : null,
                        'onGracePeriod' => $subscription->onGracePeriod(),
                    ];
                }

                $defaultPaymentMethod = $user->defaultPaymentMethod();

                if (! $defaultPaymentMethod) {
                    $paymentMethods = $user->paymentMethods();
                    $defaultPaymentMethod = $paymentMethods->first();
                }

                if ($defaultPaymentMethod && $defaultPaymentMethod->card) {
                    $paymentMethod = [
                        'brand' => $defaultPaymentMethod->card->brand ?? null,
                        'last4' => $defaultPaymentMethod->card->last4 ?? null,
                        'expMonth' => $defaultPaymentMethod->card->exp_month ?? null,
                        'expYear' => $defaultPaymentMethod->card->exp_year ?? null,
                    ];
                }

                if ($user->hasStripeId()) {
                    try {
                        $invoices = collect($user->invoices(false, [
                            'expand' => ['data.lines.data.price'],
                        ]))->map(function ($invoice) {
                            $planType = $this->getInvoicePlanType($invoice);

                            return [
                                'id' => $invoice->id,
                                'number' => $invoice->number,
                                'date' => $invoice->date()->toIso8601String(),
                                'total' => $invoice->rawTotal(),
                                'currency' => strtoupper($invoice->currency ?? config('cashier.currency', 'usd')),
                                'status' => $invoice->status,
                                'planType' => $planType,
                                'invoicePdfUrl' => $invoice->invoice_pdf,
                            ];
                        })->take(20)->values()->all();
                    } catch (\Exception $e) {
                        $invoices = [];
                    }
                }
            } catch (ApiErrorException $e) {
                $error = $this->getUserFriendlyErrorMessage($e);
            }
        } else {
            $error = 'Stripe payment processing is not configured. Please contact support for assistance.';
        }

        return Inertia::render('settings/Billing', [
            'subscription' => $subscriptionData,
            'paymentMethod' => $paymentMethod,
            'invoices' => $invoices,
            'hasStripeCustomer' => $user->hasStripeId(),
            'stripeConfigured' => $stripeConfigured,
            'defaultPriceId' => $this->defaultPriceId(),
            'defaultPriceIdAnnual' => $this->defaultPriceIdAnnual(),
            'currency' => strtoupper(config('cashier.currency', 'usd')),
            'currencyLocale' => config('cashier.currency_locale', 'en'),
            'error' => $error ?? $request->session()->get('error'),
        ]);
    }

    /**
     * Redirect the user to the Stripe Customer Portal.
     */
    public function portal(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $this->isStripeConfigured()) {
            return back()->with('error', 'Stripe payment processing is not configured. Please contact support for assistance.');
        }

        try {
            // Ensure user has a Stripe customer ID
            if (! $user->hasStripeId()) {
                $user->createAsStripeCustomer();
            }

            return $user->redirectToBillingPortal(route('billing.show'));
        } catch (ApiErrorException $e) {
            return back()->with('error', $this->getUserFriendlyErrorMessage($e));
        }
    }

    /**
     * Redirect the user to a Stripe Checkout session for a new subscription.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $priceId = $request->input('price_id') ?: $this->defaultPriceId();

        if (! $priceId) {
            Log::warning('Billing checkout: no price_id in request and no default configured');

            return back()->with('error', 'Please select a plan or set STRIPE_PRICE_ID in your environment.');
        }

        $allowedPriceIds = array_filter([
            $this->defaultPriceId(),
            $this->defaultPriceIdAnnual(),
        ]);
        if ($allowedPriceIds && ! in_array($priceId, $allowedPriceIds, true)) {
            return back()->with('error', 'Please select a valid plan.');
        }

        if (! $this->isStripeConfigured()) {
            Log::warning('Billing checkout: Stripe not configured');

            return back()->with('error', 'Stripe payment processing is not configured. Please contact support for assistance.');
        }

        try {
            if (! $user->hasStripeId()) {
                $user->createAsStripeCustomer();
            }

            $trialDays = $this->trialDaysForPriceId($priceId);
            $builder = $user->newSubscription('default', $priceId);

            if ($trialDays > 0) {
                $builder->trialDays($trialDays);
            }

            return $builder
                ->checkout([
                    'success_url' => route('billing.show').'?checkout=success',
                    'cancel_url' => route('billing.show').'?checkout=cancel',
                ])
                ->redirect();
        } catch (ApiErrorException $e) {
            Log::error('Billing checkout: Stripe error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->with('error', $this->getUserFriendlyErrorMessage($e));
        }
    }

    /**
     * Check if Stripe is properly configured.
     */
    protected function isStripeConfigured(): bool
    {
        return ! empty(config('cashier.key')) && ! empty(config('cashier.secret'));
    }

    /**
     * Default Stripe price ID for new subscriptions (monthly).
     */
    protected function defaultPriceId(): ?string
    {
        return config('services.stripe.price_id') ?: null;
    }

    /**
     * Stripe price ID for annual subscriptions (optional).
     */
    protected function defaultPriceIdAnnual(): ?string
    {
        return config('services.stripe.price_id_annual') ?: null;
    }

    /**
     * Get plan type (monthly or annual) for an invoice from its line item price.
     */
    protected function getInvoicePlanType(\Laravel\Cashier\Invoice $invoice): ?string
    {
        $stripeInvoice = $invoice->asStripeInvoice();
        $planType = $this->planTypeFromInvoiceLines($stripeInvoice);

        if ($planType === null) {
            try {
                $invoice->refresh(['lines.data.price']);
                $stripeInvoice = $invoice->asStripeInvoice();
                $planType = $this->planTypeFromInvoiceLines($stripeInvoice);
            } catch (\Throwable) {
                // Leave planType null
            }
        }

        return $planType;
    }

    /**
     * Extract plan type from a Stripe invoice's first line item price.
     */
    protected function planTypeFromInvoiceLines(\Stripe\Invoice $stripeInvoice): ?string
    {
        $lines = $stripeInvoice->lines->data ?? [];
        $firstLine = $lines[0] ?? null;

        if (! $firstLine || ! isset($firstLine->price)) {
            return null;
        }

        $priceId = is_object($firstLine->price) ? $firstLine->price->id : $firstLine->price;

        return $this->planTypeFromPriceId($priceId);
    }

    /**
     * Trial days for the given Stripe price ID (monthly: 3, annual: 7).
     */
    protected function trialDaysForPriceId(string $priceId): int
    {
        $planType = $this->planTypeFromPriceId($priceId);
        if ($planType === 'annual') {
            return config('services.stripe.trial_days_annual', 7);
        }
        if ($planType === 'monthly') {
            return config('services.stripe.trial_days_monthly', 3);
        }

        return 0;
    }

    /**
     * Derive plan type (monthly or annual) from Stripe price ID.
     */
    protected function planTypeFromPriceId(?string $stripePriceId): ?string
    {
        if (! $stripePriceId) {
            return null;
        }
        if ($stripePriceId === config('services.stripe.price_id_annual')) {
            return 'annual';
        }
        if ($stripePriceId === config('services.stripe.price_id')) {
            return 'monthly';
        }

        return null;
    }

    /**
     * Get a user-friendly error message from a Stripe exception.
     */
    protected function getUserFriendlyErrorMessage(ApiErrorException $e): string
    {
        if ($e instanceof AuthenticationException) {
            return 'Payment processing is not properly configured. Please contact support for assistance.';
        }

        return 'An error occurred while processing your request. Please try again or contact support if the problem persists.';
    }
}
