<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, config('locales.available', []), true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('locale.switch')->where('locale', 'en|nl');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::post('stripe/webhook', WebhookController::class)
    ->name('cashier.webhook');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('auth/{provider}', [App\Http\Controllers\SocialAuthController::class, 'redirect'])
    ->name('social.redirect');

Route::get('auth/{provider}/callback', [App\Http\Controllers\SocialAuthController::class, 'callback'])
    ->name('social.callback');

require __DIR__.'/settings.php';
