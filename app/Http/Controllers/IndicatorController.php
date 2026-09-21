<?php

namespace App\Http\Controllers;

use App\Models\BscPerspective;
use App\Models\Indicator;
use App\Models\IndicatorResult;
use App\Models\Process;
use App\Models\QualityObjective;
use App\Models\Subprocess;
use App\Models\User;
use App\Services\AuthEventService;
use App\Services\IndicatorExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IndicatorController extends Controller
{
    public function __construct(
        protected AuthEventService $events
    ) {}
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ], [
            'to.after_or_equal' => 'La fecha final no puede ser anterior a la fecha inicial.',
        ]);

        $from = $validated['from'] ?? null;
        $to = $validated['to'] ?? null;
        $category = $this->validCategory($request->input('categoria')) ?? 'I';
        $hasConfiguredDashboard = $category === 'I';

        $indicators = Indicator::with(['qualityObjective', 'subprocess'])
            ->visibleFor($request->user())
            ->inCategory($category)
            ->orderBy('name')
            ->get();

        $latestResults = IndicatorResult::with(['indicator.qualityObjective', 'indicator.subprocess'])
            ->where('status', 'active')
            ->whereIn('indicator_id', $indicators->pluck('id'))
            ->when($from, fn ($query) => $query->whereDate('period_end', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('period_start', '<=', $to))
            ->orderByDesc('period_end')
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->get()
            ->unique('indicator_id')
            ->keyBy('indicator_id');

        return view('indicators.dashboard', [
            'dashboard' => $this->dashboardData($indicators, $latestResults),
            'filters' => [
                'from' => $from,
                'to' => $to,
                'categoria' => $category,
            ],
            'category' => $category,
            'categoryLabel' => Indicator::CATEGORIES[$category],
            'categories' => Indicator::CATEGORIES,
            'hasConfiguredDashboard' => $hasConfiguredDashboard,
        ]);
    }

    public function list(Request $request): View
    {
        $query = Indicator::with(['responsible', 'latestResult', 'process', 'subprocess', 'bscPerspective', 'qualityObjective'])->visibleFor($request->user());

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $category = $this->validCategory($request->input('categoria'));
        $query->inCategory($category);

        $indicators = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('indicators.index', [
            'indicators' => $indicators,
            'category' => array_key_exists((string) $category, Indicator::CATEGORIES) ? $category : null,
        ]);
    }

    public function create(Request $request): View
    {
        return view('indicators.create', $this->formData([
            'defaultCategory' => $this->validCategory($request->input('categoria')),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateIndicator($request);

        $indicator = Indicator::create($validated + [
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        $this->events->record($request, 'indicator_created', true, reason: "Indicador '{$indicator->name}' creado");

        return redirect()->route('indicators.show', $indicator)
            ->with('success', 'Indicador creado correctamente.');
    }

    public function show(Indicator $indicator): View
    {
        $this->ensureCanView($indicator);

        $indicator->load(['responsible', 'creator', 'updater', 'results.creator', 'process', 'subprocess', 'bscPerspective', 'qualityObjective']);

        return view('indicators.show', [
            'indicator' => $indicator,
            'canEditResults' => $this->canEditResults(),
            'canToggleResults' => $this->canToggleResults(),
        ]);
    }

    /**
     * Descarga la ficha tecnica y los resultados del indicador en un .xlsx,
     * una hoja para cada uno. Lo puede bajar quien puede ver el indicador.
     */
    public function export(Request $request, Indicator $indicator, IndicatorExportService $exporter): StreamedResponse
    {
        $this->ensureCanView($indicator);

        $libro = $exporter->build($indicator);

        $this->events->record($request, 'indicator_exported', true, reason: "Indicador '{$indicator->name}' exportado a Excel");

        return response()->streamDownload(function () use ($libro): void {
            (new Xlsx($libro))->save('php://output');
            $libro->disconnectWorksheets();
        }, $exporter->filename($indicator), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function edit(Indicator $indicator): View
    {
        $this->ensureCanView($indicator);

        return view('indicators.edit', $this->formData(['indicator' => $indicator]));
    }

    public function update(Request $request, Indicator $indicator): RedirectResponse
    {
        $this->ensureCanView($indicator);

        $validated = $this->validateIndicator($request, $indicator);

        $indicator->update($validated + ['updated_by' => Auth::id()]);

        $this->recalculateResults($indicator);

        $this->events->record($request, 'indicator_updated', true, reason: "Indicador '{$indicator->name}' actualizado");

        return redirect()->route('indicators.show', $indicator)
            ->with('success', 'Indicador actualizado correctamente.');
    }

    public function toggle(Indicator $indicator): RedirectResponse
    {
        $this->ensureCanView($indicator);

        $indicator->update([
            'status' => $indicator->status === 'active' ? 'inactive' : 'active',
            'updated_by' => Auth::id(),
        ]);

        $status = $indicator->status === 'active' ? 'activado' : 'inactivado';

        $this->events->record(request(), 'indicator_toggled', true, reason: "Indicador '{$indicator->name}' {$status}");

        return redirect()->route('indicators.list')
            ->with('success', "Indicador {$status} correctamente.");
    }

    public function storeResult(Request $request, Indicator $indicator): RedirectResponse
    {
        $this->ensureCanView($indicator);

        $validated = $this->validateResult($request);

        $result = $indicator->results()->create(
            $this->withCalculations($validated, $indicator) + ['created_by' => Auth::id()]
        );

        $period = $result->period_start?->format('d/m/Y') . ' - ' . $result->period_end?->format('d/m/Y');
        $this->events->record($request, 'indicator_result_created', true, reason: "Resultado registrado para indicador '{$indicator->name}' ({$period})");

        return $this->backToResults($indicator, 'Resultado registrado correctamente.');
    }

    public function updateResult(Request $request, Indicator $indicator, IndicatorResult $result): RedirectResponse
    {
        $this->ensureCanView($indicator);
        $this->ensureResultBelongsTo($indicator, $result);
        $this->ensureCanEditResults();

        $validated = $this->validateResult($request);

        $result->update(
            $this->withCalculations($validated, $indicator) + ['updated_by' => Auth::id()]
        );

        $period = $result->period_start?->format('d/m/Y') . ' - ' . $result->period_end?->format('d/m/Y');
        $this->events->record($request, 'indicator_result_updated', true, reason: "Resultado actualizado para indicador '{$indicator->name}' ({$period})");

        return $this->backToResults($indicator, 'Resultado actualizado correctamente.');
    }

    public function toggleResult(Indicator $indicator, IndicatorResult $result): RedirectResponse
    {
        $this->ensureCanView($indicator);
        $this->ensureResultBelongsTo($indicator, $result);
        $this->ensureCanToggleResults();

        $result->update([
            'status' => $result->status === 'active' ? 'inactive' : 'active',
            'updated_by' => Auth::id(),
        ]);

        $status = $result->status === 'active' ? 'activado' : 'desactivado';
        $period = $result->period_start?->format('d/m/Y') . ' - ' . $result->period_end?->format('d/m/Y');
        $this->events->record(request(), 'indicator_result_toggled', true, reason: "Resultado ({$period}) {$status} para indicador '{$indicator->name}'");

        return $this->backToResults($indicator, "Resultado {$status} correctamente.");
    }

    private function validateIndicator(Request $request, ?Indicator $indicator = null): array
    {
        $max = Indicator::SCALE_MAX;

        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(array_keys(Indicator::CATEGORIES))],
            'bsc_perspective_id' => [
                'required',
                Rule::exists('bsc_perspectives', 'id')->where(function ($query) use ($indicator): void {
                    $query->where('is_active', true);

                    if ($indicator?->bsc_perspective_id) {
                        $query->orWhere('id', $indicator->bsc_perspective_id);
                    }
                }),
            ],
            'process_id' => ['nullable', Rule::exists('processes', 'id')->where('is_active', true)],
            'subprocess_id' => ['nullable', Rule::exists('subprocesses', 'id')->where('is_active', true)],
            'quality_objective_id' => [
                'required',
                Rule::exists('quality_objectives', 'id')->where(function ($query) use ($indicator): void {
                    $query->where('is_active', true);

                    if ($indicator?->quality_objective_id) {
                        $query->orWhere('id', $indicator->quality_objective_id);
                    }
                }),
            ],
            'objective' => ['required', 'string'],
            'responsible_user_id' => ['required', 'exists:users,id'],
            'formula' => ['required', 'string', 'max:255'],
            'measurement_unit' => ['required', Rule::in(array_keys(Indicator::MEASUREMENT_UNITS))],
            'frequency' => ['required', Rule::in(array_keys(Indicator::FREQUENCIES))],
            'type' => ['required', Rule::in(array_keys(Indicator::TYPES))],
            'methodological_aspects' => ['nullable', 'string'],
            'goal' => ['required', 'integer', 'min:1', 'max:' . $max],
            'threshold_acceptable' => ['required', 'integer', 'min:1', 'max:' . $max],
            'threshold_satisfactory' => ['required', 'integer', 'min:1', 'max:' . $max],
        ], [
            'goal.max' => 'La meta no puede superar ' . $max . '%.',
            'threshold_acceptable.max' => 'El umbral aceptable no puede superar ' . $max . '%.',
            'threshold_satisfactory.max' => 'El umbral satisfactorio no puede superar ' . $max . '%.',
            'bsc_perspective_id.required' => 'La perspectiva BSC es obligatoria.',
            'quality_objective_id.required' => 'El objetivo de calidad es obligatorio.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $acceptable = (int) $request->input('threshold_acceptable');
            $satisfactory = (int) $request->input('threshold_satisfactory');

            if ($satisfactory > 0 && $satisfactory <= $acceptable) {
                $validator->errors()->add(
                    'threshold_satisfactory',
                    'El umbral satisfactorio debe ser mayor que el aceptable.'
                );
            }
        });

        return $validator->validate();
    }

    private function validateResult(Request $request): array
    {
        return $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'numerator' => ['required', 'numeric'],
            'denominator' => ['required', 'numeric', 'not_in:0'],
            'period_goal' => ['required', 'numeric', 'not_in:0'],
            'compliance_formula' => ['required', Rule::in(array_keys(IndicatorResult::COMPLIANCE_FORMULAS))],
            'analysis' => ['nullable', 'string'],
            'action_number' => ['nullable', 'string', 'max:60'],
        ], [
            'period_end.after_or_equal' => 'La fecha fin no puede ser anterior a la fecha inicio.',
            'denominator.not_in' => 'El denominador no puede ser cero.',
            'period_goal.not_in' => 'La meta del periodo no puede ser cero.',
            'compliance_formula.required' => 'La fórmula de cumplimiento es obligatoria.',
        ]);
    }

    /**
     * Al cambiar los umbrales, las evaluaciones ya guardadas dejan de ser
     * coherentes con la ficha tecnica, asi que se reclasifican.
     */
    /**
     * Aplica las formulas del SGC: el resultado y el cumplimiento nunca se
     * digitan, se derivan del numerador, el denominador, la meta y la formula elegida.
     */
    private function withCalculations(array $validated, Indicator $indicator): array
    {
        $result = IndicatorResult::calculateResult(
            (float) $validated['numerator'],
            (float) $validated['denominator'],
        );

        $formula = $validated['compliance_formula'] ?? IndicatorResult::FORMULA_ASCENDING;

        $compliance = IndicatorResult::calculateCompliance(
            $result,
            (float) $validated['period_goal'],
            $formula,
        );

        return $validated + [
            'result' => $result,
            'compliance' => $compliance,
            'evaluation' => $indicator->evaluate($compliance),
        ];
    }

    /**
     * Cambiar los umbrales deja las evaluaciones guardadas fuera de sintonia
     * con la ficha, asi que se recalculan usando la formula de cada resultado.
     */
    private function recalculateResults(Indicator $indicator): void
    {
        $indicator->results()->get()->each(function (IndicatorResult $result) use ($indicator): void {
            $compliance = IndicatorResult::calculateCompliance(
                (float) $result->result,
                $result->period_goal === null ? null : (float) $result->period_goal,
                $result->compliance_formula ?? IndicatorResult::FORMULA_ASCENDING,
            );

            $evaluation = $indicator->evaluate($compliance);

            if ((float) $result->compliance !== $compliance || $evaluation !== $result->evaluation) {
                $result->update(['compliance' => $compliance, 'evaluation' => $evaluation]);
            }
        });
    }

    private function backToResults(Indicator $indicator, string $message): RedirectResponse
    {
        return redirect()
            ->route('indicators.show', ['indicator' => $indicator, 'tab' => 'resultados'])
            ->with('success', $message);
    }

    private function ensureCanView(Indicator $indicator): void
    {
        $user = Auth::user();

        if (! $user || ! $indicator->isVisibleFor($user)) {
            abort(403, 'No tienes permiso para acceder a este indicador.');
        }
    }

    private function canEditResults(): bool
    {
        return (bool) Auth::user()?->hasPermission('indicators.results_edit');
    }

    private function canToggleResults(): bool
    {
        return (bool) Auth::user()?->hasPermission('indicators.results_toggle');
    }

    private function ensureCanEditResults(): void
    {
        if (! $this->canEditResults()) {
            abort(403, 'No tienes permiso para editar resultados ya registrados.');
        }
    }

    private function ensureCanToggleResults(): void
    {
        if (! $this->canToggleResults()) {
            abort(403, 'No tienes permiso para activar o desactivar resultados.');
        }
    }

    private function ensureResultBelongsTo(Indicator $indicator, IndicatorResult $result): void
    {
        if ((int) $result->indicator_id !== (int) $indicator->id) {
            abort(404);
        }
    }

    /** Catalogos que alimentan los desplegables de la ficha tecnica. */
    private function formData(array $extra = []): array
    {
        return $extra + [
            'users' => $this->activeUsers(),
            'processes' => Process::selectable()->get(),
            'subprocesses' => Subprocess::selectable()->get(),
            'bscPerspectives' => $this->bscPerspectives($extra['indicator'] ?? null),
            'qualityObjectives' => $this->qualityObjectives($extra['indicator'] ?? null),
        ];
    }

    private function bscPerspectives(?Indicator $indicator = null)
    {
        $perspectives = BscPerspective::selectable()->get();

        if ($indicator?->bsc_perspective_id && ! $perspectives->contains('id', $indicator->bsc_perspective_id)) {
            $current = BscPerspective::find($indicator->bsc_perspective_id);

            if ($current) {
                $perspectives->push($current);
            }
        }

        return $perspectives->sortBy([['position', 'asc'], ['name', 'asc']])->values();
    }

    private function qualityObjectives(?Indicator $indicator = null)
    {
        $objectives = QualityObjective::selectable()->get();

        if ($indicator?->quality_objective_id && ! $objectives->contains('id', $indicator->quality_objective_id)) {
            $current = QualityObjective::find($indicator->quality_objective_id);

            if ($current) {
                $objectives->push($current);
            }
        }

        return $objectives->sortBy([['position', 'asc'], ['name', 'asc']])->values();
    }

    private function activeUsers()
    {
        return User::where('is_active', true)->orderBy('name')->get();
    }

    private function validCategory(?string $category): ?string
    {
        return array_key_exists((string) $category, Indicator::CATEGORIES)
            ? (string) $category
            : null;
    }

    private function dashboardData($indicators, $latestResults): array
    {
        $states = $this->dashboardStates();
        $stateCounts = collect($states)->mapWithKeys(fn ($state, $key) => [$key => 0])->all();

        foreach ($indicators as $indicator) {
            $state = $latestResults->get($indicator->id)?->evaluation ?? 'en_espera';
            $stateCounts[$state] = ($stateCounts[$state] ?? 0) + 1;
        }

        $averageCompliance = $latestResults->isEmpty()
            ? 0
            : round($latestResults->avg(fn (IndicatorResult $result) => (float) $result->compliance), 2);

        return [
            'total' => $indicators->count(),
            'average' => $averageCompliance,
            'stateChart' => [
                'labels' => collect($states)->pluck('label')->values(),
                'data' => collect($states)->keys()->map(fn ($key) => $stateCounts[$key] ?? 0)->values(),
                'colors' => collect($states)->pluck('color')->values(),
            ],
            'qualityObjectives' => $this->groupedDashboardData(
                $indicators,
                $latestResults,
                fn (Indicator $indicator) => $indicator->qualityObjective?->name ?? 'Sin objetivo de calidad'
            ),
            'subprocesses' => $this->groupedDashboardData(
                $indicators,
                $latestResults,
                fn (Indicator $indicator) => $indicator->subprocess?->full_name ?? 'Sin subproceso'
            ),
            'states' => $states,
        ];
    }

    private function groupedDashboardData($indicators, $latestResults, callable $labelResolver): array
    {
        $states = $this->dashboardStates();
        $groups = [];

        foreach ($indicators as $indicator) {
            $label = $labelResolver($indicator);

            if (! isset($groups[$label])) {
                $groups[$label] = [
                    'total' => 0,
                    'compliance_sum' => 0,
                    'compliance_count' => 0,
                    'states' => collect($states)->mapWithKeys(fn ($state, $key) => [$key => 0])->all(),
                ];
            }

            $groups[$label]['total']++;
            $result = $latestResults->get($indicator->id);
            $state = $result?->evaluation ?? 'en_espera';
            $groups[$label]['states'][$state] = ($groups[$label]['states'][$state] ?? 0) + 1;

            if ($result) {
                $groups[$label]['compliance_sum'] += (float) $result->compliance;
                $groups[$label]['compliance_count']++;
            }
        }

        ksort($groups, SORT_NATURAL | SORT_FLAG_CASE);

        $labels = array_keys($groups);

        return [
            'labels' => $labels,
            'counts' => collect($groups)->pluck('total')->values(),
            'compliance' => collect($groups)->map(function (array $group): ?float {
                if ($group['compliance_count'] === 0) {
                    return null;
                }

                return round($group['compliance_sum'] / $group['compliance_count'], 2);
            })->values(),
            'statusDatasets' => collect($states)->map(function (array $state, string $key) use ($groups): array {
                return [
                    'label' => $state['label'],
                    'data' => collect($groups)->map(function (array $group) use ($key): float {
                        if ($group['total'] === 0) {
                            return 0;
                        }

                        return round((($group['states'][$key] ?? 0) / $group['total']) * 100, 2);
                    })->values(),
                    'countData' => collect($groups)->map(fn (array $group) => $group['states'][$key] ?? 0)->values(),
                    'backgroundColor' => $state['color'],
                    'borderRadius' => 7,
                    'barPercentage' => 0.72,
                    'categoryPercentage' => 0.68,
                ];
            })->values(),
        ];
    }

    private function dashboardStates(): array
    {
        return [
            IndicatorResult::SATISFACTORY => ['label' => 'Satisfactorio', 'color' => '#70ad47'],
            IndicatorResult::ACCEPTABLE => ['label' => 'Aceptable', 'color' => '#ffff00'],
            IndicatorResult::UNSATISFACTORY => ['label' => 'Insatisfactorio', 'color' => '#ff0000'],
            'en_espera' => ['label' => 'En espera', 'color' => '#94a3b8'],
        ];
    }
}
