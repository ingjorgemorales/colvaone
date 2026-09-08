<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Services\AuthEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(
        protected AuthEventService $events
    ) {}

    public function index(Request $request): View
    {
        $query = Application::with('creator');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('applications.index', compact('applications'));
    }

    public function create(): View
    {
        return view('applications.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateApplication($request);

        $application = Application::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'url' => $validated['url'],
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        $this->events->record($request, 'application_created', true, reason: "Aplicativo '{$application->name}' creado");

        return redirect()->route('applications.index')
            ->with('success', 'Aplicativo creado correctamente.');
    }

    public function edit(Application $application): View
    {
        return view('applications.edit', compact('application'));
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        $validated = $this->validateApplication($request, $application);

        $application->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'url' => $validated['url'],
            'updated_by' => Auth::id(),
        ]);

        $this->events->record($request, 'application_updated', true, reason: "Aplicativo '{$application->name}' actualizado");

        return redirect()->route('applications.index')
            ->with('success', 'Aplicativo actualizado correctamente.');
    }

    public function toggle(Application $application): RedirectResponse
    {
        $application->update([
            'status' => $application->status === 'active' ? 'inactive' : 'active',
            'updated_by' => Auth::id(),
        ]);

        $status = $application->status === 'active' ? 'activado' : 'inactivado';

        $this->events->record(request(), 'application_toggled', true, reason: "Aplicativo '{$application->name}' {$status}");

        return redirect()->route('applications.index')
            ->with('success', "Aplicativo {$status} correctamente.");
    }

    private function validateApplication(Request $request, ?Application $application = null): array
    {
        $unique = 'unique:applications,name' . ($application ? ',' . $application->id : '');

        return $request->validate([
            'name' => ['required', 'string', 'max:255', $unique],
            'description' => ['nullable', 'string'],
            'url' => ['required', 'url:http,https', 'max:2048'],
        ], [
            'url.url' => 'La ruta debe ser una URL valida que inicie con http:// o https://.',
            'name.unique' => 'Ya existe un aplicativo registrado con ese nombre.',
        ]);
    }
}
