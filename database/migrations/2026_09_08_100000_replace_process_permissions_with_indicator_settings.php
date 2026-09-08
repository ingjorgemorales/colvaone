<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * La administracion de procesos y subprocesos deja de vivir en
     * Configuracion y pasa a ser un ajuste del modulo de Indicadores.
     * Los cuatro permisos 'processes.*' se reemplazan por uno solo:
     * 'indicators.settings' (Ajustes).
     */
    private const OLD = ['processes.view', 'processes.create', 'processes.edit', 'processes.toggle'];

    private const NEW = 'indicators.settings';

    public function up(): void
    {
        DB::table('roles')->whereNotNull('permissions')->orderBy('id')->get()
            ->each(function ($role): void {
                $permissions = json_decode($role->permissions, true) ?: [];

                // Quien administraba procesos conserva la facultad bajo el nombre nuevo.
                $tenia = count(array_intersect($permissions, self::OLD)) > 0;
                $permissions = array_values(array_diff($permissions, self::OLD));

                if ($tenia && ! in_array(self::NEW, $permissions, true)) {
                    $permissions[] = self::NEW;
                }

                DB::table('roles')->where('id', $role->id)->update([
                    'permissions' => json_encode(array_values($permissions)),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('roles')->whereNotNull('permissions')->orderBy('id')->get()
            ->each(function ($role): void {
                $permissions = json_decode($role->permissions, true) ?: [];

                if (in_array(self::NEW, $permissions, true)) {
                    $permissions = array_values(array_diff($permissions, [self::NEW]));
                    $permissions = array_values(array_unique([...$permissions, ...self::OLD]));
                }

                DB::table('roles')->where('id', $role->id)->update([
                    'permissions' => json_encode($permissions),
                    'updated_at' => now(),
                ]);
            });
    }
};
