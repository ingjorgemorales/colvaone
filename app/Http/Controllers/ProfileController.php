<?php

namespace App\Http\Controllers;

use App\Services\AuthEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request, AuthEventService $events): RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:40'],
            'position' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($request->only([
            'name', 'last_name', 'email', 'phone', 'position', 'area', 'department',
        ]));

        $events->record($request, 'profile_updated', true, $user);

        return redirect()->route('profile.edit')->with('status', 'Perfil actualizado correctamente.');
    }
}
