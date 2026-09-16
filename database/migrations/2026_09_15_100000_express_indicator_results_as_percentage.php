<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El RESULTADO pasa de razon a porcentaje: (numerador/denominador) x 100.
     * Por coherencia, la META DEL PERIODO se expresa en la misma escala.
     *
     * Las filas existentes se guardaron en escala de razon (0,975 y 0,95),
     * asi que se multiplican por 100 (97,5 y 95). El CUMPLIMIENTO no cambia:
     * es el cociente entre ambas, y al escalar las dos por igual se conserva.
     */
    public function up(): void
    {
        DB::table('indicator_results')->update([
            'result' => DB::raw('ROUND(result * 100, 2)'),
            'period_goal' => DB::raw('ROUND(period_goal * 100, 2)'),
        ]);
    }

    public function down(): void
    {
        DB::table('indicator_results')->update([
            'result' => DB::raw('ROUND(result / 100, 4)'),
            'period_goal' => DB::raw('ROUND(period_goal / 100, 4)'),
        ]);
    }
};
