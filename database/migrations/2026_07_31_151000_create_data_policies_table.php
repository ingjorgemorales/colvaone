<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_policies', function (Blueprint $table): void {
            $table->id();
            $table->string('version', 30)->unique();
            $table->date('published_at');
            $table->boolean('is_active')->default(true)->index();
            $table->longText('content');
            $table->timestamps();
        });

        Schema::create('data_policy_acceptances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('data_policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accepted_at');
            $table->timestamps();
            $table->unique(['data_policy_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_policy_acceptances');
        Schema::dropIfExists('data_policies');
    }
};
