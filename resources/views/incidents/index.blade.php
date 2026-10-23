<x-layouts.app title="Gestión de incidentes | {{ config('app.name') }}" heading="Gestión de incidentes" subheading="Registro y seguimiento de incidentes">
    @include('incidents.styles')
    <div class="incident-page incident-list">
        @include('incidents.errors')
        <form method="GET" action="{{ route('incidents.index') }}" class="incident-list-toolbar">
            <div class="incident-filter-primary">
                <div><label class="incident-sr-only" for="search">Buscar incidente</label><input id="search" name="search" value="{{ request('search') }}" class="incident-filter-field" placeholder="Buscar incidente..." maxlength="150"></div>
                <div><label class="incident-sr-only" for="status">Estado de atención</label><select id="status" name="status" class="incident-filter-field">
                    <option value="">Todos los estados</option>
                    @foreach(\App\Models\Incident::STATUSES as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
                </select></div>
                <div><label class="incident-sr-only" for="priority_id">Prioridad</label><select id="priority_id" name="priority_id" class="incident-filter-field">
                    <option value="">Prioridades: todas</option>
                    @foreach($options->get('priority', collect()) as $option)<option value="{{ $option->id }}" @selected(request('priority_id') == $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactivo)' }}</option>@endforeach
                </select></div>
                <button class="btn-secondary incident-search-button"><i data-lucide="search" style="width:14px;height:14px"></i> Buscar</button>
            </div>
            <div class="incident-buttons">
                @if(auth()->user()->hasPermission('incidents.settings'))
                    <a class="btn-secondary" href="{{ route('incidents.settings') }}"><i data-lucide="settings" style="width:16px;height:16px"></i> Ajustes</a>
                @endif
                @if(auth()->user()->hasPermission('incidents.create'))
                    <a class="btn-primary" href="{{ route('incidents.create') }}"><i data-lucide="plus" style="width:16px;height:16px"></i> Nuevo incidente</a>
                @endif
            </div>
            <div class="incident-filter-secondary">
            @foreach(['area' => 'Todas', 'failure' => 'Todos'] as $field => $label)
                <div><label for="{{ $field }}_id">{{ $field === 'area' ? 'Área' : 'Tipo de falla' }}</label><select name="{{ $field }}_id" id="{{ $field }}_id" class="incident-filter-field">
                    <option value="">{{ $label }}</option>
                    @foreach($options->get($field, collect()) as $option)<option value="{{ $option->id }}" @selected(request($field.'_id') == $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactivo)' }}</option>@endforeach
                </select></div>
            @endforeach
            <div><label for="from">Fecha inicial</label><input id="from" name="from" type="date" value="{{ request('from') }}" class="incident-filter-field"></div>
            <div><label for="to">Fecha final</label><input id="to" name="to" type="date" value="{{ request('to') }}" class="incident-filter-field"></div>
            <div><label for="activity">Actividad</label><select id="activity" name="activity" class="incident-filter-field">
                <option value="">Todas</option><option value="active" @selected(request('activity') === 'active')>Activo</option><option value="inactive" @selected(request('activity') === 'inactive')>Inactivo</option>
            </select></div>
            <div><label for="archive">Archivo</label><select id="archive" name="archive" class="incident-filter-field">
                <option value="current" @selected(request('archive', 'current') === 'current')>Sin archivar</option><option value="archived" @selected(request('archive') === 'archived')>Archivados</option><option value="all" @selected(request('archive') === 'all')>Todos</option>
            </select></div>
            @if(request()->hasAny(['search', 'status', 'priority_id', 'area_id', 'failure_id', 'from', 'to', 'activity', 'archive']))
                <a class="btn-secondary incident-search-button incident-clear-button" href="{{ route('incidents.index') }}"><i data-lucide="x" style="width:14px;height:14px"></i> Limpiar</a>
            @endif
            </div>
        </form>
        <div class="incident-count">{{ $incidents->total() }} incidentes registrados</div>
        <div class="incident-table-wrap">
            <table class="incident-table">
                <thead><tr><th>Ticket / fecha</th><th>Incidente</th><th>Reportado por</th><th>Ubicación</th><th>Prioridad</th><th>Estado de atención</th><th>Actividad</th><th>Acciones</th></tr></thead>
                <tbody>
                    @forelse($incidents as $incident)
                        <tr>
                            <td><a class="incident-ticket" href="{{ route('incidents.show', $incident) }}">{{ $incident->ticket }}</a><div class="incident-muted">{{ $incident->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</div></td>
                            <td class="incident-summary"><strong>{{ $incident->failure?->name }}</strong><div class="incident-muted">{{ \Illuminate\Support\Str::limit($incident->description, 110) }}</div></td>
                            <td>{{ $incident->reporter_name }}<div class="incident-muted">{{ $incident->area?->name }}</div></td>
                            <td class="incident-summary">{{ \Illuminate\Support\Str::limit($incident->location, 70) }}</td>
                            <td><span class="incident-pill">{{ $incident->priority?->name }}</span></td>
                            <td><span class="incident-pill {{ $incident->status }}">{{ \App\Models\Incident::STATUSES[$incident->status] }}</span></td>
                            <td><span class="incident-pill">{{ $incident->is_active ? 'Activo' : 'Inactivo' }}</span>@if($incident->archived_at)<div class="incident-muted">Archivado</div>@endif</td>
                            <td>@include('incidents.actions')</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;padding:40px;color:#64748b">No hay incidentes para los filtros seleccionados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:18px">{{ $incidents->links() }}</div>
    </div>
</x-layouts.app>
