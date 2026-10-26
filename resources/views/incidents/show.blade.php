<x-layouts.app title="{{ $incident->ticket }} | {{ config('app.name') }}" heading="{{ $incident->ticket }}" subheading="Detalle del incidente">
    @include('incidents.styles')
    <div class="incident-page incident-detail" style="max-width:1100px">
        <div class="incident-toolbar">
            <a class="btn-secondary" href="{{ route('incidents.index') }}"><i data-lucide="arrow-left" style="width:16px;height:16px"></i> Volver al listado</a>
            <div class="incident-buttons">
                <a class="btn-secondary" download href="{{ route('incidents.pdf', $incident) }}"><i data-lucide="file-down" style="width:16px;height:16px"></i> Generar PDF</a>
                @include('incidents.actions', ['detail' => true])
            </div>
        </div>
        <div class="incident-detail-summary">
            <div><h2>{{ $incident->failure?->name }}</h2><span class="incident-muted">{{ $incident->ticket }}</span></div>
            <div class="incident-buttons"><span class="incident-pill {{ $incident->status }}">{{ \App\Models\Incident::STATUSES[$incident->status] }}</span><span class="incident-pill {{ $incident->is_active ? 'tone-success' : 'tone-warning' }}">{{ $incident->is_active ? 'Activo' : 'Inactivo' }}</span>@if($incident->archived_at)<span class="incident-pill">Archivado</span>@endif</div>
        </div>
        <div class="incident-detail-sections">
            <section class="incident-detail-panel" aria-labelledby="incident-reporter-heading">
                <h2 id="incident-reporter-heading" class="incident-panel-heading"><i data-lucide="user-round" aria-hidden="true"></i>Información de quien reporta</h2>
                <dl class="incident-panel-body incident-details incident-grid">
                    <div><dt>Nombre completo</dt><dd>{{ $incident->reporter_name }}</dd></div>
                    <div><dt>Cargo</dt><dd>{{ $incident->reporter_position }}</dd></div>
                    <div class="incident-full"><dt>Área / dependencia</dt><dd>{{ $incident->area?->name }}</dd></div>
                </dl>
            </section>
            <section class="incident-detail-panel" aria-labelledby="incident-location-heading">
                <h2 id="incident-location-heading" class="incident-panel-heading"><i data-lucide="map-pin" aria-hidden="true"></i>Ubicación del incidente</h2>
                <dl class="incident-panel-body incident-details incident-grid"><div class="incident-full"><dt>Piso o zona</dt><dd>{{ $incident->zone?->name ?? 'Sin especificar' }}</dd></div><div class="incident-full"><dt>Ubicación exacta</dt><dd>{{ $incident->location }}</dd></div></dl>
            </section>
            <section class="incident-detail-panel incident-full" aria-labelledby="incident-description-heading">
                <h2 id="incident-description-heading" class="incident-panel-heading"><i data-lucide="clipboard-list" aria-hidden="true"></i>Detalles del incidente</h2>
                <dl class="incident-panel-body incident-details incident-grid">
                    <div><dt>Tipo de falla</dt><dd>{{ $incident->failure?->name }}</dd></div>
                    <div><dt>¿El incidente afecta la operación?</dt><dd>{{ $incident->impact?->name }}</dd></div>
                    <div class="incident-full"><dt>Descripción del problema</dt><dd class="incident-description">{{ $incident->description }}</dd></div>
                    <div class="incident-full"><dt>Equipos o activos afectados</dt><dd>{{ $incident->affected_assets ?: 'Sin especificar' }}</dd></div>
                </dl>
            </section>
            <section class="incident-detail-panel incident-full" aria-labelledby="incident-evidence-heading">
                <h2 id="incident-evidence-heading" class="incident-panel-heading"><i data-lucide="paperclip" aria-hidden="true"></i>Fotos o documentos de evidencia</h2>
                <div class="incident-panel-body">@include('incidents.evidence')</div>
            </section>
            <section class="incident-detail-panel incident-full" aria-labelledby="incident-tracking-heading">
                <h2 id="incident-tracking-heading" class="incident-panel-heading"><i data-lucide="history" aria-hidden="true"></i>Prioridad y seguimiento</h2>
                <dl class="incident-panel-body incident-details incident-grid three">
                    <div><dt>Prioridad sugerida</dt><dd><span class="incident-pill {{ $incident->priority_tone }}">{{ $incident->priority?->name }}</span></dd></div>
                    <div><dt>Estado de atención</dt><dd><span class="incident-pill {{ $incident->status }}">{{ \App\Models\Incident::STATUSES[$incident->status] }}</span></dd></div>
                    <div><dt>Generado por</dt><dd>{{ $incident->creator?->name }} {{ $incident->creator?->last_name }}</dd></div>
                    <div><dt>Fecha de registro</dt><dd>{{ $incident->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Última actualización</dt><dd>{{ $incident->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>
                    @if($incident->archived_at)<div><dt>Fecha de archivo</dt><dd>{{ $incident->archived_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>@endif
                </dl>
            </section>
        </div>
    </div>
</x-layouts.app>
