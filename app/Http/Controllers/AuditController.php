<?php

namespace App\Http\Controllers;

use App\Models\AuthEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuthEvent::with('user');

        if ($request->filled('search')) {
            $search = $request->input('search');

            $matchingEvents = collect(AuthEvent::EVENT_LABELS)
                ->filter(fn ($label, $key) => stripos($label, $search) !== false || stripos($key, $search) !== false)
                ->keys()
                ->all();

            $query->where(function ($q) use ($search, $matchingEvents) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");

                if (!empty($matchingEvents)) {
                    $q->orWhereIn('event', $matchingEvents);
                }

                $q->orWhereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->input('event'));
        }

        if ($request->filled('date_from')) {
            $query->where('occurred_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('occurred_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        if ($request->filled('status')) {
            $query->where('successful', $request->input('status') === 'success');
        }

        $events = $query->latest('occurred_at')->paginate(20)->withQueryString();

        $eventTypes = AuthEvent::select('event')
            ->distinct()
            ->pluck('event')
            ->sortBy(fn ($type) => AuthEvent::labelFor($type))
            ->values();

        return view('audit.index', compact('events', 'eventTypes'));
    }
}
