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
        $permissions = array_values(array_unique([
            ...$permissions,
            'ai_chat.view',
            'ai_chat.clear_own',
            'ai_chat.configure',
        ]));

        DB::table('roles')
            ->where('id', $role->id)
            ->update([
                'permissions' => json_encode($permissions),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $permissionsToRemove = ['ai_chat.view', 'ai_chat.clear_own', 'ai_chat.configure'];

        DB::table('roles')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get()
            ->each(function ($role) use ($permissionsToRemove): void {
                $permissions = json_decode($role->permissions, true) ?: [];
                $permissions = array_values(array_diff($permissions, $permissionsToRemove));

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update([
                        'permissions' => json_encode($permissions),
                        'updated_at' => now(),
                    ]);
            });
    }
};
