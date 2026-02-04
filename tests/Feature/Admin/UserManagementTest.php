<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can view users list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory(5)->create();

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/users/Index')
        ->has('users.data', 6)
    );
});

test('admin can view create user form', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/users/create');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/users/Create')
        ->has('roles')
    );
});

test('admin can create a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'moderator',
    ]);

    $response->assertRedirect('/admin/users');

    $this->assertDatabaseHas('users', [
        'name' => 'New User',
        'email' => 'newuser@example.com',
    ]);

    $newUser = User::where('email', 'newuser@example.com')->first();
    expect($newUser->hasRole('moderator'))->toBeTrue();
});

test('admin can view edit user form', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();

    $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/users/Edit')
        ->has('user')
        ->where('user.two_factor_enabled', false)
        ->has('roles')
    );
});

test('admin can disable two-factor authentication for a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->withTwoFactor()->create();

    expect($user->hasEnabledTwoFactorAuthentication())->toBeTrue();

    $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}/two-factor");

    $response->assertRedirect("/admin/users/{$user->id}/edit");

    $user->refresh();
    expect($user->hasEnabledTwoFactorAuthentication())->toBeFalse();
});

test('admin can update a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
        'name' => 'Updated Name',
        'email' => $user->email,
        'role' => 'user',
    ]);

    $response->assertRedirect('/admin/users');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
    ]);
});

test('admin can delete a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = User::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

    $response->assertRedirect('/admin/users');

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('admin cannot delete themselves', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

test('users list can be searched', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory()->create(['name' => 'John Doe']);
    User::factory()->create(['name' => 'Jane Smith']);

    $response = $this->actingAs($admin)->get('/admin/users?search=John');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/users/Index')
        ->has('users.data', 1)
    );
});
