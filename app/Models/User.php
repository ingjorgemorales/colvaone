<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

    #[Fillable([
        'name',
        'last_name',
        'document_type',
        'document_number',
        'email',
        'email_verified_at',
        'phone',
        'position',
        'area',
        'department',
        'role',
        'photo_path',
        'password',
        'is_active',
        'must_change_password',
        'last_login_at',
        'password_changed_at',
        'created_by',
        'updated_by',
    ])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Administrador',
            'admin' => 'Administrador',
            'gerente' => 'Gerente',
            'jefe' => 'Jefe',
            'operador' => 'Operador',
            'auditor' => 'Auditor',
            'user' => 'Usuario',
            'viewer' => 'Visualizador',
            default => 'Sin rol',
        };
    }

    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->name, 0, 1);
        $last = mb_substr($this->last_name ?? '', 0, 1);
        return strtoupper($first . $last);
    }

    public function roleObject(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'slug');
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'superadmin') {
            return true;
        }

        $role = Role::where('slug', $this->role)->first();

        if (!$role || !$role->is_active) {
            return false;
        }

        $permissions = $role->permissions ?? [];

        return in_array($permission, $permissions);
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignments')
            ->withPivot(['progress', 'status'])
            ->withTimestamps();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->withPivot(['member_type', 'is_active', 'assigned_by', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function managedGroups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_user')
            ->wherePivot('member_type', 'manager')
            ->wherePivot('is_active', true)
            ->withPivot(['assigned_by', 'joined_at'])
            ->withTimestamps();
    }

    public function isManagerOf(Group $group): bool
    {
        return $group->isManager($this);
    }

    public function isMemberOf(Group $group): bool
    {
        return $group->isMember($this);
    }
}
