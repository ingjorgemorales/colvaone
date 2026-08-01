<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\AuthEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount('users')->latest()->paginate(15);
        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = self::permissions();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request, AuthEventService $events): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role = Role::create([
            'name' => $request->input('name'),
            'slug' => \Illuminate\Support\Str::slug($request->input('name')),
            'description' => $request->input('description'),
            'permissions' => $request->input('permissions', []),
        ]);

        $events->record($request, 'role_created', true, $request->user(), null, "Rol {$role->name} creado");

        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role): View
    {
        $permissions = self::permissions();
        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role, AuthEventService $events): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role->update([
            'name' => $request->input('name'),
            'slug' => \Illuminate\Support\Str::slug($request->input('name')),
            'description' => $request->input('description'),
            'permissions' => $request->input('permissions', []),
        ]);

        $events->record($request, 'role_updated', true, $request->user(), null, "Rol {$role->name} actualizado");

        return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function toggle(Role $role, Request $request, AuthEventService $events): RedirectResponse
    {
        if ($role->slug === 'superadmin') {
            return redirect()->route('roles.index')->with('error', 'No se puede desactivar el rol Super Administrador.');
        }

        $role->update(['is_active' => !$role->is_active]);

        $status = $role->is_active ? 'activado' : 'desactivado';
        $events->record($request, 'role_toggled', true, $request->user(), null, "Rol {$role->name} {$status}");

        return redirect()->route('roles.index')->with('success', "Rol {$status} correctamente.");
    }

    public function destroy(Role $role, Request $request, AuthEventService $events): RedirectResponse
    {
        if ($role->slug === 'superadmin') {
            return redirect()->route('roles.index')->with('error', 'No se puede eliminar el rol Super Administrador.');
        }

        $events->record($request, 'role_deleted', true, $request->user(), null, "Rol {$role->name} eliminado");
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado.');
    }

    public static function permissions(): array
    {
        return [
            'dashboard' => [
                'name' => 'Dashboard',
                'items' => [
                    'dashboard.view' => 'Ver dashboard',
                ],
            ],
            'users' => [
                'name' => 'Usuarios',
                'items' => [
                    'users.view' => 'Ver usuarios',
                    'users.create' => 'Crear usuarios',
                    'users.edit' => 'Editar usuarios',
                    'users.delete' => 'Eliminar usuarios',
                    'users.toggle' => 'Activar/Desactivar usuarios',
                ],
            ],
            'roles' => [
                'name' => 'Roles y permisos',
                'items' => [
                    'roles.view' => 'Ver roles',
                    'roles.create' => 'Crear roles',
                    'roles.edit' => 'Editar roles',
                    'roles.delete' => 'Eliminar roles',
                ],
            ],
            'audit' => [
                'name' => 'Auditoria',
                'items' => [
                    'audit.view' => 'Ver auditoria',
                ],
            ],
            'tasks' => [
                'name' => 'Tareas',
                'items' => [
                    'tasks.view' => 'Ver tareas',
                    'tasks.create' => 'Crear tareas',
                    'tasks.edit' => 'Editar tareas',
                    'tasks.delete' => 'Eliminar tareas',
                ],
            ],
            'budgets' => [
                'name' => 'Presupuesto',
                'items' => [
                    'budgets.view' => 'Ver presupuesto',
                    'budgets.create' => 'Crear presupuesto',
                    'budgets.edit' => 'Editar presupuesto',
                    'budgets.delete' => 'Eliminar presupuesto',
                ],
            ],
            'indicators' => [
                'name' => 'Indicadores',
                'items' => [
                    'indicators.view' => 'Ver indicadores',
                ],
            ],
            'savings' => [
                'name' => 'Ahorros',
                'items' => [
                    'savings.view' => 'Ver ahorros',
                    'savings.create' => 'Crear ahorros',
                    'savings.edit' => 'Editar ahorros',
                    'savings.delete' => 'Eliminar ahorros',
                ],
            ],
            'applications' => [
                'name' => 'Aplicativos',
                'items' => [
                    'applications.view' => 'Ver aplicativos',
                    'applications.create' => 'Crear aplicativos',
                    'applications.edit' => 'Editar aplicativos',
                    'applications.delete' => 'Eliminar aplicativos',
                ],
            ],
            'contracts' => [
                'name' => 'Contratos',
                'items' => [
                    'contracts.view' => 'Ver contratos',
                    'contracts.create' => 'Crear contratos',
                    'contracts.edit' => 'Editar contratos',
                    'contracts.delete' => 'Eliminar contratos',
                ],
            ],
            'committees' => [
                'name' => 'Comites',
                'items' => [
                    'committees.view' => 'Ver comites',
                    'committees.create' => 'Crear comites',
                    'committees.edit' => 'Editar comites',
                    'committees.delete' => 'Eliminar comites',
                ],
            ],
            'settings' => [
                'name' => 'Configuracion',
                'items' => [
                    'settings.view' => 'Ver configuracion',
                    'settings.edit' => 'Editar configuracion',
                ],
            ],
        ];
    }
}
