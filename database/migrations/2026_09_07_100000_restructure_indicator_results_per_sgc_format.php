<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Alinea la seccion de Resultados con el formato del SGC:
     *
     *   PERIODO | NUMERADOR | DENOMINADOR | RESULTADO | META PERIODO |
     *   CUMPLIMIENTO | EVALUACION | ANALISIS | ACCION No
     *
     *   RESULTADO    = NUMERADOR / DENOMINADOR
     *   CUMPLIMIENTO = (RESULTADO / META) * 100   -> meta ascendente
     *                  (META / RESULTADO) * 100   -> meta descendente (llegar a cero)
     *
     * Los campos viejos eran genericos: 'field_one' era el divisor y
     * 'field_two' el dividendo, asi que se renombran en ese orden.
     */
    public function up(): void
    {
        // Sentido de la meta: define cual de las dos formulas aplica.
        Schema::table('indicators', function (Blueprint $table) {
            $table->string('goal_direction', 20)->default('ascendente')->after('goal');
        });

        Schema::table('indicator_results', function (Blueprint $table) {
            $table->renameColumn('field_one', 'denominator');
            $table->renameColumn('field_two', 'numerator');
            $table->renameColumn('description', 'analysis');
        });

        Schema::table('indicator_results', function (Blueprint $table) {
            $table->decimal('result', 16, 4)->default(0)->after('denominator');
            $table->decimal('period_goal', 16, 4)->nullable()->after('result');
            $table->decimal('compliance', 12, 2)->default(0)->change();
        });

        $this->backfill();
    }

    /**
     * Rellena los campos nuevos de los resultados que ya existen y recalcula
     * el cumplimiento con la formula nueva, para que ninguna fila quede
     * mostrando un numero que no corresponde a su formula.
     *
     * La meta del indicador se guarda de 1 a 100; el resultado es una razon
     * (numerador/denominador), asi que se traslada dividida entre 100 para
     * que ambos queden en la misma escala.
     */
    private function backfill(): void
    {
        $goals = DB::table('indicators')->pluck('goal', 'id');

        foreach (DB::table('indicator_results')->get() as $row) {
            $denominator = (float) $row->denominator;
            $result = $denominator == 0.0 ? 0.0 : round((float) $row->numerator / $denominator, 4);

            $goal = isset($goals[$row->indicator_id]) ? ((float) $goals[$row->indicator_id]) / 100 : null;

            $compliance = ($goal === null || $goal == 0.0)
                ? 0.0
                : round(($result / $goal) * 100, 2);

            DB::table('indicator_results')->where('id', $row->id)->update([
                'result' => $result,
                'period_goal' => $goal,
                'compliance' => $compliance,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('indicator_results', function (Blueprint $table) {
            $table->dropColumn(['result', 'period_goal']);
        });

        Schema::table('indicator_results', function (Blueprint $table) {
            $table->renameColumn('denominator', 'field_one');
            $table->renameColumn('numerator', 'field_two');
            $table->renameColumn('analysis', 'description');
        });

        Schema::table('indicator_results', function (Blueprint $table) {
            $table->decimal('compliance', 6, 2)->default(0)->change();
        });

        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn('goal_direction');
        });
    }
};
