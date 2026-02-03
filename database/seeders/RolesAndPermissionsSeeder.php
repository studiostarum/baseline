<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-admin-dashboard',
            'manage-users',
            'manage-roles',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        Role::firstOrCreate(['name' => 'user']);

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view-admin-dashboard',
            'manage-users',
            'manage-roles',
            'manage-settings',
        ]);

        $moderator = Role::firstOrCreate(['name' => 'moderator']);
        $moderator->syncPermissions([]);
    }
}
