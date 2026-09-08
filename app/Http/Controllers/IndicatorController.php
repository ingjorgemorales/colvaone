<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\Process;
use App\Models\IndicatorResult;
use App\Models\Subprocess;
use App\Models\User;
use App\Services\AuthEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IndicatorController extends Controller
{
    public function __construct(
        protected AuthEventService $events
    ) {}
    public function index(Request $request): View
    {
        $query = Indicator::with(['responsible', 'latestResult', 'process', 'subprocess'])->visibleFor($request->user());

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $category = $request->input('categoria');
        $query->inCategory($category);

        $indicators = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('indicators.index', [
            'indicators' => $indicators,
            'category' => array_key_exists((string) $category, Indicator::CATEGORIES) ? $category : null,
        ]);
    }

    public function create(): View
    {
        return view('indicators.create', $this->formData());
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

        $indicator->load(['responsible', 'creator', 'updater', 'results.creator', 'process', 'subprocess']);

        return view('indicators.show', [
            'indicator' => $indicator,
            'canEditResults' => $this->canEditResults(),
            'canToggleResults' => $this->canToggleResults(),
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

        $validated = $this->validateIndicator($request);

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

        return redirect()->route('indicators.index')
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

    private function validateIndicator(Request $request): array
    {
        $max = Indicator::SCALE_MAX;

        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(array_keys(Indicator::CATEGORIES))],
            'process_id' => ['nullable', Rule::exists('processes', 'id')->where('is_active', true)],
            'subprocess_id' => ['nullable', Rule::exists('subprocesses', 'id')->where('is_active', true)],
            'objective' => ['required', 'string'],
            'responsible_user_id' => ['required', 'exists:users,id'],
            'formula' => ['required', 'string', 'max:255'],
            'measurement_unit' => ['required', Rule::in(array_keys(Indicator::MEASUREMENT_UNITS))],
            'frequency' => ['required', Rule::in(array_keys(Indicator::FREQUENCIES))],
            'type' => ['required', Rule::in(array_keys(Indicator::TYPES))],
            'methodological_aspects' => ['nullable', 'string'],
            'goal' => ['required', 'integer', 'min:1', 'max:' . $max],
            'goal_direction' => ['required', Rule::in(array_keys(Indicator::GOAL_DIRECTIONS))],
            'threshold_acceptable' => ['required', 'integer', 'min:1', 'max:' . $max],
            'threshold_satisfactory' => ['required', 'integer', 'min:1', 'max:' . $max],
        ], [
            'goal.max' => 'La meta no puede superar ' . $max . '%.',
            'threshold_acceptable.max' => 'El umbral aceptable no puede superar ' . $max . '%.',
            'threshold_satisfactory.max' => 'El umbral satisfactorio no puede superar ' . $max . '%.',
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
            'analysis' => ['nullable', 'string'],
            'action_number' => ['nullable', 'string', 'max:60'],
        ], [
            'period_end.after_or_equal' => 'La fecha fin no puede ser anterior a la fecha inicio.',
            'denominator.not_in' => 'El denominador no puede ser cero.',
            'period_goal.not_in' => 'La meta del periodo no puede ser cero.',
        ]);
    }

    /**
     * Al cambiar los umbrales, las evaluaciones ya guardadas dejan de ser
     * coherentes con la ficha tecnica, asi que se reclasifican.
     */
    /**
     * Aplica las formulas del SGC: el resultado y el cumplimiento nunca se
     * digitan, se derivan del numerador, el denominador y la meta.
     */
    private function withCalculations(array $validated, Indicator $indicator): array
    {
        $result = IndicatorResult::calculateResult(
            (float) $validated['numerator'],
            (float) $validated['denominator'],
        );

        $compliance = IndicatorResult::calculateCompliance(
            $result,
            (float) $validated['period_goal'],
            $indicator->goal_direction ?? Indicator::GOAL_ASCENDING,
        );

        return $validated + [
            'result' => $result,
            'compliance' => $compliance,
            'evaluation' => $indicator->evaluate($compliance),
        ];
    }

    /**
     * Cambiar los umbrales o el sentido de la meta deja las evaluaciones
     * guardadas fuera de sintonia con la ficha, asi que se recalculan.
     */
    private function recalculateResults(Indicator $indicator): void
    {
        $indicator->results()->get()->each(function (IndicatorResult $result) use ($indicator): void {
            $compliance = IndicatorResult::calculateCompliance(
                (float) $result->result,
                $result->period_goal === null ? null : (float) $result->period_goal,
                $indicator->goal_direction ?? Indicator::GOAL_ASCENDING,
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
        ];
    }

    private function activeUsers()
    {
        return User::where('is_active', true)->orderBy('name')->get();
    }
}
