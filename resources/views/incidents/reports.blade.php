<x-layouts.app title="Reportes de incidentes | {{ config('app.name') }}" heading="Gestión de incidentes" subheading="Reportes, estadísticas y métricas operativas">
    @include('incidents.styles')
    <div class="incident-page" style="max-width:1440px">

        <!-- Toolbar -->
        <div class="incident-toolbar">
            <a class="btn-secondary" href="{{ route('incidents.index') }}">
                <i data-lucide="arrow-left" style="width:16px;height:16px"></i> Volver al listado
            </a>
            <div class="incident-buttons">
                <a class="btn-secondary" href="{{ route('incidents.reports.excel', request()->all()) }}">
                    <i data-lucide="file-spreadsheet" style="width:16px;height:16px;color:#047857"></i> Exportar a Excel (.xlsx)
                </a>
                <a class="btn-secondary" download href="{{ route('incidents.reports.pdf', request()->all()) }}">
                    <i data-lucide="file-down" style="width:16px;height:16px;color:#b91c1c"></i> Descargar informe PDF
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card" style="padding:22px;margin-bottom:24px;border:1px solid #cbd8e5;border-radius:10px;background:#fff">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;flex-wrap:wrap">
                <h2 style="font-size:15px;font-weight:700;color:#123f6e;margin:0;display:flex;align-items:center;gap:8px">
                    <i data-lucide="filter" style="width:18px;height:18px;color:#0f766e"></i>
                    Filtros del reporte
                </h2>
                @if(request()->anyFilled(['date_from', 'date_to', 'zone_id', 'failure_type', 'priority_id', 'impact_id', 'responsible_user_id', 'status']))
                    <a class="btn-secondary" href="{{ route('incidents.reports') }}" style="color:#dc2626;font-size:12px;padding:6px 12px">
                        <i data-lucide="x" style="width:13px;height:13px"></i> Limpiar filtros
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ route('incidents.reports') }}">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;align-items:end">
                    <div>
                        <label for="date_from" class="incident-field-label">Fecha desde</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="input-field">
                    </div>
                    <div>
                        <label for="date_to" class="incident-field-label">Fecha hasta</label>
                        <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="input-field">
                    </div>
                    <div>
                        <label for="zone_id" class="incident-field-label">Sede</label>
                        <select id="zone_id" name="zone_id" class="input-field">
                            <option value="">Todas las sedes</option>
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}" @selected(request('zone_id') == $z->id)>{{ $z->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="failure_type" class="incident-field-label">Clasificación</label>
                        <select id="failure_type" name="failure_type" class="input-field">
                            <option value="">Todas las clasificaciones</option>
                            <option value="risk" @selected(request('failure_type') === 'risk')>Riesgo priorizado</option>
                            <option value="general" @selected(request('failure_type') === 'general')>Incidente general</option>
                        </select>
                    </div>
                    <div>
                        <label for="priority_id" class="incident-field-label">Prioridad</label>
                        <select id="priority_id" name="priority_id" class="input-field">
                            <option value="">Todas las prioridades</option>
                            @foreach($priorities as $p)
                                <option value="{{ $p->id }}" @selected(request('priority_id') == $p->id)>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="impact_id" class="incident-field-label">¿Afecta la operación?</label>
                        <select id="impact_id" name="impact_id" class="input-field">
                            <option value="">Todas las opciones</option>
                            @foreach($impacts as $imp)
                                <option value="{{ $imp->id }}" @selected(request('impact_id') == $imp->id)>{{ $imp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="responsible_user_id" class="incident-field-label">Responsable asignado</label>
                        <select id="responsible_user_id" name="responsible_user_id" class="input-field">
                            <option value="">Todos los responsables</option>
                            @foreach($activeUsers as $u)
                                <option value="{{ $u->id }}" @selected(request('responsible_user_id') == $u->id)>{{ $u->name }} {{ $u->last_name }} ({{ $u->position ?: 'Sin cargo' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="incident-field-label">Estado de actividad</label>
                        <select id="status" name="status" class="input-field">
                            <option value="">Todos</option>
                            <option value="activo" @selected(request('status') === 'activo')>Activos</option>
                            <option value="inactivo" @selected(request('status') === 'inactivo')>Inactivos</option>
                            <option value="archivado" @selected(request('status') === 'archivado')>Archivados</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:9px 18px;font-size:13px">
                            <i data-lucide="search" style="width:15px;height:15px"></i> Generar reporte
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- KPI Metrics Grid -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px;margin-bottom:24px">
            <div class="card" style="padding:18px;border-left:4px solid #123f6e;background:#fff">
                <span class="incident-muted" style="text-transform:uppercase;font-weight:700;letter-spacing:0.4px">Total de Reportes</span>
                <div style="font-size:30px;font-weight:700;color:#123f6e;margin-top:4px">{{ $stats['total'] }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">Incidentes en el período filtrado</div>
            </div>

            <div class="card" style="padding:18px;border-left:4px solid #b45309;background:#fff">
                <span class="incident-muted" style="text-transform:uppercase;font-weight:700;letter-spacing:0.4px">Riesgos Priorizados</span>
                <div style="font-size:30px;font-weight:700;color:#b45309;margin-top:4px">{{ $stats['total_risks'] }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">
                    {{ $stats['total'] > 0 ? round(($stats['total_risks'] / $stats['total']) * 100, 1) : 0 }}% del total
                </div>
            </div>

            <div class="card" style="padding:18px;border-left:4px solid #0284c7;background:#fff">
                <span class="incident-muted" style="text-transform:uppercase;font-weight:700;letter-spacing:0.4px">Incidentes Generales</span>
                <div style="font-size:30px;font-weight:700;color:#0284c7;margin-top:4px">{{ $stats['total_generals'] }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">
                    {{ $stats['total'] > 0 ? round(($stats['total_generals'] / $stats['total']) * 100, 1) : 0 }}% del total
                </div>
            </div>

            <div class="card" style="padding:18px;border-left:4px solid #dc2626;background:#fff">
                <span class="incident-muted" style="text-transform:uppercase;font-weight:700;letter-spacing:0.4px">Afectan la Operación</span>
                <div style="font-size:30px;font-weight:700;color:#dc2626;margin-top:4px">{{ $stats['affects_operation'] }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">
                    {{ $stats['total'] > 0 ? round(($stats['affects_operation'] / $stats['total']) * 100, 1) : 0 }}% con impacto operativo
                </div>
            </div>

            <div class="card" style="padding:18px;border-left:4px solid #059669;background:#fff">
                <span class="incident-muted" style="text-transform:uppercase;font-weight:700;letter-spacing:0.4px">Estado de Registros</span>
                <div style="font-size:16px;font-weight:700;color:#1e293b;margin-top:8px">
                    <span style="color:#059669">{{ $stats['total_active'] }} activos</span> &middot;
                    <span style="color:#64748b">{{ $stats['total_archived'] }} archivados</span>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:4px">{{ $stats['total_inactive'] }} inactivos</div>
            </div>
        </div>

        <!-- Statistical Distributions Panels -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(420px,1fr));gap:20px;margin-bottom:24px">

            <!-- Distribution by Zone (Sede) -->
            <div class="incident-detail-panel">
                <h3 class="incident-panel-heading">
                    <i data-lucide="map-pin" aria-hidden="true"></i> Distribución por Sede
                </h3>
                <div class="incident-panel-body" style="padding:16px">
                    <table style="width:100%;border-collapse:collapse;font-size:13px">
                        <thead>
                            <tr style="border-bottom:2px solid #e2e8f0;text-align:left;color:#64748b">
                                <th style="padding:8px 6px">Sede</th>
                                <th style="padding:8px 6px;text-align:center">Total</th>
                                <th style="padding:8px 6px;text-align:center">Afecta Operación</th>
                                <th style="padding:8px 6px;text-align:right">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['by_zone'] as $z)
                                @php $pct = $stats['total'] > 0 ? round(($z['count'] / $stats['total']) * 100, 1) : 0; @endphp
                                <tr style="border-bottom:1px solid #f1f5f9">
                                    <td style="padding:9px 6px;font-weight:600;color:#1e293b">{{ $z['name'] }}</td>
                                    <td style="padding:9px 6px;text-align:center;font-weight:700;color:#123f6e">{{ $z['count'] }}</td>
                                    <td style="padding:9px 6px;text-align:center;color:#dc2626;font-weight:600">{{ $z['affects_count'] }}</td>
                                    <td style="padding:9px 6px;text-align:right;color:#64748b">
                                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                                            <span>{{ $pct }}%</span>
                                            <div style="width:40px;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                                                <div style="width:{{ $pct }}%;height:100%;background:#123f6e"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center;padding:16px;color:#94a3b8">No hay registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Distribution by Priority -->
            <div class="incident-detail-panel">
                <h3 class="incident-panel-heading">
                    <i data-lucide="alert-triangle" aria-hidden="true"></i> Distribución por Prioridad
                </h3>
                <div class="incident-panel-body" style="padding:16px">
                    <table style="width:100%;border-collapse:collapse;font-size:13px">
                        <thead>
                            <tr style="border-bottom:2px solid #e2e8f0;text-align:left;color:#64748b">
                                <th style="padding:8px 6px">Prioridad</th>
                                <th style="padding:8px 6px;text-align:center">Total</th>
                                <th style="padding:8px 6px;text-align:right">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['by_priority'] as $p)
                                @php $pct = $stats['total'] > 0 ? round(($p['count'] / $stats['total']) * 100, 1) : 0; @endphp
                                <tr style="border-bottom:1px solid #f1f5f9">
                                    <td style="padding:9px 6px">
                                        <span class="incident-pill {{ $p['tone'] }}">{{ $p['name'] }}</span>
                                    </td>
                                    <td style="padding:9px 6px;text-align:center;font-weight:700;color:#1e293b">{{ $p['count'] }}</td>
                                    <td style="padding:9px 6px;text-align:right;color:#64748b">
                                        <div style="display:flex;align-items:center;justify-content:flex-end;gap:8px">
                                            <span>{{ $pct }}%</span>
                                            <div style="width:50px;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden">
                                                <div style="width:{{ $pct }}%;height:100%;background:#0f766e"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:16px;color:#94a3b8">No hay registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Failures / Risks -->
            <div class="incident-detail-panel">
                <h3 class="incident-panel-heading">
                    <i data-lucide="clipboard-list" aria-hidden="true"></i> Fallas y Riesgos Frecuentes
                </h3>
                <div class="incident-panel-body" style="padding:16px">
                    <table style="width:100%;border-collapse:collapse;font-size:13px">
                        <thead>
                            <tr style="border-bottom:2px solid #e2e8f0;text-align:left;color:#64748b">
                                <th style="padding:8px 6px">Falla / Riesgo</th>
                                <th style="padding:8px 6px;text-align:center">Tipo</th>
                                <th style="padding:8px 6px;text-align:right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['by_failure'] as $f)
                                <tr style="border-bottom:1px solid #f1f5f9">
                                    <td style="padding:9px 6px;font-weight:600;color:#1e293b">{{ $f['name'] }}</td>
                                    <td style="padding:9px 6px;text-align:center">
                                        <span class="incident-pill {{ $f['is_risk'] ? 'tone-warning' : '' }}" style="font-size:10px">
                                            {{ $f['is_risk'] ? 'Riesgo' : 'General' }}
                                        </span>
                                    </td>
                                    <td style="padding:9px 6px;text-align:right;font-weight:700;color:#123f6e">{{ $f['count'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:16px;color:#94a3b8">No hay registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Responsibles -->
            <div class="incident-detail-panel">
                <h3 class="incident-panel-heading">
                    <i data-lucide="users" aria-hidden="true"></i> Carga por Responsable Asignado
                </h3>
                <div class="incident-panel-body" style="padding:16px">
                    <table style="width:100%;border-collapse:collapse;font-size:13px">
                        <thead>
                            <tr style="border-bottom:2px solid #e2e8f0;text-align:left;color:#64748b">
                                <th style="padding:8px 6px">Responsable</th>
                                <th style="padding:8px 6px">Cargo</th>
                                <th style="padding:8px 6px;text-align:right">Incidentes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stats['by_responsible'] as $resp)
                                <tr style="border-bottom:1px solid #f1f5f9">
                                    <td style="padding:9px 6px;font-weight:600;color:#1e293b">{{ $resp['name'] }}</td>
                                    <td style="padding:9px 6px;color:#64748b;font-size:12px">{{ $resp['position'] }}</td>
                                    <td style="padding:9px 6px;text-align:right;font-weight:700;color:#123f6e">{{ $resp['count'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align:center;padding:16px;color:#94a3b8">No hay registros</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Detailed Filtered Incidents Table -->
        <div class="card" style="padding:22px;border:1px solid #cbd8e5;border-radius:10px;background:#fff">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px">
                <h2 style="font-size:16px;font-weight:700;color:#123f6e;margin:0">
                    Listado Detallado de Incidentes en el Reporte ({{ $incidents->total() }})
                </h2>
                <div style="font-size:13px;color:#64748b">
                    Mostrando página {{ $incidents->currentPage() }} de {{ $incidents->lastPage() }}
                </div>
            </div>

            <div class="incident-table-wrap">
                <table class="incident-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Fecha</th>
                            <th>Clasificación</th>
                            <th>Tipo de Falla / Riesgo</th>
                            <th>Reportado Por</th>
                            <th>Sede</th>
                            <th>Responsable</th>
                            <th>Prioridad</th>
                            <th>¿Afecta?</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incidents as $inc)
                            @php
                                $isRisk = ($inc->failure_type === 'risk');
                                $affects = str_contains(mb_strtolower($inc->impact?->name ?? ''), 'sí') || str_contains(mb_strtolower($inc->impact?->name ?? ''), 'si');
                            @endphp
                            <tr>
                                <td>
                                    <a class="incident-ticket" href="{{ route('incidents.show', $inc) }}">{{ $inc->ticket }}</a>
                                </td>
                                <td style="white-space:nowrap;color:#64748b">
                                    {{ $inc->created_at ? $inc->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') : '' }}
                                </td>
                                <td>
                                    <span class="incident-pill {{ $isRisk ? 'tone-warning' : '' }}">
                                        {{ $isRisk ? 'Riesgo' : 'General' }}
                                    </span>
                                </td>
                                <td style="font-weight:600;color:#1e293b">{{ $inc->failure?->name }}</td>
                                <td>
                                    <div>{{ $inc->reporter_name }}</div>
                                    <div style="font-size:11px;color:#64748b">{{ $inc->reporter_position }}</div>
                                </td>
                                <td>{{ $inc->zone?->name ?? 'Sin sede' }}</td>
                                <td>
                                    <div>{{ $inc->responsible_name ?: ($inc->responsibleUser ? trim($inc->responsibleUser->name . ' ' . $inc->responsibleUser->last_name) : 'Sin asignar') }}</div>
                                    <div style="font-size:11px;color:#64748b">{{ $inc->responsible_position }}</div>
                                </td>
                                <td>
                                    <span class="incident-pill {{ $inc->priority_tone }}">{{ $inc->priority?->name }}</span>
                                </td>
                                <td>
                                    <span class="incident-pill {{ $affects ? 'tone-danger' : '' }}">
                                        {{ $inc->impact?->name ?? 'Sin impacto' }}
                                    </span>
                                </td>
                                <td>
                                    <a class="incident-icon" href="{{ route('incidents.show', $inc) }}" title="Ver detalle">
                                        <i data-lucide="eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" style="text-align:center;padding:24px;color:#94a3b8">
                                    No se encontraron incidentes que coincidan con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px">
                {{ $incidents->links() }}
            </div>
        </div>

    </div>
</x-layouts.app>
