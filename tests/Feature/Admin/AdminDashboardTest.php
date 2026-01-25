<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('unauthenticated users cannot access admin dashboard', function () {
    $response = $this->get('/admin');

    $response->assertRedirect(route('login'));
});

test('regular users cannot access admin dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin');

    $response->assertForbidden();
});

test('admin users can access admin dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertOk();
});

test('super admin users can access admin dashboard', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $response = $this->actingAs($superAdmin)->get('/admin');

    $response->assertOk();
});

test('admin dashboard displays stats', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $response = $this->actingAs($superAdmin)->get('/admin');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Dashboard')
        ->has('stats.users')
        ->has('stats.roles')
        ->has('stats.permissions')
        ->has('recentUsers')
    );
});
