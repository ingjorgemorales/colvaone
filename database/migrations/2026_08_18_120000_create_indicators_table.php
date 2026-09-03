<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('objective');
            $table->foreignId('responsible_user_id')->constrained('users');
            $table->string('formula');
            $table->string('measurement_unit', 60);
            $table->string('frequency', 40);
            $table->string('type', 40);
            $table->text('methodological_aspects')->nullable();
            $table->unsignedTinyInteger('goal')->default(100);
            $table->unsignedTinyInteger('threshold_acceptable')->default(80);
            $table->unsignedTinyInteger('threshold_satisfactory')->default(95);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicators');
    }
};
