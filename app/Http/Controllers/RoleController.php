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
                    'users.toggle' => 'Activar/Desactivar usuarios',
                ],
            ],
            'roles' => [
                'name' => 'Roles y permisos',
                'items' => [
                    'roles.view' => 'Ver roles',
                    'roles.create' => 'Crear roles',
                    'roles.edit' => 'Editar roles',
                    'roles.toggle' => 'Activar/Desactivar roles',
                ],
            ],
            'audit' => [
                'name' => 'Auditoria',
                'items' => [
                    'audit.view' => 'Ver auditoria',
                ],
            ],
            'budgets' => [
                'name' => 'Presupuesto',
                'items' => [
                    'budgets.view' => 'Ver presupuesto',
                    'budgets.create' => 'Crear presupuesto',
                    'budgets.edit' => 'Editar presupuesto',
                    'budgets.toggle' => 'Activar/Desactivar presupuesto',
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
                    'savings.toggle' => 'Activar/Desactivar ahorros',
                ],
            ],
            'applications' => [
                'name' => 'Aplicativos',
                'items' => [
                    'applications.view' => 'Ver aplicativos',
                    'applications.create' => 'Crear aplicativos',
                    'applications.edit' => 'Editar aplicativos',
                    'applications.toggle' => 'Activar/Desactivar aplicativos',
                ],
            ],
            'contracts' => [
                'name' => 'Contratos',
                'items' => [
                    'contracts.view' => 'Ver contratos',
                    'contracts.create' => 'Crear contratos',
                    'contracts.edit' => 'Editar contratos',
                    'contracts.toggle' => 'Activar/Desactivar contratos',
                ],
            ],
            'committees' => [
                'name' => 'Comites',
                'items' => [
                    'committees.view' => 'Ver comites',
                    'committees.create' => 'Crear comites',
                    'committees.edit' => 'Editar comites',
                    'committees.toggle' => 'Activar/Desactivar comites',
                ],
            ],
            'settings' => [
                'name' => 'Configuracion',
                'items' => [
                    'settings.view' => 'Ver configuracion',
                    'settings.edit' => 'Editar configuracion',
                ],
            ],
            'groups' => [
                'name' => 'Grupos de trabajo',
                'items' => [
                    'groups.view' => 'Ver mis grupos',
                    'groups.view_all' => 'Ver todos los grupos',
                    'groups.create' => 'Crear grupos',
                    'groups.update' => 'Editar grupos',
                    'groups.disable' => 'Activar/Desactivar grupos',
                    'groups.manage_members' => 'Gestionar integrantes',
                    'groups.assign_manager' => 'Asignar gerentes',
                ],
            ],
            'group_tasks' => [
                'name' => 'Tareas',
                'items' => [
                    'group_tasks.view' => 'Ver tareas propias/asignadas',
                    'group_tasks.view_group' => 'Ver tareas de grupos que administro',
                    'group_tasks.view_all' => 'Ver todas las tareas',
                    'group_tasks.create' => 'Crear tareas',
                    'group_tasks.assign' => 'Asignar tareas',
                    'group_tasks.reassign' => 'Reasignar tareas',
                    'group_tasks.update' => 'Editar tareas',
                    'group_tasks.update_progress' => 'Actualizar progreso',
                    'group_tasks.comment' => 'Comentar en tareas',
                    'group_tasks.complete' => 'Finalizar tareas',
                    'group_tasks.cancel' => 'Cancelar tareas',
                    'group_tasks.archive' => 'Archivar tareas',
                ],
            ],
        ];
    }
}
