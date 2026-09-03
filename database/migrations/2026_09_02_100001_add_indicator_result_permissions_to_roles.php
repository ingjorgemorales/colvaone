<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Editar y activar/desactivar resultados dejan de estar cableados al rol
     * superadmin y pasan a ser permisos asignables desde Roles y permisos.
     * No se otorgan a nadie: superadmin los conserva porque hasPermission()
     * le devuelve true por codigo, asi que el comportamiento no cambia hasta
     * que alguien los marque de forma explicita.
     */
    public function up(): void
    {
        // Sin cambios de datos: los permisos nacen sin asignar.
    }

    public function down(): void
    {
        $toRemove = ['indicators.results_edit', 'indicators.results_toggle'];

        DB::table('roles')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get()
            ->each(function ($role) use ($toRemove): void {
                $permissions = json_decode($role->permissions, true) ?: [];
                $permissions = array_values(array_diff($permissions, $toRemove));

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['permissions' => json_encode($permissions), 'updated_at' => now()]);
            });
    }
};
