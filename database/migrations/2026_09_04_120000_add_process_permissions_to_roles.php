<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Administracion del maestro de procesos y subprocesos. Solo el
     * administrador lo recibe: es una tabla maestra que afecta a todos
     * los indicadores, no algo del dia a dia.
     */
    public function up(): void
    {
        $permissions = ['processes.view', 'processes.create', 'processes.edit', 'processes.toggle'];

        $role = DB::table('roles')->where('slug', 'admin')->first();

        if (!$role || $role->permissions === null) {
            return;
        }

        $current = json_decode($role->permissions, true) ?: [];

        DB::table('roles')->where('id', $role->id)->update([
            'permissions' => json_encode(array_values(array_unique([...$current, ...$permissions]))),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $toRemove = ['processes.view', 'processes.create', 'processes.edit', 'processes.toggle'];

        DB::table('roles')->whereNotNull('permissions')->orderBy('id')->get()
            ->each(function ($role) use ($toRemove): void {
                $permissions = json_decode($role->permissions, true) ?: [];
                DB::table('roles')->where('id', $role->id)->update([
                    'permissions' => json_encode(array_values(array_diff($permissions, $toRemove))),
                    'updated_at' => now(),
                ]);
            });
    }
};
