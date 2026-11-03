<?php

namespace App\Http\Controllers;

use App\Mail\IncidentCommentNotificationMail;
use App\Mail\IncidentRiskNotificationMail;
use App\Models\Incident;
use App\Models\IncidentAttachment;
use App\Models\IncidentComment;
use App\Models\IncidentOption;
use App\Models\User;
use App\Services\AuthEventService;
use App\Services\IncidentPdfService;
use App\Services\IncidentReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
        $query = Incident::visibleFor($request->user())->with(['area', 'failure', 'priority', 'responsibleUser']);
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

        if (!empty($incident->responsible_user_id) || !empty($incident->responsible_position)) {
            $this->notifyRiskResponsibles($incident);
        }

        return redirect()->route('incidents.show', $incident)->with('success', 'Incidente registrado: '.$incident->ticket);
    }

    public function show(Request $request, Incident $incident)
    {
        $this->authorizeRecord($request, $incident);
        $incident->load(['creator', 'responsibleUser', 'area', 'zone', 'failure', 'impact', 'priority', 'attachments', 'comments.user']);

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
        $incident->load(['creator', 'responsibleUser', 'area', 'zone', 'failure', 'impact', 'priority', 'attachments']);

        return response($pdf->render($incident), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$incident->ticket.'.pdf"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function addComment(Request $request, Incident $incident, AuthEventService $events)
    {
        $this->authorizeRecord($request, $incident);
        abort_if($incident->archived_at, 403, 'No se pueden agregar comentarios a un incidente archivado.');

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:5000'],
        ]);

        $comment = $incident->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => trim($validated['comment']),
        ]);

        $events->record($request, 'incident_comment_added', true, reason: 'Comentario agregado en '.$incident->ticket);

        $this->notifyCommentInvolved($incident, $comment, $request->user());

        return back()->with('success', 'Comentario registrado correctamente.');
    }

    public function reports(Request $request)
    {
        $reportData = $this->buildReportData($request);
        $incidents = $reportData['query']->latest('id')->paginate(15)->appends($request->query());
        $stats = $reportData['stats'];

        $zones = IncidentOption::where('type', 'zone')->where('is_active', true)->orderBy('sort_order')->get();
        $priorities = IncidentOption::where('type', 'priority')->where('is_active', true)->orderBy('sort_order')->get();
        $impacts = IncidentOption::where('type', 'impact')->where('is_active', true)->orderBy('sort_order')->get();
        $activeUsers = User::where('is_active', true)->orderBy('name')->get();

        return view('incidents.reports', compact('incidents', 'stats', 'zones', 'priorities', 'impacts', 'activeUsers'));
    }

    public function exportExcel(Request $request, IncidentReportExportService $exporter, AuthEventService $events)
    {
        $reportData = $this->buildReportData($request);
        $spreadsheet = $exporter->build($reportData['allMatching'], $reportData['stats'], $reportData['filterLabels'], $request->user());

        $events->record($request, 'incident_report_exported_excel', true, reason: 'Reporte de incidentes exportado a Excel');

        $filename = 'Reporte-Incidentes-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function exportPdf(Request $request, IncidentPdfService $pdfService, AuthEventService $events)
    {
        $reportData = $this->buildReportData($request);
        $pdfOutput = $pdfService->renderReport($reportData['allMatching'], $reportData['stats'], $reportData['filterLabels'], $request->user());

        $events->record($request, 'incident_report_exported_pdf', true, reason: 'Reporte de incidentes exportado a PDF');

        $filename = 'Reporte-Incidentes-'.now()->format('Ymd-His').'.pdf';

        return response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function buildReportData(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'zone_id' => ['nullable', 'integer'],
            'failure_type' => ['nullable', 'in:risk,general'],
            'priority_id' => ['nullable', 'integer'],
            'impact_id' => ['nullable', 'integer'],
            'responsible_user_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:activo,inactivo,archivado'],
        ]);

        $query = Incident::visibleFor($request->user())
            ->with(['creator', 'responsibleUser', 'area', 'zone', 'failure', 'impact', 'priority', 'attachments']);

        $filterLabels = [];

        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
            $filterLabels[] = 'Desde: '.\Carbon\Carbon::parse($validated['date_from'])->format('d/m/Y');
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
            $filterLabels[] = 'Hasta: '.\Carbon\Carbon::parse($validated['date_to'])->format('d/m/Y');
        }

        if (!empty($validated['zone_id'])) {
            $query->where('zone_id', $validated['zone_id']);
            $zone = IncidentOption::find($validated['zone_id']);
            if ($zone) { $filterLabels[] = 'Sede: '.$zone->name; }
        }

        if (!empty($validated['failure_type'])) {
            $query->where('failure_type', $validated['failure_type']);
            $filterLabels[] = 'Clasificación: '.($validated['failure_type'] === 'risk' ? 'Riesgo priorizado' : 'Incidente general');
        }

        if (!empty($validated['priority_id'])) {
            $query->where('priority_id', $validated['priority_id']);
            $prio = IncidentOption::find($validated['priority_id']);
            if ($prio) { $filterLabels[] = 'Prioridad: '.$prio->name; }
        }

        if (!empty($validated['impact_id'])) {
            $query->where('impact_id', $validated['impact_id']);
            $imp = IncidentOption::find($validated['impact_id']);
            if ($imp) { $filterLabels[] = 'Impacto: '.$imp->name; }
        }

        if (!empty($validated['responsible_user_id'])) {
            $query->where('responsible_user_id', $validated['responsible_user_id']);
            $resp = User::find($validated['responsible_user_id']);
            if ($resp) { $filterLabels[] = 'Responsable: '.trim($resp->name.' '.$resp->last_name); }
        }

        if (!empty($validated['status'])) {
            if ($validated['status'] === 'archivado') {
                $query->whereNotNull('archived_at');
                $filterLabels[] = 'Estado: Archivados';
            } elseif ($validated['status'] === 'activo') {
                $query->where('is_active', true)->whereNull('archived_at');
                $filterLabels[] = 'Estado: Activos';
            } elseif ($validated['status'] === 'inactivo') {
                $query->where('is_active', false)->whereNull('archived_at');
                $filterLabels[] = 'Estado: Inactivos';
            }
        }

        $allMatching = (clone $query)->latest('id')->get();

        // Calculate statistics
        $total = $allMatching->count();
        $totalRisks = $allMatching->where('failure_type', 'risk')->count();
        $totalGenerals = $allMatching->where('failure_type', '!=', 'risk')->count();
        $totalActive = $allMatching->where('is_active', true)->whereNull('archived_at')->count();
        $totalInactive = $allMatching->where('is_active', false)->whereNull('archived_at')->count();
        $totalArchived = $allMatching->whereNotNull('archived_at')->count();

        $affectsOperation = $allMatching->filter(function ($inc) {
            $name = mb_strtolower($inc->impact?->name ?? '');
            return str_contains($name, 'sí') || str_contains($name, 'si');
        })->count();

        // By Zone
        $zonesGroup = $allMatching->groupBy('zone_id');
        $allZones = IncidentOption::where('type', 'zone')->orderBy('sort_order')->get();
        $byZone = [];
        foreach ($allZones as $zone) {
            $items = $zonesGroup->get($zone->id, collect());
            if ($items->isNotEmpty() || empty($validated['zone_id'])) {
                $byZone[] = [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'count' => $items->count(),
                    'affects_count' => $items->filter(fn ($i) => str_contains(mb_strtolower($i->impact?->name ?? ''), 'sí') || str_contains(mb_strtolower($i->impact?->name ?? ''), 'si'))->count(),
                ];
            }
        }
        usort($byZone, fn ($a, $b) => $b['count'] <=> $a['count']);

        // By Priority
        $prioGroup = $allMatching->groupBy('priority_id');
        $allPriorities = IncidentOption::where('type', 'priority')->orderBy('sort_order')->get();
        $byPriority = [];
        foreach ($allPriorities as $prio) {
            $items = $prioGroup->get($prio->id, collect());
            if ($items->isNotEmpty() || empty($validated['priority_id'])) {
                $tone = match ($prio->name) {
                    'Alta' => 'tone-danger',
                    'Media' => 'tone-warning',
                    'Baja' => 'tone-success',
                    default => '',
                };
                $byPriority[] = [
                    'id' => $prio->id,
                    'name' => $prio->name,
                    'tone' => $tone,
                    'count' => $items->count(),
                ];
            }
        }

        // By Failure / Risk
        $failureGroup = $allMatching->groupBy('failure_id');
        $byFailure = [];
        foreach ($failureGroup as $failId => $items) {
            $first = $items->first();
            $byFailure[] = [
                'name' => $first->failure?->name ?? 'Sin especificar',
                'is_risk' => $first->failure_type === 'risk',
                'count' => $items->count(),
            ];
        }
        usort($byFailure, fn ($a, $b) => $b['count'] <=> $a['count']);
        $byFailure = array_slice($byFailure, 0, 6);

        // By Responsible
        $respGroup = $allMatching->groupBy(function ($inc) {
            return $inc->responsible_name ?: ($inc->responsibleUser ? trim($inc->responsibleUser->name.' '.$inc->responsibleUser->last_name) : 'Sin asignar');
        });
        $byResponsible = [];
        foreach ($respGroup as $respName => $items) {
            $first = $items->first();
            $byResponsible[] = [
                'name' => $respName,
                'position' => $first->responsible_position ?: 'Sin cargo',
                'count' => $items->count(),
            ];
        }
        usort($byResponsible, fn ($a, $b) => $b['count'] <=> $a['count']);
        $byResponsible = array_slice($byResponsible, 0, 6);

        $stats = [
            'total' => $total,
            'total_risks' => $totalRisks,
            'total_generals' => $totalGenerals,
            'total_active' => $totalActive,
            'total_inactive' => $totalInactive,
            'total_archived' => $totalArchived,
            'affects_operation' => $affectsOperation,
            'by_zone' => $byZone,
            'by_priority' => $byPriority,
            'by_failure' => $byFailure,
            'by_responsible' => $byResponsible,
        ];

        return [
            'query' => $query,
            'allMatching' => $allMatching,
            'stats' => $stats,
            'filterLabels' => $filterLabels,
        ];
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
        $incident->load(['attachments', 'responsibleUser']);

        $reporter = $this->reporterData($incident);
        $profileIncomplete = !$incident->exists && in_array('', $reporter, true);

        $activeUsers = User::query()
            ->where(function ($q) use ($incident) {
                $q->where('is_active', true);
                if ($incident->exists && $incident->responsible_user_id) {
                    $q->orWhere('id', $incident->responsible_user_id);
                }
            })
            ->orderBy('name')
            ->orderBy('last_name')
            ->get(['id', 'name', 'last_name', 'position', 'email', 'document_type', 'document_number']);

        $usersByPosition = User::whereNotNull('position')
            ->where('position', '!=', '')
            ->where('is_active', true)
            ->get()
            ->groupBy(fn ($u) => mb_strtolower(trim($u->position)));

        $failureMeta = [];
        foreach (['failure', 'prioritized_risk'] as $type) {
            foreach ($options->get($type, collect()) as $opt) {
                $pos = trim($opt->responsible_position ?? '');
                $user = $pos !== '' ? $usersByPosition->get(mb_strtolower($pos))?->first() : null;

                $failureMeta[$opt->id] = [
                    'position' => $pos,
                    'user_id' => $user?->id,
                    'user_name' => $user ? trim($user->name.' '.$user->last_name) : null,
                    'user_email' => $user?->email,
                    'user' => $user ? trim($user->name.' '.$user->last_name).' ('.$user->email.')' : ($pos !== '' ? 'Sin usuario con este cargo registrado' : 'Sin cargo asignado por ahora'),
                ];
            }
        }

        return view('incidents.form', compact('incident', 'options', 'reporter', 'profileIncomplete', 'failureMeta', 'activeUsers'));
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
            'failure_type' => ['nullable', 'string', Rule::in(['general', 'risk'])],
            'responsible_user_id' => ['required', 'integer', Rule::exists('users', 'id')],
            'location' => ['required', 'string', 'max:500'],
            'description' => ['required', 'string', 'max:5000'],
            'affected_assets' => ['nullable', 'string', 'max:500'],
            'responsible_position' => ['nullable', 'string', 'max:150'],
            'responsible_name' => ['nullable', 'string', 'max:150'],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['bail', 'file', 'image', 'mimes:jpg,jpeg,png', 'extensions:jpg,jpeg,png', 'max:10240'],
            'remove_attachments' => ['nullable', 'array'],
            'remove_attachments.*' => ['integer', Rule::exists('incident_attachments', 'id')->where('incident_id', $incident->id ?? 0)],
        ];
        foreach (['zone', 'failure', 'impact', 'priority'] as $field) {
            $current = $incident->{$field.'_id'};
            $validTypes = $field === 'failure' ? ['failure', 'prioritized_risk'] : [$field];
            $rules[$field.'_id'] = [$field === 'zone' ? 'nullable' : 'required', 'integer',
                Rule::exists('incident_options', 'id')->where(fn ($q) => $q->whereIn('type', $validTypes)
                    ->where(fn ($active) => $active->where('is_active', true)->orWhere('id', $current ?? 0))),
            ];
        }
        if ($incident->exists) { $rules['status'] = ['sometimes', Rule::in(array_keys(Incident::STATUSES))]; }
        $data = $request->validate($rules, [
            'responsible_user_id.required' => 'El responsable asignado para notificación es obligatorio.',
            'responsible_user_id.exists' => 'El responsable seleccionado no es válido.',
            'attachments.*.file' => 'Solo puedes subir imágenes JPG o PNG como evidencia.',
            'attachments.*.image' => 'Solo puedes subir imágenes JPG o PNG como evidencia.',
            'attachments.*.mimes' => 'Solo puedes subir imágenes JPG o PNG como evidencia.',
            'attachments.*.extensions' => 'Solo puedes subir imágenes JPG o PNG como evidencia.',
            'attachments.*.max' => 'Cada imagen de evidencia debe pesar como máximo 10 MB.',
        ]);

        $failureOption = IncidentOption::find($data['failure_id'] ?? null);
        $data['failure_type'] = ($failureOption && $failureOption->type === 'prioritized_risk') ? 'risk' : 'general';

        $responsibleUserId = $data['responsible_user_id'] ?? null;
        if (!empty($responsibleUserId)) {
            $responsibleUser = User::find($responsibleUserId);
            if ($responsibleUser) {
                $data['responsible_user_id'] = $responsibleUser->id;
                $data['responsible_name'] = trim($responsibleUser->name.' '.$responsibleUser->last_name);
                $data['responsible_position'] = $responsibleUser->position ?: null;
            }
        } elseif (!empty($data['responsible_position'])) {
            $pos = trim($data['responsible_position']);
            if ($pos !== '' && mb_strtolower($pos) !== 'por definir' && !str_starts_with(mb_strtolower($pos), 'por definir')) {
                $foundUser = User::where('is_active', true)
                    ->where(function ($q) use ($pos) {
                        $q->where('position', $pos)
                          ->orWhereRaw('LOWER(TRIM(position)) = ?', [mb_strtolower($pos)]);
                    })->first();
                $data['responsible_user_id'] = $foundUser?->id;
                $data['responsible_position'] = $pos;
                $data['responsible_name'] = $foundUser ? trim($foundUser->name.' '.$foundUser->last_name) : ($data['responsible_name'] ?? null);
            } else {
                $data['responsible_user_id'] = null;
                $data['responsible_position'] = null;
                $data['responsible_name'] = null;
            }
        } else {
            $data['responsible_user_id'] = null;
            $data['responsible_position'] = null;
            $data['responsible_name'] = null;
        }
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

    private function notifyRiskResponsibles(Incident $incident): void
    {
        if ($incident->responsible_user_id) {
            $recipient = $incident->responsibleUser ?? User::find($incident->responsible_user_id);
            if ($recipient && !empty($recipient->email)) {
                try {
                    Mail::to($recipient->email)->send(new IncidentRiskNotificationMail($incident, $recipient));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
            return;
        }

        $pos = trim($incident->responsible_position ?? '');
        if ($pos === '' || mb_strtolower($pos) === 'por definir' || str_starts_with(mb_strtolower($pos), 'por definir')) {
            return;
        }

        $recipients = User::query()
            ->where('is_active', true)
            ->where(function ($q) use ($pos) {
                $q->where('position', $pos)
                  ->orWhereRaw('LOWER(TRIM(position)) = ?', [mb_strtolower($pos)]);
            })
            ->get();

        foreach ($recipients as $recipient) {
            if (!empty($recipient->email)) {
                try {
                    Mail::to($recipient->email)->send(new IncidentRiskNotificationMail($incident, $recipient));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    private function notifyCommentInvolved(Incident $incident, IncidentComment $comment, User $author): void
    {
        $incident->loadMissing(['creator', 'responsibleUser', 'zone', 'failure', 'priority', 'impact']);

        $recipients = collect([$incident->creator, $incident->responsibleUser])
            ->filter(fn ($u) => $u && $u->id !== $author->id && !empty($u->email))
            ->unique('id');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new IncidentCommentNotificationMail($incident, $comment, $recipient, $author));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
