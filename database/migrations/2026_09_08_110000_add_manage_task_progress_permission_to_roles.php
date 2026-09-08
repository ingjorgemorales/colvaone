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

        if (!in_array('group_tasks.manage_progress', $permissions, true)) {
            $permissions[] = 'group_tasks.manage_progress';

            DB::table('roles')
                ->where('id', $role->id)
                ->update(['permissions' => json_encode(array_values($permissions))]);
        }
    }

    public function down(): void
    {
        DB::table('roles')
            ->whereNotNull('permissions')
            ->orderBy('id')
            ->get()
            ->each(function ($role): void {
                $permissions = array_values(array_filter(
                    json_decode($role->permissions, true) ?: [],
                    fn ($permission) => $permission !== 'group_tasks.manage_progress'
                ));

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['permissions' => json_encode($permissions)]);
            });
    }
};
