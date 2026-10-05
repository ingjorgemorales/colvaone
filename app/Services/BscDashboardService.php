<?php

namespace App\Services;

use App\Models\BscPerspective;
use App\Models\Indicator;
use App\Models\IndicatorResult;
use Illuminate\Support\Collection;

/**
 * Tablero BSC: replica el de la junta. Cada indicador muestra su cumplimiento
 * acumulado, su avance frente a la meta del ano y su evolucion por periodo
 * (real vs presupuestado), agrupados por perspectiva.
 */
class BscDashboardService
{
    /** @param Collection<int, Indicator> $indicators con `results` ya filtrados al rango */
    public function build(Collection $indicators): array
    {
        $sections = $indicators
            ->groupBy(fn (Indicator $indicator) => $indicator->bsc_perspective_id ?? 0)
            ->map(function (Collection $group) {
                /** @var BscPerspective|null $perspective */
                $perspective = $group->first()->bscPerspective;

                return [
                    'name' => $perspective?->name ?? 'Sin perspectiva BSC',
                    'position' => $perspective?->position ?? PHP_INT_MAX,
                    'tiles' => $group->map(fn (Indicator $indicator) => $this->tile($indicator))->values()->all(),
                ];
            })
            ->sortBy([['position', 'asc'], ['name', 'asc']])
            ->values();

        $charts = $sections->pluck('tiles')->flatten(1)
            ->filter(fn (array $tile) => $tile['count'] > 0)
            ->map(fn (array $tile) => ['id' => $tile['id'], 'labels' => $tile['labels'], 'unit' => $tile['unit'], 'periods' => $tile['periods']])
            ->values();

        return [
            'sections' => $sections->all(),
            'charts' => $charts->all(),
            'total' => $indicators->count(),
        ];
    }

    private function tile(Indicator $indicator): array
    {
        $results = $indicator->results->sortBy([
            fn ($a, $b) => $a->period_start <=> $b->period_start,
            fn ($a, $b) => $a->period_end <=> $b->period_end,
            fn ($a, $b) => $a->id <=> $b->id,
        ])->values();

        $mode = $indicator->accumulationMode();
        $summary = $this->summarize($results, $mode);

        return [
            'id' => $indicator->id,
            'name' => $indicator->name,
            'mode' => $mode,
            'unit' => $indicator->displayUnit(),
            'labels' => $indicator->isMoney()
                ? ['actual' => 'Real', 'target' => 'Presupuestado']
                : ['actual' => 'Resultado', 'target' => 'Meta'],
            'periods' => $results->map(fn (IndicatorResult $result) => $this->period($indicator, $result))->all(),
            'count' => $results->count(),
            'actual' => $summary['actual'],
            'target' => $summary['target'],
            'figures' => $this->figures($indicator, $mode, $summary),
            'reference' => $summary['reference'],
            'notice' => $summary['notice'],
            'annual_goal_text' => $indicator->annual_goal === null ? null : $indicator->formatAmount((float) $indicator->annual_goal),
        ]
            + $this->reading($summary['compliance'])
            + ['annual' => $this->annual($indicator, $summary)];
    }

    private function period(Indicator $indicator, IndicatorResult $result): array
    {
        $compliance = (float) $result->denominator === 0.0 || (float) $result->period_goal <= 0
            ? null
            : (float) $result->compliance;

        return [
            'label' => $this->periodLabel($result),
            'range' => $result->period_start->format('d/m/Y') . ' - ' . $result->period_end->format('d/m/Y'),
            'actual' => (float) $result->numerator,
            'target' => (float) $result->denominator,
            'compliance' => $compliance,
            // Textos de la tabla de datos bajo la grafica, como la del Excel.
            'actual_text' => $indicator->formatAmount((float) $result->numerator, compact: true),
            'target_text' => $indicator->formatAmount((float) $result->denominator, compact: true),
            'compliance_text' => $compliance === null ? '-' : $this->percent($compliance, fixed: true),
        ];
    }

    /**
     * Un porcentaje como se lee en el tablero: de 0 a 100, como en el Excel de la
     * junta. El valor calculado se conserva aparte para no ocultarlo.
     */
    private function reading(?float $compliance): array
    {
        $display = $compliance === null ? null : max(0.0, min(100.0, $compliance));

        return [
            'compliance' => $compliance,
            'display' => $display,
            'display_text' => $display === null ? null : $this->percent($display),
            'compliance_text' => $compliance === null ? null : $this->percent($compliance),
        ];
    }

    /**
     * Avance frente a la meta del ano: lo acumulado contra lo que se espera en
     * todo el ano (la barra "Avance 2026" del Excel). 63.448 / 83.259 = 76,2%.
     */
    private function annual(Indicator $indicator, array $summary): array
    {
        $goal = $indicator->annual_goal === null ? null : (float) $indicator->annual_goal;
        $reading = $this->reading(null);

        if ($goal === null) {
            return ['goal_text' => null, 'notice' => null] + $reading;
        }

        $notice = null;

        if ($goal <= 0) {
            $notice = 'La meta del año debe ser mayor que cero para medir el avance.';
        } elseif ($summary['actual'] !== null && $summary['goal'] !== null) {
            $reading = $this->reading(IndicatorResult::calculateCompliance(
                IndicatorResult::calculateResult($summary['actual'], $goal),
                $summary['goal'],
                $summary['formula'],
            ));
        }

        return ['goal_text' => $indicator->formatAmount($goal), 'notice' => $notice] + $reading;
    }

