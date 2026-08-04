<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('created_by')->constrained()->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->after('group_id')->constrained('users')->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->after('assigned_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->dropForeign(['assigned_by']);
            $table->dropColumn('assigned_by');
            $table->dropForeign(['responsible_user_id']);
            $table->dropColumn('responsible_user_id');
        });
    }
};
