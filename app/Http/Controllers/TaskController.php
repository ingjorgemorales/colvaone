<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['creator', 'assignees', 'group']);

        $user = $request->user();

        $query->visibleFor($user);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('area', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'retrasada') {
                $query->whereDate('end_date', '<', today())
                    ->whereNotIn('status', Task::LOCKED_STATUSES);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assignee')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $request->assignee));
        }

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $query->orderBy('end_date', 'asc');

        $tasks = $query->paginate(15)->withQueryString();

        $groups = Group::where('status', 'active')
            ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id())->where('group_user.is_active', true))
            ->get();

        return view('tasks.index', compact('tasks', 'groups'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->hasPermission('group_tasks.view_all') || $user->hasPermission('group_tasks.create')) {
            $groups = Group::where('status', 'active')
                ->whereHas('users', fn ($q) => $q->where('users.id', $user->id)->where('group_user.is_active', true))
                ->get();
        } else {
            $groups = collect();
        }

        $groupId = request('group_id');
        $users = collect();

        if ($groupId) {
            $group = Group::find($groupId);
            if ($group && $group->isMember($user)) {
                $users = $group->activeMembers()->orderBy('name')->get();
            }
        }

        return view('tasks.create', compact('users', 'groups', 'groupId'));
    }

    public function getGroupMembers(Group $group): JsonResponse
    {
        $user = Auth::user();

        if (!$group->isMember($user) && !$user->hasPermission('group_tasks.view_all')) {
            return response()->json(['error' => 'No tienes acceso a este grupo.'], 403);
        }

        $members = $group->activeMembers()
            ->orderBy('name')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'name' => $m->name . ' ' . ($m->last_name ?? ''),
                'email' => $m->email,
                'document_type' => $m->document_type,
                'document_number' => $m->document_number,
                'role' => $m->role_label,
                'initials' => strtoupper(substr($m->name, 0, 1) . substr($m->last_name ?? '', 0, 1)),
            ]);

        return response()->json($members);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'nullable|string|max:255',
            'group_id' => 'required|exists:groups,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:today|after_or_equal:start_date',
            'priority' => 'required|in:baja,media,alta,urgente',
            'observations' => 'nullable|string',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
        ]);

        $user = Auth::user();
        $group = Group::find($validated['group_id']);

        if (!$group->isActive()) {
            return back()->withInput()->with('error', 'El grupo seleccionado esta inactivo.');
        }

        if (!$group->isMember($user) && !$user->hasPermission('group_tasks.view_all')) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        foreach ($validated['assignees'] as $assigneeId) {
            if (!$group->hasMember(User::find($assigneeId))) {
                return back()->withInput()->with('error', 'Uno o mas usuarios seleccionados no pertenecen a este grupo.');
            }
        }

        $task = Task::create([
            'created_by' => Auth::id(),
            'group_id' => $validated['group_id'],
            'assigned_by' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'area' => $validated['area'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'priority' => $validated['priority'],
            'status' => 'asignada',
            'observations' => $validated['observations'] ?? null,
        ]);

        $task->assignees()->attach($validated['assignees']);

        $assignedUsers = User::whereIn('id', $validated['assignees'])->get();
        foreach ($assignedUsers as $u) {
            $this->notifyTask(
                $u,
                $task,
                'assigned', 'Nueva tarea asignada',
                'te han asignado la tarea que creaste en ColvaOne.',
                Auth::user()
            );
        }

        return redirect()->route('tasks.index')->with('success', 'Tarea creada y notificaciones enviadas.');
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);

        $task->load(['creator', 'assignees', 'comments.user', 'group']);
        $users = User::orderBy('name')->get();

        return view('tasks.show', compact('task', 'users'));
    }

    public function edit(Task $task)
    {
        $this->authorizeTask($task);

        if ($task->isLocked()) {
            return redirect()->route('tasks.show', $task)->with('error', 'No se puede editar una tarea ' . $task->status . '.');
        }

        $task->load('assignees');
        $user = Auth::user();

        $groups = Group::where('status', 'active')
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id)->where('group_user.is_active', true))
            ->get();

        $users = collect();
        if ($task->group_id) {
            $users = $task->group->activeMembers()->orderBy('name')->get();
        }

        return view('tasks.edit', compact('task', 'users', 'groups'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $this->ensureTaskIsEditable($task);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'nullable|string|max:255',
            'group_id' => 'required|exists:groups,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:today|after_or_equal:start_date',
            'priority' => 'required|in:baja,media,alta,urgente',
            'observations' => 'nullable|string',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
        ]);

        $user = Auth::user();
        $group = Group::find($validated['group_id']);

        if (!$group->isMember($user) && !$user->hasPermission('group_tasks.view_all')) {
            abort(403, 'No tienes acceso a este grupo.');
        }

        foreach ($validated['assignees'] as $assigneeId) {
            if (!$group->hasMember(User::find($assigneeId))) {
                return back()->withInput()->with('error', 'Uno o mas usuarios seleccionados no pertenecen a este grupo.');
            }
        }

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'area' => $validated['area'] ?? null,
            'group_id' => $validated['group_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'priority' => $validated['priority'],
            'observations' => $validated['observations'] ?? null,
        ]);

        $task->assignees()->sync($validated['assignees']);

        $notified = [Auth::id()];
        foreach ($validated['assignees'] as $assigneeId) {
            if (!in_array($assigneeId, $notified)) {
                $u = User::find($assigneeId);
                if ($u) {
                    $this->notifyTask(
                        $u,
                        $task,
                        'assigned', 'Tarea actualizada',
                        'la tarea que tienes asignada fue actualizada.',
                        Auth::user()
                    );
                    $notified[] = $assigneeId;
                }
            }
        }

        return redirect()->route('tasks.index')->with('success', 'Tarea actualizada.');
    }

    public function archive(Task $task)
    {
        $this->authorizeTask($task);
        $this->ensureTaskIsEditable($task);

        $task->update(['status' => 'archivada']);
        return redirect()->route('tasks.index')->with('success', 'Tarea archivada.');
    }

    public function updateProgress(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $this->ensureTaskIsEditable($task);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $task->assignees()->updateExistingPivot($validated['user_id'], [
            'progress' => $validated['progress'],
            'status' => $validated['progress'] >= 100 ? 'completada' : ($validated['progress'] > 0 ? 'en_progreso' : 'pendiente'),
        ]);

        $avgProgress = $task->assignees()->pluck('task_assignments.progress')->avg();
        $newProgress = round($avgProgress);

        if ($newProgress >= 100) {
            $delayed = $task->days_delayed;
            $task->update(['progress' => 100, 'status' => 'finalizada', 'days_delayed' => $delayed]);
        } elseif ($newProgress > 0) {
            $task->update(['progress' => $newProgress, 'status' => 'en_progreso']);
        } else {
            $task->update(['progress' => 0, 'status' => 'asignada']);
        }

        $userWhoUpdated = User::find($validated['user_id']);

        if ($task->creator && $task->creator->id !== Auth::id()) {
            $this->notifyTask(
                $task->creator,
                $task,
                'progress', 'Progreso actualizado',
                "el usuario {$userWhoUpdated->name} actualizo el progreso de la tarea a {$validated['progress']}%.",
                Auth::user(),
                "Progreso: {$validated['progress']}%"
            );
        }

        return back()->with('success', 'Progreso actualizado.');
    }

    public function addComment(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $validated = $request->validate([
            'comment' => 'required|string',
            'parent_id' => 'nullable|exists:task_comments,id',
        ]);

        $task->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $notified = [Auth::id()];
        $actors = collect();

        if ($task->creator && !in_array($task->creator->id, $notified)) {
            $actors->push($task->creator);
            $notified[] = $task->creator->id;
        }

        foreach ($task->assignees as $a) {
            if (!in_array($a->id, $notified)) {
                $actors->push($a);
                $notified[] = $a->id;
            }
        }

        foreach ($actors as $actor) {
            $this->notifyTask(
                $actor,
                $task,
                'comment', 'Nuevo comentario',
                'se agrego un comentario en la tarea.',
                Auth::user(),
                $validated['comment']
            );
        }

        return back()->with('success', 'Comentario agregado.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $this->ensureTaskIsEditable($task);

        $validated = $request->validate([
            'status' => 'required|in:asignada,en_progreso,finalizada,cancelada,archivada',
        ]);

        $oldStatus = $task->status;
        $task->update(['status' => $validated['status']]);

        if (in_array($validated['status'], ['finalizada', 'completada'])) {
            $delayed = $task->days_delayed;
            $task->assignees()->updateExistingPivot(
                $task->assignees()->pluck('users.id')->toArray(),
                ['progress' => 100, 'status' => 'completada']
            );
            $task->update(['progress' => 100, 'days_delayed' => $delayed]);
        }

        $statusLabels = ['pendiente'=>'Pendiente','asignada'=>'Asignada','en_progreso'=>'En progreso','bloqueada'=>'Bloqueada','en_revision'=>'En revision','finalizada'=>'Finalizada','cancelada'=>'Cancelada','archivada'=>'Archivada'];
        $notified = [Auth::id()];
        $actors = collect();

        if ($task->creator && !in_array($task->creator->id, $notified)) {
            $actors->push($task->creator);
            $notified[] = $task->creator->id;
        }

        foreach ($task->assignees as $a) {
            if (!in_array($a->id, $notified)) {
                $actors->push($a);
                $notified[] = $a->id;
            }
        }

        foreach ($actors as $actor) {
            $this->notifyTask(
                $actor,
                $task,
                'status', 'Estado actualizado',
                'el estado de la tarea cambio de "' . ($statusLabels[$oldStatus] ?? $oldStatus) . '" a "' . ($statusLabels[$validated['status']] ?? $validated['status']) . '".',
                Auth::user()
            );
        }

        return back()->with('success', 'Estado actualizado.');
    }

    private function authorizeTask(Task $task): void
    {
        $user = Auth::user();

        if ($user->hasPermission('group_tasks.view_all')) {
            return;
        }

        if ($task->created_by === $user->id) {
            return;
        }

        if ($task->responsible_user_id === $user->id) {
            return;
        }

        if ($task->assignees->contains($user->id)) {
            return;
        }

        if ($user->hasPermission('group_tasks.view_group') && $task->group && $task->group->isManager($user)) {
            return;
        }

        abort(403, 'No tienes acceso a esta tarea.');
    }

    private function ensureTaskIsEditable(Task $task): void
    {
        if ($task->isLocked()) {
            abort(403, 'No se puede modificar una tarea finalizada, cancelada o archivada.');
        }
    }

    private function notifyTask(
        User $recipient,
        Task $task,
        string $action,
        string $actionLabel,
        string $actionDetail,
        User $actedBy,
        ?string $extra = null,
    ): void {
        app(TaskNotificationService::class)->send(
            $recipient,
            $task,
            $action,
            $actionLabel,
            $actionDetail,
            $actedBy,
            $extra,
        );
    }
}
