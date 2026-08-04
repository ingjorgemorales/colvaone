<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->withPivot(['member_type', 'is_active', 'assigned_by', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function activeMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->wherePivot('is_active', true)
            ->withPivot(['member_type', 'assigned_by', 'joined_at'])
            ->withTimestamps();
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->wherePivot('member_type', 'manager')
            ->wherePivot('is_active', true)
            ->withPivot(['assigned_by', 'joined_at'])
            ->withTimestamps();
    }

    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->wherePivot('member_type', 'supervisor')
            ->wherePivot('is_active', true)
            ->withPivot(['assigned_by', 'joined_at'])
            ->withTimestamps();
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
            ->wherePivot('member_type', 'member')
            ->wherePivot('is_active', true)
            ->withPivot(['assigned_by', 'joined_at'])
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getMemberCountAttribute(): int
    {
        return $this->activeMembers()->count();
    }

    public function getTaskCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function getPendingTaskCountAttribute(): int
    {
        return $this->tasks()->whereIn('status', ['pendiente', 'asignada'])->count();
    }

    public function getInProgressTaskCountAttribute(): int
    {
        return $this->tasks()->where('status', 'en_progreso')->count();
    }

    public function getCompletedTaskCountAttribute(): int
    {
        return $this->tasks()->where('status', 'finalizada')->count();
    }

    public function scopeVisibleFor($query, User $user)
    {
        if ($user->hasPermission('groups.view_all')) {
            return $query;
        }

        return $query->whereHas('users', fn ($q) => $q->where('users.id', $user->id)->where('group_user.is_active', true));
    }

    public function isManager(User $user): bool
    {
        return $this->users()
            ->where('users.id', $user->id)
            ->wherePivot('member_type', 'manager')
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function isMember(User $user): bool
    {
        return $this->users()
            ->where('users.id', $user->id)
            ->wherePivot('is_active', true)
            ->exists();
    }

    public function hasMember(User $user): bool
    {
        return $this->isMember($user);
    }
}
