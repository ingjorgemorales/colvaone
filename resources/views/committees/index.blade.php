<x-layouts.app title="Comites | {{ config('app.name') }}" heading="Comites" subheading="Registro de comites, integrantes y relatos">
    <style>
        .committee-filters { display:grid; grid-template-columns:minmax(220px,1fr) 150px 150px 150px auto auto; gap:10px; align-items:end; }
        @media (max-width: 1100px) { .committee-filters { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width: 680px) { .committee-filters { grid-template-columns:1fr; } }
    </style>

    <div class="card" style="padding:20px;margin-bottom:20px">
        <form method="GET" action="{{ route('committees.index') }}" class="committee-filters">
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Comite o relato..." class="input-field">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-field">
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-field">
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
            @if(request()->hasAny(['search','date_from','date_to','status']))
                <a href="{{ route('committees.index') }}" class="btn-secondary" style="padding:10px 16px;color:#dc2626">
                    <i data-lucide="x" style="width:16px;height:16px"></i>
                </a>
            @endif
        </form>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <p style="font-size:13px;color:#94a3b8;margin:0">{{ $committees->total() }} comites registrados</p>
        @if(auth()->user()->hasPermission('committees.create'))
            <a href="{{ route('committees.create') }}" class="btn-primary">
                <i data-lucide="plus" style="width:16px;height:16px"></i> Nuevo comite
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
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Comite</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Fecha</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Integrantes</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Relatos</th>
                        <th style="padding:12px 16px;text-align:left;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Estado</th>
                        <th style="padding:12px 16px;text-align:right;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($committees as $committee)
                        <tr style="border-bottom:1px solid rgba(18,63,110,0.04);transition:background 0.2s" onmouseover="this.style.background='rgba(18,63,110,0.02)'" onmouseout="this.style.background='transparent'">
                            <td style="padding:14px 16px">
                                <a href="{{ route('committees.show', $committee) }}" style="font-weight:600;color:#1e293b;text-decoration:none">{{ $committee->title }}</a>
                                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0">Creado por {{ $committee->creator->name ?? 'Sistema' }}</p>
                            </td>
                            <td style="padding:14px 16px;color:#64748b;white-space:nowrap">{{ $committee->committee_date->format('d/m/Y') }}</td>
                            <td style="padding:14px 16px;color:#64748b">
                                {{ $committee->members->take(2)->pluck('name')->join(', ') ?: '-' }}
                                @if($committee->members->count() > 2)
                                    <span style="padding:2px 8px;border-radius:20px;font-size:11px;color:#94a3b8;background:rgba(18,63,110,0.04)">+{{ $committee->members->count() - 2 }}</span>
                                @endif
                            </td>
                            <td style="padding:14px 16px;color:#64748b;max-width:340px">{{ Str::limit($committee->summary, 110) }}</td>
                            <td style="padding:14px 16px">
                                <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:{{ $committee->status === 'active' ? 'rgba(5,150,105,0.08)' : 'rgba(148,163,184,0.12)' }};color:{{ $committee->status_color }}">
                                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $committee->status_color }}"></span> {{ $committee->status_label }}
                                </span>
                            </td>
                            <td style="padding:14px 16px">
                                <div style="display:flex;align-items:center;gap:4px;justify-content:flex-end">
                                    <a href="{{ route('committees.show', $committee) }}" title="Ver" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#123f6e;text-decoration:none;background:rgba(18,63,110,0.04)">
                                        <i data-lucide="eye" style="width:14px;height:14px"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('committees.edit'))
                                        <a href="{{ route('committees.edit', $committee) }}" title="Editar" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#6366f1;text-decoration:none;background:rgba(18,63,110,0.04)">
                                            <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('committees.toggle'))
                                        <form method="POST" action="{{ route('committees.toggle', $committee) }}" style="margin:0">
                                            @csrf
                                            <button type="submit" title="{{ $committee->status === 'active' ? 'Desactivar' : 'Activar' }}" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:none;background:rgba(18,63,110,0.04);color:{{ $committee->status === 'active' ? '#f59e0b' : '#059669' }};cursor:pointer">
                                                <i data-lucide="{{ $committee->status === 'active' ? 'pause' : 'play' }}" style="width:14px;height:14px"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:48px 16px;text-align:center">
                                <div style="width:48px;height:48px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.04);margin:0 auto 12px">
                                    <i data-lucide="users-round" style="width:24px;height:24px;color:#cbd5e1"></i>
                                </div>
                                <p style="font-size:14px;color:#94a3b8;margin:0">No hay comites registrados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:16px">{{ $committees->withQueryString()->links() }}</div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
