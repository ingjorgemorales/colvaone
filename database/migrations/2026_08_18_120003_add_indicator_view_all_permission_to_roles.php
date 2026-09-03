<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Los indicadores dejan de verse completos por defecto: 'indicators.view'
     * pasa a significar "solo los mios" y se agrega 'indicators.view_all'
     * para quien deba verlos todos, igual que en tareas y comites.
     */
    public function up(): void
    {
        $role = DB::table('roles')->where('slug', 'admin')->first();

        if (!$role || $role->permissions === null) {
            return;
        }

        $permissions = json_decode($role->permissions, true) ?: [];
        $permissions = array_values(array_unique([...$permissions, 'indicators.view_all']));

        DB::table('roles')
            ->where('id', $role->id)
            ->update([
                'permissions' => json_encode($permissions),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('roles')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get()
            ->each(function ($role): void {
                $permissions = json_decode($role->permissions, true) ?: [];
                $permissions = array_values(array_diff($permissions, ['indicators.view_all']));

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode($permissions),
                        'updated_at' => now(),
                    ]);
            });
    }
};
