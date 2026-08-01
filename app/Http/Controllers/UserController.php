<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\AuthEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = self::roles();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request, AuthEventService $events): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'document_type' => ['required', 'string', 'max:30'],
            'document_number' => ['required', 'string', 'max:50', 'unique:users,document_number'],
            'phone' => ['required', 'string', 'max:40'],
            'position' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:30'],
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
            'role' => $request->input('role'),
            'password' => Hash::make($tempPassword),
            'is_active' => true,
            'must_change_password' => true,
            'email_verified_at' => now(),
            'created_by' => $request->user()->id,
        ]);

        $events->record($request, 'user_created', true, $request->user(), $user->email, "Usuario {$user->name} creado");

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
        $roles = self::roles();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user, AuthEventService $events): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'document_type' => ['required', 'string', 'max:30'],
            'document_number' => ['required', 'string', 'max:50', 'unique:users,document_number,' . $user->id],
            'phone' => ['required', 'string', 'max:40'],
            'position' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:30'],
        ]);

        $user->update($request->only([
            'name', 'last_name', 'email', 'document_type', 'document_number',
            'phone', 'position', 'area', 'department', 'role',
        ]) + ['updated_by' => $request->user()->id]);

        $events->record($request, 'user_updated', true, $request->user(), $user->email, "Usuario {$user->name} actualizado");

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado.');
    }

    public function toggle(User $user, Request $request, AuthEventService $events): RedirectResponse
    {
        $user->update([
            'is_active' => !$user->is_active,
            'updated_by' => $request->user()->id,
        ]);

        $status = $user->is_active ? 'activado' : 'desactivado';
        $events->record($request, 'user_toggled', true, $request->user(), $user->email, "Usuario {$user->name} {$status}");

        return redirect()->route('users.index')
            ->with('success', "Usuario {$status} correctamente.");
    }

    public function destroy(User $user, Request $request, AuthEventService $events): RedirectResponse
    {
        $events->record($request, 'user_deleted', true, $request->user(), $user->email, "Usuario {$user->name} eliminado");
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado.');
    }

    public static function roles(): array
    {
        return [
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'jefe' => 'Jefe',
            'operador' => 'Operador',
            'auditor' => 'Auditor',
        ];
    }
}
