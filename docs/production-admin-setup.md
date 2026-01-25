# Production Admin Setup

This guide covers creating admin users in production environments.

## Method 1: Database Seeder (Recommended for Initial Setup)

Run the seeder with production database credentials:

```bash
# Set production database credentials in .env or export them
php artisan db:seed --class=RolesAndPermissionsSeeder --force
```

This creates the default roles and permissions.

## Method 2: Artisan Command (Local with Production DB)

Connect to your production database locally and use tinker:

```bash
# Ensure .env has production database credentials
php artisan tinker
```

```php
// Create roles and permissions if not already seeded
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create permissions
$permissions = ['view-admin-dashboard', 'manage-users', 'manage-roles', 'manage-settings'];
foreach ($permissions as $permission) {
    Permission::firstOrCreate(['name' => $permission]);
}

// Create roles
Role::firstOrCreate(['name' => 'super-admin']);
$admin = Role::firstOrCreate(['name' => 'admin']);
$admin->syncPermissions($permissions);

// Assign super-admin to a user
$user = \App\Models\User::where('email', 'admin@example.com')->first();
$user->assignRole('super-admin');
```

## Method 3: SQL Queries (Direct Database Access)

If you only have database access (e.g., via PlanetScale, Neon console):

### Create Permissions

```sql
INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view-admin-dashboard', 'web', NOW(), NOW()),
('manage-users', 'web', NOW(), NOW()),
('manage-roles', 'web', NOW(), NOW()),
('manage-settings', 'web', NOW(), NOW())
ON DUPLICATE KEY UPDATE name=name;
```

### Create Roles

```sql
INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
('super-admin', 'web', NOW(), NOW()),
('admin', 'web', NOW(), NOW()),
('moderator', 'web', NOW(), NOW())
ON DUPLICATE KEY UPDATE name=name;
```

### Assign Role to User

```sql
-- Get the user ID and role ID first
SELECT id FROM users WHERE email = 'admin@example.com';  -- e.g., returns 1
SELECT id FROM roles WHERE name = 'super-admin';          -- e.g., returns 1

-- Assign the role
INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES
(1, 'App\\Models\\User', 1)
ON DUPLICATE KEY UPDATE role_id=role_id;
```

## Method 4: One-Time Artisan Route (Temporary)

Create a temporary route for initial setup, then remove it:

```php
// routes/web.php (TEMPORARY - REMOVE AFTER USE)
Route::get('/setup-admin/{secret}', function ($secret) {
    if ($secret !== 'your-secure-secret-here') {
        abort(404);
    }
    
    Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--force' => true]);
    
    $user = \App\Models\User::where('email', 'admin@example.com')->first();
    if ($user) {
        $user->assignRole('super-admin');
        return 'Admin role assigned to ' . $user->email;
    }
    
    return 'User not found. Please register first, then visit this URL again.';
});
```

Visit: `https://your-app.vercel.app/setup-admin/your-secure-secret-here`

**Important**: Remove this route after use!

## Verifying Admin Access

1. Log in with the admin user credentials
2. Navigate to `/admin`
3. You should see the admin dashboard

## Security Checklist

- [ ] Remove any temporary setup routes
- [ ] Set `APP_DEBUG=false` in production
- [ ] Use strong passwords for admin accounts
- [ ] Enable two-factor authentication if available
- [ ] Regularly audit admin user list
