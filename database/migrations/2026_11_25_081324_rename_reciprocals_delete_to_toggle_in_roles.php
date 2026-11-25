<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $roles = DB::table('roles')->get();

        foreach ($roles as $role) {
            $permissions = json_decode($role->permissions, true);

            if (!is_array($permissions)) {
                continue;
            }

            $changed = false;

            // Replace reciprocals.delete with reciprocals.toggle
            if (in_array('reciprocals.delete', $permissions, true)) {
                $permissions = array_values(array_map(
                    fn($p) => $p === 'reciprocals.delete' ? 'reciprocals.toggle' : $p,
                    $permissions
                ));
                $changed = true;
            }

            if ($changed) {
                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['permissions' => json_encode($permissions)]);
            }
        }
    }

    public function down(): void
    {
        $roles = DB::table('roles')->get();

        foreach ($roles as $role) {
            $permissions = json_decode($role->permissions, true);

            if (!is_array($permissions)) {
                continue;
            }

            $changed = false;

            // Revert reciprocals.toggle back to reciprocals.delete
            if (in_array('reciprocals.toggle', $permissions, true)) {
                $permissions = array_values(array_map(
                    fn($p) => $p === 'reciprocals.toggle' ? 'reciprocals.delete' : $p,
                    $permissions
                ));
                $changed = true;
            }

            if ($changed) {
                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['permissions' => json_encode($permissions)]);
            }
        }
    }
};
