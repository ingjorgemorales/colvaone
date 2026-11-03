<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Incidentes y Riesgos | ColvaOne</title>
    <style>
        @page { margin: 28px 32px 36px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        
        .header { width: 100%; border-bottom: 2px solid #123f6e; padding-bottom: 12px; margin-bottom: 14px; }
        .header table { width: 100%; border-collapse: collapse; }
        .header td { vertical-align: middle; }
        .brand img { max-height: 38px; width: auto; vertical-align: middle; }
        .report-title { text-align: right; }
        .report-title h1 { font-size: 16px; margin: 0 0 4px; color: #123f6e; font-weight: bold; }
        .meta { color: #64748b; font-size: 8.5px; }

        .filters-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px 10px; margin-bottom: 14px; font-size: 9px; color: #475569; }
        .filters-box strong { color: #123f6e; }

        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .kpi-table td { padding: 8px 10px; background: #f1f6fa; border: 1px solid #cbd8e5; text-align: center; border-radius: 4px; }
        .kpi-number { font-size: 16px; font-weight: bold; color: #123f6e; margin-bottom: 2px; }
        .kpi-label { font-size: 8.5px; color: #64748b; text-transform: uppercase; font-weight: 600; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 8.5px; margin-bottom: 14px; }
        .data-table th { background: #123f6e; color: #ffffff; font-weight: bold; padding: 6px 6px; text-align: left; border: 1px solid #0f355c; }
        .data-table td { padding: 5px 6px; border: 1px solid #e2e8f0; vertical-align: top; }
        .data-table tr:nth-child(even) td { background: #f8fafc; }

        .badge { display: inline-block; padding: 2px 5px; border-radius: 3px; font-size: 7.5px; font-weight: bold; text-transform: uppercase; }
        .badge-risk { background: #fef3c7; color: #92400e; }
        .badge-general { background: #e0f2fe; color: #0369a1; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-warning { background: #fef3c7; color: #854d0e; }
        .badge-success { background: #e6f4ee; color: #046c4e; }
        .badge-neutral { background: #f1f5f9; color: #475569; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="brand">
                    <img src="{{ $logo }}" alt="Colvatel">
                </td>
                <td class="report-title">
                    <h1>Reporte Consolidado de Incidentes y Riesgos</h1>
                    <div class="meta">
                        Generado el {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i') }} por {{ $user->name }} {{ $user->last_name }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="filters-box">
        <strong>Filtros aplicados:</strong> {{ !empty($filterLabels) ? implode('  |  ', $filterLabels) : 'Todos los registros visibles sin filtro' }}
        &nbsp;&mdash;&nbsp;
        <strong>Total de incidentes:</strong> {{ $incidents->count() }}
    </div>

    <table class="kpi-table">
        <tr>
            <td width="20%">
                <div class="kpi-number">{{ $stats['total'] ?? $incidents->count() }}</div>
                <div class="kpi-label">Total Reportes</div>
            </td>
            <td width="20%">
                <div class="kpi-number" style="color: #b45309;">{{ $stats['total_risks'] ?? 0 }}</div>
                <div class="kpi-label">Riesgos Priorizados</div>
            </td>
            <td width="20%">
                <div class="kpi-number" style="color: #0369a1;">{{ $stats['total_generals'] ?? 0 }}</div>
                <div class="kpi-label">Incidentes Generales</div>
            </td>
            <td width="20%">
                <div class="kpi-number" style="color: #b91c1c;">{{ $stats['affects_operation'] ?? 0 }}</div>
                <div class="kpi-label">Afectan Operación</div>
            </td>
            <td width="20%">
                <div class="kpi-number" style="color: #046c4e;">{{ $stats['total_active'] ?? 0 }}</div>
                <div class="kpi-label">Activos</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="8%">Ticket</th>
                <th width="10%">Fecha</th>
                <th width="11%">Clasificación</th>
                <th width="16%">Tipo de Falla / Riesgo</th>
                <th width="14%">Reportado Por</th>
                <th width="11%">Sede</th>
                <th width="14%">Responsable</th>
                <th width="8%">Prioridad</th>
                <th width="8%">¿Afecta?</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $inc)
                @php
                    $isRisk = ($inc->failure_type === 'risk');
                    $affects = str_contains(mb_strtolower($inc->impact?->name ?? ''), 'sí') || str_contains(mb_strtolower($inc->impact?->name ?? ''), 'si');
                    $partial = str_contains(mb_strtolower($inc->impact?->name ?? ''), 'parcial');
                @endphp
                <tr>
                    <td><strong>{{ $inc->ticket }}</strong></td>
                    <td>{{ $inc->created_at ? $inc->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') : '' }}</td>
                    <td>
                        <span class="badge {{ $isRisk ? 'badge-risk' : 'badge-general' }}">
                            {{ $isRisk ? 'Riesgo' : 'Incidente' }}
                        </span>
                    </td>
                    <td>{{ $inc->failure?->name ?? 'Sin especificar' }}</td>
                    <td>
                        {{ $inc->reporter_name }}
                        @if($inc->reporter_position)
                            <div class="meta">{{ $inc->reporter_position }}</div>
                        @endif
                    </td>
                    <td>{{ $inc->zone?->name ?? 'Sin sede' }}</td>
                    <td>
                        {{ $inc->responsible_name ?: ($inc->responsibleUser ? trim($inc->responsibleUser->name . ' ' . $inc->responsibleUser->last_name) : 'Sin asignar') }}
                        @if($inc->responsible_position)
                            <div class="meta">{{ $inc->responsible_position }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $inc->priority?->name === 'Alta' ? 'badge-danger' : ($inc->priority?->name === 'Media' ? 'badge-warning' : 'badge-success') }}">
                            {{ $inc->priority?->name ?? 'Normal' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $affects ? 'badge-danger' : ($partial ? 'badge-warning' : 'badge-neutral') }}">
                            {{ $affects ? 'Sí' : ($partial ? 'Parcial' : 'No') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 18px; color: #94a3b8;">
                        No hay incidentes para los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
