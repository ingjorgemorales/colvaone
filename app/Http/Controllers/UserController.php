<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'document_type' => ['nullable', 'string', 'max:30'],
            'document_number' => ['nullable', 'string', 'max:50', 'unique:users,document_number'],
            'phone' => ['nullable', 'string', 'max:40'],
            'position' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ]);

        $tempPassword = Str::random(12);

        $user = User::create([
            'name' => $request->input('name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'document_type' => $request->input('document_type'),
            'document_number' => $request->input('document_number'),
            'phone' => $request->input('phone'),
            'position' => $request->input('position'),
            'area' => $request->input('area'),
            'department' => $request->input('department'),
            'password' => Hash::make($tempPassword),
            'is_active' => true,
            'must_change_password' => true,
            'email_verified_at' => now(),
        ]);

        Mail::to($user->email)->send(new WelcomeMail(
            trim($user->name . ' ' . ($user->last_name ?? '')),
            $user->email,
            $tempPassword
        ));

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado. Se envio un correo con las credenciales temporales.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'document_type' => ['nullable', 'string', 'max:30'],
            'document_number' => ['nullable', 'string', 'max:50', 'unique:users,document_number,' . $user->id],
            'phone' => ['nullable', 'string', 'max:40'],
            'position' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        $user->update($request->only([
            'name', 'last_name', 'email', 'document_type', 'document_number',
            'phone', 'position', 'area', 'department', 'is_active',
        ]));

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado.');
    }
}
