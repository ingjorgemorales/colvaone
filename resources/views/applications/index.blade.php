<x-layouts.app title="Aplicativos | {{ config('app.name') }}" heading="Aplicativos" subheading="Directorio de aplicaciones y plataformas de Colvatel">
    <style>
        .application-filters { display:grid; grid-template-columns:minmax(220px,1fr) 150px auto auto; gap:10px; align-items:end; }
        @media (max-width: 1100px) { .application-filters { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width: 680px) { .application-filters { grid-template-columns:1fr; } }
    </style>

    <div class="card" style="padding:20px;margin-bottom:20px">
        <form method="GET" action="{{ route('applications.index') }}" class="application-filters">
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre del aplicativo..." class="input-field">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Estado</label>
                <select name="status" class="input-field">
                    <option value="">Todos</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="padding:10px 16px">
                <i data-lucide="search" style="width:16px;height:16px"></i> Filtrar
            </button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('applications.index') }}" class="btn-secondary" style="padding:10px 16px;color:#dc2626">
                    <i data-lucide="x" style="width:16px;height:16px"></i>
                </a>
            @endif
        </form>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <p style="font-size:13px;color:#94a3b8;margin:0">{{ $applications->total() }} aplicativos registrados</p>
        @if(auth()->user()->hasPermission('applications.create'))
            <a href="{{ route('applications.create') }}" class="btn-primary">
                <i data-lucide="plus" style="width:16px;height:16px"></i> Nuevo aplicativo
            </a>
        @endif
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
    @endif

    <div class="card" style="overflow:hidden">
        <div style="overflow-x:auto">
            <table style="width:100%;font-size:14px;border-collapse:collapse">
                <thead>
                    <tr style="border-bottom:1px solid rgba(18,63,110,0.06)">
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Aplicativo</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Descripcion</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Ruta</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Estado</th>
                        <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $application)
                        <tr style="border-bottom:1px solid rgba(18,63,110,0.04);transition:background 0.2s" onmouseover="this.style.background='rgba(18,63,110,0.02)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:14px 16px">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div style="width:34px;height:34px;border-radius:10px;display:grid;place-items:center;background:rgba(18,63,110,0.06);flex-shrink:0">
                                        <i data-lucide="blocks" style="width:16px;height:16px;color:#123f6e"></i>
                                    </div>
                                    <div style="min-width:0">
                                        <p style="font-weight:600;color:#1e293b;margin:0">{{ $application->name }}</p>
                                        <p style="font-size:12px;color:#94a3b8;margin:2px 0 0">Creado por {{ $application->creator->name ?? 'Sistema' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:14px 16px;color:#64748b;max-width:320px">
                                <div style="font-size:13px;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                                    {{ $application->description ?: '-' }}
                                </div>
                            </td>
                            <td style="padding:14px 16px;max-width:240px">
                                <a href="{{ $application->url }}" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:#123f6e;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%">
                                    <i data-lucide="link" style="width:12px;height:12px;flex-shrink:0"></i> {{ $application->host }}
                                </a>
                            </td>
                            <td style="padding:14px 16px">
                                <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:{{ $application->status === 'active' ? 'rgba(5,150,105,0.08)' : 'rgba(148,163,184,0.12)' }};color:{{ $application->status_color }}">
                                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $application->status_color }}"></span> {{ $application->status_label }}
                                </span>
                            </td>
                            <td style="padding:14px 16px">
                                <div style="display:flex;align-items:center;gap:4px;justify-content:flex-end">
                                    @if($application->status === 'active')
                                        <a href="{{ $application->url }}" target="_blank" rel="noopener noreferrer" title="Abrir aplicativo" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#123f6e;text-decoration:none;background:rgba(18,63,110,0.04)">
                                            <i data-lucide="external-link" style="width:14px;height:14px"></i>
                                        </a>
                                    @else
                                        <span title="Aplicativo inactivo" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#cbd5e1;background:rgba(18,63,110,0.02)">
                                            <i data-lucide="external-link" style="width:14px;height:14px"></i>
                                        </span>
                                    @endif
                                    @if(auth()->user()->hasPermission('applications.edit'))
                                        <a href="{{ route('applications.edit', $application) }}" title="Editar" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#6366f1;text-decoration:none;background:rgba(18,63,110,0.04)">
                                            <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('applications.toggle'))
                                        <form method="POST" action="{{ route('applications.toggle', $application) }}" style="margin:0">
                                            @csrf
                                            <button type="submit" title="{{ $application->status === 'active' ? 'Inactivar' : 'Activar' }}" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:none;background:rgba(18,63,110,0.04);color:{{ $application->status === 'active' ? '#94a3b8' : '#059669' }};cursor:pointer">
                                                <i data-lucide="{{ $application->status === 'active' ? 'power-off' : 'power' }}" style="width:14px;height:14px"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:48px 16px;text-align:center">
                                <div style="width:48px;height:48px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.04);margin:0 auto 12px">
                                    <i data-lucide="blocks" style="width:24px;height:24px;color:#cbd5e1"></i>
                                </div>
                                <p style="font-size:14px;color:#94a3b8;margin:0">No hay aplicativos registrados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:16px">{{ $applications->withQueryString()->links() }}</div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
