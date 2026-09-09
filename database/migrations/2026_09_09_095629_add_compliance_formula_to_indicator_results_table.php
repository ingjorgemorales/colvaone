<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('indicator_results', function (Blueprint $table) {
            $table->string('compliance_formula', 30)->default('ascendente')->after('period_goal');
        });

        $directions = \Illuminate\Support\Facades\DB::table('indicators')->pluck('goal_direction', 'id');
        foreach (\Illuminate\Support\Facades\DB::table('indicator_results')->get() as $result) {
            $direction = $directions[$result->indicator_id] ?? 'ascendente';
            \Illuminate\Support\Facades\DB::table('indicator_results')
                ->where('id', $result->id)
                ->update(['compliance_formula' => $direction ?: 'ascendente']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indicator_results', function (Blueprint $table) {
            $table->dropColumn('compliance_formula');
        });
    }
};
