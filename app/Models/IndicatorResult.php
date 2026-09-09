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

    public const FORMULA_ASCENDING = 'ascendente';
    public const FORMULA_DESCENDING = 'descendente';

    public const COMPLIANCE_FORMULAS = [
        self::FORMULA_ASCENDING => 'Ascendente: mientras mas alto, mejor',
        self::FORMULA_DESCENDING => 'Descendente: mientras mas bajo, mejor (llegar a cero)',
    ];

    protected $fillable = [
        'indicator_id',
        'period_start',
        'period_end',
        'numerator',
        'denominator',
        'result',
        'period_goal',
        'compliance_formula',
        'compliance',
        'evaluation',
        'analysis',
        'action_number',
        'status',
        'created_by',
        'updated_by',
    ];

    /** Que el objeto recien creado tenga el mismo estado que la fila en BD. */
    protected $attributes = [
        'status' => 'active',
        'compliance_formula' => self::FORMULA_ASCENDING,
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'numerator' => 'decimal:2',
        'denominator' => 'decimal:2',
        'result' => 'decimal:4',
        'period_goal' => 'decimal:4',
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

    /** RESULTADO = NUMERADOR / DENOMINADOR. Sin division por cero. */
    public static function calculateResult(float $numerator, float $denominator): float
    {
        if ($denominator == 0.0) {
            return 0.0;
        }

        return round($numerator / $denominator, 4);
    }

    /**
     * CUMPLIMIENTO segun la formula seleccionada:
     *   ascendente  -> (RESULTADO / META) * 100   la meta busca subir el valor
     *   descendente -> (META / RESULTADO) * 100   la meta busca llegar a cero
     */
    public static function calculateCompliance(float $result, ?float $goal, ?string $direction = null): float
    {
        if ($goal === null || $goal == 0.0) {
            return 0.0;
        }

        if ($direction === self::FORMULA_DESCENDING || $direction === Indicator::GOAL_DESCENDING) {
            return $result == 0.0 ? 0.0 : round(($goal / $result) * 100, 2);
        }

        return round(($result / $goal) * 100, 2);
    }

    public function getComplianceFormulaLabelAttribute(): string
    {
        return self::COMPLIANCE_FORMULAS[$this->compliance_formula] ?? self::COMPLIANCE_FORMULAS[self::FORMULA_ASCENDING];
    }

    public function getComplianceFormulaMathAttribute(): string
    {
        return ($this->compliance_formula === self::FORMULA_DESCENDING || $this->compliance_formula === Indicator::GOAL_DESCENDING)
            ? '(Meta / Resultado) x 100'
            : '(Resultado / Meta) x 100';
    }

    /** Numeros con separador de miles y sin ceros sobrantes al final. */
    private function tidy(?float $value, int $decimals = 2): string
    {
        if ($value === null) {
            return '-';
        }

        $formatted = number_format($value, $decimals, ',', '.');

        return str_contains($formatted, ',')
            ? rtrim(rtrim($formatted, '0'), ',')
            : $formatted;
    }

    public function getFormattedNumeratorAttribute(): string
    {
        return $this->tidy((float) $this->numerator);
    }

    public function getFormattedDenominatorAttribute(): string
    {
        return $this->tidy((float) $this->denominator);
    }

    public function getFormattedResultAttribute(): string
    {
        return $this->tidy((float) $this->result, 4);
    }

    public function getFormattedPeriodGoalAttribute(): string
    {
        return $this->tidy($this->period_goal === null ? null : (float) $this->period_goal, 4);
    }

    public function getFormattedComplianceAttribute(): string
    {
        return $this->tidy((float) $this->compliance);
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
