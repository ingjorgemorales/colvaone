<x-layouts.app title="{{ $incident->ticket }} | {{ config('app.name') }}" heading="{{ $incident->ticket }}" subheading="Detalle del incidente">
    @include('incidents.styles')
    <div class="incident-page" style="max-width:1100px">
        <div class="incident-toolbar">
            <a class="btn-secondary" href="{{ route('incidents.index') }}"><i data-lucide="arrow-left" style="width:16px;height:16px"></i> Volver al listado</a>
            <div class="incident-buttons">
                <a class="btn-secondary" download href="{{ route('incidents.pdf', $incident) }}"><i data-lucide="file-down" style="width:16px;height:16px"></i> Generar PDF</a>
                @include('incidents.actions', ['detail' => true])
            </div>
        </div>
        <div class="card">
            <section class="incident-section">
                <div class="incident-toolbar" style="margin-bottom:0">
                    <strong>{{ $incident->failure?->name }}</strong>
                    <div class="incident-buttons"><span class="incident-pill {{ $incident->status }}">{{ \App\Models\Incident::STATUSES[$incident->status] }}</span><span class="incident-pill">{{ $incident->is_active ? 'Activo' : 'Inactivo' }}</span>@if($incident->archived_at)<span class="incident-pill">Archivado</span>@endif</div>
                </div>
            </section>
            <section class="incident-section">
                <h2>Información de quien reporta</h2>
                <dl class="incident-details incident-grid three">
                    <div><dt>Nombre completo</dt><dd>{{ $incident->reporter_name }}</dd></div>
                    <div><dt>Cargo</dt><dd>{{ $incident->reporter_position }}</dd></div>
                    <div><dt>Área / dependencia</dt><dd>{{ $incident->area?->name }}</dd></div>
                </dl>
            </section>
            <section class="incident-section">
                <h2>Ubicación del incidente</h2>
                <dl class="incident-details incident-grid"><div><dt>Piso o zona</dt><dd>{{ $incident->zone?->name ?? 'Sin especificar' }}</dd></div><div><dt>Ubicación exacta</dt><dd>{{ $incident->location }}</dd></div></dl>
            </section>
            <section class="incident-section">
                <h2>Detalles del incidente</h2>
                <dl class="incident-details incident-grid">
                    <div><dt>Tipo de falla</dt><dd>{{ $incident->failure?->name }}</dd></div>
                    <div><dt>¿El incidente afecta la operación?</dt><dd>{{ $incident->impact?->name }}</dd></div>
                    <div class="incident-full"><dt>Descripción del problema</dt><dd class="incident-description">{{ $incident->description }}</dd></div>
                    <div class="incident-full"><dt>Equipos o activos afectados</dt><dd>{{ $incident->affected_assets ?: 'Sin especificar' }}</dd></div>
                    <div class="incident-full"><dt>Fotos o documentos de evidencia</dt><dd>
                        @include('incidents.evidence')
                    </dd></div>
                </dl>
            </section>
            <section class="incident-section">
                <h2>Prioridad y seguimiento</h2>
                <dl class="incident-details incident-grid">
                    <div><dt>Prioridad sugerida</dt><dd>{{ $incident->priority?->name }}</dd></div>
                    <div><dt>Estado de atención</dt><dd>{{ \App\Models\Incident::STATUSES[$incident->status] }}</dd></div>
                    <div><dt>Generado por</dt><dd>{{ $incident->creator?->name }} {{ $incident->creator?->last_name }}</dd></div>
                    <div><dt>Fecha de registro</dt><dd>{{ $incident->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Última actualización</dt><dd>{{ $incident->updated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>
                    @if($incident->archived_at)<div><dt>Fecha de archivo</dt><dd>{{ $incident->archived_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</dd></div>@endif
                </dl>
            </section>
        </div>
    </div>
</x-layouts.app>
