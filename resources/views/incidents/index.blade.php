<x-layouts.app title="Gestión de incidentes | {{ config('app.name') }}" heading="Gestión de incidentes" subheading="Registro y seguimiento de incidentes">
    @include('incidents.styles')
    <div class="incident-page incident-list">
        @include('incidents.errors')
        <form method="GET" action="{{ route('incidents.index') }}" class="incident-list-toolbar">
            <div class="incident-filter-primary">
                <div><label class="incident-sr-only" for="search">Buscar incidente</label><input id="search" name="search" value="{{ request('search') }}" class="incident-filter-field" placeholder="Buscar incidente..." maxlength="150"></div>
                <div><label class="incident-sr-only" for="status">Estado</label><select id="status" name="status" class="incident-filter-field">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Models\Incident::FILTER_STATES as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
                </select></div>
                <div><label class="incident-sr-only" for="priority_id">Prioridad</label><select id="priority_id" name="priority_id" class="incident-filter-field">
                    <option value="">Prioridades: todas</option>
                    @foreach($options->get('priority', collect()) as $option)<option value="{{ $option->id }}" @selected(request('priority_id') == $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactivo)' }}</option>@endforeach
                </select></div>
                <button class="btn-secondary incident-search-button"><i data-lucide="search" style="width:14px;height:14px"></i> Buscar</button>
                @if(request()->filled('search') || request()->filled('status') || request()->filled('priority_id'))
                    <a class="btn-secondary incident-search-button incident-clear-button" href="{{ route('incidents.index') }}"><i data-lucide="x" style="width:14px;height:14px"></i> Limpiar</a>
                @endif
            </div>
            <div class="incident-buttons">
                @if(auth()->user()->hasPermission('incidents.settings'))
                    <a class="btn-secondary" href="{{ route('incidents.settings') }}"><i data-lucide="settings" style="width:16px;height:16px"></i> Ajustes</a>
                @endif
                @if(auth()->user()->hasPermission('incidents.create'))
                    <a class="btn-primary" href="{{ route('incidents.create') }}"><i data-lucide="plus" style="width:16px;height:16px"></i> Nuevo incidente</a>
                @endif
            </div>
        </form>
        <div class="incident-count">{{ $incidents->total() }} {{ $incidents->total() === 1 ? 'incidente registrado' : 'incidentes registrados' }}</div>
        <div class="incident-cards">
            @forelse($incidents as $incident)
                <article class="incident-list-card" aria-labelledby="incident-title-{{ $incident->id }}">
                    <div class="incident-card-identity">
                        <a class="incident-ticket" href="{{ route('incidents.show', $incident) }}">{{ $incident->ticket }}</a>
                        <time class="incident-card-date" datetime="{{ $incident->created_at->toIso8601String() }}"><i data-lucide="calendar-days" aria-hidden="true"></i>{{ $incident->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</time>
                    </div>
                    <div class="incident-card-summary">
                        <h2 id="incident-title-{{ $incident->id }}" class="incident-card-title"><a href="{{ route('incidents.show', $incident) }}">{{ $incident->failure?->name ?? 'Incidente' }}</a></h2>
                        <p class="incident-card-description">{{ \Illuminate\Support\Str::limit($incident->description, 180) }}</p>
                    </div>
                    <dl class="incident-card-fields incident-details">
                        <div><dt>Reportado por</dt><dd>{{ $incident->reporter_name }}</dd></div>
                        <div><dt>Área / dependencia</dt><dd>{{ $incident->area?->name ?? 'Sin especificar' }}</dd></div>
                    </dl>
                    <dl class="incident-card-fields incident-details">
                        <div><dt>Ubicación</dt><dd>{{ \Illuminate\Support\Str::limit($incident->location, 100) }}</dd></div>
                        <div><dt>Prioridad</dt><dd><span class="incident-pill {{ $incident->priority_tone }}">{{ $incident->priority?->name }}</span></dd></div>
                    </dl>
                    <dl class="incident-card-state incident-details">
                        <div><dt>Estado de atención</dt><dd><span class="incident-pill {{ $incident->status }}">{{ \App\Models\Incident::STATUSES[$incident->status] }}</span></dd></div>
                    </dl>
                    <dl class="incident-card-activity-field incident-details">
                        <div><dt>Actividad</dt><dd class="incident-card-activity"><span class="incident-pill {{ $incident->is_active ? 'tone-success' : 'tone-warning' }}">{{ $incident->is_active ? 'Activo' : 'Inactivo' }}</span>@if($incident->archived_at)<span class="incident-pill">Archivado</span>@endif</dd></div>
                    </dl>
                    <div class="incident-card-actions" role="group" aria-label="Acciones de {{ $incident->ticket }}">@include('incidents.actions')</div>
                </article>
            @empty
                <div class="incident-empty" role="status"><i data-lucide="inbox" aria-hidden="true"></i><p>No hay incidentes para los filtros seleccionados.</p></div>
            @endforelse
        </div>
        <div style="margin-top:18px">{{ $incidents->links() }}</div>
    </div>
</x-layouts.app>
