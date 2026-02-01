<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Subscription;

class BillingController extends Controller
{
    /**
     * Display the billing dashboard.
     */
    public function index(): Response
    {
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
        $subscriptions = $user->subscriptions->map(fn (Subscription $subscription) => [
            'id' => $subscription->id,
            'type' => $subscription->type,
            'status' => $subscription->stripe_status,
            'onTrial' => $subscription->onTrial(),
            'trialEndsAt' => $subscription->trial_ends_at?->toIso8601String(),
            'endsAt' => $subscription->ends_at?->toIso8601String(),
            'onGracePeriod' => $subscription->onGracePeriod(),
            'createdAt' => $subscription->created_at->toIso8601String(),
        ]);

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
        if ($user->hasDefaultPaymentMethod()) {
            try {
                $defaultPaymentMethod = $user->defaultPaymentMethod();
                $paymentMethod = [
                    'brand' => $defaultPaymentMethod->card->brand ?? null,
                    'last4' => $defaultPaymentMethod->card->last4 ?? null,
                    'expMonth' => $defaultPaymentMethod->card->exp_month ?? null,
                    'expYear' => $defaultPaymentMethod->card->exp_year ?? null,
                ];
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
}
