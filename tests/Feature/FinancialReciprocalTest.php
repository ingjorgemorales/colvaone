<?php

namespace Tests\Feature;

use App\Models\FinancialReciprocal;
use App\Models\FinancialReciprocalEntity;
use App\Models\FinancialReciprocalSetting;
use App\Models\Role;
use App\Models\User;
use App\Services\FinancialReciprocalExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReciprocalTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions = []): User
    {
        $role = Role::create([
            'name' => 'Test Role ' . Role::count(),
            'slug' => 'test-role-' . Role::count(),
            'permissions' => $permissions,
            'is_active' => true,
        ]);

        return User::factory()->create([
            'role' => $role->slug,
            'position' => 'Contador',
            'area' => 'Financiera',
        ]);
    }

    private function sampleData(array $overrides = []): array
    {
        return array_merge([
            'codigo_contable_subcuenta' => '1.2.22.06',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'valor_corriente' => '0',
            'valor_no_corriente' => '5.000.000',
            'fecha' => '2026-11-30',
        ], $overrides);
    }

    public function test_navigation_includes_financiera_and_reciprocas(): void
    {
        $items = config('navigation.items');
        $financialItem = collect($items)->firstWhere('name', 'Financiera');

        $this->assertNotNull($financialItem);
        $this->assertEquals('landmark', $financialItem['icon']);
        $this->assertCount(1, $financialItem['children']);
        $this->assertEquals('Reciprocas', $financialItem['children'][0]['name']);
        $this->assertEquals('financial.reciprocals.index', $financialItem['children'][0]['route']);
    }

    public function test_number_formatting_eliminates_unnecessary_decimals_and_corrects_thousands(): void
    {
        $this->assertEquals('0', FinancialReciprocal::formatNumber(0));
        $this->assertEquals('0', FinancialReciprocal::formatNumber('0.00'));
        $this->assertEquals('1.262', FinancialReciprocal::formatNumber(1262));
        $this->assertEquals('1.262', FinancialReciprocal::formatNumber('1262.00'));
        $this->assertNotEquals('1,26', FinancialReciprocal::formatNumber(1262));
        $this->assertEquals('5.000.000', FinancialReciprocal::formatNumber(5000000));
        $this->assertEquals('5.000.000', FinancialReciprocal::formatNumber('5000000.00'));
        $this->assertEquals('24.423.688.578', FinancialReciprocal::formatNumber(24423688578));
        $this->assertEquals('27.357', FinancialReciprocal::formatNumber(27357));
        $this->assertEquals('95.761', FinancialReciprocal::formatNumber(95761));
        $this->assertEquals('58.614', FinancialReciprocal::formatNumber(58614));
        $this->assertNotEquals('27,36', FinancialReciprocal::formatNumber(27357));
        $this->assertNotEquals('95,76', FinancialReciprocal::formatNumber(95761));
        $this->assertNotEquals('58,61', FinancialReciprocal::formatNumber(58614));
        $this->assertEquals('1.234,5', FinancialReciprocal::formatNumber('1234.50'));
        $this->assertEquals('1.234,56', FinancialReciprocal::formatNumber('1234.56'));

        // Amount parser tests
        $this->assertEquals(1262.0, FinancialReciprocal::parseAmount('1.262'));
        $this->assertEquals(1262.0, FinancialReciprocal::parseAmount('1,262'));
        $this->assertEquals(27357.0, FinancialReciprocal::parseAmount('27.357'));
        $this->assertEquals(95761.0, FinancialReciprocal::parseAmount('95.761'));
        $this->assertEquals(58614.0, FinancialReciprocal::parseAmount('58.614'));
        $this->assertEquals(27357.0, FinancialReciprocal::parseAmount('27,357'));
        $this->assertEquals(95761.0, FinancialReciprocal::parseAmount('95,761'));
        $this->assertEquals(58614.0, FinancialReciprocal::parseAmount('58,614'));
        $this->assertEquals(1262.0, FinancialReciprocal::parseAmount('1262'));
        $this->assertEquals(1262.0, FinancialReciprocal::parseAmount('1.262,00'));
        $this->assertEquals(1262.0, FinancialReciprocal::parseAmount('1,262.00'));
        $this->assertEquals(5000000.0, FinancialReciprocal::parseAmount('5.000.000'));
        $this->assertEquals(5000000.0, FinancialReciprocal::parseAmount('5,000,000'));
    }

    public function test_reciprocals_index_displays_clean_formatted_numbers_without_unnecessary_decimals(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.view', 'reciprocals.create']);

        FinancialReciprocal::create([
            'codigo_contable_subcuenta' => '1.2.22.06',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'valor_corriente' => 0,
            'valor_no_corriente' => 5000000.00,
            'fecha' => '2026-11-30',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        FinancialReciprocal::create([
            'codigo_contable_subcuenta' => '2.4.40.04',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911002',
            'nombre_entidad_reciproca' => 'Madrid - Cundinamarca',
            'valor_corriente' => 58614,
            'valor_no_corriente' => 0,
            'fecha' => '2026-11-30',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        FinancialReciprocal::create([
            'codigo_contable_subcuenta' => '2.4.40.04',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911003',
            'nombre_entidad_reciproca' => 'Santiago de Tolú',
            'valor_corriente' => 27357,
            'valor_no_corriente' => 0,
            'fecha' => '2026-11-30',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        FinancialReciprocal::create([
            'codigo_contable_subcuenta' => '2.4.40.04',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911004',
            'nombre_entidad_reciproca' => 'Granada - Meta',
            'valor_corriente' => 95761,
            'valor_no_corriente' => 0,
            'fecha' => '2026-11-30',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('financial.reciprocals.index'));

        $response->assertOk();

        // Institutional CGN metadata header block
        $response->assertSee('DEPARTAMENTO');
        $response->assertSee('CUNDINAMARCA');
        $response->assertSee('MUNICIPIO:');
        $response->assertSee('BOGOTA DISTRITO CAPITAL');
        $response->assertSee('ENTIDAD:');
        $response->assertSee('COMPAÑÍA COLOMBIANA DE SERVICIOS DE VALOR AGREGADO Y TELEMÁTICOS COLVATEL S.A. ESP');
        $response->assertSee('CODIGO:');
        $response->assertSee('269411001');
        $settings = FinancialReciprocalSetting::current();
        $response->assertSee('FECHA DE CORTE:');
        $response->assertSee($settings->fecha_corte);
        $response->assertSee('CGN2005_002_SALDO_DE_OPERACIONES RECIPROCAS');
        $response->assertSee('Cifras en Miles de Pesos');

        // Formatted numbers without unnecessary decimals
        $response->assertSee('5.000.000');
        $response->assertDontSee('5.000.000,00');
        $response->assertDontSee('$ 5.000.000');

        // Verify that thousands are formatted correctly and not as decimals
        $response->assertSee('58.614');
        $response->assertDontSee('58,61');
        $response->assertSee('27.357');
        $response->assertDontSee('27,36');
        $response->assertSee('95.761');
        $response->assertDontSee('95,76');

        // Filters and Toolbar
        $response->assertSee('Buscar.....');
        $response->assertSee('Fecha inicio:');
        $response->assertSee('Fecha fin:');
        $response->assertSee('Filtrar');
        $response->assertSee('Exportar');
        $response->assertSee('Ajustes');
        $response->assertSee('Agregar');

        // Delete button should NOT be present in table actions
        $response->assertDontSee('btn-delete');
    }

    public function test_create_page_renders_form_with_clean_number_inputs(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.create']);

        $response = $this->actingAs($user)->get(route('financial.reciprocals.create'));

        $response->assertOk();
        $response->assertSee('Agregar Operación Recíproca');
        $response->assertSee('Volver al listado');
        $response->assertSee('Importar');
        $response->assertSee('value="0"', false);
        $response->assertDontSee('value="0.00"', false);
        $response->assertDontSee('Observaciones');
    }

    public function test_show_view_has_only_one_volver_button_and_no_edit_button(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.view', 'reciprocals.edit']);

        $record = FinancialReciprocal::create([
            'codigo_contable_subcuenta' => '1.2.22.06',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'valor_corriente' => 0,
            'valor_no_corriente' => 5000000.00,
            'fecha' => '2026-11-30',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('financial.reciprocals.show', $record));
        $response->assertOk();

        // Numbers formatted without decimals
        $response->assertSee('5.000.000');
        $response->assertDontSee('5.000.000,00');

        // Exactly one "Volver al listado" button
        $content = $response->getContent();
        $this->assertEquals(1, substr_count($content, 'Volver al listado'), 'Show view must have exactly ONE Volver button');

        // MUST NOT have edit button
        $response->assertDontSee('Editar');
        $response->assertDontSee(route('financial.reciprocals.edit', $record));
    }

    public function test_can_store_reciprocal_with_thousands_dots(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.create']);

        $data = $this->sampleData();

        $response = $this->actingAs($user)->post(route('financial.reciprocals.store'), $data);

        $response->assertRedirect(route('financial.reciprocals.index'));
        $this->assertDatabaseHas('financial_reciprocals', [
            'codigo_contable_subcuenta' => '1.2.22.06',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'valor_corriente' => 0.00,
            'valor_no_corriente' => 5000000.00,
            'created_by' => $user->id,
        ]);
    }

    public function test_export_service_uses_format_code_without_unnecessary_decimals(): void
    {
        $record = new FinancialReciprocal([
            'codigo_contable_subcuenta' => '1.2.22.06',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'valor_corriente' => 0,
            'valor_no_corriente' => 5000000.00,
            'fecha' => '2026-11-30',
        ]);

        $service = new FinancialReciprocalExportService();
        $spreadsheet = $service->build(collect([$record]));
        $sheet = $spreadsheet->getActiveSheet();

        // Check format code of columns E and F
        $formatCode = $sheet->getStyle('E8')->getNumberFormat()->getFormatCode();
        $this->assertEquals('#,##0.##;(#,##0.##);0', $formatCode);

        // Row 7 Headers: Exactly 6 columns (A to F)
        $this->assertEquals('Codigo Contable Subcuenta', $sheet->getCell('A7')->getValue());
        $this->assertEquals('Valor Corriente', $sheet->getCell('E7')->getValue());
        $this->assertEquals('Valor No Corriente', $sheet->getCell('F7')->getValue());
        $this->assertNull($sheet->getCell('G7')->getValue());

        // Row 8 Data
        $this->assertEquals(0, $sheet->getCell('E8')->getValue());
        $this->assertEquals(5000000, $sheet->getCell('F8')->getValue());

        // Row 9 must be empty
        $this->assertNull($sheet->getCell('A9')->getValue());
    }

    public function test_settings_view_renders_tabs_and_entities(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.view', 'reciprocals.edit']);

        FinancialReciprocalEntity::create([
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Default tab: institucional
        $response = $this->actingAs($user)->get(route('financial.reciprocals.settings'));
        $response->assertOk();
        $response->assertSee('Parámetros Institucionales');
        $response->assertSee('Entidades Recíprocas');
        $response->assertSee('CGN2005_002');

        // Tab entidades
        $responseEntities = $this->actingAs($user)->get(route('financial.reciprocals.settings', ['tab' => 'entidades']));
        $responseEntities->assertOk();
        $responseEntities->assertSee('Catálogo de Entidades Recíprocas');
        $responseEntities->assertSee('Agregar Nueva Entidad Recíproca');
        $responseEntities->assertSee('229911001');
        $responseEntities->assertSee('AGUAS DE BOGOTA S.A. E.S.P.');
    }

    public function test_can_create_update_and_toggle_reciprocal_entity(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.view', 'reciprocals.edit']);

        // Create
        $response = $this->actingAs($user)->post(route('financial.reciprocals.entities.store'), [
            'codigo_entidad_reciproca' => '999888777',
            'nombre_entidad_reciproca' => 'EMPRESA PUBLICA DE PRUEBA ESP',
        ]);
        $response->assertRedirect(route('financial.reciprocals.settings', ['tab' => 'entidades']));

        $this->assertDatabaseHas('financial_reciprocal_entities', [
            'codigo_entidad_reciproca' => '999888777',
            'nombre_entidad_reciproca' => 'EMPRESA PUBLICA DE PRUEBA ESP',
            'is_active' => true,
        ]);

        $entity = FinancialReciprocalEntity::where('codigo_entidad_reciproca', '999888777')->firstOrFail();

        // Update
        $updateResponse = $this->actingAs($user)->put(route('financial.reciprocals.entities.update', $entity), [
            'codigo_entidad_reciproca' => '999888777',
            'nombre_entidad_reciproca' => 'EMPRESA PUBLICA RENOMBRADA ESP',
        ]);
        $updateResponse->assertRedirect(route('financial.reciprocals.settings', ['tab' => 'entidades']));
        $this->assertDatabaseHas('financial_reciprocal_entities', [
            'id' => $entity->id,
            'nombre_entidad_reciproca' => 'EMPRESA PUBLICA RENOMBRADA ESP',
        ]);

        // Toggle
        $toggleResponse = $this->actingAs($user)->post(route('financial.reciprocals.entities.toggle', $entity));
        $toggleResponse->assertRedirect(route('financial.reciprocals.settings', ['tab' => 'entidades']));
        $this->assertDatabaseHas('financial_reciprocal_entities', [
            'id' => $entity->id,
            'is_active' => false,
        ]);
    }

    public function test_search_entities_endpoint(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.view']);

        FinancialReciprocalEntity::create([
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Exact code lookup
        $responseCode = $this->actingAs($user)->get(route('financial.reciprocals.entities.search', ['codigo' => '229911001']));
        $responseCode->assertOk();
        $responseCode->assertJson([
            'found' => true,
            'entity' => [
                'codigo_entidad_reciproca' => '229911001',
                'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            ],
        ]);

        // General term search
        $responseSearch = $this->actingAs($user)->get(route('financial.reciprocals.entities.search', ['q' => 'BOGOTA']));
        $responseSearch->assertOk();
        $this->assertNotEmpty($responseSearch->json());
    }

    public function test_create_and_edit_page_renders_entities_datalist(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.create', 'reciprocals.edit']);

        FinancialReciprocalEntity::create([
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $record = FinancialReciprocal::create([
            'codigo_contable_subcuenta' => '1.2.22.06',
            'nombre_subcuenta' => 'Sociedades públicas',
            'codigo_entidad_reciproca' => '229911001',
            'nombre_entidad_reciproca' => 'AGUAS DE BOGOTA S.A. E.S.P.',
            'valor_corriente' => 0,
            'valor_no_corriente' => 5000000,
            'fecha' => '2026-11-30',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Create page
        $createResponse = $this->actingAs($user)->get(route('financial.reciprocals.create'));
        $createResponse->assertOk();
        $createResponse->assertSee('id="entities_codes_list"', false);
        $createResponse->assertSee('id="entities_names_list"', false);
        $createResponse->assertSee('229911001');

        // Edit page
        $editResponse = $this->actingAs($user)->get(route('financial.reciprocals.edit', $record));
        $editResponse->assertOk();
        $editResponse->assertSee('id="entities_codes_list"', false);
        $editResponse->assertSee('id="entities_names_list"', false);
        $editResponse->assertSee('229911001');
    }

    public function test_quarterly_filtering_and_dynamic_fecha_corte_in_index_and_excel(): void
    {
        $user = $this->userWith(['financial.view', 'reciprocals.view', 'reciprocals.export']);

        // Helper test
        $this->assertEquals('1 a 31 de Marzo 2026', FinancialReciprocal::resolveFechaCorte('2026-01-01', '2026-03-31'));
        $this->assertEquals('1 a 30 de Junio 2026', FinancialReciprocal::resolveFechaCorte('2026-04-01', '2026-06-30'));
        $this->assertEquals('1 a 30 de Septiembre 2026', FinancialReciprocal::resolveFechaCorte('2026-07-01', '2026-09-30'));
        $this->assertEquals('1 a 31 de Diciembre 2026', FinancialReciprocal::resolveFechaCorte('2026-10-01', '2026-12-31'));

        // Q1 Filter in Index view
        $resQ1 = $this->actingAs($user)->get(route('financial.reciprocals.index', [
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-03-31',
        ]));
        $resQ1->assertOk();
        $resQ1->assertSee('FECHA DE CORTE:');
        $resQ1->assertSee('1 a 31 de Marzo 2026');

        // Q2 Filter in Index view
        $resQ2 = $this->actingAs($user)->get(route('financial.reciprocals.index', [
            'fecha_inicio' => '2026-04-01',
            'fecha_fin' => '2026-06-30',
        ]));
        $resQ2->assertOk();
        $resQ2->assertSee('1 a 30 de Junio 2026');

        // Q3 Filter in Index view
        $resQ3 = $this->actingAs($user)->get(route('financial.reciprocals.index', [
            'fecha_inicio' => '2026-07-01',
            'fecha_fin' => '2026-09-30',
        ]));
        $resQ3->assertOk();
        $resQ3->assertSee('1 a 30 de Septiembre 2026');

        // Q4 Filter in Index view
        $resQ4 = $this->actingAs($user)->get(route('financial.reciprocals.index', [
            'fecha_inicio' => '2026-10-01',
            'fecha_fin' => '2026-12-31',
        ]));
        $resQ4->assertOk();
        $resQ4->assertSee('1 a 31 de Diciembre 2026');

        // Passing trimestre=1 directly
        $resTrim1 = $this->actingAs($user)->get(route('financial.reciprocals.index', [
            'trimestre' => '1',
            'year' => 2026,
        ]));
        $resTrim1->assertOk();
        $resTrim1->assertSee('1 a 31 de Marzo 2026');

        // Excel export service with Q1 cut-off
        $service = new FinancialReciprocalExportService();
        $spreadsheet = $service->build(collect([]), [], $user, '1 a 31 de Marzo 2026');
        $this->assertEquals('1 a 31 de Marzo 2026', $spreadsheet->getActiveSheet()->getCell('B5')->getValue());

        // Streamed export download responds successfully
        $resExcel = $this->actingAs($user)->get(route('financial.reciprocals.export', [
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-03-31',
        ]));
        $resExcel->assertOk();
    }
}
