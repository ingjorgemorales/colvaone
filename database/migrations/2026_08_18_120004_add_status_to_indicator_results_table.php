<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los resultados no se eliminan: se desactivan. Un resultado inactivo
     * queda visible como historico pero deja de contar como el ultimo
     * cumplimiento del indicador.
     */
    public function up(): void
    {
        Schema::table('indicator_results', function (Blueprint $table) {
            $table->enum('status', ['active', 'inactive'])->default('active')->after('action_number');
        });
    }

    public function down(): void
    {
        Schema::table('indicator_results', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
