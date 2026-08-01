<?php

namespace Database\Seeders;

use App\Models\DataPolicy;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
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
