<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_options', function (Blueprint $table) {
            if (!Schema::hasColumn('incident_options', 'responsible_position')) {
                $table->string('responsible_position', 150)->nullable()->after('sort_order');
            }
        });

        Schema::table('incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('incidents', 'failure_type')) {
                $table->string('failure_type', 30)->default('general')->after('zone_id');
            }
            if (!Schema::hasColumn('incidents', 'responsible_position')) {
                $table->string('responsible_position', 150)->nullable()->after('failure_id');
            }
            if (!Schema::hasColumn('incidents', 'responsible_name')) {
                $table->string('responsible_name', 150)->nullable()->after('responsible_position');
            }
        });

        $risks = [
            ['name' => 'Problemas de liquidez', 'position' => 'Gerente financiero', 'order' => 0],
            ['name' => 'Dificultad para realizar inversiones', 'position' => 'Gerente financiero', 'order' => 1],
            ['name' => 'Incumplimiento de metas de nuevos negocios', 'position' => 'VP Operaciones', 'order' => 2],
            ['name' => 'Incrementos en costos y gastos', 'position' => 'VP Operaciones', 'order' => 3],
            ['name' => 'Dificultad para mantener equipos calificados', 'position' => 'Gerente de talento humano', 'order' => 4],
            ['name' => 'Portafolio incompleto y desactualizado', 'position' => 'VP Operaciones', 'order' => 5],
            ['name' => 'Ciberseguridad', 'position' => 'Gerente administrativo', 'order' => 6],
        ];

        foreach ($risks as $r) {
            DB::table('incident_options')->updateOrInsert(
                ['type' => 'prioritized_risk', 'name' => $r['name']],
                [
                    'responsible_position' => $r['position'],
                    'sort_order' => $r['order'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('incident_options')->where('type', 'prioritized_risk')->delete();

        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'responsible_name')) {
                $table->dropColumn('responsible_name');
            }
            if (Schema::hasColumn('incidents', 'responsible_position')) {
                $table->dropColumn('responsible_position');
            }
            if (Schema::hasColumn('incidents', 'failure_type')) {
                $table->dropColumn('failure_type');
            }
        });

        Schema::table('incident_options', function (Blueprint $table) {
            if (Schema::hasColumn('incident_options', 'responsible_position')) {
                $table->dropColumn('responsible_position');
            }
        });
    }
};
