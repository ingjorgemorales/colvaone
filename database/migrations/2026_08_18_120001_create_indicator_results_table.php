<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indicator_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indicator_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('field_one', 14, 2);
            $table->decimal('field_two', 14, 2);
            $table->decimal('compliance', 6, 2);
            $table->string('evaluation', 20);
            $table->text('description')->nullable();
            $table->string('action_number', 60)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['indicator_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicator_results');
    }
};
