<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Console\Command;

class BaselineInstall extends Command
{
    protected $signature = 'baseline:install';

    protected $description = 'Seed roles and permissions, and optionally assign the first admin user';

    public function handle(): int
    {
        $this->info('Seeding roles and permissions...');
        $this->callSilent('db:seed', ['--class' => RolesAndPermissionsSeeder::class, '--force' => true]);
        $this->info('Roles and permissions seeded.');

        $email = $this->ask('Enter email for first admin (or leave empty to skip)', '');

        if ($email === null || $email === '') {
            return self::SUCCESS;
        }

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->warn("No user found with email [{$email}]. Assign a role manually via tinker or the admin panel.");

            return self::SUCCESS;
        }

        $user->assignRole('super-admin');
        $this->info("Assigned super-admin role to [{$email}].");

        return self::SUCCESS;
    }
}
