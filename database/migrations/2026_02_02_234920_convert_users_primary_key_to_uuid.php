<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->upSqlite();

            return;
        }

        $this->upMysql();
    }

    private function upMysql(): void
    {
        Schema::table('social_accounts', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->after('id');
        });

        $userUuidMap = [];
        foreach (DB::table('users')->orderBy('id')->get() as $user) {
            $uuid = (string) Str::orderedUuid();
            $userUuidMap[$user->id] = $uuid;
            DB::table('users')->where('id', $user->id)->update(['uuid' => $uuid]);
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY uuid CHAR(36) NOT NULL, ADD UNIQUE KEY users_uuid_unique (uuid)');
        }

        $this->migrateUserForeignKey('sessions', $userUuidMap, ['sessions_user_id_index']);
        $this->migrateUserForeignKey('social_accounts', $userUuidMap, ['social_accounts_user_id_index']);
        $this->migrateUserForeignKey('subscriptions', $userUuidMap, ['subscriptions_user_id_stripe_status_index']);

        $modelHasPermissions = config('permission.table_names.model_has_permissions');
        $modelHasRoles = config('permission.table_names.model_has_roles');
        $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');
        $userModelType = 'App\Models\User';

        $this->migrateModelMorphKey($modelHasPermissions, $modelMorphKey, $userModelType, $userUuidMap, 'model_has_permissions_permission_model_type_primary', config('permission.column_names.permission_pivot_key') ?? 'permission_id');
        $this->migrateModelMorphKey($modelHasRoles, $modelMorphKey, $userModelType, $userUuidMap, 'model_has_roles_role_model_type_primary', config('permission.column_names.role_pivot_key') ?? 'role_id');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropPrimary();
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('id');
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->renameColumn('uuid', 'id');
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->primary('id');
        });

        Schema::table('social_accounts', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * @param  array<int, string>  $userUuidMap
     * @param  array<int, string>  $indexNamesToDrop
     */
    private function migrateUserForeignKey(string $tableName, array $userUuidMap, array $indexNamesToDrop = []): void
    {
        Schema::table($tableName, function (Blueprint $table): void {
            $table->uuid('user_uuid')->nullable()->after('user_id');
        });

        foreach ($userUuidMap as $oldId => $uuid) {
            DB::table($tableName)->where('user_id', $oldId)->update(['user_uuid' => $uuid]);
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexNamesToDrop): void {
            foreach ($indexNamesToDrop as $indexName) {
                try {
                    $table->dropIndex($indexName);
                } catch (\Throwable) {
                    // Index may not exist on all drivers
                }
            }
            $table->dropColumn('user_id');
        });
        Schema::table($tableName, function (Blueprint $table): void {
            $table->renameColumn('user_uuid', 'user_id');
        });
    }

    /**
     * @param  array<int, string>  $userUuidMap
     */
    private function migrateModelMorphKey(string $tableName, string $modelMorphKey, string $userModelType, array $userUuidMap, string $primaryKeyName, string $pivotColumn): void
    {
        Schema::table($tableName, function (Blueprint $table) use ($primaryKeyName): void {
            $table->dropPrimary($primaryKeyName);
        });

        $modelIdIndexName = "{$tableName}_{$modelMorphKey}_model_type_index";
        Schema::table($tableName, function (Blueprint $table) use ($modelIdIndexName): void {
            try {
                $table->dropIndex($modelIdIndexName);
            } catch (\Throwable) {
                // Index may not exist on all drivers
            }
        });

        Schema::table($tableName, function (Blueprint $table) use ($modelMorphKey): void {
            $table->uuid('model_id_new')->nullable()->after($modelMorphKey);
        });

        foreach (DB::table($tableName)->where('model_type', $userModelType)->get() as $row) {
            $oldId = $row->{$modelMorphKey};
            $uuid = $userUuidMap[$oldId] ?? null;
            if ($uuid) {
                DB::table($tableName)
                    ->where($modelMorphKey, $oldId)
                    ->where('model_type', $userModelType)
                    ->update(['model_id_new' => $uuid]);
            }
        }

        Schema::table($tableName, function (Blueprint $table) use ($modelMorphKey): void {
            $table->dropColumn($modelMorphKey);
        });
        Schema::table($tableName, function (Blueprint $table) use ($modelMorphKey): void {
            $table->renameColumn('model_id_new', $modelMorphKey);
        });

        Schema::table($tableName, function (Blueprint $table) use ($pivotColumn, $modelMorphKey, $primaryKeyName): void {
            $table->primary([$pivotColumn, $modelMorphKey, 'model_type'], $primaryKeyName);
        });
    }

    private function upSqlite(): void
    {
        Schema::table('social_accounts', function (Blueprint $table): void {
            $table->dropForeign(['user_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('uuid')->nullable()->after('id');
        });

        $userUuidMap = [];
        foreach (DB::table('users')->orderBy('id')->get() as $user) {
            $uuid = (string) Str::orderedUuid();
            $userUuidMap[$user->id] = $uuid;
            DB::table('users')->where('id', $user->id)->update(['uuid' => $uuid]);
        }

        $this->migrateUserForeignKey('sessions', $userUuidMap, ['sessions_user_id_index']);
        $this->migrateUserForeignKey('social_accounts', $userUuidMap, ['social_accounts_user_id_index']);
        $this->migrateUserForeignKey('subscriptions', $userUuidMap, ['subscriptions_user_id_stripe_status_index']);

        $modelHasPermissions = config('permission.table_names.model_has_permissions');
        $modelHasRoles = config('permission.table_names.model_has_roles');
        $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');
        $userModelType = 'App\Models\User';

        $this->migrateModelMorphKey($modelHasPermissions, $modelMorphKey, $userModelType, $userUuidMap, 'model_has_permissions_permission_model_type_primary', config('permission.column_names.permission_pivot_key') ?? 'permission_id');
        $this->migrateModelMorphKey($modelHasRoles, $modelMorphKey, $userModelType, $userUuidMap, 'model_has_roles_role_model_type_primary', config('permission.column_names.role_pivot_key') ?? 'role_id');

        DB::statement('CREATE TABLE users_new (id CHAR(36) PRIMARY KEY NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_verified_at DATETIME NULL, password VARCHAR(255) NULL, remember_token VARCHAR(100) NULL, two_factor_secret TEXT NULL, two_factor_recovery_codes TEXT NULL, two_factor_confirmed_at DATETIME NULL, created_at DATETIME NULL, updated_at DATETIME NULL, stripe_id VARCHAR(255) NULL, pm_type VARCHAR(255) NULL, pm_last_four VARCHAR(4) NULL, trial_ends_at DATETIME NULL)');
        DB::statement('INSERT INTO users_new (id, name, email, email_verified_at, password, remember_token, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at, created_at, updated_at, stripe_id, pm_type, pm_last_four, trial_ends_at) SELECT uuid, name, email, email_verified_at, password, remember_token, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at, created_at, updated_at, stripe_id, pm_type, pm_last_four, trial_ends_at FROM users');
        Schema::drop('users');
        Schema::rename('users_new', 'users');

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('email');
        });

        Schema::table('social_accounts', function (Blueprint $table): void {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        throw new \RuntimeException('Rollback of UUID conversion is not supported. Use a database backup to restore.');
    }
};
