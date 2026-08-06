<?php

namespace App\Http\Controllers;

use App\Models\Committee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommitteeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Committee::with(['creator', 'members', 'latestReport'])
            ->withCount('reports');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhereHas('reports', fn ($reports) => $reports->where('content', 'like', "%{$search}%"));
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
        $validated = $this->validateCommittee($request, true);
        $reports = collect($validated['reports'])
            ->map(fn ($report) => trim($report))
            ->filter()
            ->values();

        if ($reports->isEmpty()) {
            return back()->withInput()->withErrors(['reports' => 'Debes registrar al menos un relato.']);
        }

        $committee = DB::transaction(function () use ($validated, $reports): Committee {
            $firstReport = $reports->first();
            $committee = Committee::create([
                'title' => $validated['title'],
                'committee_date' => $validated['committee_date'],
                'summary' => $firstReport,
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            $committee->members()->attach($validated['members']);

            foreach ($reports as $report) {
                $committee->reports()->create([
                    'content' => $report,
                    'registered_at' => now(),
                    'created_by' => Auth::id(),
                ]);
            }

            return $committee;
        });

        return redirect()->route('committees.show', $committee)
            ->with('success', 'Comite creado correctamente.');
    }

    public function show(Committee $committee): View
    {
        $committee->load(['creator', 'updater', 'members', 'reports.creator']);

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
            'updated_by' => Auth::id(),
        ]);

        $committee->members()->sync($validated['members']);

        return redirect()->route('committees.show', $committee)
            ->with('success', 'Comite actualizado correctamente.');
    }

    public function addReport(Request $request, Committee $committee): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
        ]);

        $content = trim($validated['content']);
        if ($content === '') {
            return back()->withInput()->withErrors(['content' => 'El relato es obligatorio.']);
        }

        $committee->reports()->create([
            'content' => $content,
            'registered_at' => now(),
            'created_by' => Auth::id(),
        ]);

        $committee->update([
            'summary' => $content,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Relato agregado correctamente.');
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

    private function validateCommittee(Request $request, bool $includeReports = false): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'committee_date' => ['required', 'date', 'after_or_equal:today'],
            'members' => ['required', 'array', 'min:1'],
            'members.*' => ['exists:users,id'],
        ];

        if ($includeReports) {
            $rules['reports'] = ['required', 'array', 'min:1'];
            $rules['reports.*'] = ['required', 'string'];
        }

        return $request->validate($rules);
    }

    private function activeUsers()
    {
        return User::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
