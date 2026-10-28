<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('indicator_results', 'legacy_calculation')) {
            Schema::table('indicator_results', function (Blueprint $table) {
                $table->json('legacy_calculation')->nullable();
            });
        }

        // Keep the original calculation before removing its extra normalization.
        DB::table('indicator_results')->whereNull('legacy_calculation')->orderBy('id')->chunkById(200, function ($rows) {
            $indicators = DB::table('indicators')->whereIn('id', $rows->pluck('indicator_id'))->get()->keyBy('id');
            DB::transaction(function () use ($rows, $indicators) {
                foreach ($rows as $row) {
                    $indicator = $indicators->get($row->indicator_id);
                    if (!$indicator) { continue; }
                    $compliance = $this->directCompliance($row);
                    DB::table('indicator_results')->where('id', $row->id)->update([
                        'legacy_calculation' => json_encode([
                            'numerator' => $row->numerator, 'denominator' => $row->denominator,
                            'result' => $row->result, 'period_goal' => $row->period_goal,
                            'compliance_formula' => $row->compliance_formula,
                            'compliance' => $row->compliance, 'evaluation' => $row->evaluation,
                            'updated_at' => $row->updated_at,
                        ], JSON_THROW_ON_ERROR),
                        'compliance' => $compliance,
                        'evaluation' => $this->evaluate($compliance, $indicator),
                    ]);
                }
            });
        });
    }

    public function down(): void
    {
        DB::table('indicator_results')->orderBy('id')->chunkById(200, function ($rows) {
            $indicators = DB::table('indicators')->whereIn('id', $rows->pluck('indicator_id'))->get()->keyBy('id');
            DB::transaction(function () use ($rows, $indicators) {
                foreach ($rows as $row) {
                    $indicator = $indicators->get($row->indicator_id);
                    if (!$indicator) { continue; }
                    $backup = $row->legacy_calculation ? json_decode($row->legacy_calculation, true, flags: JSON_THROW_ON_ERROR) : null;
                    $unchanged = $backup && (float) $backup['numerator'] === (float) $row->numerator
                        && (float) $backup['denominator'] === (float) $row->denominator
                        && $backup['compliance_formula'] === $row->compliance_formula
                        && $backup['updated_at'] === $row->updated_at;
                    if ($unchanged) {
                        $values = array_intersect_key($backup, array_flip(['result', 'period_goal', 'compliance', 'evaluation']));
                    } else {
                        // Rollback never undoes later edits to the real result or its target.
                        $result = (float) $row->denominator == 0.0 ? 0.0 : round((float) $row->numerator / (float) $row->denominator * 100, 2);
                        $goal = $row->period_goal ?? $indicator->goal;
                        $compliance = (float) $goal == 0.0 ? 0.0 : ($row->compliance_formula === 'descendente'
                            ? ($result == 0.0 ? 0.0 : round((float) $goal / $result * 100, 2))
                            : round($result / (float) $goal * 100, 2));
                        $values = ['result' => $result, 'period_goal' => $goal, 'compliance' => $compliance, 'evaluation' => $this->evaluate($compliance, $indicator)];
                    }
                    DB::table('indicator_results')->where('id', $row->id)->update($values);
                }
            });
        });
        Schema::table('indicator_results', fn (Blueprint $table) => $table->dropColumn('legacy_calculation'));
    }

    private function directCompliance(object $row): float
    {
        $actual = (float) $row->numerator;
        $target = (float) $row->denominator;
        if ($target == 0.0) { return 0.0; }
        if ($row->compliance_formula === 'descendente') {
            return $actual == 0.0 ? 0.0 : round($target / $actual * 100, 2);
        }

        return round($actual / $target * 100, 2);
    }

    private function evaluate(float $compliance, object $indicator): string
    {
        return $compliance >= $indicator->threshold_satisfactory ? 'satisfactorio'
            : ($compliance >= $indicator->threshold_acceptable ? 'aceptable' : 'insatisfactorio');
    }
};
