<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('incident_options')
            ->where('type', 'zone')
            ->update(['is_active' => false]);

        $sedes = [
            'Cofradía bodega 3',
            'ETB Salitre',
            'Galan',
            'Funza',
            'Terreno',
        ];

        foreach ($sedes as $order => $name) {
            DB::table('incident_options')->updateOrInsert(
                ['type' => 'zone', 'name' => $name],
                [
                    'sort_order' => $order,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('incident_options')
            ->where('type', 'zone')
            ->whereIn('name', ['Piso 1', 'Piso 2', 'Parqueadero'])
            ->update(['is_active' => true]);

        DB::table('incident_options')
            ->where('type', 'zone')
            ->whereIn('name', ['Cofradía bodega 3', 'ETB Salitre', 'Galan', 'Funza', 'Terreno'])
            ->update(['is_active' => false]);
    }
};
