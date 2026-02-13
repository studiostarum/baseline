<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;

class BillingController extends Controller
{
    private const BILLING_PERMISSIONS = ['manage-users', 'manage-roles', 'manage-settings'];

    /**
     * Display the billing dashboard.
     */
    public function index(): Response
    {
        $user = request()->user();
        if (! $user->hasRole('super-admin') && ! $user->hasAnyPermission(self::BILLING_PERMISSIONS)) {
            abort(403, __('errors.unauthorized'));
        }

        $activeSubscriptions = Subscription::query()
            ->where('stripe_status', 'active')
            ->count();

        $trialingSubscriptions = Subscription::query()
            ->where('stripe_status', 'trialing')
            ->count();

        $canceledSubscriptions = Subscription::query()
            ->where('stripe_status', 'canceled')
            ->count();

        $totalSubscriptions = Subscription::count();

        $recentSubscriptions = Subscription::query()
            ->with('user:id,name,email')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Subscription $subscription) => [
                'id' => $subscription->id,
                'user' => $subscription->user ? [
                    'id' => $subscription->user->id,
                    'name' => $subscription->user->name,
                    'email' => $subscription->user->email,
                ] : null,
                'type' => $subscription->type,
                'status' => $subscription->stripe_status,
                'createdAt' => $subscription->created_at->toIso8601String(),
            ]);

        return Inertia::render('admin/billing/Index', [
            'stats' => [
                'active' => $activeSubscriptions,
                'trialing' => $trialingSubscriptions,
                'canceled' => $canceledSubscriptions,
                'total' => $totalSubscriptions,
            ],
            'recentSubscriptions' => $recentSubscriptions,
        ]);
    }

    /**
     * Display users with subscription info.
     */
    public function users(Request $request): Response
    {
        $requestUser = $request->user();
        if (! $requestUser->hasRole('super-admin') && ! $requestUser->hasRole('moderator') && ! $requestUser->hasAnyPermission(self::BILLING_PERMISSIONS)) {
            abort(403, __('errors.unauthorized'));
        }

        $query = User::query()->with('subscriptions');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'none') {
                $query->doesntHave('subscriptions');
            } else {
                $query->whereHas('subscriptions', function ($q) use ($status): void {
                    $q->where('stripe_status', $status);
                });
            }
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $users = $query->paginate(10)->withQueryString();

        $usersData = $users->through(function (User $user) {
            $subscription = $user->subscription('default');

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'createdAt' => $user->created_at->toIso8601String(),
                'subscription' => $subscription ? [
                    'status' => $subscription->stripe_status,
                    'onTrial' => $subscription->onTrial(),
                    'onGracePeriod' => $subscription->onGracePeriod(),
                ] : null,
            ];
        });

        return Inertia::render('admin/billing/Users', [
            'users' => $usersData,
            'filters' => $request->only(['search', 'status', 'sort', 'direction']),
        ]);
    }

    /**
     * Display a user's billing details.
     */
    public function show(User $user): Response
    {
        $requestUser = request()->user();
        if (! $requestUser->hasRole('super-admin') && ! $requestUser->hasRole('moderator') && ! $requestUser->hasAnyPermission(self::BILLING_PERMISSIONS)) {
            abort(403, __('errors.unauthorized'));
        }

        $subscriptions = $user->subscriptions->map(function (Subscription $subscription) {
            $priceId = $subscription->stripe_price ?? $subscription->items->first()?->stripe_price;
            $typeDisplayName = $priceId ? $this->resolvePriceProductName($priceId) : null;

            $renewsAt = null;
            try {
                $periodEnd = $subscription->currentPeriodEnd();
                $renewsAt = $periodEnd?->toIso8601String();
            } catch (\Throwable) {
                // Stripe API may be unavailable
            }

            return [
                'id' => $subscription->id,
                'type' => $subscription->type,
                'typeDisplayName' => $typeDisplayName ?? $subscription->type,
                'status' => $subscription->stripe_status,
                'onTrial' => $subscription->onTrial(),
                'trialEndsAt' => $subscription->trial_ends_at?->toIso8601String(),
                'renewsAt' => $renewsAt,
                'endsAt' => $subscription->ends_at?->toIso8601String(),
                'onGracePeriod' => $subscription->onGracePeriod(),
                'createdAt' => $subscription->created_at->toIso8601String(),
            ];
        });

        $invoices = [];
        if ($user->hasStripeId()) {
            try {
                $invoices = collect($user->invoices())->map(fn ($invoice) => [
                    'id' => $invoice->id,
                    'number' => $invoice->number,
                    'date' => $invoice->date()->toIso8601String(),
                    'total' => $invoice->total(),
                    'status' => $invoice->status,
                    'invoicePdfUrl' => $invoice->invoice_pdf,
                ])->take(20)->values()->all();
            } catch (\Exception $e) {
                $invoices = [];
            }
        }

        $paymentMethod = null;
        if ($user->hasStripeId()) {
            try {
                $defaultPaymentMethod = $user->defaultPaymentMethod();
                if (! $defaultPaymentMethod) {
                    $defaultPaymentMethod = $user->paymentMethods()->first();
                }
                if ($defaultPaymentMethod && $defaultPaymentMethod->card) {
                    $paymentMethod = [
                        'brand' => $defaultPaymentMethod->card->brand ?? null,
                        'last4' => $defaultPaymentMethod->card->last4 ?? null,
                        'expMonth' => $defaultPaymentMethod->card->exp_month ?? null,
                        'expYear' => $defaultPaymentMethod->card->exp_year ?? null,
                    ];
                }
            } catch (\Exception $e) {
                $paymentMethod = null;
            }
        }

        return Inertia::render('admin/billing/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'stripeId' => $user->stripe_id,
                'createdAt' => $user->created_at->toIso8601String(),
            ],
            'subscriptions' => $subscriptions,
            'invoices' => $invoices,
            'paymentMethod' => $paymentMethod,
        ]);
    }

    /**
     * Resolve Stripe price ID to the product name from Stripe.
     *
     * @return string|null Product name, or null on failure
     */
    private function resolvePriceProductName(string $priceId): ?string
    {
        try {
            $price = Cashier::stripe()->prices->retrieve($priceId, [
                'expand' => ['product'],
            ]);

            $product = $price->product;
            if (is_object($product) && isset($product->name)) {
                return $product->name;
            }

            return null;
        } catch (\Throwable) {
            return null;
        }
    }
}
