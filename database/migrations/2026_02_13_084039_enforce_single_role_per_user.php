<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ROLE_PRIORITY = [
        'super-admin' => 0,
        'admin' => 1,
        'moderator' => 2,
        'user' => 3,
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $modelHasRoles = config('permission.table_names.model_has_roles');
        $rolesTable = config('permission.table_names.roles');
        $rolePivotKey = config('permission.column_names.role_pivot_key', 'role_id');
        $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');

        $duplicates = DB::table($modelHasRoles)
            ->select($modelMorphKey, 'model_type')
            ->groupBy($modelMorphKey, 'model_type')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $rows = DB::table($modelHasRoles)
                ->join($rolesTable, "{$rolesTable}.id", '=', "{$modelHasRoles}.{$rolePivotKey}")
                ->where("{$modelHasRoles}.{$modelMorphKey}", $dup->{$modelMorphKey})
                ->where("{$modelHasRoles}.model_type", $dup->model_type)
                ->orderByRaw($this->rolePriorityOrderSql($rolesTable))
                ->get(["{$modelHasRoles}.{$rolePivotKey}", "{$modelHasRoles}.{$modelMorphKey}", "{$modelHasRoles}.model_type"]);

            foreach ($rows->skip(1) as $row) {
                DB::table($modelHasRoles)
                    ->where($rolePivotKey, $row->{$rolePivotKey})
                    ->where($modelMorphKey, $row->{$modelMorphKey})
                    ->where('model_type', $row->model_type)
                    ->delete();
            }
        }

        Schema::table($modelHasRoles, function (Blueprint $table) use ($modelMorphKey): void {
            $table->unique([$modelMorphKey, 'model_type'], 'model_has_roles_model_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $modelHasRoles = config('permission.table_names.model_has_roles');
        $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');

        Schema::table($modelHasRoles, function (Blueprint $table): void {
            $table->dropUnique('model_has_roles_model_unique');
        });
    }

    private function rolePriorityOrderSql(string $rolesTable): string
    {
        $cases = collect(self::ROLE_PRIORITY)
            ->map(fn (int $priority, string $name) => "WHEN '{$name}' THEN {$priority}")
            ->implode(' ');

        return "CASE {$rolesTable}.name {$cases} ELSE 99 END ASC";
    }
};
