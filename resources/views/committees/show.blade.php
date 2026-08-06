<x-layouts.app title="{{ $committee->title }} | {{ config('app.name') }}" heading="{{ $committee->title }}" subheading="Detalle del comite">
    <div style="display:grid;gap:20px;grid-template-columns:minmax(0,2fr) minmax(280px,1fr)">
        <div style="display:flex;flex-direction:column;gap:20px">
            <div class="card" style="padding:24px">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:18px">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;background:rgba(18,63,110,0.06);color:#123f6e">
                            <i data-lucide="calendar-days" style="width:12px;height:12px"></i> {{ $committee->committee_date->format('d/m/Y') }}
                        </span>
                        <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;background:{{ $committee->status === 'active' ? 'rgba(5,150,105,0.08)' : 'rgba(148,163,184,0.12)' }};color:{{ $committee->status_color }}">
                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $committee->status_color }}"></span> {{ $committee->status_label }}
                        </span>
                    </div>
                    <div style="display:flex;gap:6px">
                        @if(auth()->user()->hasPermission('committees.edit'))
                            <a href="{{ route('committees.edit', $committee) }}" class="btn-secondary" style="padding:7px 14px;font-size:12px">
                                <i data-lucide="pencil" style="width:14px;height:14px"></i> Editar
                            </a>
                        @endif
                    </div>
                </div>

                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 10px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="file-text" style="width:16px;height:16px;color:#123f6e"></i> Relatos
                </h3>
                <div style="font-size:14px;color:#475569;line-height:1.7;white-space:pre-line">{{ $committee->summary }}</div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px">
            <div class="card" style="padding:20px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="users-round" style="width:16px;height:16px;color:#059669"></i> Integrantes
                </h3>
                <div style="display:flex;flex-direction:column;gap:10px">
                    @foreach($committee->members as $member)
                        <div style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:10px;background:rgba(18,63,110,0.02)">
                            <div style="width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:rgba(18,63,110,0.06);font-size:11px;font-weight:600;color:#123f6e;flex-shrink:0">{{ $member->initials }}</div>
                            <div style="min-width:0">
                                <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0">{{ $member->name }} {{ $member->last_name }}</p>
                                <p style="font-size:11px;color:#94a3b8;margin:2px 0 0">{{ $member->email }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card" style="padding:20px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="info" style="width:16px;height:16px;color:#123f6e"></i> Informacion
                </h3>
                <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
                    <div style="display:flex;justify-content:space-between;gap:12px">
                        <span style="color:#94a3b8">Creado por</span>
                        <span style="font-weight:500;color:#1e293b;text-align:right">{{ $committee->creator->name ?? 'Sistema' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;gap:12px">
                        <span style="color:#94a3b8">Creado</span>
                        <span style="font-weight:500;color:#1e293b;text-align:right">{{ $committee->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;gap:12px">
                        <span style="color:#94a3b8">Actualizado</span>
                        <span style="font-weight:500;color:#1e293b;text-align:right">{{ $committee->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;gap:12px">
                        <span style="color:#94a3b8">Integrantes</span>
                        <span style="font-weight:500;color:#1e293b;text-align:right">{{ $committee->members->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:20px">
        <a href="{{ route('committees.index') }}" class="btn-secondary">
            <i data-lucide="arrow-left" style="width:16px;height:16px"></i> Volver a comites
        </a>
    </div>

    <style>
        @media (max-width: 980px) {
            div[style*="grid-template-columns:minmax(0,2fr)"] { grid-template-columns: 1fr !important; }
        }
    </style>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
