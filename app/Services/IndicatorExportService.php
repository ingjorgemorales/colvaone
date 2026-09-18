<?php

namespace App\Services;

use App\Models\Indicator;
use App\Models\IndicatorResult;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Arma el libro de Excel de un indicador: una hoja con la ficha tecnica y
 * otra con los resultados, con la misma informacion que se ve en pantalla.
 */
class IndicatorExportService
{
    private const AZUL = 'FF123F6E';
    private const AZUL_SUAVE = 'FFEDF2F8';
    private const BORDE = 'FFD6DEE8';
    private const GRIS = 'FF64748B';

    /** Mismos colores de evaluacion que usa la vista: fuente y fondo. */
    private const COLORES_EVALUACION = [
        IndicatorResult::SATISFACTORY => ['FF047857', 'FFE7F5F0'],
        IndicatorResult::ACCEPTABLE => ['FFB45309', 'FFFDF4E3'],
        IndicatorResult::UNSATISFACTORY => ['FFDC2626', 'FFFDEBEB'],
    ];

    private const FORMATO_PORCENTAJE = '#,##0.00"%"';
    private const FORMATO_NUMERO = '#,##0.00';

    /** Ancho util de la columna de valores en la ficha, en caracteres. */
    private const ANCHO_VALOR = 96;

    /** La marca Colvatel ocupa la fila 1 de cada hoja; el contenido arranca en la 2. */
    private const LOGO = 'images/logo-login.png';
    private const ALTO_LOGO = 40;
    private const ALTO_IMAGEN = 42;

    public function build(Indicator $indicator): Spreadsheet
    {
        $indicator->loadMissing(['responsible', 'creator', 'updater', 'process', 'subprocess', 'bscPerspective', 'qualityObjective', 'results']);

        $libro = new Spreadsheet();
        $libro->getProperties()
            ->setCreator(config('app.name'))
            ->setTitle($indicator->name)
            ->setSubject('Ficha tecnica y resultados del indicador');

        $this->hojaFicha($libro->getActiveSheet(), $indicator);
        $this->hojaResultados($libro->createSheet(), $indicator);

        $libro->setActiveSheetIndex(0);

        return $libro;
    }

    public function filename(Indicator $indicator): string
    {
        $nombre = Str::of($indicator->name)->slug('-')->limit(60, '')->value();

        return sprintf('indicador-%d-%s-%s.xlsx', $indicator->id, $nombre ?: 'sin-nombre', now()->format('Ymd'));
    }

    /**
     * Marca Colvatel arriba de cada hoja: la misma imagen del inicio de sesion.
     * Si el archivo no esta, la hoja sale igual pero sin logo.
     */
    private function logo(Worksheet $hoja): void
    {
        $hoja->getRowDimension(1)->setRowHeight(self::ALTO_LOGO);

        $ruta = public_path(self::LOGO);

        if (! is_file($ruta)) {
            return;
        }

        $logo = new Drawing();
        $logo->setName('Colvatel');
        $logo->setDescription('Colvatel');
        $logo->setPath($ruta);
        $logo->setHeight(self::ALTO_IMAGEN);
        $logo->setOffsetX(8);
        $logo->setOffsetY(6);
        $logo->setCoordinates('A1');
        $logo->setWorksheet($hoja);
    }

    // ---------------------------------------------------------------- ficha

