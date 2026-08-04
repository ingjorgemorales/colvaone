<x-layouts.app title="Grupos de trabajo | {{ config('app.name') }}" heading="Grupos de trabajo" subheading="Gestion de grupos y equipos">
    <div style="margin-bottom:20px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar grupo..." style="padding:9px 14px;border-radius:10px;border:1px solid rgba(18,63,110,0.12);background:white;font-size:13px;width:220px;outline:none">
            <select name="status" style="padding:9px 14px;border-radius:10px;border:1px solid rgba(18,63,110,0.12);background:white;font-size:13px;color:#475569;outline:none;cursor:pointer">
                <option value="">Todos los estados</option>
                <option value="active" {{ request('status')=='active'?'selected':'' }}>Activo</option>
                <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactivo</option>
            </select>
            <button type="submit" class="btn-secondary" style="padding:9px 16px;font-size:13px">
                <i data-lucide="search" style="width:14px;height:14px"></i> Buscar
            </button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('groups.index') }}" class="btn-secondary" style="padding:9px 16px;font-size:13px;color:#dc2626">
                    <i data-lucide="x" style="width:14px;height:14px"></i> Limpiar
                </a>
            @endif
        </form>
        @if(auth()->user()->hasPermission('groups.create'))
        <a href="{{ route('groups.create') }}" class="btn-primary">
            <i data-lucide="plus" style="width:16px;height:16px"></i> Nuevo grupo
        </a>
        @endif
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#dc2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.1)">{{ session('error') }}</div>
    @endif

    <div style="display:grid;gap:16px;grid-template-columns:repeat(auto-fill,minmax(340px,1fr))">
        @forelse($groups as $group)
            <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
                <div style="display:flex;align-items:flex-start;justify-content:space-between">
                    <div style="display:flex;align-items:center;gap:10px">
                        <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:{{ $group->status==='active' ? 'rgba(5,150,105,0.06)' : 'rgba(148,163,184,0.1)' }}">
                            <i data-lucide="users-round" style="width:20px;height:20px;color:{{ $group->status==='active' ? '#059669' : '#94a3b8' }}"></i>
                        </div>
                        <div>
                            <a href="{{ route('groups.show', $group) }}" style="font-size:15px;font-weight:600;color:#1e293b;text-decoration:none">{{ $group->name }}</a>
                            <p style="font-size:12px;color:#94a3b8;margin:2px 0 0">{{ $group->description ? Str::limit($group->description, 50) : 'Sin descripcion' }}</p>
                        </div>
                    </div>
                    <span style="display:inline-flex;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;color:{{ $group->status==='active' ? '#059669' : '#94a3b8' }};background:{{ $group->status==='active' ? 'rgba(5,150,105,0.08)' : 'rgba(148,163,184,0.1)' }}">{{ $group->status==='active' ? 'Activo' : 'Inactivo' }}</span>
                </div>

                <div style="display:flex;gap:16px;font-size:12px;color:#64748b">
                    <div style="display:flex;align-items:center;gap:4px">
                        <i data-lucide="user-check" style="width:14px;height:14px;color:#123f6e"></i>
                        {{ $group->managers->pluck('name')->join(', ') ?: 'Sin gerente' }}
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;text-align:center">
                    <div style="padding:8px;border-radius:8px;background:rgba(18,63,110,0.02)">
                        <div style="font-size:18px;font-weight:700;color:#1e293b">{{ $group->member_count }}</div>
                        <div style="font-size:10px;color:#94a3b8">Miembros</div>
                    </div>
                    <div style="padding:8px;border-radius:8px;background:rgba(245,158,11,0.04)">
                        <div style="font-size:18px;font-weight:700;color:#f59e0b">{{ $group->pending_task_count }}</div>
                        <div style="font-size:10px;color:#94a3b8">Pendientes</div>
                    </div>
                    <div style="padding:8px;border-radius:8px;background:rgba(99,102,241,0.04)">
                        <div style="font-size:18px;font-weight:700;color:#6366f1">{{ $group->in_progress_task_count }}</div>
                        <div style="font-size:10px;color:#94a3b8">En curso</div>
                    </div>
                    <div style="padding:8px;border-radius:8px;background:rgba(5,150,105,0.04)">
                        <div style="font-size:18px;font-weight:700;color:#059669">{{ $group->completed_task_count }}</div>
                        <div style="font-size:10px;color:#94a3b8">Finalizadas</div>
                    </div>
                </div>

                <div style="display:flex;gap:6px;padding-top:12px;border-top:1px solid rgba(18,63,110,0.06)">
                    <a href="{{ route('groups.show', $group) }}" class="btn-secondary" style="flex:1;justify-content:center;padding:8px;font-size:12px">
                        <i data-lucide="eye" style="width:14px;height:14px"></i> Ver
                    </a>
                    @if(auth()->user()->hasPermission('groups.update'))
                    <a href="{{ route('groups.edit', $group) }}" class="btn-secondary" style="flex:1;justify-content:center;padding:8px;font-size:12px">
                        <i data-lucide="pencil" style="width:14px;height:14px"></i> Editar
                    </a>
                    @endif
                    @if(auth()->user()->hasPermission('groups.disable') && $group->slug !== 'superadmin')
                    <form method="POST" action="{{ route('groups.toggle', $group) }}" style="flex:1">
                        @csrf
                        <button type="submit" class="btn-secondary" style="width:100%;justify-content:center;padding:8px;font-size:12px;color:{{ $group->status==='active' ? '#f59e0b' : '#059669' }}">
                            <i data-lucide="{{ $group->status==='active' ? 'pause' : 'play' }}" style="width:14px;height:14px"></i> {{ $group->status==='active' ? 'Desactivar' : 'Activar' }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card" style="padding:40px;text-align:center;color:#94a3b8;grid-column:1/-1">
                <div style="display:flex;flex-direction:column;align-items:center;gap:8px">
                    <i data-lucide="users-round" style="width:32px;height:32px;color:#cbd5e1"></i>
                    No hay grupos de trabajo registrados
                </div>
            </div>
        @endforelse
    </div>

    <div style="margin-top:16px">{{ $groups->links() }}</div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
