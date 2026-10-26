<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\IncidentAttachment;
use App\Models\IncidentOption;
use App\Services\AuthEventService;
use App\Services\IncidentPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(array_keys(Incident::FILTER_STATES))],
            'priority_id' => ['nullable', 'integer'],
        ]);
        $query = Incident::visibleFor($request->user())->with(['area', 'failure', 'priority']);
        $query->search($filters['search'] ?? '');
        $status = $filters['status'] ?? null;
        if (isset(Incident::STATUSES[$status ?? ''])) { $query->where('status', $status); }
        elseif ($status === 'archivado') { $query->whereNotNull('archived_at'); }
        elseif (in_array($status, ['activo', 'inactivo'], true)) { $query->where('is_active', $status === 'activo'); }
        if (!empty($filters['priority_id'])) { $query->where('priority_id', $filters['priority_id']); }
        $incidents = $query->latest('id')->paginate(15)->appends($request->only(['search', 'status', 'priority_id']));
        $options = IncidentOption::where('type', 'priority')->orderBy('sort_order')->orderBy('id')->get()->groupBy('type');

        return view('incidents.index', compact('incidents', 'options'));
    }

    public function create()
    {
        return $this->form(new Incident);
    }

    public function store(Request $request, AuthEventService $events)
    {
        $incident = $this->save($request, new Incident);
        $events->record($request, 'incident_created', true, reason: $incident->ticket.' creado');

        return redirect()->route('incidents.show', $incident)->with('success', 'Incidente registrado: '.$incident->ticket);
    }

    public function show(Request $request, Incident $incident)
    {
        $this->authorizeRecord($request, $incident);
        $incident->load(['creator', 'area', 'zone', 'failure', 'impact', 'priority', 'attachments']);

        return view('incidents.show', compact('incident'));
    }

    public function edit(Request $request, Incident $incident)
    {
        $this->authorizeRecord($request, $incident);
        abort_if($incident->archived_at, 403, 'Restaura el incidente antes de editarlo.');

        return $this->form($incident);
    }

    public function update(Request $request, Incident $incident, AuthEventService $events)
    {
        $this->authorizeRecord($request, $incident);
        abort_if($incident->archived_at, 403, 'Restaura el incidente antes de editarlo.');
        $this->save($request, $incident);
        $events->record($request, 'incident_updated', true, reason: $incident->ticket.' actualizado');

        return redirect()->route('incidents.show', $incident)->with('success', 'Incidente actualizado.');
    }

    public function toggle(Request $request, Incident $incident, AuthEventService $events)
    {
        $this->authorizeRecord($request, $incident);
        abort_if($incident->archived_at, 403);
        $incident->update(['is_active' => !$incident->is_active]);
        $events->record($request, 'incident_toggled', true, reason: $incident->ticket.($incident->is_active ? ' activado' : ' inactivado'));

        return back()->with('success', 'Estado de actividad actualizado.');
    }

    public function archive(Request $request, Incident $incident, AuthEventService $events)
    {
        $this->authorizeRecord($request, $incident);
        abort_if(!$incident->archived_at && $incident->status !== 'resuelto', 422, 'Solo se pueden archivar incidentes resueltos.');
        $incident->update(['archived_at' => $incident->archived_at ? null : now()]);
        $events->record($request, 'incident_archived', true, reason: $incident->ticket.($incident->archived_at ? ' archivado' : ' restaurado'));

        return back()->with('success', $incident->archived_at ? 'Incidente archivado.' : 'Incidente restaurado.');
    }

    public function attachment(Request $request, Incident $incident, IncidentAttachment $attachment)
    {
        $this->authorizeRecord($request, $incident);
        abort_unless($attachment->incident_id === $incident->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function preview(Request $request, Incident $incident, IncidentAttachment $attachment)
    {
        $this->authorizeRecord($request, $incident);
        abort_unless($attachment->incident_id === $incident->id, 404);
        abort_unless($attachment->is_image || $attachment->mime_type === 'application/pdf', 415);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    public function pdf(Request $request, Incident $incident, IncidentPdfService $pdf)
    {
        $this->authorizeRecord($request, $incident);
        $incident->load(['creator', 'area', 'zone', 'failure', 'impact', 'priority', 'attachments']);

        return response($pdf->render($incident), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$incident->ticket.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeRecord(Request $request, Incident $incident): void
    {
        abort_unless(Incident::visibleFor($request->user())->whereKey($incident->id)->exists(), 403);
    }

    private function form(Incident $incident)
    {
        $options = IncidentOption::query()->where(function ($q) use ($incident) {
            $q->where('is_active', true);
            if ($incident->exists) {
                $q->orWhereIn('id', collect(['area', 'zone', 'failure', 'impact', 'priority'])->map(fn ($f) => $incident->{$f.'_id'})->filter());
            }
        })->orderBy('sort_order')->orderBy('id')->get()->groupBy('type');
        $incident->load('attachments');

        $reporter = $this->reporterData($incident);
        $profileIncomplete = !$incident->exists && in_array('', $reporter, true);

        return view('incidents.form', compact('incident', 'options', 'reporter', 'profileIncomplete'));
    }

    private function reporterData(Incident $incident): array
    {
        $user = auth()->user();

        return [
            'reporter_name' => $incident->exists ? $incident->reporter_name : trim($user->name.' '.$user->last_name),
            'reporter_position' => trim($incident->exists ? $incident->reporter_position : ($user->position ?? '')),
            'reporter_area' => trim($incident->exists ? ($incident->area?->name ?? '') : ($user->area ?? '')),
        ];
    }

    private function save(Request $request, Incident $incident): Incident
    {
        $rules = [
            'location' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:5000'],
            'affected_assets' => ['nullable', 'string', 'max:500'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
            'remove_attachments' => ['nullable', 'array'],
            'remove_attachments.*' => ['integer', Rule::exists('incident_attachments', 'id')->where('incident_id', $incident->id ?? 0)],
        ];
        foreach (['zone', 'failure', 'impact', 'priority'] as $field) {
            $current = $incident->{$field.'_id'};
            $rules[$field.'_id'] = [$field === 'zone' ? 'nullable' : 'required', 'integer',
                Rule::exists('incident_options', 'id')->where(fn ($q) => $q->where('type', $field)
                    ->where(fn ($active) => $active->where('is_active', true)->orWhere('id', $current ?? 0))),
            ];
        }
        if ($incident->exists) { $rules['status'] = ['required', Rule::in(array_keys(Incident::STATUSES))]; }
        $data = $request->validate($rules);
        $removed = $incident->attachments()->whereIn('id', $data['remove_attachments'] ?? [])->get();
        if ($incident->attachments()->count() - $removed->count() + count($request->file('attachments', [])) > 10) {
            throw ValidationException::withMessages(['attachments' => 'El incidente admite hasta 10 evidencias.']);
        }
        unset($data['attachments'], $data['remove_attachments']);
        $reporter = [];
        if (!$incident->exists) {
            $reporter = Validator::make($this->reporterData($incident), [
                'reporter_name' => ['required', 'string', 'max:150'],
                'reporter_position' => ['required', 'string', 'max:150'],
                'reporter_area' => ['required', 'string', 'max:150'],
            ], [
                'reporter_name.required' => 'Completa tu nombre en Mi perfil antes de registrar el incidente.',
                'reporter_position.required' => 'Completa tu cargo en Mi perfil antes de registrar el incidente.',
                'reporter_area.required' => 'Completa tu área en Mi perfil antes de registrar el incidente.',
            ])->validate();
            $data['reporter_name'] = $reporter['reporter_name'];
            $data['reporter_position'] = $reporter['reporter_position'];
            $data['created_by'] = $request->user()->id;
            $data['status'] = 'reportado';
        }
        $paths = [];
        try {
            DB::transaction(function () use ($incident, $data, $request, $removed, $reporter, &$paths) {
                if (!$incident->exists) {
                    $data['area_id'] = IncidentOption::firstOrCreate(
                        ['type' => 'area', 'name' => $reporter['reporter_area']],
                        ['sort_order' => (IncidentOption::where('type', 'area')->max('sort_order') ?? -1) + 1],
                    )->id;
                }
                $incident->fill($data)->save();
                foreach ($request->file('attachments', []) as $file) {
                    $path = $file->store('incidents/'.$incident->id, 'local');
                    if (!$path) { throw new \RuntimeException('No se pudo guardar la evidencia.'); }
                    $paths[] = $path;
                    $incident->attachments()->create([
                        'path' => $path, 'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
                        'mime_type' => $file->getMimeType(), 'size' => $file->getSize(),
                    ]);
                }
                $incident->attachments()->whereIn('id', $removed->modelKeys())->delete();
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($paths);
            throw $e;
        }
        Storage::disk('local')->delete($removed->pluck('path')->all());

        return $incident;
    }
}
