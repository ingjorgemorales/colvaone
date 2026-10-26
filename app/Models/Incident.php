<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Incident extends BaseModel
{
    public const STATUSES = ['reportado' => 'Reportado', 'en_atencion' => 'En atención', 'resuelto' => 'Resuelto'];
    public const FILTER_STATES = self::STATUSES + ['activo' => 'Activo', 'inactivo' => 'Inactivo', 'archivado' => 'Archivado'];

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

    public function getPriorityToneAttribute(): string
    {
        return match (Str::lower(trim($this->priority?->name ?? ''))) {
            'alta' => 'tone-danger', 'media' => 'tone-warning', 'baja' => 'tone-success', default => '',
        };
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = trim($search);
        if ($search === '') { return $query; }
        $pattern = '%'.$search.'%';
        $driver = $query->getConnection()->getDriverName();
        $ticket = match ($driver) {
            'sqlite' => "printf('INC-%06d', incidents.id)",
            'mysql', 'mariadb' => "CONCAT('INC-', LPAD(incidents.id, GREATEST(6, LENGTH(incidents.id)), '0'))",
            'pgsql' => "'INC-' || LPAD(incidents.id::text, GREATEST(6, LENGTH(incidents.id::text)), '0')",
            'sqlsrv' => "CONCAT('INC-', FORMAT(incidents.id, '000000'))",
            default => 'CAST(incidents.id AS CHAR)',
        };

        // Keep every search branch inside the visibility scope, including related records.
        return $query->where(function (Builder $matches) use ($search, $pattern, $driver, $ticket) {
            foreach (['reporter_name', 'reporter_position', 'description', 'location', 'affected_assets'] as $field) {
                $matches->orWhereLike('incidents.'.$field, $pattern);
            }
            $matches->orWhereRaw($ticket.' LIKE ?', [$pattern]);
            foreach (['area', 'zone', 'failure', 'impact', 'priority'] as $relation) {
                $matches->orWhereHas($relation, fn (Builder $related) => $related->whereLike('name', $pattern));
            }
            $matches->orWhereHas('attachments', fn (Builder $files) => $files->whereLike('original_name', $pattern));
            foreach (['created_at', 'updated_at', 'archived_at'] as $field) {
                $column = 'incidents.'.$field;
                [$formatted, $stored] = match ($driver) {
                    'sqlite' => ["strftime('%d/%m/%Y %H:%M', $column)", "CAST($column AS TEXT)"],
                    'mysql', 'mariadb' => ["DATE_FORMAT($column, '%d/%m/%Y %H:%i')", "CAST($column AS CHAR)"],
                    'pgsql' => ["TO_CHAR($column, 'DD/MM/YYYY HH24:MI')", "CAST($column AS TEXT)"],
                    'sqlsrv' => ["CONVERT(VARCHAR(10), $column, 103) + ' ' + CONVERT(VARCHAR(8), $column, 108)", "CONVERT(VARCHAR(19), $column, 120)"],
                    default => ["CAST($column AS CHAR)", "CAST($column AS CHAR)"],
                };
                $matches->orWhereRaw($formatted.' LIKE ?', ['%'.str_replace('-', '/', $search).'%'])
                    ->orWhereRaw($stored.' LIKE ?', [$pattern]);
            }
            foreach (self::STATUSES as $value => $label) {
                if (Str::contains(Str::lower($label), Str::lower($search))) { $matches->orWhere('status', $value); }
            }
            foreach ([1 => 'Activo', 0 => 'Inactivo'] as $active => $label) {
                if (Str::contains(Str::lower($label), Str::lower($search))) { $matches->orWhere('is_active', $active); }
            }
            if (Str::contains('archivado', Str::lower($search))) { $matches->orWhereNotNull('archived_at'); }
        });
    }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function area(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'area_id'); }
    public function zone(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'zone_id'); }
    public function failure(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'failure_id'); }
    public function impact(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'impact_id'); }
    public function priority(): BelongsTo { return $this->belongsTo(IncidentOption::class, 'priority_id'); }
    public function attachments(): HasMany { return $this->hasMany(IncidentAttachment::class); }
}
