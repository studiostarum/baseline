<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            if (config('baseline.features.admin', true)) {
                Route::middleware(['web', 'auth', 'verified', 'admin'])
                    ->prefix('admin')
                    ->name('admin.')
                    ->group(base_path('routes/admin.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        $middleware->web(append: [
            SetLocale::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'ensure.user.has.password' => \App\Http\Middleware\EnsureUserHasPassword::class,
            'password.confirm.modal' => \App\Http\Middleware\RequirePasswordModal::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            $response = Inertia::render('errors/Forbidden', [
                'message' => $e->getMessage(),
            ])->toResponse($request);
            $response->setStatusCode(403);

            return $response;
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $translations = config('baseline.features.locales', true)
                ? (function () {
                    $path = lang_path(app()->getLocale().'.json');

                    return file_exists($path)
                        ? (json_decode((string) file_get_contents($path), true) ?? [])
                        : [];
                })()
                : [];

            $response = Inertia::render('errors/NotFound', [
                'message' => null,
                'translations' => $translations,
                'auth' => [
                    'user' => $request->user(),
                ],
            ])->toResponse($request);
            $response->setStatusCode(404);

            return $response;
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 403) {
                return null;
            }
            $response = Inertia::render('errors/Forbidden', [
                'message' => $e->getMessage(),
            ])->toResponse($request);
            $response->setStatusCode(403);

            return $response;
        });
    })->create();
