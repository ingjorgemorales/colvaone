<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\IndicatorResult;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IndicatorController extends Controller
{
    public function index(Request $request): View
    {
        $query = Indicator::with(['responsible', 'latestResult'])->visibleFor($request->user());

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
        return view('indicators.create', ['users' => $this->activeUsers()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateIndicator($request);

        $indicator = Indicator::create($validated + [
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('indicators.show', $indicator)
            ->with('success', 'Indicador creado correctamente.');
    }

    public function show(Indicator $indicator): View
    {
        $this->ensureCanView($indicator);

        $indicator->load(['responsible', 'creator', 'updater', 'results.creator']);

        return view('indicators.show', [
            'indicator' => $indicator,
            'canEditResults' => $this->canEditResults(),
            'canToggleResults' => $this->canToggleResults(),
        ]);
    }

    public function edit(Indicator $indicator): View
    {
        $this->ensureCanView($indicator);

        return view('indicators.edit', [
            'indicator' => $indicator,
            'users' => $this->activeUsers(),
        ]);
    }

    public function update(Request $request, Indicator $indicator): RedirectResponse
    {
        $this->ensureCanView($indicator);

        $validated = $this->validateIndicator($request);

        $indicator->update($validated + ['updated_by' => Auth::id()]);

        $this->recalculateResults($indicator);

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

        return redirect()->route('indicators.index')
            ->with('success', "Indicador {$status} correctamente.");
    }

    public function storeResult(Request $request, Indicator $indicator): RedirectResponse
    {
        $this->ensureCanView($indicator);

        $validated = $this->validateResult($request);

        $compliance = IndicatorResult::calculateCompliance(
            (float) $validated['field_one'],
            (float) $validated['field_two'],
        );

        $indicator->results()->create($validated + [
            'compliance' => $compliance,
            'evaluation' => $indicator->evaluate($compliance),
            'created_by' => Auth::id(),
        ]);

        return $this->backToResults($indicator, 'Resultado registrado correctamente.');
    }

    public function updateResult(Request $request, Indicator $indicator, IndicatorResult $result): RedirectResponse
    {
        $this->ensureCanView($indicator);
        $this->ensureResultBelongsTo($indicator, $result);
        $this->ensureCanEditResults();

        $validated = $this->validateResult($request);

        $compliance = IndicatorResult::calculateCompliance(
            (float) $validated['field_one'],
            (float) $validated['field_two'],
        );

        $result->update($validated + [
            'compliance' => $compliance,
            'evaluation' => $indicator->evaluate($compliance),
            'updated_by' => Auth::id(),
        ]);

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

        return $this->backToResults($indicator, "Resultado {$status} correctamente.");
    }

    private function validateIndicator(Request $request): array
    {
        $max = Indicator::SCALE_MAX;

        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(array_keys(Indicator::CATEGORIES))],
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
            'field_one' => ['required', 'numeric', 'min:0'],
            'field_two' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'action_number' => ['nullable', 'string', 'max:60'],
        ], [
            'period_end.after_or_equal' => 'La fecha fin no puede ser anterior a la fecha inicio.',
        ]);
    }

    /**
     * Al cambiar los umbrales, las evaluaciones ya guardadas dejan de ser
     * coherentes con la ficha tecnica, asi que se reclasifican.
     */
    private function recalculateResults(Indicator $indicator): void
    {
        $indicator->results()->get()->each(function (IndicatorResult $result) use ($indicator): void {
            $evaluation = $indicator->evaluate((float) $result->compliance);

            if ($evaluation !== $result->evaluation) {
                $result->update(['evaluation' => $evaluation]);
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

    private function activeUsers()
    {
        return User::where('is_active', true)->orderBy('name')->get();
    }
}
