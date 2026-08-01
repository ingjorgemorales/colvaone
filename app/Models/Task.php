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
        'title',
        'description',
        'area',
        'start_date',
        'end_date',
        'priority',
        'status',
        'progress',
        'observations',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
        return $this->status !== 'completada' && $this->end_date->isPast();
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
            'en_progreso' => 'En progreso',
            'completada' => 'Completada',
            'cancelada' => 'Cancelada',
            'vencida' => 'Vencida',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => '#f59e0b',
            'en_progreso' => '#6366f1',
            'completada' => '#059669',
            'cancelada' => '#94a3b8',
            'vencida' => '#ef4444',
            default => '#94a3b8',
        };
    }
}
