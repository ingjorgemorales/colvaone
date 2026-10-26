<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $incident->ticket }}</title>
    <style>
        @page { margin:36px 40px 48px; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#243247; line-height:1.5; }
        h1 { font-size:21px; margin:0 0 4px; color:#123f6e; }
        h2 { font-size:12px; color:#123f6e; border-bottom:1px solid #dce4ed; padding-bottom:7px; margin:22px 0 10px; page-break-after:avoid; }
        .brand { font-size:12px; font-weight:bold; color:#123f6e; margin-bottom:16px; }
        .brand img { width:170px; height:auto; vertical-align:middle; }
        .brand span { margin-left:12px; vertical-align:middle; }
        .meta { color:#64748b; font-size:9px; }
        .status { background:#edf2f7; padding:8px 12px; margin:12px 0; }
        table { width:100%; border-collapse:collapse; table-layout:fixed; }
        td { width:50%; vertical-align:top; padding:6px 12px 8px 0; word-wrap:break-word; }
        .label { color:#64748b; font-size:9px; margin-bottom:3px; }
        .text { white-space:pre-wrap; word-wrap:break-word; }
        .evidence-index td { border-bottom:1px solid #e2e8f0; font-size:9px; }
        .evidence-index td:first-child { width:80%; }
        .photo-page { page-break-before:always; }
        .photo-frame { text-align:center; padding:12px 0; }
        .photo-frame img { max-width:490px; max-height:550px; }
        .caption { font-size:10px; word-wrap:break-word; }
        .notice { color:#925b05; font-size:9px; }
    </style>
</head>
<body>
    <div class="brand"><img src="{{ $logo }}" alt="Logo Colvatel"><span>ONE</span></div>
    <h1>Gestión de incidentes</h1>
    <div class="meta">{{ $incident->ticket }} | Generado el {{ now()->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</div>
    <div class="status"><strong>{{ \App\Models\Incident::STATUSES[$incident->status] }}</strong> · {{ $incident->is_active ? 'Activo' : 'Inactivo' }}{{ $incident->archived_at ? ' · Archivado' : '' }}</div>
    <h2>Información de quien reporta</h2>
    <table>
        <tr><td><div class="label">Nombre completo</div>{{ $incident->reporter_name }}</td><td><div class="label">Cargo</div>{{ $incident->reporter_position }}</td></tr>
        <tr><td colspan="2"><div class="label">Área / dependencia</div>{{ $incident->area?->name }}</td></tr>
    </table>
    <h2>Ubicación del incidente</h2>
    <table><tr><td><div class="label">Piso o zona</div>{{ $incident->zone?->name ?? 'Sin especificar' }}</td><td><div class="label">Ubicación exacta</div>{{ $incident->location }}</td></tr></table>
    <h2>Detalles del incidente</h2>
    <table><tr><td><div class="label">Tipo de falla</div>{{ $incident->failure?->name }}</td><td><div class="label">Afectación a la operación</div>{{ $incident->impact?->name }}</td></tr></table>
    <div class="label">Descripción del problema</div><div class="text">{{ $incident->description }}</div>
    <div class="label" style="margin-top:12px">Equipos o activos afectados</div><div class="text">{{ $incident->affected_assets ?: 'Sin especificar' }}</div>
    <h2>Prioridad y seguimiento</h2>
    <table>
        <tr><td><div class="label">Prioridad sugerida</div>{{ $incident->priority?->name }}</td><td><div class="label">Generado por</div>{{ $incident->creator?->name }} {{ $incident->creator?->last_name }}</td></tr>
        <tr><td><div class="label">Fecha de registro</div>{{ $incident->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td><td><div class="label">Última actualización</div>{{ $incident->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td></tr>
        @if($incident->archived_at)<tr><td colspan="2"><div class="label">Fecha de archivo</div>{{ $incident->archived_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td></tr>@endif
    </table>
    <h2>Evidencias adjuntas ({{ $evidence->count() }})</h2>
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
    @foreach($evidence->whereNotNull('source') as $item)
        <div class="photo-page">
            <div class="brand"><img src="{{ $logo }}" alt="Logo Colvatel"><span>{{ $incident->ticket }}</span></div>
            <h2>Evidencia fotográfica {{ $loop->iteration }}</h2>
            <div class="caption">{{ $item['name'] }}</div>
            <div class="photo-frame"><img src="{{ $item['source'] }}" alt="{{ $item['name'] }}"></div>
        </div>
    @endforeach
</body>
</html>
