<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (!Schema::hasColumn('incidents', 'responsible_user_id')) {
                $table->foreignId('responsible_user_id')->nullable()->after('failure_id')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            if (Schema::hasColumn('incidents', 'responsible_user_id')) {
                $table->dropForeign(['responsible_user_id']);
                $table->dropColumn('responsible_user_id');
            }
        });
    }
};
