<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('last_name')->nullable()->after('name');
            $table->string('document_type', 30)->nullable()->after('last_name');
            $table->string('document_number', 50)->nullable()->unique()->after('document_type');
            $table->string('phone', 40)->nullable()->after('email');
            $table->string('position')->nullable()->after('phone');
            $table->string('area')->nullable()->after('position');
            $table->string('department')->nullable()->after('area');
            $table->string('photo_path')->nullable()->after('department');
            $table->boolean('is_active')->default(true)->index()->after('password');
            $table->boolean('must_change_password')->default(true)->index()->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('last_login_at');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropSoftDeletes();
            $table->dropUnique(['document_number']);
            $table->dropColumn([
                'last_name',
                'document_type',
                'document_number',
                'phone',
                'position',
                'area',
                'department',
                'photo_path',
                'is_active',
                'must_change_password',
                'last_login_at',
            ]);
        });
    }
};