    private function hojaFicha(Worksheet $hoja, Indicator $indicator): void
    {
        $hoja->setTitle('Ficha tecnica');
        $hoja->setShowGridlines(false);
        $hoja->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
        $hoja->getColumnDimension('A')->setWidth(30);

        foreach (['B', 'C', 'D', 'E'] as $columna) {
            $hoja->getColumnDimension($columna)->setWidth(24);
        }

        $this->logo($hoja);

        $fila = $this->encabezado($hoja, $indicator);

        $fila = $this->seccion($hoja, $fila, 'Identificacion');
        $fila = $this->dato($hoja, $fila, 'ID', (string) $indicator->id);
        $fila = $this->dato($hoja, $fila, 'Nombre', $indicator->name);
        $fila = $this->dato($hoja, $fila, 'Categoria', $indicator->category_label);
        $fila = $this->dato($hoja, $fila, 'Perspectiva BSC', $indicator->bsc_perspective_label);
        $fila = $this->dato($hoja, $fila, 'Proceso', $indicator->process_label);
        $fila = $this->dato($hoja, $fila, 'Subproceso', $indicator->subprocess_label);
        $fila = $this->dato($hoja, $fila, 'Objetivo de calidad', $indicator->quality_objective_label);

        $fila = $this->seccion($hoja, $fila, 'Medicion');
        $fila = $this->dato($hoja, $fila, 'Responsable', $this->responsable($indicator));
        $fila = $this->dato($hoja, $fila, 'Unidad de medicion', $indicator->measurement_unit_label);
        $fila = $this->dato($hoja, $fila, 'Frecuencia', $indicator->frequency_label);
        $fila = $this->dato($hoja, $fila, 'Tipo', $indicator->type_label);
        $fila = $this->dato($hoja, $fila, 'Meta', $indicator->goal . '%');
        $fila = $this->dato($hoja, $fila, 'Formula', $indicator->formula);

        $fila = $this->seccion($hoja, $fila, 'Detalle');
        $fila = $this->dato($hoja, $fila, 'Objetivo del indicador', $indicator->objective);
        $fila = $this->dato($hoja, $fila, 'Aspectos metodologicos', $indicator->methodological_aspects);

        $fila = $this->seccion($hoja, $fila, 'Rango de evaluacion');
        $fila = $this->dato($hoja, $fila, 'Insatisfactorio', sprintf('Menor a %d%%', $indicator->threshold_acceptable));
        $fila = $this->dato($hoja, $fila, 'Aceptable', sprintf('Desde %d%% y menor a %d%%', $indicator->threshold_acceptable, $indicator->threshold_satisfactory));
        $fila = $this->dato($hoja, $fila, 'Satisfactorio', sprintf('Desde %d%%', $indicator->threshold_satisfactory));

        $fila = $this->seccion($hoja, $fila, 'Registro');
        $fila = $this->dato($hoja, $fila, 'Estado', $indicator->status_label);
        $fila = $this->dato($hoja, $fila, 'Creado por', sprintf(
            '%s el %s',
            $indicator->creator->name ?? 'Sistema',
            $indicator->created_at?->format('d/m/Y') ?? '-'
        ));

        if ($indicator->updater) {
            $fila = $this->dato($hoja, $fila, 'Ultima edicion', sprintf(
                '%s el %s',
                $indicator->updater->name,
                $indicator->updated_at?->format('d/m/Y H:i') ?? '-'
            ));
        }

        $hoja->getStyle('A6:E' . ($fila - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::BORDE);
    }

    /** Banda con el nombre del indicador, bajo el logo. Devuelve la primera fila libre. */
    private function encabezado(Worksheet $hoja, Indicator $indicator): int
    {
        $hoja->mergeCells('A2:E2');
        $this->texto($hoja, 'A2', 'FICHA TECNICA DEL INDICADOR');
        $hoja->getStyle('A2')->getFont()->setBold(true)->setSize(13)->getColor()->setARGB('FFFFFFFF');
        $hoja->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::AZUL);
        $hoja->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
        $hoja->getRowDimension(2)->setRowHeight(30);

        $hoja->mergeCells('A3:E3');
        $this->texto($hoja, 'A3', $indicator->name);
        $hoja->getStyle('A3')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB(self::AZUL);
        $hoja->getStyle('A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
        $hoja->getRowDimension(3)->setRowHeight(22);

        $hoja->mergeCells('A4:E4');
        $this->texto($hoja, 'A4', 'Generado el ' . now()->format('d/m/Y H:i'));
        $hoja->getStyle('A4')->getFont()->setSize(9)->getColor()->setARGB(self::GRIS);
        $hoja->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);

        return 6;
    }

