<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los campos descriptivos dejan de tener tope de longitud en el
     * formulario. TEXT solo aguanta 65.535 bytes y, con STRICT_TRANS_TABLES
     * activo, pasarse lanzaria un error en vez de truncar. LONGTEXT los
     * deja practicamente ilimitados, igual que los relatos de comites.
     */
    public function up(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->longText('objective')->change();
            $table->longText('methodological_aspects')->nullable()->change();
        });

        Schema::table('indicator_results', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->text('objective')->change();
            $table->text('methodological_aspects')->nullable()->change();
        });

        Schema::table('indicator_results', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }
};
