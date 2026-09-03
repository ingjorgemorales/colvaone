<x-layouts.app title="Indicadores | {{ config('app.name') }}" heading="Indicadores" subheading="Ficha tecnica, metas y resultados por periodo">
    <style>
        .indicator-filters { display:grid; grid-template-columns:minmax(0,1fr) 170px auto auto; gap:10px; align-items:end; }
        @media (max-width: 760px) { .indicator-filters { grid-template-columns:1fr; } }

        /* Cada indicador es su propia tarjeta, con aire entre una y otra. */
        .ind-table { width:100%; font-size:14px; border-collapse:separate; border-spacing:0 12px; min-width:940px; }
        .ind-table thead th {
            padding:0 18px 2px; text-align:left; font-size:12px; font-weight:600;
            color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em;
        }
        .ind-table thead th:last-child { text-align:right; }
        .ind-table tbody tr {
            background:white;
            box-shadow:0 2px 10px rgba(18,63,110,0.05);
            transition:box-shadow 0.2s, transform 0.2s;
        }
        .ind-table tbody tr:hover { box-shadow:0 6px 22px rgba(18,63,110,0.10); transform:translateY(-1px); }
        .ind-table tbody td {
            padding:16px 18px; vertical-align:middle;
            border-top:1px solid rgba(18,63,110,0.05);
            border-bottom:1px solid rgba(18,63,110,0.05);
        }
        .ind-table tbody td:first-child { border-left:1px solid rgba(18,63,110,0.05); border-radius:12px 0 0 12px; }
        .ind-table tbody td:last-child  { border-right:1px solid rgba(18,63,110,0.05); border-radius:0 12px 12px 0; }
        .ind-table tbody tr.is-empty { box-shadow:none; }
        .ind-table tbody tr.is-empty:hover { box-shadow:none; transform:none; }
        .ind-table tbody tr.is-empty td { border:1px solid rgba(18,63,110,0.05); border-radius:12px; }
    </style>

    <div class="card" style="padding:20px;margin-bottom:8px">
        <form method="GET" action="{{ route('indicators.index') }}" class="indicator-filters">
            <div>
                <label style="display:block;font-size:12px;font-weight:500;color:#64748b;margin-bottom:4px">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre del indicador..." class="input-field">
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
                <a href="{{ route('indicators.index') }}" class="btn-secondary" style="padding:10px 16px;color:#dc2626">
                    <i data-lucide="x" style="width:16px;height:16px"></i>
                </a>
            @endif
        </form>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin:26px 0 14px">
        <p style="font-size:13px;color:#94a3b8;margin:0;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            {{ $indicators->total() }} {{ $indicators->total() === 1 ? 'indicador' : 'indicadores' }}
            @if($category)
                en
                <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;color:#123f6e;background:rgba(18,63,110,0.07)">
                    {{ \App\Models\Indicator::CATEGORIES[$category] }}
                    <a href="{{ route('indicators.index', request()->except(['categoria','page'])) }}" title="Quitar filtro" style="display:flex;color:#94a3b8">
                        <i data-lucide="x" style="width:12px;height:12px"></i>
                    </a>
                </span>
            @else
                registrados
            @endif
        </p>
        @if(auth()->user()->hasPermission('indicators.create'))
            <a href="{{ route('indicators.create') }}" class="btn-primary">
                <i data-lucide="plus" style="width:16px;height:16px"></i> Nuevo indicador
            </a>
        @endif
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
    @endif

    <div style="overflow-x:auto;padding-bottom:4px">
            <table class="ind-table">
                <thead>
                    <tr>
                        <th style="width:78px">ID</th>
                        <th>Nombre</th>
                        <th>Cumplimiento</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($indicators as $indicator)
                        @php $last = $indicator->latestResult; @endphp
                        <tr>
                            <td style="width:78px">
                                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:26px;padding:0 9px;border-radius:7px;font-size:12px;font-weight:700;font-variant-numeric:tabular-nums;color:#123f6e;background:rgba(18,63,110,0.06)">
                                    {{ $indicator->id }}
                                </span>
                            </td>
                            <td style="max-width:320px">
                                <a href="{{ route('indicators.show', $indicator) }}" style="font-weight:600;color:#1e293b;text-decoration:none">{{ $indicator->name }}</a>
                                <p style="font-size:12px;color:#94a3b8;margin:2px 0 0">
                                    Meta {{ $indicator->goal }}% &middot; {{ $indicator->frequency_label }}
                                    @if($indicator->category)
                                        &middot; <span style="color:#123f6e;font-weight:600">{{ $indicator->category_label }}</span>
                                    @endif
                                </p>
                            </td>
                            <td>
                                @if($last)
                                    <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;background:{{ $last->evaluation_background }};color:{{ $last->evaluation_color }}">
                                        <span style="width:7px;height:7px;border-radius:50%;background:{{ $last->evaluation_color }}"></span>
                                        {{ rtrim(rtrim(number_format((float) $last->compliance, 2, ',', '.'), '0'), ',') }}%
                                    </span>
                                    <p style="font-size:11px;color:#94a3b8;margin:3px 0 0">{{ $last->evaluation_label }}</p>
                                @else
                                    <span style="font-size:12px;color:#cbd5e1">Sin resultados</span>
                                @endif
                            </td>
                            <td style="color:#64748b">
                                {{ $indicator->responsible->name ?? '-' }} {{ $indicator->responsible->last_name ?? '' }}
                            </td>
                            <td>
                                <span style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:{{ $indicator->status === 'active' ? 'rgba(5,150,105,0.08)' : 'rgba(148,163,184,0.12)' }};color:{{ $indicator->status_color }}">
                                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $indicator->status_color }}"></span> {{ $indicator->status_label }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:4px;justify-content:flex-end">
                                    <a href="{{ route('indicators.show', $indicator) }}" title="Ver" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#123f6e;text-decoration:none;background:rgba(18,63,110,0.04)">
                                        <i data-lucide="eye" style="width:14px;height:14px"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('indicators.edit'))
                                        <a href="{{ route('indicators.edit', $indicator) }}" title="Editar ficha tecnica" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;color:#6366f1;text-decoration:none;background:rgba(18,63,110,0.04)">
                                            <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('indicators.toggle'))
                                        <form method="POST" action="{{ route('indicators.toggle', $indicator) }}" style="margin:0">
                                            @csrf
                                            <button type="submit" title="{{ $indicator->status === 'active' ? 'Inactivar' : 'Activar' }}" style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:none;background:rgba(18,63,110,0.04);color:{{ $indicator->status === 'active' ? '#94a3b8' : '#059669' }};cursor:pointer">
                                                <i data-lucide="{{ $indicator->status === 'active' ? 'power-off' : 'power' }}" style="width:14px;height:14px"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty">
                            <td colspan="6" style="padding:48px 16px;text-align:center">
                                <div style="width:48px;height:48px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.04);margin:0 auto 12px">
                                    <i data-lucide="chart-no-axes-combined" style="width:24px;height:24px;color:#cbd5e1"></i>
                                </div>
                                <p style="font-size:14px;color:#94a3b8;margin:0 0 4px">No hay indicadores registrados.</p>
                                @if(auth()->user()->hasPermission('indicators.create'))
                                    <p style="font-size:13px;color:#cbd5e1;margin:0">Crea el primero con el boton "Nuevo indicador".</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>

    <div style="margin-top:16px">{{ $indicators->withQueryString()->links() }}</div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
