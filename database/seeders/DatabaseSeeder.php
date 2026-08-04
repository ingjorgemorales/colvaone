<?php

namespace Database\Seeders;

use App\Models\DataPolicy;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DataPolicy::query()->updateOrCreate(
            ['version' => '2026.07'],
            [
                'published_at' => '2026-07-31',
                'is_active' => true,
                'content' => 'La organizacion tratara los datos personales de los usuarios internos para autenticacion, autorizacion, trazabilidad, seguridad, notificaciones y gestion administrativa. El acceso esta limitado a personal autorizado y toda actividad sensible podra ser auditada.',
            ],
        );

        $roles = [
            ['name' => 'Super Administrador', 'slug' => 'superadmin', 'description' => 'Control total del sistema', 'permissions' => null],
            ['name' => 'Administrador', 'slug' => 'admin', 'description' => 'Administracion general del sistema', 'permissions' => ['dashboard.view', 'users.view', 'users.create', 'users.edit', 'users.delete', 'users.toggle', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'audit.view', 'settings.view', 'settings.edit', 'groups.view', 'groups.view_all', 'groups.create', 'groups.update', 'groups.disable', 'groups.manage_members', 'groups.assign_manager', 'group_tasks.view', 'group_tasks.view_all', 'group_tasks.create', 'group_tasks.assign', 'group_tasks.reassign', 'group_tasks.update', 'group_tasks.update_progress', 'group_tasks.comment', 'group_tasks.complete', 'group_tasks.cancel', 'group_tasks.archive', 'group_tasks.delete']],
            ['name' => 'Gerente', 'slug' => 'gerente', 'description' => 'Gestion y supervision', 'permissions' => ['dashboard.view', 'users.view', 'users.create', 'users.edit', 'audit.view', 'indicators.view', 'budgets.view', 'budgets.create', 'budgets.edit', 'tasks.view', 'tasks.create', 'tasks.edit', 'groups.view', 'groups.create', 'groups.update', 'groups.manage_members', 'group_tasks.view', 'group_tasks.create', 'group_tasks.assign', 'group_tasks.reassign', 'group_tasks.update', 'group_tasks.update_progress', 'group_tasks.comment', 'group_tasks.complete', 'group_tasks.cancel']],
            ['name' => 'Jefe', 'slug' => 'jefe', 'description' => 'Gestion de equipo', 'permissions' => ['dashboard.view', 'users.view', 'users.create', 'audit.view', 'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete']],
            ['name' => 'Operador', 'slug' => 'operador', 'description' => 'Operaciones diarias', 'permissions' => ['dashboard.view', 'tasks.view', 'tasks.create', 'tasks.edit', 'savings.view', 'savings.create', 'savings.edit']],
            ['name' => 'Auditor', 'slug' => 'auditor', 'description' => 'Auditoria y reportes', 'permissions' => ['dashboard.view', 'audit.view', 'indicators.view', 'users.view']],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        if (User::withTrashed()->where('email', 'admin@crm.test')->exists()) {
            User::withTrashed()->where('email', 'admin@crm.test')->first()->update([
                'name' => 'Administrador',
                'last_name' => 'Principal',
                'email_verified_at' => now(),
                'password' => Hash::make('TempPass!2026'),
                'is_active' => true,
                'must_change_password' => true,
                'role' => 'admin',
                'position' => 'Superadministrador',
                'area' => 'Administracion',
                'department' => 'Tecnologia',
            ]);
        } else {
            User::create([
                'email' => 'admin@crm.test',
                'name' => 'Administrador',
                'last_name' => 'Principal',
                'email_verified_at' => now(),
                'password' => Hash::make('TempPass!2026'),
                'is_active' => true,
                'must_change_password' => true,
                'role' => 'admin',
                'position' => 'Superadministrador',
                'area' => 'Administracion',
                'department' => 'Tecnologia',
            ]);
        }
    }
}