    /** Titulo de bloque dentro de la ficha. */
    private function seccion(Worksheet $hoja, int $fila, string $titulo): int
    {
        $hoja->mergeCells("A{$fila}:E{$fila}");
        $this->texto($hoja, "A{$fila}", mb_strtoupper($titulo));
        $hoja->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(10)->getColor()->setARGB(self::AZUL);
        $hoja->getStyle("A{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::AZUL_SUAVE);
        $hoja->getStyle("A{$fila}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
        $hoja->getRowDimension($fila)->setRowHeight(20);

        return $fila + 1;
    }

    /** Par etiqueta / valor. El valor ocupa B:E y crece hacia abajo si es largo. */
    private function dato(Worksheet $hoja, int $fila, string $etiqueta, ?string $valor): int
    {
        $valor = trim((string) $valor);
        $valor = $valor === '' ? '-' : $valor;

        $this->texto($hoja, "A{$fila}", $etiqueta);
        $hoja->getStyle("A{$fila}")->getFont()->setBold(true)->setSize(10)->getColor()->setARGB(self::GRIS);
        $hoja->getStyle("A{$fila}")->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1)->setWrapText(true);

        $hoja->mergeCells("B{$fila}:E{$fila}");
        $this->texto($hoja, "B{$fila}", $valor);
        $hoja->getStyle("B{$fila}")->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true)->setIndent(1);

        // Excel no auto-ajusta el alto de una celda combinada, asi que se calcula.
        $hoja->getRowDimension($fila)->setRowHeight($this->alto($valor));

        return $fila + 1;
    }

    /** Alto aproximado en puntos segun cuantas lineas ocupa el texto. */
    private function alto(string $valor): float
    {
        $lineas = 0;

        foreach (preg_split('/\R/u', $valor) ?: [''] as $linea) {
            $lineas += max(1, (int) ceil(mb_strlen($linea) / self::ANCHO_VALOR));
        }

        return min(409.0, max(18.0, $lineas * 14.5));
    }

    // ----------------------------------------------------------- resultados

    private function hojaResultados(Worksheet $hoja, Indicator $indicator): void
    {
        $hoja->setTitle('Resultados');
        $hoja->setShowGridlines(false);

        // Trece columnas no caben a lo alto: se imprime apaisado y ajustado al ancho,
        // repitiendo los encabezados en cada pagina.
        $hoja->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $hoja->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
        $hoja->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);

        $columnas = [
            'A' => ['ID', 8],
            'B' => ['Periodo inicio', 15],
            'C' => ['Periodo fin', 15],
            'D' => ['Numerador', 14],
            'E' => ['Denominador', 15],
            'F' => ['Resultado', 14],
            'G' => ['Meta periodo', 15],
            'H' => ['Formula cumplimiento', 27],
            'I' => ['Cumplimiento', 15],
            'J' => ['Evaluacion', 17],
            'K' => ['Analisis', 52],
            'L' => ['Accion No', 13],
            'M' => ['Estado', 12],
        ];

        $this->logo($hoja);

