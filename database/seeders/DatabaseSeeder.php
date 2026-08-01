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
            ['name' => 'Administrador', 'slug' => 'admin', 'description' => 'Administracion general del sistema', 'permissions' => ['dashboard.view', 'users.view', 'users.create', 'users.edit', 'users.delete', 'users.toggle', 'roles.view', 'roles.create', 'roles.edit', 'roles.delete', 'audit.view', 'settings.view', 'settings.edit']],
            ['name' => 'Gerente', 'slug' => 'gerente', 'description' => 'Gestion y supervision', 'permissions' => ['dashboard.view', 'users.view', 'users.create', 'users.edit', 'audit.view', 'indicators.view', 'budgets.view', 'budgets.create', 'budgets.edit', 'tasks.view', 'tasks.create', 'tasks.edit']],
            ['name' => 'Jefe', 'slug' => 'jefe', 'description' => 'Gestion de equipo', 'permissions' => ['dashboard.view', 'users.view', 'users.create', 'audit.view', 'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete']],
            ['name' => 'Operador', 'slug' => 'operador', 'description' => 'Operaciones diarias', 'permissions' => ['dashboard.view', 'tasks.view', 'tasks.create', 'tasks.edit', 'savings.view', 'savings.create', 'savings.edit']],
            ['name' => 'Auditor', 'slug' => 'auditor', 'description' => 'Auditoria y reportes', 'permissions' => ['dashboard.view', 'audit.view', 'indicators.view', 'users.view']],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }

        User::query()->updateOrCreate(
            ['email' => 'admin@crm.test'],
            [
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
            ],
        );
    }
}
