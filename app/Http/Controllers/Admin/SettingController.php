<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $user = request()->user();
        if (! $user->hasRole('moderator') && ! $user->hasPermissionTo('manage-settings')) {
            abort(403, __('errors.unauthorized'));
        }

        $settings = Setting::all()->pluck('value', 'key')->toArray();

        return Inertia::render('admin/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(SettingRequest $request): RedirectResponse
    {
        if (! $request->user()->hasPermissionTo('manage-settings')) {
            abort(403, __('errors.unauthorized'));
        }

        foreach ($request->validated('settings') as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
