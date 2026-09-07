<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clasificacion del indicador dentro del mapa de procesos del SGC.
     * Ambos son opcionales para no invalidar los indicadores ya creados,
     * que quedan como "Sin asignar" hasta que alguien los clasifique.
     */
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->string('process', 60)->nullable()->index()->after('category');
            $table->string('subprocess', 60)->nullable()->index()->after('process');
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropIndex(['process']);
            $table->dropIndex(['subprocess']);
            $table->dropColumn(['process', 'subprocess']);
        });
    }
};
