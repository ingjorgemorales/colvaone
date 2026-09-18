<?php

namespace App\Http\Controllers;

use App\Models\BscPerspective;
use App\Models\Process;
use App\Models\QualityObjective;
use App\Models\Subprocess;
use App\Services\AuthEventService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Maestro de procesos y subprocesos del SGC. Se administran juntos porque
 * comparten estructura y siempre se consultan en pareja.
 */
class ProcessController extends Controller
{
    private const TYPE_PROCESS = 'procesos';
    private const TYPE_SUBPROCESS = 'subprocesos';
    private const TYPE_BSC_PERSPECTIVE = 'perspectivas-bsc';
    private const TYPE_QUALITY_OBJECTIVE = 'objetivos-calidad';

    public function __construct(
        protected AuthEventService $events
    ) {}

    public function index(): View
    {
        return view('processes.index', [
            'processes' => Process::withCount('indicators')->orderBy('position')->orderBy('name')->get(),
            'subprocesses' => Subprocess::withCount('indicators')->orderBy('position')->orderBy('name')->get(),
            'bscPerspectives' => BscPerspective::withCount('indicators')->orderBy('position')->orderBy('name')->get(),
            'qualityObjectives' => QualityObjective::withCount('indicators')->orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $validated = $this->validateItem($request, $type);

        $model = $this->modelFor($type);
        $validated['position'] = ((int) $model::max('position')) + 10;
        $validated['is_active'] = true;

        $item = $model::create($validated);

        $eventName = $this->eventName($type, 'created');
        $this->events->record($request, $eventName, true, reason: "{$this->label($type)} '{$item->name}' creado");

        return $this->back($type, $this->label($type) . ' creado correctamente.');
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $item = $this->findOrFail($type, $id);
        $item->update($this->validateItem($request, $type, $item));

        $eventName = $this->eventName($type, 'updated');
        $this->events->record($request, $eventName, true, reason: "{$this->label($type)} '{$item->name}' actualizado");

        return $this->back($type, $this->label($type) . ' actualizado correctamente.');
    }

    public function toggle(string $type, int $id): RedirectResponse
    {
        $item = $this->findOrFail($type, $id);
        $item->update(['is_active' => ! $item->is_active]);

        $estado = $item->is_active ? 'activado' : 'inactivado';
        $eventName = $this->eventName($type, 'toggled');
        $this->events->record(request(), $eventName, true, reason: "{$this->label($type)} '{$item->name}' {$estado}");

        return $this->back($type, $this->label($type) . " {$estado} correctamente.");
    }

    /**
     * Sube o baja un elemento intercambiando su posicion con el vecino.
     * Se hace en transaccion para que nunca queden dos con la misma.
     */
    public function move(Request $request, string $type, int $id): RedirectResponse
    {
        $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $item = $this->findOrFail($type, $id);
        $model = $this->modelFor($type);
        $up = $request->input('direction') === 'up';

        $neighbour = $model::query()
            ->when($up,
                fn ($q) => $q->where('position', '<', $item->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $item->position)->orderBy('position'),
            )
            ->first();

        if (! $neighbour) {
            return $this->back($type, 'Ya esta en el extremo de la lista.');
        }

        DB::transaction(function () use ($item, $neighbour): void {
            $posItem = $item->position;
            $item->update(['position' => $neighbour->position]);
            $neighbour->update(['position' => $posItem]);
        });

        $eventName = $this->eventName($type, 'moved');
        $dir = $up ? 'arriba' : 'abajo';
        $this->events->record($request, $eventName, true, reason: "Orden de {$this->label($type)} '{$item->name}' movido hacia {$dir}");

        return $this->back($type, 'Orden actualizado.');
    }

    private function validateItem(Request $request, string $type, ?Model $current = null): array
    {
        if ($type === self::TYPE_PROCESS) {
            return $request->validate([
                'name' => ['required', 'string', 'max:150', Rule::unique('processes', 'name')->ignore($current)],
            ], [
                'name.unique' => 'Ya existe un proceso con ese nombre.',
            ]);
        }

        if ($type === self::TYPE_QUALITY_OBJECTIVE) {
            return $request->validate([
                'name' => ['required', 'string', 'max:255', Rule::unique('quality_objectives', 'name')->ignore($current)],
            ], [
                'name.unique' => 'Ya existe un objetivo de calidad con ese nombre.',
            ]);
        }

        if ($type === self::TYPE_BSC_PERSPECTIVE) {
            return $request->validate([
                'name' => ['required', 'string', 'max:150', Rule::unique('bsc_perspectives', 'name')->ignore($current)],
            ], [
                'name.unique' => 'Ya existe una perspectiva BSC con ese nombre.',
            ]);
        }

        return $request->validate([
            'code' => ['nullable', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:200'],
        ]);
    }

    /**
     * Sin comodines: un tipo desconocido aborta en vez de caer por defecto
     * en subprocesos, aunque la restriccion de ruta ya lo filtre.
     *
     * @return class-string<Model>
     */
    private function modelFor(string $type): string
    {
        return match ($type) {
            self::TYPE_PROCESS => Process::class,
            self::TYPE_SUBPROCESS => Subprocess::class,
            self::TYPE_BSC_PERSPECTIVE => BscPerspective::class,
            self::TYPE_QUALITY_OBJECTIVE => QualityObjective::class,
            default => abort(404),
        };
    }

    private function findOrFail(string $type, int $id): Model
    {
        return $this->modelFor($type)::findOrFail($id);
    }

    private function label(string $type): string
    {
        return match ($type) {
            self::TYPE_PROCESS => 'Proceso',
            self::TYPE_SUBPROCESS => 'Subproceso',
            self::TYPE_BSC_PERSPECTIVE => 'Perspectiva BSC',
            self::TYPE_QUALITY_OBJECTIVE => 'Objetivo de calidad',
            default => abort(404),
        };
    }

    private function eventName(string $type, string $action): string
    {
        return match ($type) {
            self::TYPE_PROCESS => "process_{$action}",
            self::TYPE_SUBPROCESS => "subprocess_{$action}",
            self::TYPE_BSC_PERSPECTIVE => "bsc_perspective_{$action}",
            self::TYPE_QUALITY_OBJECTIVE => "quality_objective_{$action}",
            default => abort(404),
        };
    }

    private function back(string $type, string $message): RedirectResponse
    {
        return redirect()
            ->route('processes.index', ['tab' => $type])
            ->with('success', $message);
    }
}
