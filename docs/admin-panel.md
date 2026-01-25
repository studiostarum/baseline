# Admin Panel

The admin panel provides user management, role management, and settings configuration.

## Setup

### 1. Seed Roles and Permissions

Run the seeder to create default roles and permissions:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

This creates:
- **Roles**: `super-admin`, `admin`, `moderator`
- **Permissions**: `view-admin-dashboard`, `manage-users`, `manage-roles`, `manage-settings`

### 2. Assign Admin Role to a User

Use tinker to assign a role:

```bash
php artisan tinker
```

```php
// Assign super-admin role (full access)
User::find(1)->assignRole('super-admin');

// Or assign admin role
User::where('email', 'admin@example.com')->first()->assignRole('admin');

// Assign multiple roles
$user = User::find(1);
$user->assignRole(['admin', 'moderator']);
```

### 3. Access the Admin Panel

Navigate to `/admin` in your browser. Only users with `super-admin` or `admin` roles can access it.

## Managing Roles via Tinker

```php
// Check user roles
User::find(1)->getRoleNames();

// Remove a role
User::find(1)->removeRole('admin');

// Sync roles (replaces all existing roles)
User::find(1)->syncRoles(['moderator']);

// Check if user has role
User::find(1)->hasRole('admin'); // true/false

// Check if user has any role
User::find(1)->hasAnyRole(['admin', 'moderator']);
```

## Managing Permissions via Tinker

```php
// Give permission directly to user
User::find(1)->givePermissionTo('manage-users');

// Revoke permission
User::find(1)->revokePermissionTo('manage-users');

// Check permission
User::find(1)->hasPermissionTo('manage-users');

// Give permission to role
$role = \Spatie\Permission\Models\Role::findByName('moderator');
$role->givePermissionTo('manage-settings');
```

## Default Role Permissions

| Role | Permissions |
|------|-------------|
| super-admin | All permissions (bypasses checks) |
| admin | view-admin-dashboard, manage-users, manage-roles, manage-settings |
| moderator | view-admin-dashboard |

## Production Setup

For production environments (Vercel, etc.), see [Production Admin Setup](./production-admin-setup.md).
