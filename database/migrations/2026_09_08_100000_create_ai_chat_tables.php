<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 40)->default('openai');
            $table->string('endpoint')->default('https://api.openai.com/v1/responses');
            $table->string('model', 120)->default('gpt-5');
            $table->text('api_key')->nullable();
            $table->longText('system_prompt')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedSmallInteger('max_context_messages')->default(12);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ai_chat_conversations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('ai_chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_chat_conversations')->cascadeOnDelete();
            $table->string('role', 20);
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_conversations');
        Schema::dropIfExists('ai_chat_settings');
    }
};
