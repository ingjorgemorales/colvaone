<?php

namespace Tests\Feature;

use App\Models\BscPerspective;
use App\Models\Indicator;
use App\Models\IndicatorResult;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tablero BSC II. Las reglas salen de la reunion con Andres (21/09/2026):
 * misma ficha de dos variables, tarjeta de cumplimiento acumulado a la
 * izquierda y barras por periodo a la derecha, para cada indicador; lo que
 * es plata se suma, lo demas se promedia y algunos leen solo el ultimo mes.
 */
class BscDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 9, 22)->startOfDay());
    }

    // ------------------------------------------------------------ estructura

    public function test_muestra_todos_los_indicadores_agrupados_por_perspectiva_en_su_orden(): void
    {
        $user = $this->admin();
        $negocio = $this->perspective('Resultados de negocio');
        $cliente = $this->perspective('Cliente');

        $recompra = $this->indicator($user, ['name' => 'Recompra', 'bsc_perspective_id' => $cliente->id]);
        $ingresos = $this->indicator($user, ['name' => 'Ingresos totales', 'bsc_perspective_id' => $negocio->id]);
        $costos = $this->indicator($user, ['name' => 'Costos operativos', 'bsc_perspective_id' => $negocio->id, 'measurement_unit' => 'porcentaje']);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 7547, 6081);

        $response = $this->actingAs($user)->get('/indicators?categoria=II')->assertOk();
        $bsc = $response->viewData('bsc');

        $this->assertSame(['Resultados de negocio', 'Cliente'], array_column($bsc['sections'], 'name'));
        $this->assertSame(['Costos operativos', 'Ingresos totales'], array_column($bsc['sections'][0]['tiles'], 'name'));
        $this->assertSame([$recompra->id], array_column($bsc['sections'][1]['tiles'], 'id'));
        $this->assertSame(3, $bsc['total']);

        // Porcentajes y cantidades tambien entran: no es un tablero solo de pesos.
        $this->assertContains($costos->id, array_column($bsc['sections'][0]['tiles'], 'id'));

        // Solo se dibuja la grafica de quien tiene resultados.
        $this->assertSame([$ingresos->id], array_column($bsc['charts'], 'id'));
        $response->assertSee('id="bsc-chart-' . $ingresos->id . '"', false)->assertDontSee('id="bsc-chart-' . $recompra->id . '"', false);
        $response->assertSee('No hay resultados activos en este rango.');
    }

    public function test_los_indicadores_sin_perspectiva_quedan_al_final(): void
    {
        $user = $this->admin();
        $negocio = $this->perspective('Resultados de negocio');
        $this->indicator($user, ['name' => 'Sin clasificar', 'bsc_perspective_id' => null]);
        $this->indicator($user, ['name' => 'Ingresos totales', 'bsc_perspective_id' => $negocio->id]);

        $bsc = $this->actingAs($user)->get('/indicators?categoria=II')->viewData('bsc');

        $this->assertSame(['Resultados de negocio', 'Sin perspectiva BSC'], array_column($bsc['sections'], 'name'));
    }

    public function test_sin_indicadores_muestra_el_estado_vacio(): void
    {
        $this->actingAs($this->admin())->get('/indicators?categoria=II')
            ->assertOk()
            ->assertSee('Aún no hay indicadores BSC activos para tu usuario.')
            ->assertDontSee('chart.umd.min.js');
    }

    // ------------------------------------------------------------ acumulados

    /** El ejemplo de Andres: 63.448 / 57.061 da 111%, "pero como es mayor, dejo 100%". */
    public function test_plata_suma_los_periodos_y_el_tablero_lee_el_cumplimiento_hasta_cien(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user);
        $this->addResult($ingresos, '2026-01-01', '2026-01-31', 55901, 50980);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 7547, 6081);

        $response = $this->actingAs($user)->get('/indicators?categoria=II')->assertOk();
        $tile = $this->tile($response, $ingresos);

        $this->assertSame('sum', $tile['mode']);
        $this->assertSame(63448.0, $tile['actual']);
        $this->assertSame(57061.0, $tile['target']);
        $this->assertSame(111.19, $tile['compliance']);
        $this->assertSame(100.0, $tile['display']);
        $this->assertSame('Suma de 2 períodos', $tile['reference']);
        $this->assertSame([['label' => 'Real acumulado', 'value' => '$ 63.448'], ['label' => 'Presupuestado acumulado', 'value' => '$ 57.061']], $tile['figures']);

        // El cumplimiento mensual se conserva tal cual, aunque pase de 100.
        $this->assertSame(124.11, $tile['periods'][1]['compliance']);
        $this->assertSame('ene. 2026', $tile['periods'][0]['label']);

        // Un decimal, como el Excel de la junta.
        $response->assertSee('Calculado: <strong style="color:#475569">111,2%</strong>', false);
    }

    /**
     * Jorge (24/09/2026): "$ 57.061,36" se leia como 57 mil pesos con 36 centavos.
     * El Excel de la junta trabaja en millones y sin decimales: $ 57.061 millones.
     */
    public function test_los_millones_se_muestran_como_millones_y_sin_decimales(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user, ['measurement_unit' => 'millones']);
        $this->addResult($ingresos, '2026-01-01', '2026-01-31', 55901, 50980.36);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 7547, 6081);

        $response = $this->actingAs($user)->get('/indicators?categoria=II');
        $tile = $this->tile($response, $ingresos);

        $this->assertSame('sum', $tile['mode'], 'Los millones tambien son plata: se suman.');
        $this->assertSame(57061.36, $tile['target'], 'El calculo conserva los decimales.');
        $this->assertSame([
            ['label' => 'Real acumulado', 'value' => '$ 63.448 millones'],
            ['label' => 'Presupuestado acumulado', 'value' => '$ 57.061 millones'],
        ], $tile['figures']);
        $this->assertSame(111.19, $tile['compliance']);

        $chart = collect($response->viewData('bsc')['charts'])->firstWhere('id', $ingresos->id);
        $this->assertSame(['money' => true, 'prefix' => '$ ', 'suffix' => ' millones', 'decimals' => 0, 'caption' => 'Cifras en millones de pesos'], $chart['unit']);

        $response->assertSee('$ 57.061 millones')->assertDontSee('57.061,36');
    }

    public function test_los_pesos_tampoco_muestran_centavos(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 7547.4, 6081.6);

        $tile = $this->tile($this->actingAs($user)->get('/indicators?categoria=II'), $ingresos);

        $this->assertSame('$ 7.547', $tile['figures'][0]['value']);
        $this->assertSame('$ 6.082', $tile['figures'][1]['value']);
    }

    public function test_la_unidad_millones_se_puede_elegir_en_la_ficha(): void
    {
        $user = $this->admin();
        $indicator = $this->indicator($user, ['measurement_unit' => 'millones']);

        $this->assertTrue($indicator->isMoney());
        $this->assertSame('Suma de los periodos (automatico)', $indicator->accumulation_mode_label);
        $this->actingAs($user)->get(route('indicators.edit', $indicator))->assertOk()->assertSee('Millones de pesos (COP)');
        $this->get(route('indicators.show', $indicator))->assertOk()->assertSee('Millones de pesos (COP)');
    }

    /**
     * Comentarios de la revision (02/10/2026): falta el cumplimiento entre el resultado
     * acumulado y la meta del ano, la ultima barra del Excel: 63.448 / 83.259 = 76,2%.
     */
    public function test_el_avance_del_ano_compara_lo_acumulado_con_la_meta_del_ano(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user, ['measurement_unit' => 'millones', 'annual_goal' => 83259]);
        $this->addResult($ingresos, '2026-01-01', '2026-01-31', 55901, 50980.36);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 7547, 6081);

        $response = $this->actingAs($user)->get('/indicators?categoria=II');
        $tile = $this->tile($response, $ingresos);

        $this->assertSame(76.21, $tile['annual']['compliance']);
        $this->assertSame('76,2%', $tile['annual']['display_text']);
        $this->assertSame('$ 83.259 millones', $tile['annual']['goal_text']);
        $this->assertSame('$ 83.259 millones', $tile['annual_goal_text'], 'La meta del ano va tambien en el titulo, como en el Excel.');

        $response->assertSee('Avance frente a la meta del año')
            ->assertSee('Meta del año: $ 83.259 millones');
    }

    public function test_sin_meta_del_ano_la_tarjeta_explica_como_activarla(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 7547, 6081);

        $response = $this->actingAs($user)->get('/indicators?categoria=II');

        $this->assertNull($this->tile($response, $ingresos)['annual']['compliance']);
        $response->assertSee('Registra la meta del año en la ficha técnica');
    }

    /** Comentarios de la revision: tabla de datos bajo la grafica y cumplimiento con un decimal. */
    public function test_la_grafica_lleva_tabla_de_datos_con_un_decimal_en_el_cumplimiento(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user, ['measurement_unit' => 'millones']);
        $this->addResult($ingresos, '2026-01-01', '2026-01-31', 7613, 7315.92);
        $this->addResult($ingresos, '2026-02-01', '2026-02-28', 8288, 7655.5);

        $response = $this->actingAs($user)->get('/indicators?categoria=II');
        $periods = $this->tile($response, $ingresos)['periods'];

        // Como las celdas del Excel: "$ 7.316" (la unidad va arriba) y "104,1%".
        $this->assertSame('$ 7.316', $periods[0]['target_text']);
        $this->assertSame('$ 7.613', $periods[0]['actual_text']);
        $this->assertSame('104,1%', $periods[0]['compliance_text']);
        $this->assertSame('108,3%', $periods[1]['compliance_text']);

        $html = $response->getContent();
        $this->assertStringContainsString('class="bsc-data-table"', $html);
        $this->assertMatchesRegularExpression('/<td>\$ 7\.316<\/td>/', $html);
        $this->assertStringContainsString('<td>104,1%</td>', $html);
        $this->assertStringContainsString('Cifras en millones de pesos', $html);
    }

    public function test_la_meta_del_ano_se_guarda_con_formato_colombiano(): void
    {
        $user = $this->admin();
        $indicator = $this->indicator($user, ['measurement_unit' => 'millones']);

        $datos = fn (string $meta) => [
            'name' => 'Ingresos totales', 'category' => 'II', 'annual_goal' => $meta,
            'bsc_perspective_id' => $this->perspective('Resultados de negocio')->id,
            'quality_objective_id' => \App\Models\QualityObjective::firstOrCreate(['name' => 'Valor'], ['position' => 1, 'is_active' => true])->id,
            'objective' => 'Objetivo.', 'responsible_user_id' => $user->id, 'formula' => 'A / B',
            'measurement_unit' => 'millones', 'frequency' => 'mensual', 'type' => 'eficacia',
            'goal' => 100, 'threshold_acceptable' => 95, 'threshold_satisfactory' => 100,
        ];

        $this->actingAs($user)->put(route('indicators.update', $indicator), $datos('83.259'))->assertSessionHasNoErrors();
        $this->assertSame('83259.00', $indicator->fresh()->annual_goal);

        $this->put(route('indicators.update', $indicator), $datos('cero'))->assertSessionHasErrors('annual_goal');
        $this->put(route('indicators.update', $indicator), $datos('0'))->assertSessionHasErrors(['annual_goal' => 'La meta del año debe ser mayor que cero.']);

        $this->put(route('indicators.update', $indicator), $datos(''))->assertSessionHasNoErrors();
        $this->assertNull($indicator->fresh()->annual_goal, 'Vacio la quita.');

        $indicator->update(['annual_goal' => 83259]);
        $this->get(route('indicators.edit', $indicator))->assertOk()->assertSee('value="83259"', false);
        $this->get(route('indicators.show', $indicator))->assertOk()->assertSee('$ 83.259 millones');
    }

    public function test_porcentajes_y_cantidades_se_promedian(): void
    {
        $user = $this->admin();
        $costos = $this->indicator($user, ['name' => 'Costos operativos', 'measurement_unit' => 'porcentaje']);
        $this->addResult($costos, '2026-07-01', '2026-07-31', 90, 85.6);
        $this->addResult($costos, '2026-08-01', '2026-08-31', 80, 85.6);

        $tile = $this->tile($this->actingAs($user)->get('/indicators?categoria=II'), $costos);

        $this->assertSame('average', $tile['mode']);
        $this->assertSame(85.0, $tile['actual']);
        $this->assertSame(85.6, $tile['target']);
        $this->assertSame('Promedio de 2 períodos', $tile['reference']);
        $this->assertSame('Resultado promedio', $tile['figures'][0]['label']);
        $this->assertSame('85', $tile['figures'][0]['value']);
        $this->assertSame(99.3, $tile['compliance']);
    }

    /** Ratio de caja y novedades tecnicas "se grafican el ultimo mes". */
    public function test_el_modo_ultimo_periodo_lee_solo_el_mes_mas_reciente(): void
    {
        $user = $this->admin();
        $ratio = $this->indicator($user, [
            'name' => 'Ratio de caja',
            'measurement_unit' => 'cantidad',
            'accumulation_mode' => Indicator::ACCUMULATION_LAST,
        ]);
        $this->addResult($ratio, '2026-07-01', '2026-07-31', 1.9, 1.8);
        $this->addResult($ratio, '2026-08-01', '2026-08-31', 1.2, 1.5);

        $response = $this->actingAs($user)->get('/indicators?categoria=II');
        $tile = $this->tile($response, $ratio);

        $this->assertSame('last', $tile['mode']);
        $this->assertSame(1.2, $tile['actual']);
        $this->assertSame(1.5, $tile['target']);
        $this->assertSame(80.0, $tile['compliance']);
        $this->assertSame('Último período: ago. 2026', $tile['reference']);
        $this->assertCount(2, $tile['periods'], 'La grafica sigue mostrando todos los meses.');
        $response->assertSee('Cumplimiento del último período');
    }

    public function test_un_modo_explicito_manda_sobre_el_automatico(): void
    {
        $user = $this->admin();
        $plata = $this->indicator($user, ['accumulation_mode' => Indicator::ACCUMULATION_AVERAGE]);
        $porcentaje = $this->indicator($user, ['name' => 'Otro', 'measurement_unit' => 'porcentaje', 'accumulation_mode' => Indicator::ACCUMULATION_SUM]);

        $this->assertSame('average', $plata->accumulationMode());
        $this->assertSame('sum', $porcentaje->accumulationMode());
        $this->assertSame('Promedio de los periodos', $plata->accumulation_mode_label);
        $this->assertSame('Suma de los periodos (automatico)', $this->indicator($user, ['name' => 'Auto'])->accumulation_mode_label);
    }

    /** EBITDA del tablero de la junta: -668 real contra 1.367 estimado. */
    public function test_un_acumulado_negativo_se_lee_como_cero_sin_ocultar_el_calculo(): void
    {
        $user = $this->admin();
        $ebitda = $this->indicator($user, ['name' => 'EBITDA']);
        $meses = [[-523, -734], [-719, 456], [-173, 657], [-38, 655], [214, 571], [-348, 292], [392, -337], [527, -193]];

        foreach ($meses as $i => [$real, $estimado]) {
            $inicio = sprintf('2026-%02d-01', $i + 1);
            $this->addResult($ebitda, $inicio, date('Y-m-t', strtotime($inicio)), $real, $estimado);
        }

        $tile = $this->tile($this->actingAs($user)->get('/indicators?categoria=II'), $ebitda);

        $this->assertSame(-668.0, $tile['actual']);
        $this->assertSame(1367.0, $tile['target']);
        $this->assertSame(-48.87, $tile['compliance']);
        $this->assertSame(0.0, $tile['display']);
        $this->assertCount(8, $tile['periods']);
        $this->assertSame(-523.0, $tile['periods'][0]['actual'], 'Las barras conservan los negativos.');
    }

    public function test_con_meta_acumulada_negativa_no_inventa_un_porcentaje(): void
    {
        $user = $this->admin();
        $ebitda = $this->indicator($user, ['name' => 'EBITDA']);
        $this->addResult($ebitda, '2026-01-01', '2026-01-31', -523, -734);

        $tile = $this->tile($this->actingAs($user)->get('/indicators?categoria=II'), $ebitda);

        $this->assertNull($tile['compliance']);
        $this->assertStringContainsString('La meta acumulada es negativa', $tile['notice']);
        $this->assertSame('$ -523', $tile['figures'][0]['value']);
    }

    // --------------------------------------------------- guardas del acumulado

    public function test_meta_cero_deja_el_cumplimiento_sin_calcular_pero_ingreso_cero_es_real(): void
    {
        $user = $this->admin();
        $sinMeta = $this->indicator($user, ['name' => 'Sin meta']);
        $this->addResult($sinMeta, '2026-08-01', '2026-08-31', 100, 0);
        $sinVentas = $this->indicator($user, ['name' => 'Sin ventas']);
        $this->addResult($sinVentas, '2026-08-01', '2026-08-31', 0, 100);

        $response = $this->actingAs($user)->get('/indicators?categoria=II');

        $this->assertNull($this->tile($response, $sinMeta)['compliance']);
        $this->assertNull($this->tile($response, $sinMeta)['periods'][0]['compliance']);
        $this->assertSame(0.0, $this->tile($response, $sinVentas)['compliance']);
        $response->assertSee('El acumulado de la meta es cero');
    }

    public function test_periodos_superpuestos_no_se_cuentan_dos_veces(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 100, 100);
        $this->addResult($ingresos, '2026-08-15', '2026-09-15', 100, 100);

        $tile = $this->tile($this->actingAs($user)->get('/indicators?categoria=II'), $ingresos);

        $this->assertNull($tile['compliance']);
        $this->assertNull($tile['actual']);
        $this->assertStringContainsString('períodos superpuestos', $tile['notice']);
    }

    public function test_metas_o_formulas_distintas_no_se_promedian_en_silencio(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user);
        $this->addResult($ingresos, '2026-07-01', '2026-07-31', 100, 100);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 100, 100, ['period_goal' => 90]);

        $tile = $this->tile($this->actingAs($user)->get('/indicators?categoria=II'), $ingresos);

        $this->assertNull($tile['compliance']);
        $this->assertStringContainsString('metas o fórmulas diferentes', $tile['notice']);
    }

    // ------------------------------------------------------------- filtros

    public function test_por_defecto_lee_el_ano_en_curso_y_el_filtro_de_fechas_acota_todo(): void
    {
        $user = $this->admin();
        $ingresos = $this->indicator($user);
        $this->addResult($ingresos, '2025-12-01', '2025-12-31', 1000, 1000);
        $this->addResult($ingresos, '2026-07-01', '2026-07-31', 7000, 7000);
        $this->addResult($ingresos, '2026-08-01', '2026-08-31', 7547, 6081);
        $this->addResult($ingresos, '2026-06-01', '2026-06-30', 5, 5, ['status' => 'inactive']);

        $response = $this->actingAs($user)->get('/indicators?categoria=II');
        $this->assertSame('2026-01-01', $response->viewData('filters')['from']);
        $this->assertSame('2026-09-22', $response->viewData('filters')['to']);
        $this->assertFalse($response->viewData('filters')['custom']);
        $this->assertSame(['jul. 2026', 'ago. 2026'], array_column($this->tile($response, $ingresos)['periods'], 'label'));

        $response = $this->get('/indicators?categoria=II&from=2026-08-01&to=2026-08-31');
        $tile = $this->tile($response, $ingresos);
        $this->assertTrue($response->viewData('filters')['custom']);
        $this->assertSame(1, $tile['count']);
        $this->assertSame(7547.0, $tile['actual']);
        $this->assertSame(100.0, $tile['display']);
    }

    public function test_rango_de_fechas_invalido_se_rechaza(): void
    {
        $this->actingAs($this->admin())->from('/indicators?categoria=II')
            ->get('/indicators?categoria=II&from=2026-09-01&to=2026-08-01')
            ->assertRedirect('/indicators?categoria=II')->assertSessionHasErrors('to');
    }

    // ---------------------------------------------------------- permisos

    public function test_solo_ve_lo_suyo_de_la_categoria_y_activo(): void
    {
        Role::create(['name' => 'BSC propio', 'slug' => 'bsc-test', 'is_active' => true, 'permissions' => ['indicators.category_ii']]);
        $user = User::factory()->create(['role' => 'bsc-test']);
        $other = $this->admin();

        $own = $this->indicator($user, ['name' => 'Propio']);
        $this->indicator($other, ['name' => 'Ajeno']);
        $this->indicator($user, ['name' => 'Del SGC', 'category' => 'I']);
        $this->indicator($user, ['name' => 'Inactivo', 'status' => 'inactive']);
        $this->indicator($user, ['name' => 'Eliminado'])->delete();

        $bsc = $this->actingAs($user)->get('/indicators?categoria=II')->assertOk()->viewData('bsc');
        $ids = collect($bsc['sections'])->pluck('tiles')->flatten(1)->pluck('id')->all();

        $this->assertSame([$own->id], $ids);
        $this->get('/indicators?categoria=I')->assertForbidden();
    }

    public function test_sin_permiso_de_la_categoria_no_entra_aunque_vea_todo(): void
    {
        Role::create(['name' => 'SGC global', 'slug' => 'sgc-test', 'is_active' => true, 'permissions' => ['indicators.view_all', 'indicators.category_i']]);

        $this->actingAs(User::factory()->create(['role' => 'sgc-test']))
            ->get('/indicators?categoria=II')->assertForbidden();
    }

    public function test_el_dashboard_sgc_y_las_otras_categorias_siguen_igual(): void
    {
        $user = $this->admin();
        $sgc = $this->indicator($user, ['category' => 'I']);
        $this->addResult($sgc, '2026-08-01', '2026-08-31', 90, 100);

        $this->actingAs($user)->get('/indicators?categoria=I')
            ->assertOk()->assertSee('indicatorStateChart')->assertSee('Cumplimiento promedio')->assertDontSee('id="bsc-chart-', false);
        $this->get('/indicators?categoria=III')->assertOk()->assertSee('Dashboard pendiente')->assertDontSee('id="bsc-chart-', false);
        $this->get('/indicators?categoria=IV')->assertOk()->assertSee('Dashboard pendiente');
    }

    // ---------------------------------------------------------- ficha

    public function test_la_ficha_guarda_y_valida_el_modo_de_acumulado(): void
    {
        $user = $this->admin();
        $indicator = $this->indicator($user);

        $datos = fn (string $modo) => [
            'name' => 'Ratio de caja', 'category' => 'II', 'accumulation_mode' => $modo,
            'bsc_perspective_id' => $this->perspective('Resultados de negocio')->id,
            'quality_objective_id' => \App\Models\QualityObjective::firstOrCreate(['name' => 'Valor'], ['position' => 1, 'is_active' => true])->id,
            'objective' => 'Objetivo.', 'responsible_user_id' => $user->id, 'formula' => 'A / B',
            'measurement_unit' => 'cantidad', 'frequency' => 'mensual', 'type' => 'eficacia',
            'goal' => 100, 'threshold_acceptable' => 80, 'threshold_satisfactory' => 95,
        ];

        $this->actingAs($user)->put(route('indicators.update', $indicator), $datos('last'))->assertRedirect();
        $this->assertSame('last', $indicator->fresh()->accumulation_mode);

        $this->put(route('indicators.update', $indicator), $datos('cualquiera'))->assertSessionHasErrors('accumulation_mode');

        $this->put(route('indicators.update', $indicator), $datos(''))->assertRedirect();
        $this->assertNull($indicator->fresh()->accumulation_mode, 'Vacio vuelve al automatico.');

        $this->get(route('indicators.edit', $indicator))->assertOk()->assertSee('Acumulado en el dashboard');
        $this->get(route('indicators.show', $indicator))->assertOk()->assertSee('Promedio de los periodos (automatico)');
    }

    // ---------------------------------------------------------- ayudas

    private function admin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function perspective(string $name): BscPerspective
    {
        return BscPerspective::firstOrCreate(['name' => $name], ['position' => $name === 'Cliente' ? 20 : 10, 'is_active' => true]);
    }

    private function tile($response, Indicator $indicator): array
    {
        return collect($response->viewData('bsc')['sections'])
            ->pluck('tiles')->flatten(1)
            ->firstWhere('id', $indicator->id);
    }

    private function indicator(User $user, array $attributes = []): Indicator
    {
        return Indicator::create(array_replace([
            'name' => 'Ingresos totales',
            'category' => 'II',
            'objective' => 'Cumplir el presupuesto de ingresos.',
            'responsible_user_id' => $user->id,
            'formula' => '(Ingresos reales / Ingresos presupuestados) x 100',
            'measurement_unit' => 'pesos',
            'frequency' => 'mensual',
            'type' => 'eficacia',
            'goal' => 100,
            'goal_direction' => 'ascendente',
            'threshold_acceptable' => 95,
            'threshold_satisfactory' => 100,
            'status' => 'active',
            'created_by' => $user->id,
        ], $attributes));
    }

    private function addResult(Indicator $indicator, string $start, string $end, float $actual, float $target, array $attributes = []): IndicatorResult
    {
        $goal = $attributes['period_goal'] ?? 100;
        $direction = $attributes['compliance_formula'] ?? 'ascendente';
        $result = IndicatorResult::calculateResult($actual, $target);
        $compliance = IndicatorResult::calculateCompliance($result, $goal, $direction);

        return $indicator->results()->create(array_replace([
            'period_start' => $start,
            'period_end' => $end,
            'numerator' => $actual,
            'denominator' => $target,
            'result' => $result,
            'period_goal' => $goal,
            'compliance_formula' => $direction,
            'compliance' => $compliance,
            'evaluation' => $indicator->evaluate($compliance),
            'status' => 'active',
            'created_by' => $indicator->created_by,
        ], $attributes));
    }
}
