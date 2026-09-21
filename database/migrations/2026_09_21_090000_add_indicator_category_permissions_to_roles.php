<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const CATEGORY_PERMISSIONS = [
        'indicators.category_i',
        'indicators.category_ii',
        'indicators.category_iii',
        'indicators.category_iv',
    ];

    public function up(): void
    {
        DB::table('roles')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get()
            ->each(function ($role): void {
                $permissions = json_decode($role->permissions, true) ?: [];

                if (! $this->hasIndicatorAccess($permissions)) {
                    return;
                }

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode(array_values(array_unique([
                            ...$permissions,
                            ...self::CATEGORY_PERMISSIONS,
                        ]))),
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        DB::table('roles')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get()
            ->each(function ($role): void {
                $permissions = json_decode($role->permissions, true) ?: [];

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode(array_values(array_diff($permissions, self::CATEGORY_PERMISSIONS))),
                        'updated_at' => now(),
                    ]);
            });
    }

    private function hasIndicatorAccess(array $permissions): bool
    {
        return count(array_intersect($permissions, ['indicators.view', 'indicators.view_all'])) > 0;
    }
};
