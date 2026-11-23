<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reciprocal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('departamento', 150)->default('CUNDINAMARCA');
            $table->string('municipio', 150)->default('BOGOTA DISTRITO CAPITAL');
            $table->string('entidad', 255)->default('COMPAÑÍA COLOMBIANA DE SERVICIOS DE VALOR AGREGADO Y TELEMÁTICOS COLVATEL S.A. ESP');
            $table->string('codigo', 50)->default('269411001');
            $table->string('fecha_corte', 150)->default('1 a 31 de Marzo 2026');
            $table->string('formato', 150)->default('CGN2005_002_SALDO_DE_OPERACIONES RECIPROCAS');
            $table->string('cifras', 100)->default('Cifras en Miles de Pesos');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        DB::table('financial_reciprocal_settings')->insert([
            'departamento' => 'CUNDINAMARCA',
            'municipio' => 'BOGOTA DISTRITO CAPITAL',
            'entidad' => 'COMPAÑÍA COLOMBIANA DE SERVICIOS DE VALOR AGREGADO Y TELEMÁTICOS COLVATEL S.A. ESP',
            'codigo' => '269411001',
            'fecha_corte' => '1 a 31 de Marzo 2026',
            'formato' => 'CGN2005_002_SALDO_DE_OPERACIONES RECIPROCAS',
            'cifras' => 'Cifras en Miles de Pesos',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reciprocal_settings');
    }
};