        $hoja->mergeCells('A2:M2');
        $this->texto($hoja, 'A2', 'RESULTADOS - ' . $indicator->name);
        $hoja->getStyle('A2')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB(self::AZUL);
        $hoja->getStyle('A2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setIndent(1);
        $hoja->getRowDimension(2)->setRowHeight(24);

        foreach ($columnas as $letra => [$titulo, $ancho]) {
            $hoja->getColumnDimension($letra)->setWidth($ancho);
            $this->texto($hoja, $letra . '3', $titulo);
        }

        $hoja->getStyle('A3:M3')->getFont()->setBold(true)->setSize(10)->getColor()->setARGB('FFFFFFFF');
        $hoja->getStyle('A3:M3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::AZUL);
        $hoja->getStyle('A3:M3')->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setWrapText(true);
        $hoja->getRowDimension(3)->setRowHeight(30);
        // Solo filas. Congelar tambien la columna A partiria el logo en dos:
        // la imagen se sale de esa columna y el corte quedaria justo encima.
        $hoja->freezePane('A4');

        if ($indicator->results->isEmpty()) {
            $hoja->mergeCells('A4:M4');
            $this->texto($hoja, 'A4', 'Aun no hay resultados registrados para este indicador.');
            $hoja->getStyle('A4')->getFont()->getColor()->setARGB(self::GRIS);
            $hoja->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            return;
        }

        $fila = 4;

        foreach ($indicator->results as $resultado) {
            $this->filaResultado($hoja, $fila, $resultado);
            $fila++;
        }

        $ultima = $fila - 1;

        $hoja->getStyle("A3:M{$ultima}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::BORDE);
        $hoja->getStyle("A4:M{$ultima}")->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $hoja->getStyle("H4:H{$ultima}")->getAlignment()->setWrapText(true);
        $hoja->getStyle("K4:K{$ultima}")->getAlignment()->setWrapText(true);
        $hoja->setAutoFilter("A3:M{$ultima}");
    }

    private function filaResultado(Worksheet $hoja, int $fila, IndicatorResult $resultado): void
    {
        $this->numero($hoja, "A{$fila}", (float) $resultado->id, '0');
        $hoja->getStyle("A{$fila}")->getFont()->setBold(true)->getColor()->setARGB(self::AZUL);
        $hoja->getStyle("A{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->fecha($hoja, "B{$fila}", $resultado->period_start);
        $this->fecha($hoja, "C{$fila}", $resultado->period_end);

        $this->numero($hoja, "D{$fila}", (float) $resultado->numerator, self::FORMATO_NUMERO);
        $this->numero($hoja, "E{$fila}", (float) $resultado->denominator, self::FORMATO_NUMERO);
        $this->numero($hoja, "F{$fila}", (float) $resultado->result, self::FORMATO_PORCENTAJE);
        $this->numero($hoja, "G{$fila}", $resultado->period_goal === null ? null : (float) $resultado->period_goal, self::FORMATO_PORCENTAJE);

        $this->texto($hoja, "H{$fila}", sprintf(
            "%s
%s",
            $resultado->compliance_formula === IndicatorResult::FORMULA_DESCENDING ? 'Descendente' : 'Ascendente',
            $resultado->compliance_formula_math
        ));
        $hoja->getStyle("H{$fila}")->getFont()->setSize(10);

        $this->numero($hoja, "I{$fila}", (float) $resultado->compliance, self::FORMATO_PORCENTAJE);
        $hoja->getStyle("I{$fila}")->getFont()->setBold(true);

        $this->texto($hoja, "J{$fila}", $resultado->evaluation_label);
        [$fuente, $fondo] = self::COLORES_EVALUACION[$resultado->evaluation] ?? [self::GRIS, 'FFF4F6F9'];
        $hoja->getStyle("J{$fila}")->getFont()->setBold(true)->getColor()->setARGB($fuente);
        $hoja->getStyle("J{$fila}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fondo);
        $hoja->getStyle("J{$fila}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $this->texto($hoja, "K{$fila}", trim((string) $resultado->analysis) ?: '-');
        $this->texto($hoja, "L{$fila}", $resultado->action_number ?: '-');
        $this->texto($hoja, "M{$fila}", $resultado->status_label);

        if (! $resultado->isActive()) {
            $hoja->getStyle("A{$fila}:M{$fila}")->getFont()->getColor()->setARGB('FF94A3B8');
        }
    }

    private function responsable(Indicator $indicator): string
    {
        $nombre = trim(sprintf(
            '%s %s',
            $indicator->responsible->name ?? '',
            $indicator->responsible->last_name ?? ''
        ));

        return $nombre !== '' ? $nombre : 'Sin asignar';
    }

    // -------------------------------------------------------------- helpers

    /**
     * Siempre como texto explicito: un dato que empiece por "=" o "+" no puede
     * terminar interpretado por Excel como formula.
     */
    private function texto(Worksheet $hoja, string $celda, ?string $valor): void
    {
        $hoja->setCellValueExplicit($celda, (string) $valor, DataType::TYPE_STRING);
    }

    private function numero(Worksheet $hoja, string $celda, ?float $valor, string $formato): void
    {
        if ($valor === null) {
            $this->texto($hoja, $celda, '-');

            return;
        }

        $hoja->setCellValueExplicit($celda, $valor, DataType::TYPE_NUMERIC);
        $hoja->getStyle($celda)->getNumberFormat()->setFormatCode($formato);
        $hoja->getStyle($celda)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private function fecha(Worksheet $hoja, string $celda, mixed $fecha): void
    {
        if (! $fecha) {
            $this->texto($hoja, $celda, '-');

            return;
        }

        $hoja->setCellValueExplicit($celda, ExcelDate::PHPToExcel($fecha), DataType::TYPE_NUMERIC);
        $hoja->getStyle($celda)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        $hoja->getStyle($celda)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}
