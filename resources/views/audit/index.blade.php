<x-layouts.app title="Auditoria | {{ config('app.name') }}" heading="Auditoria del sistema" subheading="Registro de todas las actividades de la aplicacion">
    <div class="card" style="padding:20px;margin-bottom:20px">
        <form method="GET" action="{{ route('audit.index') }}" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
            <div style="flex:1;min-width:200px">
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Usuario, evento, descripcion..." class="input-field">
            </div>
            <div style="min-width:160px">
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Tipo de evento</label>
                <select name="event" class="input-field">
                    <option value="">Todos</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type }}" {{ request('event') === $type ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:140px">
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Estado</label>
                <select name="status" class="input-field">
                    <option value="">Todos</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Exitoso</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Fallido</option>
                </select>
            </div>
            <div style="min-width:140px">
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field">
            </div>
            <div style="min-width:140px">
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field">
            </div>
            <div style="display:flex;gap:8px">
                <button type="submit" class="btn-primary" style="padding:10px 16px">
                    <i data-lucide="search" style="width:16px;height:16px"></i> Filtrar
                </button>
                <a href="{{ route('audit.index') }}" class="btn-secondary" style="padding:10px 16px">
                    <i data-lucide="x" style="width:16px;height:16px"></i>
                </a>
            </div>
        </form>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <p style="font-size:13px;color:#94a3b8;margin:0">{{ $events->total() }} registros de auditoria</p>
    </div>

    <div class="card" style="overflow:hidden">
        <div style="overflow-x:auto">
            <table style="width:100%;font-size:14px;border-collapse:collapse">
                <thead>
                    <tr style="border-bottom:1px solid rgba(18,63,110,0.06)">
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Fecha/Hora</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Usuario</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Evento</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Descripcion</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">IP</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr style="border-bottom:1px solid rgba(18,63,110,0.04);transition:background 0.2s" onmouseover="this.style.background='rgba(18,63,110,0.02)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 16px;font-size:13px;color:#64748b;white-space:nowrap">
                                {{ $event->occurred_at ? $event->occurred_at->format('d/m/Y H:i:s') : '-' }}
                            </td>
                            <td style="padding:12px 16px">
                                <div style="display:flex;align-items:center;gap:8px">
                                    @if($event->user)
                                        <div class="avatar" style="width:28px;height:28px;font-size:9px;border-radius:7px">{{ $event->user->initials }}</div>
                                        <div>
                                            <p style="font-weight:500;color:#1e293b;margin:0;font-size:13px">{{ $event->user->name }} {{ $event->user->last_name }}</p>
                                            <p style="font-size:11px;color:#94a3b8;margin:1px 0 0">{{ $event->email }}</p>
                                        </div>
                                    @else
                                        <span style="font-size:13px;color:#64748b">{{ $event->email }}</span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding:12px 16px">
                                @php
                                    $eventColors = [
                                        'login' => ['bg' => 'rgba(5,150,105,0.08)', 'text' => '#059669'],
                                        'logout' => ['bg' => 'rgba(100,116,139,0.08)', 'text' => '#64748b'],
                                        'login_failed' => ['bg' => 'rgba(220,38,38,0.08)', 'text' => '#dc2626'],
                                        'password_reset' => ['bg' => 'rgba(245,158,11,0.08)', 'text' => '#d97706'],
                                        'password_changed' => ['bg' => 'rgba(245,158,11,0.08)', 'text' => '#d97706'],
                                        'user_created' => ['bg' => 'rgba(5,150,105,0.08)', 'text' => '#059669'],
                                        'user_updated' => ['bg' => 'rgba(18,63,110,0.08)', 'text' => '#123f6e'],
                                        'user_deleted' => ['bg' => 'rgba(220,38,38,0.08)', 'text' => '#dc2626'],
                                        'user_toggled' => ['bg' => 'rgba(245,158,11,0.08)', 'text' => '#d97706'],
                                        'role_created' => ['bg' => 'rgba(5,150,105,0.08)', 'text' => '#059669'],
                                        'role_updated' => ['bg' => 'rgba(18,63,110,0.08)', 'text' => '#123f6e'],
                                        'role_deleted' => ['bg' => 'rgba(220,38,38,0.08)', 'text' => '#dc2626'],
                                        'profile_updated' => ['bg' => 'rgba(18,63,110,0.08)', 'text' => '#123f6e'],
                                    ];
                                    $color = $eventColors[$event->event] ?? ['bg' => 'rgba(100,116,139,0.08)', 'text' => '#64748b'];
                                @endphp
                                <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:{{ $color['bg'] }};color:{{ $color['text'] }}">{{ ucwords(str_replace('_', ' ', $event->event)) }}</span>
                            </td>
                            <td style="padding:12px 16px;font-size:13px;color:#64748b;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $event->reason ?: '-' }}</td>
                            <td style="padding:12px 16px;font-size:13px;color:#94a3b8">{{ $event->ip_address }}</td>
                            <td style="padding:12px 16px">
                                @if($event->successful)
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#059669"><i data-lucide="check-circle" style="width:14px;height:14px"></i> Exitoso</span>
                                @else
                                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#dc2626"><i data-lucide="x-circle" style="width:14px;height:14px"></i> Fallido</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:48px 16px;text-align:center">
                                <div style="width:48px;height:48px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.04);margin:0 auto 12px">
                                    <i data-lucide="scan-search" style="width:24px;height:24px;color:#cbd5e1"></i>
                                </div>
                                <p style="font-size:14px;color:#94a3b8;margin:0">No hay registros de auditoria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:16px">{{ $events->withQueryString()->links() }}</div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
