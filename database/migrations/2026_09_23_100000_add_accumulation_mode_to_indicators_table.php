<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Como se resume el rango consultado en el dashboard BSC: suma, promedio
     * o el ultimo periodo. Nulo significa automatico: lo que es plata se suma
     * y los porcentajes o cantidades se promedian, asi que los indicadores
     * existentes no necesitan tocarse.
     */
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->string('accumulation_mode', 20)->nullable()->after('bsc_perspective_id');
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn('accumulation_mode');
        });
    }
};