    /**
     * Resume el rango segun el modo. En suma y promedio el cumplimiento es el
     * mismo cociente (el promedio divide numerador y denominador por igual);
     * lo que cambia es el valor que se lee como "acumulado".
     */
    private function summarize(Collection $results, string $mode): array
    {
        $empty = ['actual' => null, 'target' => null, 'compliance' => null, 'goal' => null, 'formula' => null, 'reference' => null, 'notice' => null];

        if ($results->isEmpty()) {
            return $empty;
        }

        if ($mode === Indicator::ACCUMULATION_LAST) {
            $last = $results->sortBy([
                fn ($a, $b) => $a->period_end <=> $b->period_end,
                fn ($a, $b) => $a->id <=> $b->id,
            ])->last();

            return [
                'actual' => (float) $last->numerator,
                'target' => (float) $last->denominator,
                'compliance' => (float) $last->denominator === 0.0 ? null : (float) $last->compliance,
                'goal' => (float) $last->period_goal > 0 ? (float) $last->period_goal : null,
                'formula' => $last->compliance_formula,
                'reference' => 'Último período: ' . $this->periodLabel($last),
                'notice' => null,
            ];
        }

        $count = $results->count();
        $average = $mode === Indicator::ACCUMULATION_AVERAGE;
        $reference = ($average ? 'Promedio de ' : 'Suma de ') . $count . ($count === 1 ? ' período' : ' períodos');

        if ($this->overlaps($results)) {
            return ['reference' => $reference, 'notice' => 'Hay períodos superpuestos. Revisa los resultados para no contar dos veces el mismo tramo.'] + $empty;
        }

        $actual = $results->sum(fn (IndicatorResult $result) => (float) $result->numerator);
        $target = $results->sum(fn (IndicatorResult $result) => (float) $result->denominator);

        if ($average) {
            $actual /= $count;
            $target /= $count;
        }

        $values = ['actual' => round($actual, 2), 'target' => round($target, 2), 'reference' => $reference];
        $goals = $results->map(fn (IndicatorResult $result) => (float) $result->period_goal)->unique();
        $formulas = $results->pluck('compliance_formula')->unique();

        if ($goals->count() !== 1 || $goals->first() <= 0 || $formulas->count() !== 1) {
            return $values + ['compliance' => null, 'goal' => null, 'formula' => null, 'notice' => 'Los períodos tienen metas o fórmulas diferentes. Revisa los resultados antes de comparar su acumulado.'];
        }

        $values += ['goal' => $goals->first(), 'formula' => $formulas->first()];

        if ($target == 0.0) {
            return $values + ['compliance' => null, 'notice' => 'El acumulado de la meta es cero, así que no hay contra qué medir el cumplimiento.'];
        }

        if ($target < 0) {
            // Con una meta negativa (p. ej. EBITDA estimado en perdida) el cociente
            // invierte su sentido: un mejor resultado daria menos cumplimiento.
            return $values + ['compliance' => null, 'notice' => 'La meta acumulada es negativa, así que el cumplimiento no se puede leer como porcentaje.'];
        }

        return $values + [
            'compliance' => IndicatorResult::calculateCompliance(
                IndicatorResult::calculateResult($actual, $target),
                $goals->first(),
                $formulas->first(),
            ),
            'notice' => null,
        ];
    }

    /** Las dos cifras que acompanan al cumplimiento, nombradas segun el modo. */
    private function figures(Indicator $indicator, string $mode, array $summary): array
    {
        if ($summary['actual'] === null) {
            return [];
        }

        $money = $indicator->isMoney();

        [$actual, $target] = match ($mode) {
            Indicator::ACCUMULATION_LAST => $money ? ['Real', 'Presupuestado'] : ['Resultado', 'Meta'],
            Indicator::ACCUMULATION_AVERAGE => $money
                ? ['Real promedio', 'Presupuestado promedio']
                : ['Resultado promedio', 'Meta promedio'],
            default => $money
                ? ['Real acumulado', 'Presupuestado acumulado']
                : ['Resultado acumulado', 'Meta acumulada'],
        };

        return [
            ['label' => $actual, 'value' => $indicator->formatAmount($summary['actual'])],
            ['label' => $target, 'value' => $indicator->formatAmount($summary['target'])],
        ];
    }

    private function overlaps(Collection $results): bool
    {
        $furthestEnd = null;

        foreach ($results as $result) {
            if ($furthestEnd && $result->period_start->lte($furthestEnd)) {
                return true;
            }

            $furthestEnd = $furthestEnd && $furthestEnd->gt($result->period_end) ? $furthestEnd : $result->period_end;
        }

        return false;
    }

    /** Mes completo como "ago. 2026"; cualquier otro tramo con sus fechas. */
    private function periodLabel(IndicatorResult $result): string
    {
        $fullMonth = $result->period_start->isStartOfMonth()
            && $result->period_end->isSameMonth($result->period_start)
            && $result->period_end->day === $result->period_end->daysInMonth;

        return $fullMonth
            ? $result->period_start->locale('es')->translatedFormat('M Y')
            : $result->period_start->format('d/m/Y') . ' - ' . $result->period_end->format('d/m/Y');
    }

    /**
     * Un decimal, como el Excel de la junta (111,2%). En las tarjetas se omite el
     * ",0" (100%); en la tabla de la grafica va siempre, igual que en el Excel.
     */
    private function percent(float $value, bool $fixed = false): string
    {
        $formatted = number_format($value, 1, ',', '.');
        $formatted = $formatted === '-0,0' ? '0,0' : $formatted;

        return ($fixed || ! str_ends_with($formatted, ',0') ? $formatted : substr($formatted, 0, -2)) . '%';
    }
}
