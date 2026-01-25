<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can view roles list', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/roles');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/roles/Index')
        ->has('roles.data')
    );
});

test('admin can view create role form', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/roles/create');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/roles/Create')
        ->has('permissions')
    );
});

test('admin can create a role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/roles', [
        'name' => 'editor',
        'permissions' => ['manage-users'],
    ]);

    $response->assertRedirect('/admin/roles');

    $this->assertDatabaseHas('roles', [
        'name' => 'editor',
    ]);

    $role = Role::where('name', 'editor')->first();
    expect($role->hasPermissionTo('manage-users'))->toBeTrue();
});

test('admin can view edit role form', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $role = Role::findByName('moderator');

    $response = $this->actingAs($admin)->get("/admin/roles/{$role->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/roles/Edit')
        ->has('role')
        ->has('permissions')
    );
});

test('admin can update a role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $role = Role::create(['name' => 'test-role']);

    $response = $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
        'name' => 'updated-role',
        'permissions' => ['manage-settings'],
    ]);

    $response->assertRedirect('/admin/roles');

    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'updated-role',
    ]);
});

test('admin can delete a role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $role = Role::create(['name' => 'deletable-role']);

    $response = $this->actingAs($admin)->delete("/admin/roles/{$role->id}");

    $response->assertRedirect('/admin/roles');

    $this->assertDatabaseMissing('roles', [
        'name' => 'deletable-role',
    ]);
});

test('admin cannot delete super-admin role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $superAdminRole = Role::findByName('super-admin');

    $response = $this->actingAs($admin)->delete("/admin/roles/{$superAdminRole->id}");

    $response->assertRedirect('/admin/roles');

    $this->assertDatabaseHas('roles', [
        'name' => 'super-admin',
    ]);
});
