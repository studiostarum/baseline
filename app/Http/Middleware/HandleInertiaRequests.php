<?php

namespace App\Http\Middleware;

use App\Models\Setting;
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
            'name' => $this->appName(),
            'siteDescription' => $this->siteDescription(),
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $user,
                'can_access_admin' => $this->safeHasAnyRole($user, ['super-admin', 'admin', 'moderator']),
                'can_manage_users' => $this->safeHasPermissionTo($user, 'manage-users'),
                'can_manage_roles' => $this->safeHasPermissionTo($user, 'manage-roles'),
                'can_manage_settings' => $this->safeHasPermissionTo($user, 'manage-settings'),
            ],
            'locale' => app()->getLocale(),
            'locales' => config('baseline.features.locales', true)
                ? collect(config('locales.available', []))
                    ->mapWithKeys(fn (string $code) => [$code => config("locales.labels.{$code}", $code)])
                    ->all()
                : [],
            'translations' => config('baseline.features.locales', true)
                ? (function () {
                    $path = lang_path(app()->getLocale().'.json');
                    if (file_exists($path)) {
                        return json_decode(file_get_contents($path), true) ?? [];
                    }

                    return [];
                })()
                : [],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'showPasswordConfirmModal' => $request->boolean('confirm_password'),
            'features' => config('baseline.features', ['admin' => true, 'billing' => true, 'locales' => true]),
            'footer_social_links' => $this->footerSocialLinks(),
        ];
    }

    /**
     * Check if the user has any of the given roles without throwing when roles are not seeded.
     */
    protected function safeHasAnyRole(?object $user, array $roles): bool
    {
        if ($user === null) {
            return false;
        }

        try {
            return $user->hasAnyRole($roles);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Check if the user has the given permission without throwing when permissions are not seeded.
     */
    protected function safeHasPermissionTo(?object $user, string $permission): bool
    {
        if ($user === null) {
            return false;
        }

        try {
            return $user->hasRole('super-admin') || $user->hasPermissionTo($permission);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function appName(): string
    {
        try {
            $name = Setting::get('site_name');
        } catch (\Throwable) {
            return config('app.name');
        }

        return $name !== null && $name !== '' ? (string) $name : config('app.name');
    }

    protected function siteDescription(): string
    {
        try {
            $description = Setting::get('site_description');
        } catch (\Throwable) {
            return '';
        }

        return $description !== null ? (string) $description : '';
    }

    /**
     * @return array<int, array{platform: string, url: string}>
     */
    protected function footerSocialLinks(): array
    {
        try {
            $json = Setting::get('footer_social_links', '[]');
            $decoded = is_string($json) ? json_decode($json, true) : $json;

            if (! is_array($decoded)) {
                return [];
            }

            return array_values(array_filter($decoded, function (mixed $item): bool {
                return is_array($item)
                    && isset($item['platform'], $item['url'])
                    && is_string($item['platform'])
                    && is_string($item['url'])
                    && $item['url'] !== '';
            }));
        } catch (\Throwable) {
            return [];
        }
    }
}
