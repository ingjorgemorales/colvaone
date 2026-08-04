<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by',
        'group_id',
        'assigned_by',
        'responsible_user_id',
        'title',
        'description',
        'area',
        'start_date',
        'end_date',
        'priority',
        'status',
        'progress',
        'observations',
        'block_reason',
        'days_delayed',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_assignments')
            ->withPivot(['progress', 'status'])
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    public function isDelayed(): bool
    {
        return !in_array($this->status, ['finalizada', 'cancelada', 'archivada']) && $this->end_date->isPast();
    }

    public function getDaysDelayedAttribute(): int
    {
        return (int) $this->attributes['days_delayed'];
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'baja' => '#94a3b8',
            'media' => '#f59e0b',
            'alta' => '#f97316',
            'urgente' => '#ef4444',
            default => '#94a3b8',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'asignada' => 'Asignada',
            'en_progreso' => 'En progreso',
            'bloqueada' => 'Bloqueada',
            'en_revision' => 'En revision',
            'finalizada' => 'Finalizada',
            'cancelada' => 'Cancelada',
            'archivada' => 'Archivada',
            'completada' => 'Completada',
            'vencida' => 'Vencida',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => '#f59e0b',
            'asignada' => '#8b5cf6',
            'en_progreso' => '#6366f1',
            'bloqueada' => '#ef4444',
            'en_revision' => '#f97316',
            'finalizada' => '#059669',
            'completada' => '#059669',
            'cancelada' => '#94a3b8',
            'archivada' => '#64748b',
            'vencida' => '#ef4444',
            default => '#94a3b8',
        };
    }

    public function scopeVisibleFor($query, User $user)
    {
        if ($user->hasPermission('group_tasks.view_all')) {
            return $query;
        }

        if ($user->hasPermission('group_tasks.view')) {
            $groupIds = $user->groups()->wherePivot('is_active', true)->pluck('groups.id');
            return $query->whereIn('group_id', $groupIds)->orWhere('created_by', $user->id);
        }

        return $query->where('responsible_user_id', $user->id)
            ->orWhereHas('assignees', fn ($q) => $q->where('users.id', $user->id));
    }
}
