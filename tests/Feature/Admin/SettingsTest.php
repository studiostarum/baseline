<?php

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can view settings page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/settings');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Settings')
        ->has('settings')
    );
});

test('admin can update settings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/settings', [
        'settings' => [
            'site_name' => 'My Site',
            'contact_email' => 'contact@example.com',
        ],
    ]);

    $response->assertRedirect('/admin/settings');

    $this->assertDatabaseHas('settings', [
        'key' => 'site_name',
        'value' => 'My Site',
    ]);

    $this->assertDatabaseHas('settings', [
        'key' => 'contact_email',
        'value' => 'contact@example.com',
    ]);
});

test('settings can be retrieved by key', function () {
    Setting::set('test_key', 'test_value');

    expect(Setting::get('test_key'))->toBe('test_value');
    expect(Setting::get('nonexistent', 'default'))->toBe('default');
});
