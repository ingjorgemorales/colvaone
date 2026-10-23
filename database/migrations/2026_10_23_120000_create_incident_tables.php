<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_options', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->index();
            $table->string('name', 150);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['type', 'name']);
        });

        $catalogs = [
            'area' => DB::table('users')->whereNotNull('area')->where('area', '!=', '')->distinct()->pluck('area')->all(),
            'zone' => ['Piso 1', 'Piso 2', 'Parqueadero'],
            'failure' => ['Falla eléctrica', 'Daño en infraestructura', 'Aire acondicionado', 'Otro'],
            'impact' => ['Sí, afecta la operación', 'Parcialmente', 'No afecta la operación'],
            'priority' => ['Baja', 'Media', 'Alta'],
        ];
        foreach ($catalogs as $type => $names) {
            foreach ($names as $order => $name) {
                DB::table('incident_options')->insert([
                    'type' => $type, 'name' => $name, 'sort_order' => $order,
                    'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('reporter_name', 150);
            $table->string('reporter_position', 150);
            foreach (['area', 'zone', 'failure', 'impact', 'priority'] as $field) {
                $table->foreignId($field.'_id')->nullable($field === 'zone')
                    ->constrained('incident_options')->restrictOnDelete();
            }
            $table->string('location', 500);
            $table->text('description');
            $table->string('affected_assets', 500)->nullable();
            $table->string('status', 30)->default('reportado')->index();
            $table->boolean('is_active')->default(true);
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();
            $table->index(['created_by', 'created_at']);
        });

        Schema::create('incident_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_attachments');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('incident_options');
    }
};
