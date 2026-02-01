<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $user,
                'can_access_admin' => $user?->hasAnyRole(['super-admin', 'admin']) ?? false,
            ],
            'locale' => app()->getLocale(),
            'locales' => collect(config('locales.available', []))
                ->mapWithKeys(fn (string $code) => [$code => config("locales.labels.{$code}", $code)])
                ->all(),
            'translations' => (function () {
                $path = lang_path(app()->getLocale().'.json');
                if (file_exists($path)) {
                    return json_decode(file_get_contents($path), true) ?? [];
                }

                return [];
            })(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
