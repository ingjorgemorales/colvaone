<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('committee_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained()->cascadeOnDelete();
            $table->longText('content');
            $table->dateTime('registered_at');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        DB::table('committees')
            ->whereNotNull('summary')
            ->where('summary', '<>', '')
            ->orderBy('id')
            ->get()
            ->each(function ($committee): void {
                DB::table('committee_reports')->insert([
                    'committee_id' => $committee->id,
                    'content' => $committee->summary,
                    'registered_at' => $committee->created_at ?? now(),
                    'created_by' => $committee->created_by,
                    'created_at' => $committee->created_at ?? now(),
                    'updated_at' => $committee->updated_at ?? now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_reports');
    }
};
