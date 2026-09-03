<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorResult extends BaseModel
{
    use HasFactory;

    public const UNSATISFACTORY = 'insatisfactorio';
    public const ACCEPTABLE = 'aceptable';
    public const SATISFACTORY = 'satisfactorio';

    protected $fillable = [
        'indicator_id',
        'period_start',
        'period_end',
        'field_one',
        'field_two',
        'compliance',
        'evaluation',
        'description',
        'action_number',
        'status',
        'created_by',
        'updated_by',
    ];

    /** Que el objeto recien creado tenga el mismo estado que la fila en BD. */
    protected $attributes = [
        'status' => 'active',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'field_one' => 'decimal:2',
        'field_two' => 'decimal:2',
        'compliance' => 'decimal:2',
    ];

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Cumplimiento = Campo 2 / Campo 1 * 100. Sin division por cero.
     */
    public static function calculateCompliance(float $fieldOne, float $fieldTwo): float
    {
        if ($fieldOne == 0.0) {
            return 0.0;
        }

        return round(($fieldTwo / $fieldOne) * 100, 2);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'active' ? 'Activo' : 'Inactivo';
    }

    public function getEvaluationLabelAttribute(): string
    {
        return match ($this->evaluation) {
            self::SATISFACTORY => 'Satisfactorio',
            self::ACCEPTABLE => 'Aceptable',
            self::UNSATISFACTORY => 'Insatisfactorio',
            default => $this->evaluation,
        };
    }

    public function getEvaluationColorAttribute(): string
    {
        return match ($this->evaluation) {
            self::SATISFACTORY => '#059669',
            self::ACCEPTABLE => '#f59e0b',
            self::UNSATISFACTORY => '#ef4444',
            default => '#94a3b8',
        };
    }

    public function getEvaluationBackgroundAttribute(): string
    {
        return match ($this->evaluation) {
            self::SATISFACTORY => 'rgba(5,150,105,0.10)',
            self::ACCEPTABLE => 'rgba(245,158,11,0.12)',
            self::UNSATISFACTORY => 'rgba(239,68,68,0.10)',
            default => 'rgba(148,163,184,0.12)',
        };
    }
}
