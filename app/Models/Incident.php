<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends BaseModel
{
    public const STATUSES = ['reportado' => 'Reportado', 'en_atencion' => 'En atención', 'resuelto' => 'Resuelto'];

    protected $fillable = [
        'created_by', 'reporter_name', 'reporter_position', 'area_id', 'zone_id',
        'failure_id', 'impact_id', 'priority_id', 'location', 'description',
        'affected_assets', 'status', 'is_active', 'archived_at',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'archived_at' => 'datetime'];
    }

    public function scopeVisibleFor(Builder $query, User $user): Builder
    {
        if ($user->hasPermission('incidents.view_all')) {
            return $query;
        }

        return $user->hasPermission('incidents.view')
            ? $query->where('created_by', $user->id)
            : $query->whereRaw('1 = 0');
    }

    public function getTicketAttribute(): string
    {
        return 'INC-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function area(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'area_id'); }
    public function zone(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'zone_id'); }
    public function failure(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'failure_id'); }
    public function impact(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'impact_id'); }
    public function priority(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'priority_id'); }
    public function attachments(): HasMany { return $this->hasMany(IncidentAttachment::class); }
}
