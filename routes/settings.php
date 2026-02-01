<?php

use App\Http\Controllers\Settings\BillingController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('settings/profile/social/{provider}/link', [SocialAuthController::class, 'link'])
        ->name('social.link');
    Route::get('settings/profile/social/{provider}/unlink', [SocialAuthController::class, 'showUnlinkConfirm'])
        ->middleware(['ensure.user.has.password', 'password.confirm'])
        ->name('social.unlink.confirm');
    Route::delete('settings/profile/social/{provider}/unlink', [SocialAuthController::class, 'unlink'])
        ->middleware('password.confirm')
        ->name('social.unlink');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('user-password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    Route::get('settings/billing', [BillingController::class, 'show'])
        ->name('billing.show');

    Route::post('settings/billing/portal', [BillingController::class, 'portal'])
        ->name('billing.portal');

    Route::post('settings/billing/checkout', [BillingController::class, 'checkout'])
        ->name('billing.checkout');
});
