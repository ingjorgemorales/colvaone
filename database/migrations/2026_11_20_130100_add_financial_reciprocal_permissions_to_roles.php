<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionsByRole = [
            'admin' => [
                'financial.view',
                'reciprocals.view',
                'reciprocals.create',
                'reciprocals.edit',
                'reciprocals.delete',
                'reciprocals.export',
                'reciprocals.import',
            ],
            'gerente' => [
                'financial.view',
                'reciprocals.view',
                'reciprocals.create',
                'reciprocals.edit',
                'reciprocals.export',
                'reciprocals.import',
            ],
            'jefe' => [
                'financial.view',
                'reciprocals.view',
                'reciprocals.create',
                'reciprocals.edit',
                'reciprocals.export',
            ],
            'operador' => [
                'financial.view',
                'reciprocals.view',
                'reciprocals.create',
            ],
            'auditor' => [
                'financial.view',
                'reciprocals.view',
                'reciprocals.export',
            ],
        ];

        foreach ($permissionsByRole as $slug => $permissionsToAdd) {
            $role = DB::table('roles')->where('slug', $slug)->first();

            if (! $role || $role->permissions === null) {
                continue;
            }

            $permissions = json_decode($role->permissions, true) ?: [];
            $permissions = array_values(array_unique([...$permissions, ...$permissionsToAdd]));

            DB::table('roles')
                ->where('id', $role->id)
                ->update([
                    'permissions' => json_encode($permissions),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $permissionsToRemove = [
            'financial.view',
            'reciprocals.view',
            'reciprocals.create',
            'reciprocals.edit',
            'reciprocals.delete',
            'reciprocals.export',
            'reciprocals.import',
        ];

        DB::table('roles')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get()
            ->each(function ($role) use ($permissionsToRemove): void {
                $permissions = json_decode($role->permissions, true) ?: [];
                $permissions = array_values(array_diff($permissions, $permissionsToRemove));

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode($permissions),
                        'updated_at' => now(),
                    ]);
            });
    }
};

