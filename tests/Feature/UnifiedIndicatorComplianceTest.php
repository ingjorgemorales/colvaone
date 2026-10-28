<?php

namespace Tests\Feature;

use App\Models\BscPerspective;
use App\Models\Indicator;
use App\Models\IndicatorResult;
use App\Models\QualityObjective;
use App\Models\User;
use App\Services\IndicatorExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UnifiedIndicatorComplianceTest extends TestCase
{
    use RefreshDatabase;

    private function indicator(User $user, string $category = 'I', array $extra = []): Indicator
    {
        return Indicator::create(array_replace([
            'name' => 'Indicador de prueba '.$category, 'category' => $category,
            'bsc_perspective_id' => BscPerspective::firstOrFail()->id,
            'quality_objective_id' => QualityObjective::firstOrFail()->id,
            'objective' => 'Objetivo de prueba', 'responsible_user_id' => $user->id,
            'formula' => 'Resultado / Meta', 'measurement_unit' => 'porcentaje',
            'frequency' => 'mensual', 'type' => 'eficacia', 'goal' => 95,
            'annual_goal' => 1200, 'threshold_acceptable' => 80, 'threshold_satisfactory' => 95,
            'status' => 'active', 'created_by' => $user->id,
        ], $extra));
    }

    private function payload(array $extra = []): array
    {
        return array_replace([
            'period_start' => '2026-08-01', 'period_end' => '2026-08-31',
            'numerator' => '10,8', 'denominator' => '11,8', 'compliance_formula' => 'ascendente',
            'analysis' => 'Análisis preservado', 'action_number' => 'ACC-001',
        ], $extra);
    }

    public function test_all_categories_save_one_direct_compliance_without_legacy_goals(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user);
        foreach (array_keys(Indicator::CATEGORIES) as $category) {
            $indicator = $this->indicator($user, $category);
            $this->post(route('indicators.results.store', $indicator), $this->payload([
                'period_goal' => 1, 'compliance' => 999, 'evaluation' => 'satisfactorio',
            ]))->assertSessionHasNoErrors();
            $result = $indicator->results()->firstOrFail();
            $this->assertSame(91.53, (float) $result->compliance);
            $this->assertSame('aceptable', $result->evaluation);
            $this->assertNull($result->period_goal);
            $this->put(route('indicators.results.update', [$indicator, $result]), $this->payload(['compliance_formula' => 'descendente']))
                ->assertSessionHasNoErrors();
            $result->refresh();
            $this->assertSame(109.26, (float) $result->compliance);
            $this->assertSame('satisfactorio', $result->evaluation);
            $this->assertSame('(Meta / Resultado) x 100', $result->compliance_formula_math);
        }
    }

    public function test_forms_and_read_views_remove_duplicate_fields_in_all_categories(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user);
        foreach (array_keys(Indicator::CATEGORIES) as $category) {
            $indicator = $this->indicator($user, $category);
            $this->get(route('indicators.create', ['categoria' => $category]))->assertOk()
                ->assertDontSee('name="goal"', false)->assertSee('name="annual_goal"', false);
            $this->get(route('indicators.edit', $indicator))->assertOk()
                ->assertDontSee('name="goal"', false)->assertSee('name="annual_goal"', false);
            $html = $this->get(route('indicators.show', $indicator))->assertOk()->getContent();
            $this->assertStringNotContainsString('name="period_goal"', $html);
            $this->assertStringNotContainsString('Meta periodo', $html);
            $this->assertStringNotContainsString('Numerador', $html);
            $this->assertStringNotContainsString('Denominador', $html);
            $this->assertSame(1, substr_count($html, '<th style="text-align:right">Cumplimiento</th>'));
            $this->assertStringContainsString('Meta del año', $html);
        }
    }

    public function test_indicator_can_be_created_without_the_removed_goal_and_annual_goal_is_preserved(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $indicator = $this->indicator($user);
        $fields = $indicator->only(['name', 'category', 'bsc_perspective_id', 'quality_objective_id', 'objective', 'responsible_user_id', 'formula', 'measurement_unit', 'frequency', 'type', 'threshold_acceptable', 'threshold_satisfactory']);
        $this->actingAs($user)->post(route('indicators.store'), $fields + ['annual_goal' => '83.259'])->assertSessionHasNoErrors();
        $this->assertSame('83259.00', Indicator::latest('id')->firstOrFail()->annual_goal);
        $this->put(route('indicators.update', $indicator), $fields + ['goal' => 1, 'annual_goal' => '1.200'])->assertSessionHasNoErrors();
        $this->assertSame(95, $indicator->fresh()->goal, 'The historical goal is not overwritten by an obsolete request field.');
        $this->assertSame('1200.00', $indicator->fresh()->annual_goal);
    }

    public function test_excel_has_one_compliance_column_and_correct_numeric_values(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $indicator = $this->indicator($user);
        $this->actingAs($user)->post(route('indicators.results.store', $indicator), $this->payload(['compliance_formula' => 'descendente']))->assertSessionHasNoErrors();
        $book = app(IndicatorExportService::class)->build($indicator->fresh());
        $sheet = $book->getSheetByName('Resultados');
        $this->assertSame(['ID', 'Periodo inicio', 'Periodo fin', 'Resultado', 'Meta', 'Cumplimiento', 'Formula cumplimiento', 'Evaluacion', 'Analisis', 'Accion No', 'Estado'], $sheet->rangeToArray('A3:K3')[0]);
        $this->assertSame(10.8, $sheet->getCell('D4')->getValue());
        $this->assertSame(11.8, $sheet->getCell('E4')->getValue());
        $this->assertSame(109.26, $sheet->getCell('F4')->getValue());
        $this->assertSame('Satisfactorio', $sheet->getCell('H4')->getValue());
        $this->assertSame('A3:K4', $sheet->getAutoFilter()->getRange());
        $labels = array_column($book->getSheetByName('Ficha tecnica')->toArray(), 0);
        $this->assertNotContains('Meta', $labels);
        $this->assertContains('Meta del año', $labels);
        $book->disconnectWorksheets();
    }

    public function test_migration_backs_up_original_values_and_reclassifies_all_categories_idempotently(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $rows = [];
        foreach (array_keys(Indicator::CATEGORIES) as $category) {
            $indicator = $this->indicator($user, $category);
            $rows[] = $indicator->results()->create($this->payload(['numerator' => 94, 'denominator' => 100]) + [
                'created_by' => $user->id, 'result' => 94, 'period_goal' => 95,
                'compliance' => 98.95, 'evaluation' => 'satisfactorio',
            ]);
        }
        $migration = require database_path('migrations/2026_10_27_100000_unify_indicator_compliance.php');
        $migration->up();
        foreach ($rows as $row) {
            $before = $row->updated_at->format('Y-m-d H:i:s');
            $row->refresh();
            $this->assertSame(94.0, (float) $row->compliance);
            $this->assertSame('aceptable', $row->evaluation);
            $this->assertSame(98.95, (float) $row->legacy_calculation['compliance']);
            $this->assertSame('satisfactorio', $row->legacy_calculation['evaluation']);
            $this->assertSame(95.0, (float) $row->period_goal);
            $this->assertSame('Análisis preservado', $row->analysis);
            $this->assertSame($before, $row->updated_at->format('Y-m-d H:i:s'));
        }
        $migration->up();
        $this->assertSame(98.95, (float) $rows[0]->fresh()->legacy_calculation['compliance']);
        $migration->down();
        foreach ($rows as $row) {
            $row->refresh();
            $this->assertSame(98.95, (float) $row->compliance);
            $this->assertSame('satisfactorio', $row->evaluation);
            $this->assertSame(94.0, (float) $row->numerator);
        }
        $migration->up();
    }

    public function test_sgc_charts_and_bsc_periods_use_the_same_compliance_as_results(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user);
        $sgc = $this->indicator($user);
        $this->post(route('indicators.results.store', $sgc), $this->payload(['compliance_formula' => 'descendente']))->assertSessionHasNoErrors();
        $dashboard = $this->get(route('indicators.index', ['categoria' => 'I']))->assertOk()->viewData('dashboard');
        $this->assertSame(109.26, $dashboard['average']);
        $this->assertSame(109.26, $dashboard['qualityObjectives']['compliance'][0]);
        $this->assertSame(1, $dashboard['stateChart']['data'][0]);
        $bsc = $this->indicator($user, 'II', ['measurement_unit' => 'pesos', 'annual_goal' => 40]);
        $this->post(route('indicators.results.store', $bsc), $this->payload(['compliance_formula' => 'descendente']))->assertSessionHasNoErrors();
        $this->post(route('indicators.results.store', $bsc), $this->payload([
            'period_start' => '2026-09-01', 'period_end' => '2026-09-30',
            'numerator' => '9,6', 'denominator' => '17,6', 'compliance_formula' => 'descendente',
        ]))->assertSessionHasNoErrors();
        $data = $this->get(route('indicators.index', ['categoria' => 'II', 'from' => '2026-01-01', 'to' => '2026-12-31']))->assertOk()->viewData('bsc');
        $tile = $data['sections'][0]['tiles'][0];
        $this->assertSame([109.26, 183.33], array_column($tile['periods'], 'compliance'));
        $this->assertSame(144.12, $tile['compliance']);
        $this->assertSame(196.08, $tile['annual']['compliance']);
        $this->assertSame([10.8, 9.6], array_column($tile['periods'], 'actual'));
        $this->assertSame([11.8, 17.6], array_column($tile['periods'], 'target'));
    }

    public function test_rollback_preserves_later_edits_instead_of_restoring_old_inputs(): void
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $indicator = $this->indicator($user);
        $result = $indicator->results()->create($this->payload(['numerator' => 10.8, 'denominator' => 11.8]) + [
            'created_by' => $user->id, 'period_goal' => 95, 'result' => 91.53,
            'compliance' => 96.35, 'evaluation' => 'satisfactorio',
        ]);
        $migration = require database_path('migrations/2026_10_27_100000_unify_indicator_compliance.php');
        $migration->up();
        $this->actingAs($user)->put(route('indicators.results.update', [$indicator, $result]), $this->payload([
            'numerator' => '40', 'denominator' => '100', 'analysis' => 'Actualizado después de migrar',
        ]))->assertSessionHasNoErrors();
        $migration->down();
        $result->refresh();
        $this->assertSame(40.0, (float) $result->numerator);
        $this->assertSame(100.0, (float) $result->denominator);
        $this->assertSame('Actualizado después de migrar', $result->analysis);
        $this->assertSame(42.11, (float) $result->compliance);
        $migration->up();
    }

    public function test_zero_and_negative_amounts_do_not_trigger_a_second_percentage_normalization(): void
    {
        $this->assertSame(0.0, IndicatorResult::calculateCompliance(0, 100));
        $this->assertSame(0.0, IndicatorResult::calculateCompliance(100, 0));
        $this->assertSame(0.0, IndicatorResult::calculateCompliance(0, 100, 'descendente'));
        $this->assertSame(-48.87, IndicatorResult::calculateCompliance(-668, 1367));
        $this->assertSame(109.26, IndicatorResult::calculateCompliance(10.8, 11.8, 'descendente'));
    }
}
