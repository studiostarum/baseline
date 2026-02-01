<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can view billing dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/billing');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/billing/Index')
        ->has('stats')
        ->has('recentSubscriptions')
    );
});

test('admin can view billing users list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory(5)->create();

    $response = $this->actingAs($admin)->get('/admin/billing/users');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/billing/Users')
        ->has('users.data', 6)
    );
});

test('admin can view user billing details', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();

    $response = $this->actingAs($admin)->get("/admin/billing/users/{$user->id}");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/billing/Show')
        ->has('user')
        ->has('subscriptions')
        ->has('invoices')
    );
});

test('non-admin cannot access billing dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/billing');

    $response->assertForbidden();
});

test('non-admin cannot access billing users list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/billing/users');

    $response->assertForbidden();
});

test('non-admin cannot view user billing details', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    $response = $this->actingAs($user)->get("/admin/billing/users/{$targetUser->id}");

    $response->assertForbidden();
});

test('billing users list can be searched', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($admin)->get('/admin/billing/users?search=John');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/billing/Users')
        ->has('users.data', 1)
    );
});

test('guest cannot access billing pages', function () {
    $response = $this->get('/admin/billing');
    $response->assertRedirect(route('login'));

    $response = $this->get('/admin/billing/users');
    $response->assertRedirect(route('login'));
});
