<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('super-admin');

        $studioAdmin = User::firstOrCreate(
            ['email' => 'studio.starum@gmail.com'],
            [
                'name' => 'Studio Starum',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );
        $studioAdmin->assignRole('admin');

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
