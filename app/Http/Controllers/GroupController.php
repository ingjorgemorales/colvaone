<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $query = Group::with(['creator', 'managers']);

        if ($request->user()->hasPermission('groups.view_all')) {
            // Admin sees all
        } else {
            $query->whereHas('users', fn ($q) => $q->where('users.id', Auth::id())->where('group_user.is_active', true));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $groups = $query->latest()->paginate(15)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('groups.index', compact('groups', 'users'));
    }

    public function create()
    {
        if (!Auth::user()->hasPermission('groups.create')) {
            abort(403);
        }
        $users = User::where('is_active', true)->orderBy('name')->get();
        return view('groups.create', compact('users'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('groups.create')) {
            abort(403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:groups,name',
            'description' => 'nullable|string',
            'managers' => 'required|array|min:1',
            'managers.*' => 'exists:users,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $group = Group::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            // Attach managers
            foreach ($validated['managers'] as $managerId) {
                $group->users()->attach($managerId, [
                    'member_type' => 'manager',
                    'is_active' => true,
                    'assigned_by' => Auth::id(),
                    'joined_at' => now(),
                ]);
            }

            // Attach members (excluding managers already added)
            $memberIds = array_diff($validated['members'] ?? [], $validated['managers']);
            foreach ($memberIds as $memberId) {
                $group->users()->attach($memberId, [
                    'member_type' => 'member',
                    'is_active' => true,
                    'assigned_by' => Auth::id(),
                    'joined_at' => now(),
                ]);
            }
        });

        return redirect()->route('groups.index')->with('success', 'Grupo creado correctamente.');
    }

    public function show(Group $group)
    {
        $this->authorizeGroup($group);

        $user = Auth::user();
        $tasksQuery = $group->tasks()->visibleFor($user);
        $tasks = (clone $tasksQuery)
            ->with(['creator', 'responsible', 'assignees'])
            ->latest()
            ->get();

        $stats = [
            'total' => (clone $tasksQuery)->count(),
            'pending' => (clone $tasksQuery)->whereIn('status', ['pendiente', 'asignada'])->count(),
            'in_progress' => (clone $tasksQuery)->where('status', 'en_progreso')->count(),
            'blocked' => (clone $tasksQuery)->where('status', 'bloqueada')->count(),
            'in_review' => (clone $tasksQuery)->where('status', 'en_revision')->count(),
            'completed' => (clone $tasksQuery)->whereIn('status', ['finalizada', 'completada'])->count(),
            'delayed' => (clone $tasksQuery)->whereNotIn('status', ['finalizada', 'completada', 'cancelada', 'archivada'])->whereDate('end_date', '<', today())->count(),
        ];

        $group->load(['creator', 'managers', 'members']);
        $group->setRelation('tasks', $tasks);

        $allUsers = User::where('is_active', true)->orderBy('name')->get();

        return view('groups.show', compact('group', 'stats', 'allUsers'));
    }

    public function edit(Group $group)
    {
        $this->authorizeGroup($group);

        if (!Auth::user()->hasPermission('groups.update')) {
            abort(403);
        }

        $group->load(['managers', 'members']);
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('groups.edit', compact('group', 'users'));
    }

    public function update(Request $request, Group $group)
    {
        $this->authorizeGroup($group);

        if (!Auth::user()->hasPermission('groups.update')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:groups,name,' . $group->id,
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'managers' => 'required|array|min:1',
            'managers.*' => 'exists:users,id',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        DB::transaction(function () use ($validated, $group) {
            $group->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
            ]);

            // Sync managers
            $group->users()->wherePivot('member_type', 'manager')->detach();
            foreach ($validated['managers'] as $managerId) {
                $existing = $group->users()->where('users.id', $managerId)->first();
                if ($existing) {
                    $existing->pivot->update(['member_type' => 'manager']);
                } else {
                    $group->users()->attach($managerId, [
                        'member_type' => 'manager',
                        'is_active' => true,
                        'assigned_by' => Auth::id(),
                        'joined_at' => now(),
                    ]);
                }
            }

            // Sync members (excluding managers)
            $memberIds = array_diff($validated['members'] ?? [], $validated['managers']);
            $group->users()->wherePivot('member_type', 'member')->detach();
            foreach ($memberIds as $memberId) {
                $existing = $group->users()->where('users.id', $memberId)->first();
                if ($existing) {
                    $existing->pivot->update(['member_type' => 'member']);
                } else {
                    $group->users()->attach($memberId, [
                        'member_type' => 'member',
                        'is_active' => true,
                        'assigned_by' => Auth::id(),
                        'joined_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('groups.index')->with('success', 'Grupo actualizado.');
    }

    public function destroy(Group $group, Request $request)
    {
        $this->authorizeGroup($group);

        if ($group->tasks()->count() > 0) {
            return redirect()->route('groups.index')->with('error', 'No se puede eliminar un grupo con tareas asociadas. Desactivalo en su lugar.');
        }

        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Grupo eliminado.');
    }

    public function toggle(Group $group, Request $request)
    {
        $this->authorizeGroup($group);

        $group->update(['status' => $group->status === 'active' ? 'inactive' : 'active']);
        $status = $group->status === 'active' ? 'activado' : 'desactivado';

        return redirect()->route('groups.index')->with('success', "Grupo {$status}.");
    }

    public function addMember(Request $request, Group $group)
    {
        $this->authorizeGroup($group);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'member_type' => 'required|in:manager,supervisor,member',
        ]);

        if ($group->users()->where('users.id', $validated['user_id'])->wherePivot('is_active', true)->exists()) {
            return back()->with('error', 'El usuario ya es integrante de este grupo.');
        }

        $group->users()->attach($validated['user_id'], [
            'member_type' => $validated['member_type'],
            'is_active' => true,
            'assigned_by' => Auth::id(),
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Integrante agregado.');
    }

    public function removeMember(Group $group, User $user)
    {
        $this->authorizeGroup($group);

        $group->users()->where('users.id', $user->id)->updateExistingPivot($user->id, [
            'is_active' => false,
            'left_at' => now(),
        ]);

        return back()->with('success', 'Integrante removido del grupo.');
    }

    private function authorizeGroup(Group $group): void
    {
        $user = Auth::user();

        if ($user->hasPermission('groups.view_all')) {
            return;
        }

        if (!$group->isMember($user)) {
            abort(403, 'No tienes acceso a este grupo.');
        }
    }
}
