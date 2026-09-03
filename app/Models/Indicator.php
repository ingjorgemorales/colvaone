<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indicator extends BaseModel
{
    use HasFactory, SoftDeletes;

    /** Limite superior de meta y umbrales. */
    public const SCALE_MAX = 100;

    public const MEASUREMENT_UNITS = [
        'porcentaje' => 'Porcentaje (%)',
        'cantidad' => 'Cantidad',
        'dias' => 'Dias',
        'horas' => 'Horas',
        'pesos' => 'Pesos (COP)',
    ];

    public const FREQUENCIES = [
        'diaria' => 'Diaria',
        'semanal' => 'Semanal',
        'quincenal' => 'Quincenal',
        'mensual' => 'Mensual',
        'bimestral' => 'Bimestral',
        'trimestral' => 'Trimestral',
        'semestral' => 'Semestral',
        'anual' => 'Anual',
    ];

    /** Clasificacion que alimenta los sub-botones del menu. */
    public const CATEGORIES = [
        'I' => 'Indicador I',
        'II' => 'Indicador II',
        'III' => 'Indicador III',
    ];

    public const TYPES = [
        'eficacia' => 'Eficacia',
        'eficiencia' => 'Eficiencia',
        'efectividad' => 'Efectividad',
        'cumplimiento' => 'Cumplimiento',
        'calidad' => 'Calidad',
    ];

    protected $fillable = [
        'name',
        'category',
        'objective',
        'responsible_user_id',
        'formula',
        'measurement_unit',
        'frequency',
        'type',
        'methodological_aspects',
        'goal',
        'threshold_acceptable',
        'threshold_satisfactory',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'goal' => 'integer',
        'threshold_acceptable' => 'integer',
        'threshold_satisfactory' => 'integer',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Sin clasificar';
    }

    public function scopeInCategory($query, ?string $category)
    {
        if (! $category || ! array_key_exists($category, self::CATEGORIES)) {
            return $query;
        }

        return $query->where('indicators.category', $category);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(IndicatorResult::class)
            ->orderByDesc('period_start')
            ->orderByDesc('id');
    }

    /** Un resultado inactivo no representa el cumplimiento vigente. */
    public function latestResult(): HasOne
    {
        return $this->hasOne(IndicatorResult::class)
            ->where('status', 'active')
            ->latestOfMany('period_start');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Activo' : 'Inactivo';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status === 'active' ? '#059669' : '#94a3b8';
    }

    public function getMeasurementUnitLabelAttribute(): string
    {
        return self::MEASUREMENT_UNITS[$this->measurement_unit] ?? $this->measurement_unit;
    }

    public function getFrequencyLabelAttribute(): string
    {
        return self::FREQUENCIES[$this->frequency] ?? $this->frequency;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Limita la consulta a lo que el usuario puede ver: todos, o solo los
     * propios (creados por el o donde figura como responsable).
     */
    public function scopeVisibleFor($query, User $user)
    {
        if ($user->hasPermission('indicators.view_all')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('indicators.created_by', $user->id)
                ->orWhere('indicators.responsible_user_id', $user->id);
        });
    }

    /**
     * Un usuario ve un indicador si tiene permiso global o si es suyo.
     */
    public function isVisibleFor(User $user): bool
    {
        return $user->hasPermission('indicators.view_all')
            || (int) $this->created_by === (int) $user->id
            || (int) $this->responsible_user_id === (int) $user->id;
    }

    /**
     * Clasifica un porcentaje de cumplimiento contra los umbrales del indicador.
     */
    public function evaluate(float $compliance): string
    {
        if ($compliance >= $this->threshold_satisfactory) {
            return IndicatorResult::SATISFACTORY;
        }

        if ($compliance >= $this->threshold_acceptable) {
            return IndicatorResult::ACCEPTABLE;
        }

        return IndicatorResult::UNSATISFACTORY;
    }
}
