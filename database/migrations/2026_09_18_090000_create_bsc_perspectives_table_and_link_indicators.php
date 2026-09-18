<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bsc_perspectives', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('bsc_perspectives')->insert([
            [
                'name' => 'Resultados de negocio',
                'position' => 10,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Cliente',
                'position' => 20,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Procesos internos',
                'position' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Aprendizaje y crecimiento',
                'position' => 40,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::table('indicators', function (Blueprint $table) {
            $table->foreignId('bsc_perspective_id')
                ->nullable()
                ->after('category')
                ->constrained('bsc_perspectives')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bsc_perspective_id');
        });

        Schema::dropIfExists('bsc_perspectives');
    }
};
