<?php

namespace App\Http\Controllers;

use App\Models\FinancialReciprocal;
use App\Models\FinancialReciprocalEntity;
use App\Models\FinancialReciprocalSetting;
use App\Services\FinancialReciprocalExportService;
use App\Support\LocalizedNumber;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialReciprocalController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $trimestre = $request->input('trimestre');
        $year = $request->input('year') ? (int) $request->input('year') : null;
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // If quarter is selected but start/end dates are not explicitly set or differ
        if ($trimestre && in_array((int) $trimestre, [1, 2, 3, 4], true)) {
            if (empty($fechaInicio) || empty($fechaFin)) {
                $qDates = FinancialReciprocal::getQuarterDates((int) $trimestre, $year);
                $fechaInicio = $qDates['inicio'];
                $fechaFin = $qDates['fin'];
            }
        } elseif (!empty($fechaInicio) && !empty($fechaFin)) {
            // Detect quarter from dates if not provided
            $detected = FinancialReciprocal::detectQuarter($fechaInicio, $fechaFin);
            if ($detected && empty($trimestre)) {
                $trimestre = (string) $detected;
            }
        }

        $baseQuery = FinancialReciprocal::query()
            ->search($search)
            ->dateRange($fechaInicio, $fechaFin);

        $records = $baseQuery
            ->with(['creator'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        $settings = FinancialReciprocalSetting::current();
        $fechaCorte = FinancialReciprocal::resolveFechaCorte(
            $fechaInicio,
            $fechaFin,
            $settings->fecha_corte,
            $trimestre ? (int) $trimestre : null,
            $year
        );

        return view('financial.reciprocals.index', [
            'records' => $records,
            'settings' => $settings,
            'search' => $search,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'trimestre' => $trimestre,
            'selectedYear' => $year,
            'fechaCorte' => $fechaCorte,
        ]);
    }

    public function settings(Request $request): View
    {
        $settings = FinancialReciprocalSetting::current();
        $tab = $request->query('tab', 'institucional');
        if (!in_array($tab, ['institucional', 'entidades'], true)) {
            $tab = 'institucional';
        }

        $searchEntity = trim((string) $request->query('buscar_entidad', ''));

        $entitiesQuery = FinancialReciprocalEntity::query()
            ->search($searchEntity)
            ->orderBy('nombre_entidad_reciproca');

        $entities = $entitiesQuery->paginate(20)->withQueryString();
        $totalEntities = FinancialReciprocalEntity::count();
        $activeEntities = FinancialReciprocalEntity::active()->count();

        return view('financial.reciprocals.settings', compact(
            'settings',
            'tab',
            'entities',
            'searchEntity',
            'totalEntities',
            'activeEntities'
        ));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'departamento' => 'required|string|max:150',
            'municipio' => 'required|string|max:150',
            'entidad' => 'required|string|max:255',
            'codigo' => 'required|string|max:50',
            'fecha_corte' => 'required|string|max:150',
            'formato' => 'required|string|max:150',
            'cifras' => 'required|string|max:100',
        ], [
            'departamento.required' => 'El departamento es obligatorio.',
            'municipio.required' => 'El municipio es obligatorio.',
            'entidad.required' => 'La entidad es obligatoria.',
            'codigo.required' => 'El código es obligatorio.',
            'fecha_corte.required' => 'La fecha de corte es obligatoria.',
            'formato.required' => 'El nombre del formato CGN es obligatorio.',
            'cifras.required' => 'La expresión de cifras es obligatoria.',
        ]);

        $validated['updated_by'] = $request->user()->id;

        $settings = FinancialReciprocalSetting::current();
        $settings->update($validated);

        return redirect()
            ->route('financial.reciprocals.settings', ['tab' => 'institucional'])
            ->with('success', 'Ajustes del formato CGN actualizados correctamente.');
    }

    public function storeEntity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_entidad_reciproca' => 'required|string|max:50|unique:financial_reciprocal_entities,codigo_entidad_reciproca',
            'nombre_entidad_reciproca' => 'required|string|max:255',
        ], [
            'codigo_entidad_reciproca.required' => 'El código de la entidad recíproca es obligatorio.',
            'codigo_entidad_reciproca.unique' => 'Ya existe una entidad registrada con este código.',
            'nombre_entidad_reciproca.required' => 'El nombre de la entidad recíproca es obligatorio.',
        ]);

        $validated['codigo_entidad_reciproca'] = trim($validated['codigo_entidad_reciproca']);
        $validated['nombre_entidad_reciproca'] = trim($validated['nombre_entidad_reciproca']);
        $validated['is_active'] = true;
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        FinancialReciprocalEntity::create($validated);

        return redirect()
            ->route('financial.reciprocals.settings', ['tab' => 'entidades'])
            ->with('success', 'Entidad recíproca agregada correctamente.');
    }

    public function updateEntity(Request $request, FinancialReciprocalEntity $entity): RedirectResponse
    {
        $validated = $request->validate([
            'codigo_entidad_reciproca' => 'required|string|max:50|unique:financial_reciprocal_entities,codigo_entidad_reciproca,' . $entity->id,
            'nombre_entidad_reciproca' => 'required|string|max:255',
        ], [
            'codigo_entidad_reciproca.required' => 'El código de la entidad recíproca es obligatorio.',
            'codigo_entidad_reciproca.unique' => 'Ya existe otra entidad registrada con este código.',
            'nombre_entidad_reciproca.required' => 'El nombre de la entidad recíproca es obligatorio.',
        ]);

        $validated['codigo_entidad_reciproca'] = trim($validated['codigo_entidad_reciproca']);
        $validated['nombre_entidad_reciproca'] = trim($validated['nombre_entidad_reciproca']);
        $validated['updated_by'] = $request->user()->id;

        $entity->update($validated);

        return redirect()
            ->route('financial.reciprocals.settings', ['tab' => 'entidades'])
            ->with('success', 'Entidad recíproca actualizada correctamente.');
    }

    public function toggleEntity(Request $request, FinancialReciprocalEntity $entity): RedirectResponse
    {
        $entity->update([
            'is_active' => !$entity->is_active,
            'updated_by' => $request->user()->id,
        ]);

        $estado = $entity->is_active ? 'activada' : 'inactivada';

        return redirect()
            ->route('financial.reciprocals.settings', ['tab' => 'entidades'])
            ->with('success', "Entidad recíproca {$estado} correctamente.");
    }

    public function searchEntities(Request $request): JsonResponse
    {
        $codigo = trim((string) $request->query('codigo', ''));
        if ($codigo !== '') {
            $entity = FinancialReciprocalEntity::active()
                ->where('codigo_entidad_reciproca', $codigo)
                ->first();

            return response()->json([
                'found' => (bool) $entity,
                'entity' => $entity,
            ]);
        }

        $term = trim((string) $request->query('q', ''));
        $entities = FinancialReciprocalEntity::active()
            ->search($term)
            ->orderBy('nombre_entidad_reciproca')
            ->limit(30)
            ->get(['id', 'codigo_entidad_reciproca', 'nombre_entidad_reciproca']);

        return response()->json($entities);
    }

    public function create(): View
    {
        $knownEntities = FinancialReciprocalEntity::active()
            ->orderBy('nombre_entidad_reciproca')
            ->get(['id', 'codigo_entidad_reciproca', 'nombre_entidad_reciproca']);

        return view('financial.reciprocals.create', [
            'reciprocal' => new FinancialReciprocal([
                'fecha' => now()->toDateString(),
                'valor_corriente' => 0,
                'valor_no_corriente' => 0,
            ]),
            'knownEntities' => $knownEntities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAndPrepare($request);

        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        $record = FinancialReciprocal::create($validated);

        return redirect()
            ->route('financial.reciprocals.index')
            ->with('success', "Operación recíproca #{$record->id} registrada correctamente.");
    }

    public function show(FinancialReciprocal $reciprocal): View
    {
        $reciprocal->load(['creator', 'updater']);

        return view('financial.reciprocals.show', [
            'reciprocal' => $reciprocal,
        ]);
    }

    public function edit(FinancialReciprocal $reciprocal): View
    {
        $knownEntities = FinancialReciprocalEntity::active()
            ->orderBy('nombre_entidad_reciproca')
            ->get(['id', 'codigo_entidad_reciproca', 'nombre_entidad_reciproca']);

        return view('financial.reciprocals.edit', [
            'reciprocal' => $reciprocal,
            'knownEntities' => $knownEntities,
        ]);
    }

    public function update(Request $request, FinancialReciprocal $reciprocal): RedirectResponse
    {
        $validated = $this->validateAndPrepare($request);

        $validated['updated_by'] = $request->user()->id;

        $reciprocal->update($validated);

        return redirect()
            ->route('financial.reciprocals.index')
            ->with('success', "Operación recíproca #{$reciprocal->id} actualizada correctamente.");
    }

    public function destroy(FinancialReciprocal $reciprocal): RedirectResponse
    {
        $reciprocal->delete();

        return redirect()
            ->route('financial.reciprocals.index')
            ->with('success', "Operación recíproca #{$reciprocal->id} eliminada correctamente.");
    }

    public function toggleActive(FinancialReciprocal $reciprocal): RedirectResponse
    {
        $reciprocal->update([
            'is_active' => !$reciprocal->is_active,
            'updated_by' => auth()->id(),
        ]);

        $estado = $reciprocal->is_active ? 'activada' : 'inactivada';

        return redirect()
            ->route('financial.reciprocals.index')
            ->with('success', "Operación recíproca #{$reciprocal->id} {$estado} correctamente.");
    }

    public function export(Request $request, FinancialReciprocalExportService $exportService): BinaryFileResponse
    {
        $search = $request->input('search');
        $trimestre = $request->input('trimestre');
        $year = $request->input('year') ? (int) $request->input('year') : null;
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        if ($trimestre && in_array((int) $trimestre, [1, 2, 3, 4], true)) {
            if (empty($fechaInicio) || empty($fechaFin)) {
                $qDates = FinancialReciprocal::getQuarterDates((int) $trimestre, $year);
                $fechaInicio = $qDates['inicio'];
                $fechaFin = $qDates['fin'];
            }
        } elseif (!empty($fechaInicio) && !empty($fechaFin)) {
            $detected = FinancialReciprocal::detectQuarter($fechaInicio, $fechaFin);
            if ($detected && empty($trimestre)) {
                $trimestre = (string) $detected;
            }
        }

        $baseQuery = FinancialReciprocal::query()
            ->search($search)
            ->dateRange($fechaInicio, $fechaFin);

        $records = (clone $baseQuery)
            ->with(['creator'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $settings = FinancialReciprocalSetting::current();
        $fechaCorte = FinancialReciprocal::resolveFechaCorte(
            $fechaInicio,
            $fechaFin,
            $settings->fecha_corte,
            $trimestre ? (int) $trimestre : null,
            $year
        );

        $filterLabels = [];
        if (filled($search)) {
            $filterLabels[] = "Búsqueda: '{$search}'";
        }
        if ($trimestre) {
            $filterLabels[] = "Trimestre: {$trimestre}";
        }
        if (filled($fechaInicio)) {
            $filterLabels[] = "Desde: {$fechaInicio}";
        }
        if (filled($fechaFin)) {
            $filterLabels[] = "Hasta: {$fechaFin}";
        }

        $spreadsheet = $exportService->build($records, $filterLabels, $request->user(), $fechaCorte);
        $filename = 'CGN2005-002-Operaciones-Reciprocas-' . now()->format('Ymd-His') . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'reciprocal_exp_');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ])->deleteFileAfterSend(true);
    }

    public function downloadTemplate(FinancialReciprocalExportService $exportService): BinaryFileResponse
    {
        $spreadsheet = $exportService->buildTemplate();
        $filename = 'Plantilla-CGN2005-002-Operaciones-Reciprocas.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'reciprocal_tpl_');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ])->deleteFileAfterSend(true);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ], [
            'archivo.required' => 'Debes seleccionar un archivo para importar.',
            'archivo.file' => 'El archivo seleccionado no es válido.',
            'archivo.uploaded' => 'El archivo no pudo subirse al servidor. Verifica que sea un archivo válido (.xlsx, .xls o .csv) y no exceda 10 MB.',
            'archivo.mimes' => 'El archivo debe estar en formato Excel (.xlsx, .xls) o CSV.',
            'archivo.max' => 'El tamaño máximo permitido es 10MB.',
        ]);

        $file = $request->file('archivo');

        try {
            $reader = IOFactory::createReaderForFile($file->getRealPath());
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            if (method_exists($reader, 'setReadEmptyCells')) {
                $reader->setReadEmptyCells(false);
            }
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, false, false);

            if (empty($rows) || count($rows) < 2) {
                return back()->withErrors(['archivo' => 'El archivo no contiene filas de datos para procesar.'])->withInput();
            }

            // Detect header row (official CGN file has headers around row 7, plain files around row 1)
            $headerRowIndex = null;

            for ($r = 0; $r < min(20, count($rows)); $r++) {
                $rowCells = array_filter(array_map('strval', $rows[$r]));
                $rowStr = mb_strtolower(implode(' ', $rowCells));

                if (str_contains($rowStr, 'codigo contable') || str_contains($rowStr, 'subcuenta')) {
                    $headerRowIndex = $r;
                    break;
                }
            }

            if ($headerRowIndex === null) {
                $headerRowIndex = 0;
            }

            $rawHeaders = array_map(fn ($h) => mb_strtolower(trim((string) $h)), $rows[$headerRowIndex]);

            $findCol = function (array $candidates) use ($rawHeaders): ?int {
                foreach ($candidates as $cand) {
                    $idx = array_search(mb_strtolower($cand), $rawHeaders, true);
                    if ($idx !== false) {
                        return $idx;
                    }
                }
                foreach ($candidates as $cand) {
                    foreach ($rawHeaders as $idx => $header) {
                        if (str_contains($header, mb_strtolower($cand))) {
                            return $idx;
                        }
                    }
                }
                return null;
            };

            $colCodSubcuenta = $findCol(['codigo contable subcuenta', 'codigo contable', 'cod subcuenta', 'subcuenta']);
            $colNomSubcuenta = $findCol(['nombre de la subcuenta', 'nombre subcuenta', 'nom subcuenta']);
            $colCodEntidad = $findCol(['codigo entidad reciproca', 'codigo entidad', 'cod entidad', 'entidad reciproca']);
            $colNomEntidad = $findCol(['nombre entidad reciproca', 'nombre entidad', 'nom entidad']);
            $colValCorriente = $findCol(['valor corriente', 'corriente']);
            $colValNoCorriente = $findCol(['valor no corriente', 'no corriente']);
            $colFecha = $findCol(['fecha']);

            if ($colCodSubcuenta === null || $colNomSubcuenta === null || $colCodEntidad === null || $colNomEntidad === null) {
                return back()->withErrors(['archivo' => 'No se pudieron identificar las columnas requeridas (Código Contable Subcuenta, Nombre de la Subcuenta, Código Entidad Recíproca, Nombre Entidad Recíproca).'])->withInput();
            }

            $rowsToInsert = [];
            $entitiesToInsert = [];
            $rowErrors = [];
            $userId = $request->user()->id;
            // Default date: upload date (fecha en la que se sube el documento)
            $fechaSubida = now()->toDateString();

            for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $excelRowNum = $i + 1;

                $codSub = trim((string) ($row[$colCodSubcuenta] ?? ''));
                $nomSub = trim((string) ($row[$colNomSubcuenta] ?? ''));
                $codEnt = trim((string) ($row[$colCodEntidad] ?? ''));
                $nomEnt = trim((string) ($row[$colNomEntidad] ?? ''));
                $valCorrRaw = $colValCorriente !== null ? ($row[$colValCorriente] ?? '') : '';
                $valNoCorrRaw = $colValNoCorriente !== null ? ($row[$colValNoCorriente] ?? '') : '';

                // Skip completely empty row
                if ($codSub === '' && $nomSub === '' && $codEnt === '' && $nomEnt === '' && $valCorrRaw === '' && $valNoCorrRaw === '') {
                    continue;
                }

                // Skip summary or totals rows that may appear at the bottom
                if (mb_stripos($codSub, 'total') !== false || mb_stripos($nomSub, 'total') !== false || mb_stripos($codEnt, 'total') !== false) {
                    continue;
                }

                // Field validations per row with detailed error messages
                $missing = [];
                if ($codSub === '') {
                    $missing[] = 'Código Contable Subcuenta';
                }
                if ($nomSub === '') {
                    $missing[] = 'Nombre de la Subcuenta';
                }
                if ($codEnt === '') {
                    $missing[] = 'Código Entidad Recíproca';
                }
                if ($nomEnt === '') {
                    $missing[] = 'Nombre Entidad Recíproca';
                }

                if (!empty($missing)) {
                    $rowErrors[] = "Fila {$excelRowNum}: Campos obligatorios incompletos (" . implode(', ', $missing) . ').';
                    continue;
                }

                // Validate numeric format for amounts
                if ($valCorrRaw !== '' && $valCorrRaw !== null && !is_numeric($valCorrRaw)) {
                    $cleanCorrTest = preg_replace('/[\s\x{00A0}$.,\-]/u', '', (string) $valCorrRaw);
                    if ($cleanCorrTest !== '' && !ctype_digit($cleanCorrTest)) {
                        $rowErrors[] = "Fila {$excelRowNum}: El Valor Corriente ('{$valCorrRaw}') no es un número válido.";
                        continue;
                    }
                }

                if ($valNoCorrRaw !== '' && $valNoCorrRaw !== null && !is_numeric($valNoCorrRaw)) {
                    $cleanNoCorrTest = preg_replace('/[\s\x{00A0}$.,\-]/u', '', (string) $valNoCorrRaw);
                    if ($cleanNoCorrTest !== '' && !ctype_digit($cleanNoCorrTest)) {
                        $rowErrors[] = "Fila {$excelRowNum}: El Valor No Corriente ('{$valNoCorrRaw}') no es un número válido.";
                        continue;
                    }
                }

                // Parse currency values using FinancialReciprocal amount parser (preserves thousands)
                $valCorr = FinancialReciprocal::parseAmount($valCorrRaw);
                $valNoCorr = FinancialReciprocal::parseAmount($valNoCorrRaw);

                // Date: default is the upload date (la fecha en la que se sube el documento)
                $fechaVal = $fechaSubida;
                $fechaRaw = $colFecha !== null ? ($row[$colFecha] ?? null) : null;
                if (!empty($fechaRaw)) {
                    if (is_numeric($fechaRaw)) {
                        $fechaVal = ExcelDate::excelToDateTimeObject($fechaRaw)->format('Y-m-d');
                    } else {
                        try {
                            $fechaVal = Carbon::parse($fechaRaw)->format('Y-m-d');
                        } catch (\Throwable $e) {
                            $fechaVal = $fechaSubida;
                        }
                    }
                }

                $rowsToInsert[] = [
                    'codigo_contable_subcuenta' => $codSub,
                    'nombre_subcuenta' => $nomSub,
                    'codigo_entidad_reciproca' => $codEnt,
                    'nombre_entidad_reciproca' => $nomEnt,
                    'valor_corriente' => $valCorr,
                    'valor_no_corriente' => $valNoCorr,
                    'fecha' => $fechaVal,
                    'is_active' => true,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ];

                $entitiesToInsert[] = [
                    'codigo_entidad_reciproca' => $codEnt,
                    'nombre_entidad_reciproca' => $nomEnt,
                ];
            }

            // If there are validation errors in rows, stop and report them explicitly!
            if (!empty($rowErrors)) {
                $displayErrors = array_slice($rowErrors, 0, 15);
                if (count($rowErrors) > 15) {
                    $displayErrors[] = '... y ' . (count($rowErrors) - 15) . ' errores adicionales en otras filas del archivo.';
                }
                return back()->withErrors(['archivo' => $displayErrors])->withInput();
            }

            if (empty($rowsToInsert)) {
                return back()->withErrors(['archivo' => 'No se encontraron registros válidos para importar en el archivo.'])->withInput();
            }

            $imported = 0;
            foreach ($rowsToInsert as $data) {
                FinancialReciprocal::create($data);
                $imported++;
            }

            foreach ($entitiesToInsert as $ent) {
                FinancialReciprocalEntity::firstOrCreate(
                    ['codigo_entidad_reciproca' => $ent['codigo_entidad_reciproca']],
                    [
                        'nombre_entidad_reciproca' => $ent['nombre_entidad_reciproca'],
                        'is_active' => true,
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]
                );
            }

            return redirect()
                ->route('financial.reciprocals.index')
                ->with('success', "Se importaron correctamente {$imported} operaciones recíprocas con fecha {$fechaSubida}.");
        } catch (\Throwable $e) {
            return back()->withErrors(['archivo' => 'Ocurrió un error al procesar el archivo: ' . $e->getMessage()])->withInput();
        }
    }

    private function validateAndPrepare(Request $request): array
    {
        $validated = $request->validate([
            'codigo_contable_subcuenta' => 'required|string|max:50',
            'nombre_subcuenta' => 'required|string|max:255',
            'codigo_entidad_reciproca' => 'required|string|max:50',
            'nombre_entidad_reciproca' => 'required|string|max:255',
            'valor_corriente' => 'required',
            'valor_no_corriente' => 'required',
            'fecha' => 'required|date',
        ], [
            'codigo_contable_subcuenta.required' => 'El código contable de la subcuenta es obligatorio.',
            'nombre_subcuenta.required' => 'El nombre de la subcuenta es obligatorio.',
            'codigo_entidad_reciproca.required' => 'El código de la entidad recíproca es obligatorio.',
            'nombre_entidad_reciproca.required' => 'El nombre de la entidad recíproca es obligatorio.',
            'valor_corriente.required' => 'El valor corriente es obligatorio.',
            'valor_no_corriente.required' => 'El valor no corriente es obligatorio.',
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha ingresada no tiene un formato válido.',
        ]);

        $validated['valor_corriente'] = FinancialReciprocal::parseAmount($validated['valor_corriente']);
        $validated['valor_no_corriente'] = FinancialReciprocal::parseAmount($validated['valor_no_corriente']);

        return $validated;
    }

    private function parseFechaDeCorte(?string $text): ?string
    {
        if (blank($text)) {
            return null;
        }

        $text = trim($text);

        // If it's already YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $text)) {
            return $text;
        }

        // If it's DD/MM/YYYY
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        $months = [
            'enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04',
            'mayo' => '05', 'junio' => '06', 'julio' => '07', 'agosto' => '08',
            'septiembre' => '09', 'octubre' => '10', 'noviembre' => '11', 'diciembre' => '12'
        ];

        $lower = mb_strtolower($text);
        $foundMonth = null;
        foreach ($months as $name => $num) {
            if (str_contains($lower, $name)) {
                $foundMonth = $num;
                break;
            }
        }

        if (preg_match('/(\d{4})/', $text, $yearMatch)) {
            $year = $yearMatch[1];
            $month = $foundMonth ?: '03';

            if (preg_match('/(?:a|\b)\s*(\d{1,2})\s*de\b/i', $text, $dayMatch)) {
                $day = sprintf('%02d', (int) $dayMatch[1]);
            } else {
                $day = (string) Carbon::createFromDate((int) $year, (int) $month, 1)->endOfMonth()->day;
            }

            return "{$year}-{$month}-{$day}";
        }

        return null;
    }
}
