<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_chat_settings') && Schema::hasColumn('ai_chat_settings', 'temperature')) {
            Schema::table('ai_chat_settings', function (Blueprint $table): void {
                $table->dropColumn('temperature');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('ai_chat_settings') && !Schema::hasColumn('ai_chat_settings', 'temperature')) {
            Schema::table('ai_chat_settings', function (Blueprint $table): void {
                $table->decimal('temperature', 3, 2)->default(0.30)->after('is_active');
            });
        }
    }
};
