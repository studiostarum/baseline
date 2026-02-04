<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

if (config('baseline.features.locales', true)) {
    Route::get('/locale/{locale}', function (string $locale) {
        $available = config('locales.available', []);
        if (in_array($locale, $available, true)) {
            session(['locale' => $locale]);

            return redirect()->back()->cookie(
                'locale',
                $locale,
                (int) config('locales.cookie_minutes', 60 * 24 * 365),
                '/',
                null,
                request()->secure(),
                true,
                false,
                'lax',
            );
        }

        return redirect()->back();
    })->name('locale.switch')->where('locale', 'en|nl');
}

Route::get('/', function () {
    return Inertia::render('Home', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::post('stripe/webhook', WebhookController::class)
    ->name('cashier.webhook');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('forbidden', function () {
    $response = Inertia::render('errors/Forbidden', [
        'message' => null,
    ])->toResponse(request());
    $response->setStatusCode(403);

    return $response;
})->name('forbidden');

Route::get('auth/{provider}', [App\Http\Controllers\SocialAuthController::class, 'redirect'])
    ->name('social.redirect');

Route::get('auth/{provider}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])
    ->name('social.callback');

require __DIR__.'/settings.php';
