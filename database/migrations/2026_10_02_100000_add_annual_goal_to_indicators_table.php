<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Meta del ano en la unidad del indicador (p. ej. 83.259 millones en Ingresos
     * totales). En el Excel de la junta es un valor escrito a mano, no una suma,
     * y con el se calcula el avance del ano: real acumulado / meta del ano.
     * Es opcional: sin ella el tablero simplemente no muestra ese avance.
     */
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->decimal('annual_goal', 16, 2)->nullable()->after('accumulation_mode');
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn('annual_goal');
        });
    }
};
