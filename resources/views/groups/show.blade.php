<x-layouts.app title="{{ $group->name }} | {{ config('app.name') }}" heading="{{ $group->name }}" subheading="Detalle del grupo de trabajo">
    <div style="display:grid;gap:20px;grid-template-columns:2fr 1fr">
        <div style="display:flex;flex-direction:column;gap:20px">
            <!-- Stats -->
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px">
                <div class="card" style="padding:16px;text-align:center">
                    <div style="font-size:24px;font-weight:700;color:#f59e0b">{{ $stats['pending'] }}</div>
                    <div style="font-size:11px;color:#94a3b8">Pendientes</div>
                </div>
                <div class="card" style="padding:16px;text-align:center">
                    <div style="font-size:24px;font-weight:700;color:#6366f1">{{ $stats['in_progress'] }}</div>
                    <div style="font-size:11px;color:#94a3b8">En curso</div>
                </div>
                <div class="card" style="padding:16px;text-align:center">
                    <div style="font-size:24px;font-weight:700;color:#ef4444">{{ $stats['blocked'] + $stats['delayed'] }}</div>
                    <div style="font-size:11px;color:#94a3b8">Bloqueadas/Vencidas</div>
                </div>
                <div class="card" style="padding:16px;text-align:center">
                    <div style="font-size:24px;font-weight:700;color:#059669">{{ $stats['completed'] }}</div>
                    <div style="font-size:11px;color:#94a3b8">Finalizadas</div>
                </div>
            </div>

            <!-- Tasks -->
            <div class="card" style="padding:24px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                    <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0;display:flex;align-items:center;gap:8px">
                        <i data-lucide="list-checks" style="width:16px;height:16px;color:#123f6e"></i>
                        Tareas del grupo ({{ $group->tasks->count() }})
                    </h3>
                    @if(auth()->user()->hasPermission('group_tasks.create'))
                    <a href="{{ route('tasks.create') }}?group_id={{ $group->id }}" class="btn-primary" style="padding:7px 14px;font-size:12px">
                        <i data-lucide="plus" style="width:14px;height:14px"></i> Nueva tarea
                    </a>
                    @endif
                </div>

                @if($group->tasks->count() > 0)
                <div style="overflow-x:auto">
                    <table style="width:100%;border-collapse:separate;border-spacing:0;font-size:12px">
                        <thead>
                            <tr>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Tarea</th>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Asignada a</th>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Fecha fin</th>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Progreso</th>
                                <th style="text-align:left;padding:10px 12px;font-weight:600;color:#475569;border-bottom:2px solid rgba(18,63,110,0.06)">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->tasks->take(10) as $task)
                            <tr style="transition:background 0.15s" onmouseover="this.style.background='rgba(18,63,110,0.02)'" onmouseout="this.style.background='transparent'">
                                <td style="padding:10px 12px;border-bottom:1px solid rgba(18,63,110,0.04)">
                                    <a href="{{ route('tasks.show', $task) }}" style="font-weight:600;color:#1e293b;text-decoration:none">{{ Str::limit($task->title, 40) }}</a>
                                    @if($task->isDelayed())
                                        <span style="display:inline-flex;align-items:center;gap:3px;margin-left:4px;padding:1px 5px;border-radius:4px;font-size:9px;font-weight:600;color:#ef4444;background:rgba(239,68,68,0.08)">!</span>
                                    @endif
                                </td>
                                <td style="padding:10px 12px;border-bottom:1px solid rgba(18,63,110,0.04);color:#64748b">{{ $task->assignees->pluck('name')->join(', ') ?: '—' }}</td>
                                <td style="padding:10px 12px;border-bottom:1px solid rgba(18,63,110,0.04);color:{{ $task->isDelayed() ? '#ef4444' : '#64748b' }}">{{ $task->end_date->format('d/m') }}</td>
                                <td style="padding:10px 12px;border-bottom:1px solid rgba(18,63,110,0.04)">
                                    <div style="display:flex;align-items:center;gap:6px">
                                        <div style="flex:1;max-width:60px;height:4px;border-radius:2px;background:rgba(18,63,110,0.06);overflow:hidden">
                                            <div style="height:100%;width:{{ $task->progress }}%;background:{{ $task->progress >= 100 ? '#059669' : ($task->progress >= 50 ? '#6366f1' : '#f59e0b') }}"></div>
                                        </div>
                                        <span style="font-size:11px;font-weight:600;color:#475569">{{ $task->progress }}%</span>
                                    </div>
                                </td>
                                <td style="padding:10px 12px;border-bottom:1px solid rgba(18,63,110,0.04)">
                                    <span style="display:inline-flex;padding:2px 8px;border-radius:5px;font-size:10px;font-weight:600;color:white;background:{{ $task->status_color }}">{{ $task->status_label }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($group->tasks->count() > 10)
                    <a href="{{ route('tasks.index') }}?group_id={{ $group->id }}" style="display:block;text-align:center;margin-top:12px;font-size:12px;color:#123f6e;font-weight:500">Ver todas las tareas ({{ $group->tasks->count() }})</a>
                @endif
                @else
                    <p style="font-size:13px;color:#94a3b8;text-align:center;padding:20px">No hay tareas en este grupo</p>
                @endif
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px">
            <!-- Info -->
            <div class="card" style="padding:20px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="info" style="width:16px;height:16px;color:#123f6e"></i>
                    Informacion
                </h3>
                <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Estado</span>
                        <span style="font-weight:500;color:{{ $group->status==='active' ? '#059669' : '#94a3b8' }}">{{ $group->status==='active' ? 'Activo' : 'Inactivo' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Creado por</span>
                        <span style="font-weight:500;color:#1e293b">{{ $group->creator->name }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Fecha creacion</span>
                        <span style="font-weight:500;color:#1e293b">{{ $group->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Tareas totales</span>
                        <span style="font-weight:500;color:#1e293b">{{ $stats['total'] }}</span>
                    </div>
                </div>
                @if($group->description)
                <div style="margin-top:14px;padding-top:14px;border-top:1px solid rgba(18,63,110,0.06)">
                    <p style="font-size:12px;color:#94a3b8;margin:0 0 4px">Descripcion</p>
                    <p style="font-size:13px;color:#475569;margin:0;line-height:1.5">{{ $group->description }}</p>
                </div>
                @endif
            </div>

            <!-- Managers -->
            <div class="card" style="padding:20px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="user-check" style="width:16px;height:16px;color:#059669"></i>
                    Gerentes
                </h3>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @forelse($group->managers as $m)
                        <div style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;background:rgba(5,150,105,0.03)">
                            <div style="width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:rgba(5,150,105,0.08);font-size:11px;font-weight:600;color:#059669">{{ strtoupper(substr($m->name,0,1).substr($m->last_name??'',0,1)) }}</div>
                            <div>
                                <span style="font-size:13px;font-weight:500;color:#1e293b">{{ $m->name }} {{ $m->last_name }}</span>
                                <span style="font-size:11px;color:#94a3b8;display:block">{{ $m->email }}</span>
                            </div>
                        </div>
                    @empty
                        <p style="font-size:12px;color:#94a3b8">Sin gerentes asignados</p>
                    @endforelse
                </div>
            </div>

            <!-- Members -->
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                    <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0;display:flex;align-items:center;gap:8px">
                        <i data-lucide="users" style="width:16px;height:16px;color:#6366f1"></i>
                        Integrantes ({{ $group->activeMembers->count() }})
                    </h3>
                    @if(auth()->user()->hasPermission('groups.manage_members'))
                    <button onclick="document.getElementById('addMemberModal').style.display='grid'" style="width:28px;height:28px;border-radius:8px;border:1px solid rgba(18,63,110,0.12);background:white;display:grid;place-items:center;cursor:pointer;color:#123f6e">
                        <i data-lucide="user-plus" style="width:14px;height:14px"></i>
                    </button>
                    @endif
                </div>
                <div style="display:flex;flex-direction:column;gap:6px">
                    @forelse($group->activeMembers as $member)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px;border-radius:8px;background:rgba(18,63,110,0.02)">
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:rgba(18,63,110,0.06);font-size:10px;font-weight:600;color:#123f6e">{{ strtoupper(substr($member->name,0,1).substr($member->last_name??'',0,1)) }}</div>
                                <div>
                                    <span style="font-size:12px;font-weight:500;color:#1e293b">{{ $member->name }}</span>
                                    <span style="display:inline-block;padding:1px 6px;border-radius:4px;font-size:9px;font-weight:600;margin-left:6px;color:{{ $member->pivot->member_type==='manager' ? '#059669' : ($member->pivot->member_type==='supervisor' ? '#f59e0b' : '#6366f1') }};background:{{ $member->pivot->member_type==='manager' ? 'rgba(5,150,105,0.08)' : ($member->pivot->member_type==='supervisor' ? 'rgba(245,158,11,0.08)' : 'rgba(99,102,241,0.08)') }}">{{ ucfirst($member->pivot->member_type) }}</span>
                                </div>
                            </div>
                            @if(auth()->user()->hasPermission('groups.manage_members'))
                            <form method="POST" action="{{ route('groups.members.remove', [$group, $member]) }}" onsubmit="return confirm('Remover a {{ $member->name }} del grupo?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="width:24px;height:24px;border-radius:6px;border:none;background:rgba(239,68,68,0.04);color:#ef4444;cursor:pointer;display:grid;place-items:center" title="Remover">
                                    <i data-lucide="x" style="width:12px;height:12px"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    @empty
                        <p style="font-size:12px;color:#94a3b8">No hay integrantes</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Add Member Modal -->
    @if(auth()->user()->hasPermission('groups.manage_members'))
    <div id="addMemberModal" style="position:fixed;inset:0;z-index:9999;display:none;place-items:center;background:rgba(18,63,110,0.3);backdrop-filter:blur(4px)" onclick="this.style.display='none'">
        <div class="card" style="padding:28px;max-width:420px;width:90%" onclick="event.stopPropagation()">
            <h3 style="font-size:16px;font-weight:600;color:#1e293b;margin:0 0 16px">Agregar integrante</h3>
            <form method="POST" action="{{ route('groups.members.add', $group) }}" style="display:flex;flex-direction:column;gap:14px">
                @csrf
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Usuario</label>
                    <select name="user_id" required class="input-field" style="cursor:pointer">
                        <option value="">Seleccionar usuario...</option>
                        @foreach($allUsers as $u)
                            @if(!$group->users->contains($u->id))
                            <option value="{{ $u->id }}">{{ $u->name }} {{ $u->last_name }} ({{ $u->role_label }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Rol en el grupo</label>
                    <select name="member_type" class="input-field" style="cursor:pointer">
                        <option value="member">Integrante</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="manager">Gerente</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end">
                    <button type="button" onclick="document.getElementById('addMemberModal').style.display='none'" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div style="margin-top:20px">
        <a href="{{ route('groups.index') }}" class="btn-secondary" style="font-size:13px">
            <i data-lucide="arrow-left" style="width:14px;height:14px"></i> Volver a grupos
        </a>
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
