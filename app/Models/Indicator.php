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
        'millones' => 'Millones de pesos (COP)',
    ];

    /** Unidades que son plata: se suman y se muestran sin decimales, como en el tablero de la junta. */
    public const MONEY_UNITS = ['pesos', 'millones'];

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

    public const GOAL_ASCENDING = 'ascendente';
    public const GOAL_DESCENDING = 'descendente';

    /** Define cual de las dos formulas de cumplimiento se aplica. */
    public const GOAL_DIRECTIONS = [
        self::GOAL_ASCENDING => 'Ascendente: mientras mas alto, mejor',
        self::GOAL_DESCENDING => 'Descendente: mientras mas bajo, mejor (llegar a cero)',
    ];

    /** Clasificacion que alimenta los sub-botones del menu. */
    public const CATEGORIES = [
        'I' => 'Indicador SGC I',
        'II' => 'Indicador BSC II',
        'III' => 'Indicador CX III',
        'IV' => 'Indicador Bono IV',
    ];

    public const CATEGORY_PERMISSIONS = [
        'I' => 'indicators.category_i',
        'II' => 'indicators.category_ii',
        'III' => 'indicators.category_iii',
        'IV' => 'indicators.category_iv',
    ];

    public const ACCUMULATION_SUM = 'sum';
    public const ACCUMULATION_AVERAGE = 'average';
    public const ACCUMULATION_LAST = 'last';

    /**
     * Como se resume el rango en el dashboard BSC. Sin valor es automatico:
     * lo que es plata se suma y los porcentajes o cantidades se promedian.
     */
    public const ACCUMULATION_MODES = [
        self::ACCUMULATION_SUM => 'Suma de los periodos',
        self::ACCUMULATION_AVERAGE => 'Promedio de los periodos',
        self::ACCUMULATION_LAST => 'Ultimo periodo registrado',
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
        'bsc_perspective_id',
        'accumulation_mode',
        'annual_goal',
        'process_id',
        'subprocess_id',
        'objective',
        'responsible_user_id',
        'formula',
        'measurement_unit',
        'frequency',
        'type',
        'methodological_aspects',
        'quality_objective_id',
        'goal',
        'goal_direction',
        'threshold_acceptable',
        'threshold_satisfactory',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'goal' => 'integer',
        'annual_goal' => 'decimal:2',
        'threshold_acceptable' => 'integer',
        'threshold_satisfactory' => 'integer',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(Process::class);
    }

    public function subprocess(): BelongsTo
    {
        return $this->belongsTo(Subprocess::class);
    }

    public function bscPerspective(): BelongsTo
    {
        return $this->belongsTo(BscPerspective::class);
    }

    public function qualityObjective(): BelongsTo
    {
        return $this->belongsTo(QualityObjective::class);
    }

    public function getProcessLabelAttribute(): string
    {
        return $this->process?->name ?? 'Sin asignar';
    }

    public function getSubprocessLabelAttribute(): string
    {
        return $this->subprocess?->full_name ?? 'Sin asignar';
    }

    public function getBscPerspectiveLabelAttribute(): string
    {
        return $this->bscPerspective?->name ?? 'Sin asignar';
    }

    /** El modo que realmente aplica, resolviendo el automatico por la unidad. */
    public function accumulationMode(): string
    {
        if (array_key_exists((string) $this->accumulation_mode, self::ACCUMULATION_MODES)) {
            return $this->accumulation_mode;
        }

        return $this->isMoney() ? self::ACCUMULATION_SUM : self::ACCUMULATION_AVERAGE;
    }

    public function isMoney(): bool
    {
        return in_array($this->measurement_unit, self::MONEY_UNITS, true);
    }

    /**
     * Como se escriben las cifras del indicador. La plata va sin decimales, igual que
     * el Excel de la junta ($ #,##0), y dice "millones" cuando se registro en millones:
     * sin eso, 57.061 millones se lee como 57 mil pesos.
     */
    public function displayUnit(): array
    {
        return match ($this->measurement_unit) {
            'millones' => ['money' => true, 'prefix' => '$ ', 'suffix' => ' millones', 'decimals' => 0, 'caption' => 'Cifras en millones de pesos'],
            'pesos' => ['money' => true, 'prefix' => '$ ', 'suffix' => '', 'decimals' => 0, 'caption' => 'Cifras en pesos'],
            default => ['money' => false, 'prefix' => '', 'suffix' => '', 'decimals' => 2, 'caption' => null],
        };
    }

    /** Una cifra en la unidad del indicador. Compacta omite "millones", para tablas donde ya se dice arriba. */
    public function formatAmount(float $value, bool $compact = false): string
    {
        $unit = $this->displayUnit();

        $formatted = number_format(round($value, $unit['decimals']), $unit['decimals'], ',', '.');

        if ($unit['decimals'] > 0) {
            $formatted = rtrim(rtrim($formatted, '0'), ',');
        }

        // Redondear -0,4 da "-0"; eso es cero.
        $formatted = $formatted === '-0' ? '0' : $formatted;

        return $unit['prefix'] . $formatted . ($compact ? '' : $unit['suffix']);
    }

    public function getAnnualGoalLabelAttribute(): string
    {
        return $this->annual_goal === null ? 'Sin registrar' : $this->formatAmount((float) $this->annual_goal);
    }

    public function getAccumulationModeLabelAttribute(): string
    {
        $label = self::ACCUMULATION_MODES[$this->accumulationMode()];

        return array_key_exists((string) $this->accumulation_mode, self::ACCUMULATION_MODES)
            ? $label
            : $label . ' (automatico)';
    }

    public function getQualityObjectiveLabelAttribute(): string
    {
        return $this->qualityObjective?->name ?? 'Sin asignar';
    }

    public function getGoalDirectionLabelAttribute(): string
    {
        return self::GOAL_DIRECTIONS[$this->goal_direction] ?? self::GOAL_DIRECTIONS[self::GOAL_ASCENDING];
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Sin clasificar';
    }

    public static function categoryPermission(?string $category): ?string
    {
        return self::CATEGORY_PERMISSIONS[$category] ?? null;
    }

    public static function categoryPermissions(): array
    {
        return array_values(self::CATEGORY_PERMISSIONS);
    }

    public static function allowedCategoriesFor(User $user): array
    {
        return collect(self::CATEGORIES)
            ->filter(fn (string $label, string $category) => $user->hasPermission(self::categoryPermission($category)))
            ->all();
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
        $allowedCategories = array_keys(self::allowedCategoriesFor($user));

        if ($allowedCategories === []) {
            return $query->whereRaw('1 = 0');
        }

        $query->whereIn('indicators.category', $allowedCategories);

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
        $categoryPermission = self::categoryPermission($this->category);

        if (! $categoryPermission || ! $user->hasPermission($categoryPermission)) {
            return false;
        }

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
