<x-layouts.app title="Tareas | {{ config('app.name') }}" heading="Tareas" subheading="Gestion y asignacion de tareas por grupo de trabajo">
    <div style="margin-bottom:20px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar tarea..." style="padding:9px 14px;border-radius:10px;border:1px solid rgba(18,63,110,0.12);background:white;font-size:13px;width:220px;outline:none">
            <select name="group_id" style="padding:9px 14px;border-radius:10px;border:1px solid rgba(18,63,110,0.12);background:white;font-size:13px;color:#475569;outline:none;cursor:pointer">
                <option value="">Todos los grupos</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}" {{ request('group_id')==$g->id?'selected':'' }}>{{ $g->name }}</option>
                @endforeach
            </select>
            <select name="status" style="padding:9px 14px;border-radius:10px;border:1px solid rgba(18,63,110,0.12);background:white;font-size:13px;color:#475569;outline:none;cursor:pointer">
                <option value="">Todos los estados</option>
                <option value="pendiente" {{ request('status')=='pendiente'?'selected':'' }}>Pendiente</option>
                <option value="asignada" {{ request('status')=='asignada'?'selected':'' }}>Asignada</option>
                <option value="en_progreso" {{ request('status')=='en_progreso'?'selected':'' }}>En progreso</option>
                <option value="bloqueada" {{ request('status')=='bloqueada'?'selected':'' }}>Bloqueada</option>
                <option value="en_revision" {{ request('status')=='en_revision'?'selected':'' }}>En revision</option>
                <option value="finalizada" {{ request('status')=='finalizada'?'selected':'' }}>Finalizada</option>
                <option value="cancelada" {{ request('status')=='cancelada'?'selected':'' }}>Cancelada</option>
                <option value="archivada" {{ request('status')=='archivada'?'selected':'' }}>Archivada</option>
            </select>
            <select name="priority" style="padding:9px 14px;border-radius:10px;border:1px solid rgba(18,63,110,0.12);background:white;font-size:13px;color:#475569;outline:none;cursor:pointer">
                <option value="">Todas las prioridades</option>
                <option value="baja" {{ request('priority')=='baja'?'selected':'' }}>Baja</option>
                <option value="media" {{ request('priority')=='media'?'selected':'' }}>Media</option>
                <option value="alta" {{ request('priority')=='alta'?'selected':'' }}>Alta</option>
                <option value="urgente" {{ request('priority')=='urgente'?'selected':'' }}>Urgente</option>
            </select>
            <button type="submit" class="btn-secondary" style="padding:9px 16px;font-size:13px">
                <i data-lucide="search" style="width:14px;height:14px"></i> Buscar
            </button>
            @if(request()->hasAny(['search','status','priority','group_id']))
                <a href="{{ route('tasks.index') }}" class="btn-secondary" style="padding:9px 16px;font-size:13px;color:#dc2626">
                    <i data-lucide="x" style="width:14px;height:14px"></i> Limpiar
                </a>
            @endif
        </form>
        @if(auth()->user()->hasPermission('group_tasks.create'))
        <a href="{{ route('tasks.create') }}" class="btn-primary">
            <i data-lucide="plus" style="width:16px;height:16px"></i> Nueva tarea
        </a>
        @endif
    </div>

    @if(session('success'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
    @endif

    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:13px">
            <thead>
                <tr>
                    <th style="text-align:left;padding:12px 16px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Tarea</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Grupo</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Asignados</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Fecha fin</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Progreso</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Estado</th>
                    <th style="text-align:left;padding:12px 16px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr style="transition:background 0.15s" onmouseover="this.style.background='rgba(18,63,110,0.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:14px 16px;border-bottom:1px solid rgba(18,63,110,0.04)">
                            <div style="display:flex;align-items:center;gap:10px">
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $task->priority_color }};flex-shrink:0" title="{{ ucfirst($task->priority) }}"></span>
                                <div>
                                    <a href="{{ route('tasks.show', $task) }}" style="font-weight:600;color:#1e293b;text-decoration:none">{{ $task->title }}</a>
                                    @if($task->isDelayed())
                                        <span style="display:inline-flex;align-items:center;gap:4px;margin-left:6px;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:600;color:#ef4444;background:rgba(239,68,68,0.08)">
                                            <i data-lucide="alert-triangle" style="width:10px;height:10px"></i> Retrasada
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 16px;border-bottom:1px solid rgba(18,63,110,0.04)">
                            <span style="display:inline-flex;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:500;color:#123f6e;background:rgba(18,63,110,0.06)">{{ $task->group->name ?? '—' }}</span>
                        </td>
                        <td style="padding:14px 16px;border-bottom:1px solid rgba(18,63,110,0.04)">
                            <div style="display:flex;flex-wrap:wrap;gap:4px">
                                @foreach($task->assignees->take(3) as $a)
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;color:#475569;background:rgba(18,63,110,0.04)">{{ $a->name }}</span>
                                @endforeach
                                @if($task->assignees->count() > 3)
                                    <span style="padding:2px 8px;border-radius:20px;font-size:11px;color:#94a3b8;background:rgba(18,63,110,0.04)">+{{ $task->assignees->count() - 3 }}</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding:14px 16px;border-bottom:1px solid rgba(18,63,110,0.04);color:{{ $task->isDelayed() ? '#ef4444' : '#64748b' }};font-weight:{{ $task->isDelayed() ? '600' : '400' }}">{{ $task->end_date->format('d/m/Y') }}</td>
                        <td style="padding:14px 16px;border-bottom:1px solid rgba(18,63,110,0.04)">
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="flex:1;max-width:80px;height:6px;border-radius:3px;background:rgba(18,63,110,0.06);overflow:hidden">
                                    <div style="height:100%;width:{{ $task->progress }}%;border-radius:3px;background:{{ $task->progress >= 100 ? '#059669' : ($task->progress >= 50 ? '#6366f1' : '#f59e0b') }};transition:width 0.3s"></div>
                                </div>
                                <span style="font-size:12px;font-weight:600;color:#475569;min-width:32px">{{ $task->progress }}%</span>
                            </div>
                        </td>
                        <td style="padding:14px 16px;border-bottom:1px solid rgba(18,63,110,0.04)">
                            <span style="display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;color:white;background:{{ $task->status_color }}">{{ $task->status_label }}</span>
                        </td>
                        <td style="padding:14px 16px;border-bottom:1px solid rgba(18,63,110,0.04)">
                            <div style="display:flex;gap:6px">
                                <a href="{{ route('tasks.show', $task) }}" style="width:32px;height:32px;border-radius:8px;display:grid;place-items:center;background:rgba(18,63,110,0.04);color:#123f6e;text-decoration:none;transition:background 0.15s" onmouseover="this.style.background='rgba(18,63,110,0.1)'" onmouseout="this.style.background='rgba(18,63,110,0.04)'" title="Ver">
                                    <i data-lucide="eye" style="width:15px;height:15px"></i>
                                </a>
                                @if(auth()->user()->hasPermission('group_tasks.update'))
                                <a href="{{ route('tasks.edit', $task) }}" style="width:32px;height:32px;border-radius:8px;display:grid;place-items:center;background:rgba(18,63,110,0.04);color:#6366f1;text-decoration:none;transition:background 0.15s" onmouseover="this.style.background='rgba(18,63,110,0.1)'" onmouseout="this.style.background='rgba(18,63,110,0.04)'" title="Editar">
                                    <i data-lucide="pencil" style="width:15px;height:15px"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermission('group_tasks.archive'))
                                <form method="POST" action="{{ route('tasks.archive', $task) }}" onsubmit="return confirm('Archivar esta tarea?')">
                                    @csrf
                                    <button type="submit" style="width:32px;height:32px;border-radius:8px;display:grid;place-items:center;background:rgba(245,158,11,0.04);color:#f59e0b;border:none;cursor:pointer;transition:background 0.15s" onmouseover="this.style.background='rgba(245,158,11,0.1)'" onmouseout="this.style.background='rgba(245,158,11,0.04)'" title="Archivar">
                                        <i data-lucide="archive" style="width:15px;height:15px"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:40px 16px;text-align:center;color:#94a3b8">
                            <div style="display:flex;flex-direction:column;align-items:center;gap:8px">
                                <i data-lucide="list-checks" style="width:32px;height:32px;color:#cbd5e1"></i>
                                No hay tareas registradas
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px">{{ $tasks->links() }}</div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
