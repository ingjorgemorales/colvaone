<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CommitteeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Committee::with(['creator', 'members']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('committee_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('committee_date', '<=', $request->input('date_to'));
        }

        $committees = $query->orderByDesc('committee_date')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('committees.index', compact('committees'));
    }

    public function create(): View
    {
        $users = $this->activeUsers();

        return view('committees.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCommittee($request);

        $committee = Committee::create([
            'title' => $validated['title'],
            'committee_date' => $validated['committee_date'],
            'summary' => $validated['summary'],
            'status' => 'active',
            'created_by' => Auth::id(),
        ]);

        $committee->members()->attach($validated['members']);

        return redirect()->route('committees.show', $committee)
            ->with('success', 'Comite creado correctamente.');
    }

    public function show(Committee $committee): View
    {
        $committee->load(['creator', 'updater', 'members']);

        return view('committees.show', compact('committee'));
    }

    public function edit(Committee $committee): View
    {
        $committee->load('members');
        $users = $this->activeUsers();

        return view('committees.edit', compact('committee', 'users'));
    }

    public function update(Request $request, Committee $committee): RedirectResponse
    {
        $validated = $this->validateCommittee($request);

        $committee->update([
            'title' => $validated['title'],
            'committee_date' => $validated['committee_date'],
            'summary' => $validated['summary'],
            'updated_by' => Auth::id(),
        ]);

        $committee->members()->sync($validated['members']);

        return redirect()->route('committees.show', $committee)
            ->with('success', 'Comite actualizado correctamente.');
    }

    public function toggle(Committee $committee): RedirectResponse
    {
        $committee->update([
            'status' => $committee->status === 'active' ? 'inactive' : 'active',
            'updated_by' => Auth::id(),
        ]);

        $status = $committee->status === 'active' ? 'activado' : 'desactivado';

        return redirect()->route('committees.index')
            ->with('success', "Comite {$status} correctamente.");
    }

    private function validateCommittee(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'committee_date' => ['required', 'date'],
            'summary' => ['required', 'string'],
            'members' => ['required', 'array', 'min:1'],
            'members.*' => ['exists:users,id'],
        ]);
    }

    private function activeUsers()
    {
        return User::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
