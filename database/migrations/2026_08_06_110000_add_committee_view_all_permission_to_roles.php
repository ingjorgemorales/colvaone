<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $role = DB::table('roles')->where('slug', 'admin')->first();

        if (!$role || $role->permissions === null) {
            return;
        }

        $permissions = json_decode($role->permissions, true) ?: [];
        $permissions = array_values(array_unique([...$permissions, 'committees.view_all']));

        DB::table('roles')
            ->where('id', $role->id)
            ->update([
                'permissions' => json_encode($permissions),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('roles')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get()
            ->each(function ($role): void {
                $permissions = json_decode($role->permissions, true) ?: [];
                $permissions = array_values(array_diff($permissions, ['committees.view_all']));

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode($permissions),
                        'updated_at' => now(),
                    ]);
            });
    }
};
