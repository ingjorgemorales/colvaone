<?php

namespace App\Services;

use App\Models\FinancialReciprocalSetting;
use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReciprocalExportService
{
    private const COLOR_PRIMARY = 'FF123F6E';
    private const COLOR_HEADER = 'FF1D5F99';
    private const COLOR_ZEBRA = 'FFF8FAFC';
    private const COLOR_BORDER = 'FFE2E8F0';

    public function build(Collection $records, array $filterLabels = [], ?User $user = null, ?string $fechaCorte = null): Spreadsheet
    {
        $settings = FinancialReciprocalSetting::current();
        $corteText = $fechaCorte ?: $settings->fecha_corte;

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator(config('app.name', 'ColvaOne'))
            ->setTitle($settings->formato)
            ->setSubject('Módulo Financiera - Operaciones Recíprocas');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Operaciones Recíprocas');
        $sheet->setShowGridLines(true);

        // CGN Header Block matching official format
        $sheet->setCellValue('A1', 'DEPARTAMENTO');
        $sheet->setCellValue('B1', $settings->departamento);
        $sheet->setCellValue('D1', $settings->formato);

        $sheet->setCellValue('A2', 'MUNICIPIO:');
        $sheet->setCellValue('B2', $settings->municipio);

        $sheet->setCellValue('A3', 'ENTIDAD:');
        $sheet->setCellValue('B3', $settings->entidad);

        $sheet->setCellValue('A4', 'CODIGO:');
        $sheet->setCellValueExplicit('B4', (string) $settings->codigo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->setCellValue('A5', 'FECHA DE CORTE:');
        $sheet->setCellValue('B5', $corteText);

        $sheet->setCellValue('F6', $settings->cifras);
        $sheet->getStyle('F6')->getFont()->setItalic(true)->setSize(10)->getColor()->setARGB('FF64748B');
        $sheet->getStyle('F6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $metaHeadersStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1E293B'], 'size' => 10],
        ];
        $sheet->getStyle('A1:A5')->applyFromArray($metaHeadersStyle);
        $sheet->getStyle('B1:B5')->getFont()->setSize(10)->getColor()->setARGB('FF334155');
        $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB(self::COLOR_PRIMARY);

        // Table Headers (Row 7) - Exact 6 columns matching official CGN file without Valor Total
        $headers = [
            'A7' => 'Codigo Contable Subcuenta',
            'B7' => 'Nombre de la Subcuenta',
            'C7' => 'Codigo entidad Reciproca',
            'D7' => 'Nombre entidad Reciproca',
            'E7' => 'Valor Corriente',
            'F7' => 'Valor No Corriente',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A7:F7';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(28);

        // Data Rows (Row 8+) - Sin columna de totales y sin fila final de totales
        $currentRow = 8;

        foreach ($records as $index => $record) {
            $valCorriente = (float) $record->valor_corriente;
            $valNoCorriente = (float) $record->valor_no_corriente;

            $sheet->setCellValueExplicit('A' . $currentRow, (string) $record->codigo_contable_subcuenta, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $currentRow, $record->nombre_subcuenta);
            $sheet->setCellValueExplicit('C' . $currentRow, (string) $record->codigo_entidad_reciproca, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('D' . $currentRow, $record->nombre_entidad_reciproca);
            $sheet->setCellValue('E' . $currentRow, $valCorriente);
            $sheet->setCellValue('F' . $currentRow, $valNoCorriente);

            // Alignments
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $currentRow . ':F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Zebra Striping
            if ($index % 2 === 1) {
                $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB(self::COLOR_ZEBRA);
            }

            $currentRow++;
        }

        // Apply number format to currency columns (sin decimales innecesarios y formato de miles corregido)
        if ($currentRow > 8) {
            $sheet->getStyle('E8:F' . ($currentRow - 1))
                ->getNumberFormat()
                ->setFormatCode('#,##0.##;(#,##0.##);0');

            $sheet->getStyle('A8:F' . ($currentRow - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
            ]);
        }

        // Auto-fit columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }

    public function buildTemplate(): Spreadsheet
    {
        $settings = FinancialReciprocalSetting::current();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator(config('app.name', 'ColvaOne'))
            ->setTitle($settings->formato)
            ->setSubject('Plantilla Oficial de Operaciones Recíprocas');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Operaciones Recíprocas');
        $sheet->setShowGridLines(true);

        // Row 1 to 5: CGN official metadata matching user mockup
        $sheet->setCellValue('A1', 'DEPARTAMENTO');
        $sheet->setCellValue('B1', $settings->departamento);
        $sheet->setCellValue('D1', $settings->formato);

        $sheet->setCellValue('A2', 'MUNICIPIO:');
        $sheet->setCellValue('B2', $settings->municipio);

        $sheet->setCellValue('A3', 'ENTIDAD:');
        $sheet->setCellValue('B3', $settings->entidad);

        $sheet->setCellValue('A4', 'CODIGO:');
        $sheet->setCellValueExplicit('B4', (string) $settings->codigo, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->setCellValue('A5', 'FECHA DE CORTE:');
        $sheet->setCellValue('B5', $settings->fecha_corte);

        $sheet->setCellValue('F6', $settings->cifras);
        $sheet->getStyle('F6')->getFont()->setItalic(true)->setSize(10)->getColor()->setARGB('FF64748B');
        $sheet->getStyle('F6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $metaHeadersStyle = [
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1E293B'], 'size' => 10],
        ];
        $sheet->getStyle('A1:A5')->applyFromArray($metaHeadersStyle);
        $sheet->getStyle('B1:B5')->getFont()->setSize(10)->getColor()->setARGB('FF334155');
        $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(11)->getColor()->setARGB(self::COLOR_PRIMARY);

        // Row 7: Exact table headers from official format (NO Observaciones y NO Valor Total)
        $headers = [
            'A7' => 'Codigo Contable Subcuenta',
            'B7' => 'Nombre de la Subcuenta',
            'C7' => 'Codigo entidad Reciproca',
            'D7' => 'Nombre entidad Reciproca',
            'E7' => 'Valor Corriente',
            'F7' => 'Valor No Corriente',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A7:F7')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(28);

        // Row 8: Sample row exactly from the user's Excel mockup
        $sheet->setCellValueExplicit('A8', '1.2.22.06', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B8', 'Sociedades públicas');
        $sheet->setCellValueExplicit('C8', '229911001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('D8', 'AGUAS DE BOGOTA S.A. E.S.P.');
        $sheet->setCellValue('E8', 0);
        $sheet->setCellValue('F8', 5000000);

        $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E8:F8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E8:F8')->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle('A8:F8')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_BORDER]]],
        ]);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
