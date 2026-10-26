<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $incident->ticket }}</title>
    <style>
        @page { margin:36px 40px 48px; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:11px; color:#243247; line-height:1.45; }
        h1 { font-size:21px; margin:0 0 4px; color:#123f6e; }
        .brand { font-size:12px; font-weight:bold; color:#123f6e; margin-bottom:14px; }
        .brand img { width:170px; height:auto; vertical-align:middle; }
        .brand span { margin-left:12px; vertical-align:middle; }
        .report-header { margin-bottom:12px; }
        .report-header td { padding:0; vertical-align:middle; }
        .report-header .brand { margin:0; }
        .report-header .report-title { text-align:right; }
        .report-header h1 { font-size:18px; }
        .meta { color:#64748b; font-size:9px; }
        .ticket { font-size:13px; font-weight:bold; color:#123f6e; margin:4px 0; }
        .report-summary { border:1px solid #c9d9e7; border-left:4px solid #123f6e; border-radius:6px; padding:12px 14px; margin:14px 0; page-break-inside:avoid; }
        .failure { font-size:13px; font-weight:bold; margin-bottom:8px; word-wrap:break-word; }
        .report-card { border:1px solid #cbd8e5; border-radius:6px; margin-bottom:10px; }
        .compact-card { page-break-inside:avoid; }
        .card-heading { margin:0; padding:7px 12px; font-size:11px; font-weight:bold; color:#123f6e; background:#f1f6fa; border-bottom:1px solid #d8e2ed; border-radius:6px 6px 0 0; page-break-after:avoid; }
        .card-body { padding:6px 12px; }
        .badge { display:inline-block; padding:3px 7px; border-radius:4px; background:#edf2f7; font-size:9px; }
        .tone-success, .resuelto { background:#e6f4ee; color:#046c4e; }
        .tone-warning, .reportado { background:#fef3c7; color:#854d0e; }
        .tone-danger { background:#fee2e2; color:#b91c1c; }
        .en_atencion { background:#eeecff; color:#5145a0; }
        .summary-fields td, .three-columns td { width:33.33%; }
        .summary-fields td { padding-bottom:0; }
        table { width:100%; border-collapse:collapse; table-layout:fixed; }
        td { width:50%; vertical-align:top; padding:4px 12px 5px 0; word-wrap:break-word; }
        .label { color:#64748b; font-size:9px; margin-bottom:3px; page-break-after:avoid; }
        .text { word-wrap:break-word; }
        .text-field { margin:6px 0 8px; }
        .evidence-index { margin-top:2px; }
        .evidence-index td { border-bottom:1px solid #e2e8f0; font-size:9px; }
        .evidence-index td:first-child { width:80%; }
        .photo-page { page-break-before:always; }
        .photo-frame { text-align:center; padding:14px 0; }
        .photo-frame img { max-width:490px; max-height:520px; }
        .caption { font-size:10px; word-wrap:break-word; }
        .notice { color:#925b05; font-size:9px; }
    </style>
</head>
<body>
    <table class="report-header"><tr>
        <td><div class="brand"><img src="{{ $logo }}" alt="Logo Colvatel"><span>ONE</span></div></td>
        <td class="report-title"><h1>Gestión de incidentes</h1><div class="ticket">{{ $incident->ticket }}</div><div class="meta">Generado el {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</div></td>
    </tr></table>
    <div class="report-summary">
        <div class="failure">{{ $incident->failure?->name }}</div>
        <table class="summary-fields"><tr>
            <td><div class="label">Estado de atención</div><span class="badge {{ $incident->status }}">{{ \App\Models\Incident::STATUSES[$incident->status] }}</span></td>
            <td><div class="label">Actividad</div><span class="badge {{ $incident->is_active ? 'tone-success' : 'tone-warning' }}">{{ $incident->is_active ? 'Activo' : 'Inactivo' }}</span>@if($incident->archived_at) <span class="badge">Archivado</span>@endif</td>
            <td><div class="label">Prioridad</div><span class="badge {{ $incident->priority_tone }}">{{ $incident->priority?->name }}</span></td>
        </tr></table>
    </div>
    <div class="report-card compact-card" data-section="reporter">
        <h2 class="card-heading">Información de quien reporta</h2>
        <div class="card-body"><table class="three-columns">
            <tr><td><div class="label">Nombre completo</div>{{ $incident->reporter_name }}</td><td><div class="label">Cargo</div>{{ $incident->reporter_position }}</td><td><div class="label">Área / dependencia</div>{{ $incident->area?->name }}</td></tr>
        </table></div>
    </div>
    <div class="report-card compact-card" data-section="location">
        <h2 class="card-heading">Ubicación del incidente</h2>
        <div class="card-body"><table><tr><td><div class="label">Piso o zona</div>{{ $incident->zone?->name ?? 'Sin especificar' }}</td><td><div class="label">Ubicación exacta</div>{{ $incident->location }}</td></tr></table></div>
    </div>
    <div class="report-card" data-section="description">
        <h2 class="card-heading">Detalles del incidente</h2>
        <div class="card-body">
            <table><tr><td><div class="label">Tipo de falla</div>{{ $incident->failure?->name }}</td><td><div class="label">Afectación a la operación</div>{{ $incident->impact?->name }}</td></tr></table>
            <div class="text-field"><div class="label">Descripción del problema</div><div class="text">{!! nl2br(e($incident->description)) !!}</div></div>
            <div class="text-field"><div class="label">Equipos o activos afectados</div><div class="text">{{ $incident->affected_assets ?: 'Sin especificar' }}</div></div>
        </div>
    </div>
    <div class="report-card compact-card" data-section="tracking">
        <h2 class="card-heading">Prioridad y seguimiento</h2>
        <div class="card-body"><table>
            <tr><td><div class="label">Prioridad sugerida</div>{{ $incident->priority?->name }}</td><td><div class="label">Generado por</div>{{ $incident->creator?->name }} {{ $incident->creator?->last_name }}</td></tr>
            <tr><td><div class="label">Fecha de registro</div>{{ $incident->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td><td><div class="label">Última actualización</div>{{ $incident->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td></tr>
            @if($incident->archived_at)<tr><td colspan="2"><div class="label">Fecha de archivo</div>{{ $incident->archived_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td></tr>@endif
        </table></div>
    </div>
    <div class="report-card{{ $evidence->count() <= 3 ? ' compact-card' : '' }}" data-section="evidence">
        <h2 class="card-heading">Evidencias adjuntas ({{ $evidence->count() }})</h2>
        <div class="card-body">
            @if($evidence->isEmpty())
                <div class="meta">Sin evidencias adjuntas.</div>
            @else
                <table class="evidence-index">
                    @foreach($evidence as $item)
                        <tr><td>{{ $loop->iteration }}. {{ $item['name'] }}@if($item['is_image'] && !$item['source'])<div class="notice">Imagen no disponible para este reporte. Consulta el original en la aplicación.</div>@endif</td><td>{{ number_format($item['size'] / 1024, 0) }} KB</td></tr>
                    @endforeach
                </table>
                @if($evidence->contains('is_image', false))<p class="meta">Los documentos adjuntos se relacionan por nombre y se pueden consultar desde el incidente en la aplicación.</p>@endif
            @endif
        </div>
    </div>
    @foreach($evidence->whereNotNull('source') as $item)
        <div class="photo-page">
            <div class="brand"><img src="{{ $logo }}" alt="Logo Colvatel"><span>{{ $incident->ticket }}</span></div>
            <div class="report-card">
                <h2 class="card-heading">Evidencia fotográfica {{ $loop->iteration }}</h2>
                <div class="card-body">
                    <div class="caption">{{ $item['name'] }}</div>
                    <div class="photo-frame"><img src="{{ $item['source'] }}" alt="{{ $item['name'] }}"></div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
