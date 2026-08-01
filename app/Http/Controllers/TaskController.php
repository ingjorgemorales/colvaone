<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\AuthEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskAssignedMail;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['creator', 'assignees']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('area', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assignee')) {
            $query->whereHas('assignees', fn ($q) => $q->where('users.id', $request->assignee));
        }

        $query->orderBy('end_date', 'asc');

        $tasks = $query->paginate(15)->withQueryString();

        $users = User::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'users'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        return view('tasks.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'required|in:baja,media,alta,urgente',
            'observations' => 'nullable|string',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
        ]);

        $task = Task::create([
            'created_by' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'area' => $validated['area'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'priority' => $validated['priority'],
            'status' => 'pendiente',
            'observations' => $validated['observations'] ?? null,
        ]);

        $task->assignees()->attach($validated['assignees']);

        $assignedUsers = User::whereIn('id', $validated['assignees'])->get();
        foreach ($assignedUsers as $user) {
            Mail::to($user->email)->queue(new TaskAssignedMail($task, $user));
        }

        return redirect()->route('tasks.index')->with('success', 'Tarea creada y notificaciones enviadas.');
    }

    public function show(Task $task)
    {
        $task->load(['creator', 'assignees', 'comments.user']);
        $users = User::orderBy('name')->get();

        return view('tasks.show', compact('task', 'users'));
    }

    public function edit(Task $task)
    {
        $task->load('assignees');
        $users = User::orderBy('name')->get();

        return view('tasks.edit', compact('task', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'area' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'priority' => 'required|in:baja,media,alta,urgente',
            'status' => 'required|in:pendiente,en_progreso,completada,cancelada',
            'observations' => 'nullable|string',
            'assignees' => 'required|array|min:1',
            'assignees.*' => 'exists:users,id',
        ]);

        $task->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'area' => $validated['area'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'priority' => $validated['priority'],
            'status' => $validated['status'],
            'observations' => $validated['observations'] ?? null,
        ]);

        $task->assignees()->sync($validated['assignees']);

        return redirect()->route('tasks.index')->with('success', 'Tarea actualizada.');
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Tarea eliminada.');
    }

    public function updateProgress(Request $request, Task $task)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $task->assignees()->updateExistingPivot($validated['user_id'], [
            'progress' => $validated['progress'],
            'status' => $validated['progress'] >= 100 ? 'completada' : ($validated['progress'] > 0 ? 'en_progreso' : 'pendiente'),
        ]);

        $avgProgress = $task->assignees()->pluck('task_assignments.progress')->avg();
        $task->update(['progress' => round($avgProgress)]);

        if ($avgProgress >= 100) {
            $task->update(['status' => 'completada']);
        } elseif ($avgProgress > 0) {
            $task->update(['status' => 'en_progreso']);
        }

        return back()->with('success', 'Progreso actualizado.');
    }

    public function addComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        $task->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return back()->with('success', 'Comentario agregado.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => 'required|in:pendiente,en_progreso,completada,cancelada,vencida',
        ]);

        $task->update(['status' => $validated['status']]);

        if ($validated['status'] === 'completada') {
            $task->assignees()->updateExistingPivot(
                $task->assignees()->pluck('id')->toArray(),
                ['progress' => 100, 'status' => 'completada']
            );
            $task->update(['progress' => 100]);
        }

        return back()->with('success', 'Estado actualizado.');
    }
}
