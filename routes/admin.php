<?php

use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('users', UserController::class);
Route::resource('roles', RoleController::class);

Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
Route::get('billing/users', [BillingController::class, 'users'])->name('billing.users');
Route::get('billing/users/{user}', [BillingController::class, 'show'])->name('billing.show');
