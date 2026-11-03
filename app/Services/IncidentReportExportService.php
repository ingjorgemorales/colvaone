<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncidentReportExportService
{
    private const COLOR_HEADER = 'FF123F6E';
    private const COLOR_SUBHEADER = 'FF24538A';
    private const COLOR_ZEBRA = 'FFF8FAFC';
    private const COLOR_BORDER = 'FFE2E8F0';

    public function build(Collection $incidents, array $stats, array $filterLabels, User $user): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator(config('app.name', 'ColvaOne'))
            ->setTitle('Reporte de Gestión de Incidentes')
            ->setSubject('Reporte y estadísticas de incidentes y riesgos');

        // Sheet 1: Detalle de incidentes
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detalle de Incidentes');
        $this->buildDetailSheet($sheet, $incidents, $filterLabels, $user);

        // Sheet 2: Resumen y Estadísticas
        $statsSheet = $spreadsheet->createSheet();
        $statsSheet->setTitle('Resumen y Estadísticas');
        $this->buildStatsSheet($statsSheet, $stats, $filterLabels);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function buildDetailSheet(Worksheet $sheet, Collection $incidents, array $filterLabels, User $user): void
    {
        // Title banner
        $sheet->setCellValue('A1', 'COLVAONE - REPORTE DE GESTIÓN DE INCIDENTES Y RIESGOS');
        $sheet->mergeCells('A1:R1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_HEADER);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(32);

        // Metadata block
        $generatedAt = now()->timezone(config('app.timezone'))->format('d/m/Y H:i');
        $filtersText = !empty($filterLabels) ? implode(' | ', $filterLabels) : 'Sin filtros (Todos los registros visibles)';

        $sheet->setCellValue('A2', 'Generado por: ' . $user->name . ' ' . $user->last_name . ' (' . ($user->position ?: 'Sin cargo') . ')');
        $sheet->setCellValue('A3', 'Fecha de generación: ' . $generatedAt);
        $sheet->setCellValue('A4', 'Filtros aplicados: ' . $filtersText);
        $sheet->setCellValue('A5', 'Total de incidentes en reporte: ' . $incidents->count());

        $sheet->getStyle('A2:A5')->getFont()->setSize(10)->getColor()->setARGB('FF475569');

        // Headers row
        $headers = [
            'A' => 'Ticket',
            'B' => 'Fecha Registro',
            'C' => 'Clasificación',
            'D' => 'Tipo de Falla / Riesgo',
            'E' => 'Reportado Por',
            'F' => 'Cargo Reportante',
            'G' => 'Área / Dependencia',
            'H' => 'Sede',
            'I' => 'Ubicación Exacta',
            'J' => 'Responsable Asignado',
            'K' => 'Cargo Responsable',
            'L' => '¿Afecta Operación?',
            'M' => 'Prioridad',
            'N' => 'Actividad',
            'O' => 'Archivado',
            'P' => 'Descripción',
            'Q' => 'Equipos Afectados',
            'R' => 'Evidencias',
        ];

        $headerRow = 7;
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
        }

        $headerRange = 'A' . $headerRow . ':R' . $headerRow;
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_HEADER);
        $sheet->getStyle($headerRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        $row = 8;
        foreach ($incidents as $inc) {
            $isRisk = ($inc->failure_type === 'risk');
            $createdAt = $inc->created_at ? $inc->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') : '';

            $sheet->setCellValue('A' . $row, $inc->ticket);
            $sheet->setCellValue('B' . $row, $createdAt);
            $sheet->setCellValue('C' . $row, $isRisk ? 'Riesgo priorizado' : 'Incidente general');
            $sheet->setCellValue('D' . $row, $inc->failure?->name ?? 'Sin especificar');
            $sheet->setCellValue('E' . $row, $inc->reporter_name);
            $sheet->setCellValue('F' . $row, $inc->reporter_position);
            $sheet->setCellValue('G' . $row, $inc->area?->name ?? 'Sin área');
            $sheet->setCellValue('H' . $row, $inc->zone?->name ?? 'Sin sede');
            $sheet->setCellValue('I' . $row, $inc->location);
            $sheet->setCellValue('J' . $row, $inc->responsible_name ?: ($inc->responsibleUser ? trim($inc->responsibleUser->name . ' ' . $inc->responsibleUser->last_name) : 'Sin asignar'));
            $sheet->setCellValue('K' . $row, $inc->responsible_position ?: 'Sin especificar');
            $sheet->setCellValue('L' . $row, $inc->impact?->name ?? 'Sin especificar');
            $sheet->setCellValue('M' . $row, $inc->priority?->name ?? 'Normal');
            $sheet->setCellValue('N' . $row, $inc->is_active ? 'Activo' : 'Inactivo');
            $sheet->setCellValue('O' . $row, $inc->archived_at ? 'Archivado' : 'No');
            $sheet->setCellValue('P' . $row, $inc->description);
            $sheet->setCellValue('Q' . $row, $inc->affected_assets ?: 'Ninguno');
            $sheet->setCellValue('R' . $row, $inc->attachments_count ?? $inc->attachments->count());

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle('A' . $row . ':R' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_ZEBRA);
            }

            $row++;
        }

        $lastRow = max($row - 1, 8);
        $dataRange = 'A7:R' . $lastRow;

        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);
        $sheet->getStyle('A8:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('L8:O' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('R8:R' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (array_keys($headers) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function buildStatsSheet(Worksheet $sheet, array $stats, array $filterLabels): void
    {
        // Header
        $sheet->setCellValue('A1', 'COLVAONE - RESUMEN ESTADÍSTICO DE INCIDENTES');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_HEADER);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Global KPIs
        $sheet->setCellValue('A3', 'MÉTRICA GLOBAL');
        $sheet->setCellValue('B3', 'CANTIDAD');
        $sheet->setCellValue('C3', 'PORCENTAJE');
        $sheet->getStyle('A3:C3')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A3:C3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_SUBHEADER);

        $total = $stats['total'] ?? 0;
        $kpiRows = [
            ['Total de incidentes reportados', $total, $total > 0 ? '100%' : '0%'],
            ['Riesgos priorizados', $stats['total_risks'] ?? 0, $total > 0 ? round((($stats['total_risks'] ?? 0) / $total) * 100, 1) . '%' : '0%'],
            ['Incidentes generales', $stats['total_generals'] ?? 0, $total > 0 ? round((($stats['total_generals'] ?? 0) / $total) * 100, 1) . '%' : '0%'],
            ['Afectan la operación', $stats['affects_operation'] ?? 0, $total > 0 ? round((($stats['affects_operation'] ?? 0) / $total) * 100, 1) . '%' : '0%'],
            ['Activos', $stats['total_active'] ?? 0, $total > 0 ? round((($stats['total_active'] ?? 0) / $total) * 100, 1) . '%' : '0%'],
            ['Inactivos', $stats['total_inactive'] ?? 0, $total > 0 ? round((($stats['total_inactive'] ?? 0) / $total) * 100, 1) . '%' : '0%'],
            ['Archivados', $stats['total_archived'] ?? 0, $total > 0 ? round((($stats['total_archived'] ?? 0) / $total) * 100, 1) . '%' : '0%'],
        ];

        $r = 4;
        foreach ($kpiRows as $kpi) {
            $sheet->setCellValue('A' . $r, $kpi[0]);
            $sheet->setCellValue('B' . $r, $kpi[1]);
            $sheet->setCellValue('C' . $r, $kpi[2]);
            $r++;
        }
        $sheet->getStyle('A3:C' . ($r - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);

        // By Zone
        $r += 2;
        $sheet->setCellValue('A' . $r, 'DISTRIBUCIÓN POR SEDE');
        $sheet->setCellValue('B' . $r, 'TOTAL');
        $sheet->setCellValue('C' . $r, 'AFECTA OPERACIÓN');
        $sheet->setCellValue('D' . $r, 'PORCENTAJE');
        $sheet->getStyle('A' . $r . ':D' . $r)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A' . $r . ':D' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_SUBHEADER);
        $startZone = $r;
        $r++;

        foreach ($stats['by_zone'] ?? [] as $zone) {
            $sheet->setCellValue('A' . $r, $zone['name']);
            $sheet->setCellValue('B' . $r, $zone['count']);
            $sheet->setCellValue('C' . $r, $zone['affects_count']);
            $sheet->setCellValue('D' . $r, $total > 0 ? round(($zone['count'] / $total) * 100, 1) . '%' : '0%');
            $r++;
        }
        $sheet->getStyle('A' . $startZone . ':D' . max($r - 1, $startZone))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);

        // By Priority
        $r += 2;
        $sheet->setCellValue('A' . $r, 'DISTRIBUCIÓN POR PRIORIDAD');
        $sheet->setCellValue('B' . $r, 'TOTAL');
        $sheet->setCellValue('C' . $r, 'PORCENTAJE');
        $sheet->getStyle('A' . $r . ':C' . $r)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A' . $r . ':C' . $r)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::COLOR_SUBHEADER);
        $startPrio = $r;
        $r++;

        foreach ($stats['by_priority'] ?? [] as $prio) {
            $sheet->setCellValue('A' . $r, $prio['name']);
            $sheet->setCellValue('B' . $r, $prio['count']);
            $sheet->setCellValue('C' . $r, $total > 0 ? round(($prio['count'] / $total) * 100, 1) . '%' : '0%');
            $r++;
        }
        $sheet->getStyle('A' . $startPrio . ':C' . max($r - 1, $startPrio))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDER);

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
    }
}
