<?php

namespace App\Http\Controllers;

use App\Models\IncidentOption;
use App\Services\AuthEventService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IncidentOptionController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate(['type' => ['nullable', 'string', Rule::in(array_keys(IncidentOption::TYPES))]]);
        $currentType = $validated['type'] ?? 'area';
        $options = IncidentOption::orderBy('sort_order')->orderBy('id')->get()->groupBy('type');

        return view('incidents.settings', compact('options', 'currentType'));
    }

    public function store(Request $request, AuthEventService $events)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(IncidentOption::TYPES))],
            'name' => ['required', 'string', 'max:150', Rule::unique('incident_options')->where('type', $request->input('type'))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        $option = IncidentOption::create($data);
        $events->record($request, 'incident_option_created', true, reason: 'Opción '.$option->name.' creada');

        return back()->with('success', 'Opción agregada.');
    }

    public function update(Request $request, IncidentOption $option, AuthEventService $events)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('incident_options')->where('type', $option->type)->ignore($option->id)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);
        $option->update($data);
        $events->record($request, 'incident_option_updated', true, reason: 'Opción '.$option->name.' actualizada');

        return back()->with('success', 'Opción actualizada.');
    }

    public function toggle(Request $request, IncidentOption $option, AuthEventService $events)
    {
        $option->update(['is_active' => !$option->is_active]);
        $events->record($request, 'incident_option_toggled', true, reason: 'Actividad de opción '.$option->name.' actualizada');

        return back()->with('success', 'Estado de la opción actualizado.');
    }
}
