<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialReciprocal extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'financial_reciprocals';

    protected $fillable = [
        'codigo_contable_subcuenta',
        'nombre_subcuenta',
        'codigo_entidad_reciproca',
        'nombre_entidad_reciproca',
        'valor_corriente',
        'valor_no_corriente',
        'fecha',
        'observaciones',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'valor_corriente' => 'decimal:2',
            'valor_no_corriente' => 'decimal:2',
            'fecha' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function parseAmount(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $text = trim(preg_replace('/[\s\x{00A0}$]/u', '', (string) $value));
        if ($text === '' || $text === '-' || $text === '0') {
            return 0.0;
        }

        $hasDot = str_contains($text, '.');
        $hasComma = str_contains($text, ',');

        if ($hasDot && $hasComma) {
            // Whichever is last is the decimal separator
            if (strrpos($text, ',') > strrpos($text, '.')) {
                // Comma is decimal: 1.262,00 or 1.262,50
                $clean = str_replace('.', '', $text);
                $clean = str_replace(',', '.', $clean);
            } else {
                // Dot is decimal: 1,262.00 or 1,262.50
                $clean = str_replace(',', '', $text);
            }
            return (float) $clean;
        }

        if ($hasDot) {
            // Multiple dots is always thousands: 5.000.000, 24.423.688.578
            if (substr_count($text, '.') > 1) {
                return (float) str_replace('.', '', $text);
            }

            // Single dot: e.g. 1.262 or 12.345 or 1262.00 or 0.50
            // If matches groups of 3 digits e.g. 1.262, 10.000, 999.999
            if ($text[0] !== '0' && preg_match('/^\d{1,3}\.\d{3}$/', $text)) {
                // In Colombia/accounting: 1.262 is 1262!
                return (float) str_replace('.', '', $text);
            }

            // Otherwise standard decimal dot e.g. 1262.50 or 0.50
            return (float) $text;
        }

        if ($hasComma) {
            // Multiple commas: 5,000,000
            if (substr_count($text, ',') > 1) {
                return (float) str_replace(',', '', $text);
            }

            // Single comma: e.g. 1,262 or 10,000 from English/Excel or 1262,50
            if ($text[0] !== '0' && preg_match('/^\d{1,3},\d{3}$/', $text)) {
                return (float) str_replace(',', '', $text);
            }

            // Otherwise decimal comma: 1262,50 or 0,5
            return (float) str_replace(',', '.', $text);
        }

        return (float) $text;
    }

    public static function formatNumber(float|int|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $num = (float) $value;

        // If integer (no decimal fraction)
        if (fmod(abs($num), 1.0) == 0.0) {
            return number_format($num, 0, ',', '.');
        }

        // Format with up to 2 decimals, eliminating unnecessary trailing zeros
        $formatted = number_format($num, 2, ',', '.');
        return rtrim(rtrim($formatted, '0'), ',');
    }

    public static function getQuarterDates(int $trimestre, ?int $year = null): array
    {
        $y = $year ?: now()->year;
        return match ($trimestre) {
            1 => [
                'inicio' => "{$y}-01-01",
                'fin' => "{$y}-03-31",
                'corte' => "1 a 31 de Marzo {$y}",
                'label' => "1er Trimestre (1 Ene - 31 Mar)",
            ],
            2 => [
                'inicio' => "{$y}-04-01",
                'fin' => "{$y}-06-30",
                'corte' => "1 a 30 de Junio {$y}",
                'label' => "2do Trimestre (1 Abr - 30 Jun)",
            ],
            3 => [
                'inicio' => "{$y}-07-01",
                'fin' => "{$y}-09-30",
                'corte' => "1 a 30 de Septiembre {$y}",
                'label' => "3er Trimestre (1 Jul - 30 Sep)",
            ],
            4 => [
                'inicio' => "{$y}-10-01",
                'fin' => "{$y}-12-31",
                'corte' => "1 a 31 de Diciembre {$y}",
                'label' => "4to Trimestre (1 Oct - 31 Dic)",
            ],
            default => [
                'inicio' => "{$y}-01-01",
                'fin' => "{$y}-12-31",
                'corte' => "1 a 31 de Diciembre {$y}",
                'label' => "Anual {$y}",
            ],
        };
    }

    public static function detectQuarter(?string $fechaInicio, ?string $fechaFin): ?int
    {
        if (empty($fechaInicio) || empty($fechaFin)) {
            if (!empty($fechaFin)) {
                try {
                    $end = Carbon::parse($fechaFin);
                    if ($end->month === 3) return 1;
                    if ($end->month === 6) return 2;
                    if ($end->month === 9) return 3;
                    if ($end->month === 12) return 4;
                } catch (\Throwable $e) {}
            }
            return null;
        }

        try {
            $start = Carbon::parse($fechaInicio);
            $end = Carbon::parse($fechaFin);

            if ($start->month === 1 && $end->month === 3) return 1;
            if ($start->month === 4 && $end->month === 6) return 2;
            if ($start->month === 7 && $end->month === 9) return 3;
            if ($start->month === 10 && $end->month === 12) return 4;
        } catch (\Throwable $e) {}

        return null;
    }

    public static function resolveFechaCorte(?string $fechaInicio, ?string $fechaFin, ?string $fallback = null, ?int $trimestre = null, ?int $year = null): string
    {
        $y = $year;

        // If quarter number is directly provided
        if ($trimestre && in_array((int)$trimestre, [1, 2, 3, 4], true)) {
            $dates = self::getQuarterDates((int)$trimestre, $y);
            return $dates['corte'];
        }

        // If start and end dates are provided
        if (!empty($fechaInicio) && !empty($fechaFin)) {
            try {
                $start = Carbon::parse($fechaInicio);
                $end = Carbon::parse($fechaFin);
                $y = $y ?: $end->year;

                if ($start->month === 1 && $end->month === 3) {
                    return "1 a 31 de Marzo {$y}";
                }
                if ($start->month === 4 && $end->month === 6) {
                    return "1 a 30 de Junio {$y}";
                }
                if ($start->month === 7 && $end->month === 9) {
                    return "1 a 30 de Septiembre {$y}";
                }
                if ($start->month === 10 && $end->month === 12) {
                    return "1 a 31 de Diciembre {$y}";
                }

                $meses = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];

                if ($start->month === $end->month && $start->year === $end->year) {
                    return "{$start->day} a {$end->day} de {$meses[$end->month]} {$y}";
                }

                return "{$start->day} de {$meses[$start->month]} a {$end->day} de {$meses[$end->month]} {$y}";
            } catch (\Throwable $e) {}
        }

        // If only end date is provided
        if (!empty($fechaFin)) {
            try {
                $end = Carbon::parse($fechaFin);
                $y = $y ?: $end->year;
                if ($end->month <= 3) return "1 a 31 de Marzo {$y}";
                if ($end->month <= 6) return "1 a 30 de Junio {$y}";
                if ($end->month <= 9) return "1 a 30 de Septiembre {$y}";
                return "1 a 31 de Diciembre {$y}";
            } catch (\Throwable $e) {}
        }

        // Use fallback if provided
        if (!empty($fallback)) {
            return $fallback;
        }

        $now = now();
        $q = (int) ceil($now->month / 3);
        $dates = self::getQuarterDates($q, $y ?: $now->year);
        return $dates['corte'];
    }

    public function getValorTotalAttribute(): float
    {
        return (float) ($this->valor_corriente + $this->valor_no_corriente);
    }

    public function getValorCorrienteFormateadoAttribute(): string
    {
        return self::formatNumber($this->valor_corriente);
    }

    public function getValorNoCorrienteFormateadoAttribute(): string
    {
        return self::formatNumber($this->valor_no_corriente);
    }

    public function getValorTotalFormateadoAttribute(): string
    {
        return self::formatNumber($this->valor_total);
    }

    public function getValorCorrienteInputAttribute(): string
    {
        return self::formatNumber($this->valor_corriente);
    }

    public function getValorNoCorrienteInputAttribute(): string
    {
        return self::formatNumber($this->valor_no_corriente);
    }

    public function getFechaFormateadaAttribute(): string
    {
        if (! $this->fecha) {
            return '-';
        }

        return $this->fecha instanceof Carbon
            ? $this->fecha->format('d/m/Y')
            : Carbon::parse($this->fecha)->format('d/m/Y');
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (blank($search)) {
            return $query;
        }

        $term = trim($search);

        return $query->where(function (Builder $q) use ($term): void {
            $q->where('codigo_contable_subcuenta', 'like', "%{$term}%")
                ->orWhere('nombre_subcuenta', 'like', "%{$term}%")
                ->orWhere('codigo_entidad_reciproca', 'like', "%{$term}%")
                ->orWhere('nombre_entidad_reciproca', 'like', "%{$term}%");
        });
    }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if (filled($from)) {
            $query->whereDate('fecha', '>=', $from);
        }

        if (filled($to)) {
            $query->whereDate('fecha', '<=', $to);
        }

        return $query;
    }
}

