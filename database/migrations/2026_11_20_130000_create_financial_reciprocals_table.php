<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reciprocals', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_contable_subcuenta', 50)->index();
            $table->string('nombre_subcuenta', 255);
            $table->string('codigo_entidad_reciproca', 50)->index();
            $table->string('nombre_entidad_reciproca', 255);
            $table->decimal('valor_corriente', 18, 2)->default(0.00);
            $table->decimal('valor_no_corriente', 18, 2)->default(0.00);
            $table->date('fecha')->index();
            $table->text('observaciones')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reciprocals');
    }
};

