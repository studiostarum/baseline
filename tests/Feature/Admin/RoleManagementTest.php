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
        ->has('role.permissions')
        ->has('permissions')
    );
});

test('system role slug is not updated when updating permissions', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $role = Role::findByName('moderator');

    $response = $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
        'name' => 'attempted-new-slug',
        'permissions' => ['view-admin-dashboard'],
    ]);

    $response->assertRedirect('/admin/roles');
    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
        'name' => 'moderator',
    ]);
});

test('edit role form includes role permissions for pre-selecting checkboxes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $role = Role::findByName('admin');

    $response = $this->actingAs($admin)->get("/admin/roles/{$role->id}/edit");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/roles/Edit')
        ->where('role.id', $role->id)
        ->where('role.name', 'admin')
        ->has('role.permissions', 4)
        ->where('role.permissions.0.name', 'view-admin-dashboard')
    );
});

test('admin can update a role display name only', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $role = Role::create(['name' => 'test-role']);

    $response = $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
        'name' => $role->name,
        'display_name' => 'Test Role Label',
        'permissions' => ['manage-settings'],
    ]);

    $response->assertRedirect('/admin/roles');

    $role->refresh();
    expect($role->name)->toBe('test-role');
    expect($role->display_name)->toBe('Test Role Label');
    expect($role->getPermissionNames()->isEmpty())->toBeTrue();
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

    $response->assertForbidden();
    $this->assertDatabaseHas('roles', [
        'name' => 'super-admin',
    ]);
});
