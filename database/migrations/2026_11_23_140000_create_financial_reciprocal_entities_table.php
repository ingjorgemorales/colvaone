<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reciprocal_entities', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_entidad_reciproca', 50)->unique();
            $table->string('nombre_entidad_reciproca', 255)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Populate from existing financial_reciprocals records
        if (Schema::hasTable('financial_reciprocals')) {
            $existing = DB::table('financial_reciprocals')
                ->select(
                    DB::raw('TRIM(codigo_entidad_reciproca) as codigo_entidad_reciproca'),
                    DB::raw('TRIM(nombre_entidad_reciproca) as nombre_entidad_reciproca'),
                    DB::raw('MIN(created_by) as created_by')
                )
                ->whereNotNull('codigo_entidad_reciproca')
                ->where('codigo_entidad_reciproca', '!=', '')
                ->whereNotNull('nombre_entidad_reciproca')
                ->where('nombre_entidad_reciproca', '!=', '')
                ->groupBy(DB::raw('TRIM(codigo_entidad_reciproca)'), DB::raw('TRIM(nombre_entidad_reciproca)'))
                ->orderBy('nombre_entidad_reciproca')
                ->get();

            $now = now();
            foreach ($existing as $item) {
                if (empty($item->codigo_entidad_reciproca)) {
                    continue;
                }

                DB::table('financial_reciprocal_entities')->updateOrInsert(
                    ['codigo_entidad_reciproca' => $item->codigo_entidad_reciproca],
                    [
                        'nombre_entidad_reciproca' => $item->nombre_entidad_reciproca,
                        'is_active' => true,
                        'created_by' => $item->created_by,
                        'updated_by' => $item->created_by,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reciprocal_entities');
    }
};
