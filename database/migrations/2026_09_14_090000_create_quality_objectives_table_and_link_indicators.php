<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_objectives', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('quality_objectives')->insert([
            [
                'name' => 'Aumentar el valor de la Compañía',
                'position' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Satisfacer las necesidades y expectativas de nuestros clientes.',
                'position' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Asegurar la calidad de los procesos.',
                'position' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Fomentar la especialización y el crecimiento de los colaboradores.',
                'position' => 40,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'No aplica',
                'position' => 50,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table('indicators', function (Blueprint $table) {
            $table->foreignId('quality_objective_id')
                ->nullable()
                ->after('methodological_aspects')
                ->constrained('quality_objectives')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quality_objective_id');
        });

        Schema::dropIfExists('quality_objectives');
    }
};
