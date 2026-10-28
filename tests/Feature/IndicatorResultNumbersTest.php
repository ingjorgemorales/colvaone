<?php

namespace Tests\Feature;

use App\Models\Indicator;
use App\Models\IndicatorResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Las cifras de resultados se copian de la tabla o del Excel con el formato
 * colombiano. Antes "7.547" se guardaba como 7,55 sin ningun aviso.
 */
class IndicatorResultNumbersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guarda_las_cifras_escritas_con_formato_colombiano(): void
    {
        [$user, $indicator] = $this->preparar();

        $this->actingAs($user)
            ->post(route('indicators.results.store', $indicator), $this->datos('7.547', '6.081,04'))
            ->assertRedirect()->assertSessionHasNoErrors();

        $result = $indicator->results()->firstOrFail();
        $this->assertSame('7547.00', $result->numerator);
        $this->assertSame('6081.04', $result->denominator);
        $this->assertSame(124.11, (float) $result->result);
        $this->assertSame(124.11, (float) $result->compliance);
    }

    public function test_lo_que_ya_funcionaba_sigue_igual(): void
    {
        [$user, $indicator] = $this->preparar();

        $this->actingAs($user)->post(route('indicators.results.store', $indicator), $this->datos('7547', '5959.82'))
            ->assertSessionHasNoErrors();

        $result = $indicator->results()->firstOrFail();
        $this->assertSame('7547.00', $result->numerator);
        $this->assertSame('5959.82', $result->denominator);
    }

    public function test_al_editar_tambien_se_leen_bien(): void
    {
        [$user, $indicator] = $this->preparar();
        $this->actingAs($user)->post(route('indicators.results.store', $indicator), $this->datos('1', '1'));
        $result = $indicator->results()->firstOrFail();

        $this->put(route('indicators.results.update', [$indicator, $result]), $this->datos('7.732', '5.959,82'))
            ->assertSessionHasNoErrors();

        $result->refresh();
        $this->assertSame('7732.00', $result->numerator);
        $this->assertSame('5959.82', $result->denominator);
        $this->assertNull($result->period_goal);
        $this->assertSame(129.74, (float) $result->result);
        $this->assertSame(129.74, (float) $result->compliance);
    }

    public function test_una_cifra_ilegible_se_rechaza_con_un_mensaje_claro(): void
    {
        [$user, $indicator] = $this->preparar();

        $this->actingAs($user)->post(route('indicators.results.store', $indicator), $this->datos('7.5.4', '6.081'))
            ->assertSessionHasErrors(['numerator' => 'El resultado no es un número válido. Escríbelo como 7547, 7.547 o 5.959,82.']);

        $this->assertSame(0, IndicatorResult::count());
    }

    public function test_un_denominador_cero_escrito_con_decimales_sigue_siendo_cero(): void
    {
        [$user, $indicator] = $this->preparar();

        $this->actingAs($user)->post(route('indicators.results.store', $indicator), $this->datos('7.547', '0,00'))
            ->assertSessionHasErrors(['denominator' => 'La meta no puede ser cero.']);
    }

    public function test_el_formulario_acepta_texto_y_edita_con_coma_decimal(): void
    {
        [$user, $indicator] = $this->preparar();
        $this->actingAs($user)->post(route('indicators.results.store', $indicator), $this->datos('7.547', '6.081,04'));

        $html = $this->get(route('indicators.show', $indicator))->assertOk()->getContent();

        $this->assertStringContainsString('name="numerator" type="text" inputmode="decimal"', $html);
        $this->assertStringContainsString('window.parseLocalizedNumber', $html);
        $this->assertStringContainsString("denominator: '6081,04'", $html, 'Al editar llega con coma decimal, sin puntos que confundan.');
        $this->assertStringNotContainsString('type="number"', $html);
    }

    private function preparar(): array
    {
        $user = User::factory()->create(['role' => 'superadmin']);

        $indicator = Indicator::create([
            'name' => 'Ingresos totales', 'category' => 'II', 'objective' => 'Objetivo.',
            'responsible_user_id' => $user->id, 'formula' => 'Real / Presupuestado',
            'measurement_unit' => 'pesos', 'frequency' => 'mensual', 'type' => 'eficacia',
            'goal' => 100, 'goal_direction' => 'ascendente',
            'threshold_acceptable' => 95, 'threshold_satisfactory' => 100,
            'status' => 'active', 'created_by' => $user->id,
        ]);

        return [$user, $indicator];
    }

    private function datos(string $numerador, string $denominador): array
    {
        return [
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-31',
            'numerator' => $numerador,
            'denominator' => $denominador,
            'compliance_formula' => 'ascendente',
        ];
    }
}
