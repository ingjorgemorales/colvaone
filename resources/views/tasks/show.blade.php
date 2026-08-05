<x-layouts.app title="{{ $task->title }} | {{ config('app.name') }}" heading="{{ $task->title }}" subheading="Detalle de tarea">
    <div style="display:grid;gap:20px;grid-template-columns:2fr 1fr">
        <div style="display:flex;flex-direction:column;gap:20px">
            <div class="card" style="padding:24px">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px">
                    <div style="display:flex;align-items:center;gap:10px">
                        <span style="width:10px;height:10px;border-radius:50%;background:{{ $task->priority_color }}"></span>
                        <span style="display:inline-flex;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;color:white;background:{{ $task->status_color }}">{{ $task->status_label }}</span>
                        @if($task->isDelayed())
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;color:#ef4444;background:rgba(239,68,68,0.08)">
                                <i data-lucide="alert-triangle" style="width:12px;height:12px"></i> Retrasada
                            </span>
                        @endif
                    </div>
                    <div style="display:flex;gap:6px">
                        @if(auth()->user()->hasPermission('group_tasks.update') && !in_array($task->status, ['finalizada','cancelada','archivada']))
                        <a href="{{ route('tasks.edit', $task) }}" class="btn-secondary" style="padding:7px 14px;font-size:12px">
                            <i data-lucide="pencil" style="width:14px;height:14px"></i> Editar
                        </a>
                        @endif
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;font-size:13px">
                    <div>
                        <span style="color:#94a3b8">Grupo</span>
                        <p style="font-weight:500;color:#123f6e;margin:2px 0 0">{{ $task->group->name ?? '—' }}</p>
                    </div>
                    <div>
                        <span style="color:#94a3b8">Area</span>
                        <p style="font-weight:500;color:#1e293b;margin:2px 0 0">{{ $task->area ?? 'Sin area' }}</p>
                    </div>
                    <div>
                        <span style="color:#94a3b8">Prioridad</span>
                        <p style="font-weight:500;color:{{ $task->priority_color }};margin:2px 0 0;text-transform:capitalize">{{ $task->priority }}</p>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;font-size:13px">
                    <div>
                        <span style="color:#94a3b8">Fecha inicio</span>
                        <p style="font-weight:500;color:#1e293b;margin:2px 0 0">{{ $task->start_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <span style="color:#94a3b8">Fecha fin</span>
                        <p style="font-weight:500;color:{{ $task->isDelayed() ? '#ef4444' : '#1e293b' }};margin:2px 0 0">{{ $task->end_date->format('d/m/Y') }}</p>
                    </div>
                </div>

                @if($task->description)
                    <div style="margin-bottom:16px">
                        <span style="font-size:13px;color:#94a3b8">Descripcion</span>
                        <p style="font-size:13px;color:#475569;margin:4px 0 0;line-height:1.6">{!! nl2br(e($task->description)) !!}</p>
                    </div>
                @endif

                @if($task->observations)
                    <div style="padding:12px 16px;border-radius:10px;font-size:13px;color:#475569;background:rgba(245,158,11,0.04);border:1px solid rgba(245,158,11,0.1)">
                        <div style="display:flex;align-items:flex-start;gap:8px">
                            <i data-lucide="file-text" style="width:14px;height:14px;flex-shrink:0;margin-top:2px;color:#f59e0b"></i>
                            <div>
                                <span style="font-weight:600;color:#92400e">Observaciones: </span>
                                {!! nl2br(e($task->observations)) !!}
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="message-square" style="width:16px;height:16px;color:#6366f1"></i>
                    Comentarios ({{ $task->comments->count() }})
                </h3>

                <div style="margin-bottom:20px">
                    <form method="POST" action="{{ route('tasks.comments.add', $task) }}" style="display:flex;gap:8px">
                        @csrf
                        <input name="comment" type="text" required placeholder="Escribe un comentario..." class="input-field" style="flex:1">
                        <button type="submit" class="btn-primary" style="padding:9px 16px">
                            <i data-lucide="send" style="width:14px;height:14px"></i>
                        </button>
                    </form>
                </div>

                <div style="display:flex;flex-direction:column;gap:12px">
                    @forelse($task->comments->sortByDesc('created_at') as $comment)
                        <div style="display:flex;gap:10px;padding:12px;border-radius:10px;background:rgba(18,63,110,0.02)">
                            <div style="width:32px;height:32px;border-radius:50%;display:grid;place-items:center;background:rgba(18,63,110,0.06);font-size:11px;font-weight:600;color:#123f6e;flex-shrink:0">{{ strtoupper(substr($comment->user->name,0,1)) }}</div>
                            <div style="flex:1;min-width:0">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                                    <span style="font-size:13px;font-weight:600;color:#1e293b">{{ $comment->user->name }}</span>
                                    <span style="font-size:11px;color:#94a3b8">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p style="font-size:13px;color:#475569;margin:0">{{ $comment->comment }}</p>
                            </div>
                        </div>
                    @empty
                        <p style="font-size:13px;color:#94a3b8;text-align:center;padding:20px">No hay comentarios aun</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:20px">
            <div class="card" style="padding:20px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="trending-up" style="width:16px;height:16px;color:#059669"></i>
                    Progreso general
                </h3>
                <div style="text-align:center;margin-bottom:16px">
                    <div style="font-size:36px;font-weight:700;color:#1e293b">{{ $task->progress }}%</div>
                    <div style="width:100%;height:8px;border-radius:4px;background:rgba(18,63,110,0.06);margin-top:8px;overflow:hidden">
                        <div style="height:100%;width:{{ $task->progress }}%;border-radius:4px;background:{{ $task->progress >= 100 ? '#059669' : ($task->progress >= 50 ? '#6366f1' : '#f59e0b') }};transition:width 0.3s"></div>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px">
                    @foreach($task->assignees as $assignee)
                        <div style="padding:10px;border-radius:8px;background:rgba(18,63,110,0.02)">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
                                <span style="font-size:12px;font-weight:500;color:#1e293b">{{ $assignee->name }}</span>
                                <span style="font-size:11px;font-weight:600;color:{{ $assignee->pivot->progress >= 100 ? '#059669' : '#475569' }}">{{ $assignee->pivot->progress }}%</span>
                            </div>
                            <div style="width:100%;height:4px;border-radius:2px;background:rgba(18,63,110,0.06);overflow:hidden">
                                <div style="height:100%;width:{{ $assignee->pivot->progress }}%;border-radius:2px;background:{{ $assignee->pivot->progress >= 100 ? '#059669' : ($assignee->pivot->progress >= 50 ? '#6366f1' : '#f59e0b') }}"></div>
                            </div>
                            @if(auth()->user()->hasPermission('group_tasks.update_progress'))
                            <form method="POST" action="{{ route('tasks.progress.update', $task) }}" style="display:flex;gap:6px;margin-top:8px;align-items:center" x-data="{ val: {{ $assignee->pivot->progress }}, orig: {{ $assignee->pivot->progress }} }">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $assignee->id }}">
                                <input type="range" name="progress" min="{{ $assignee->pivot->progress }}" max="100" step="5" value="{{ $assignee->pivot->progress }}" x-model="val" style="flex:1;accent-color:#123f6e;cursor:pointer">
                                <span x-text="val + '%'" style="font-size:11px;font-weight:600;color:#475569;min-width:32px;text-align:right"></span>
                                <button type="submit" x-show="val != orig" x-transition style="padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;color:white;background:#059669;border:none;cursor:pointer;white-space:nowrap">Guardar</button>
                            </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card" style="padding:20px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="info" style="width:16px;height:16px;color:#123f6e"></i>
                    Informacion
                </h3>
                <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Grupo</span>
                        <span style="font-weight:500;color:#1e293b">{{ $task->group->name ?? '—' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Creada por</span>
                        <span style="font-weight:500;color:#1e293b">{{ $task->creator->name }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Creada</span>
                        <span style="font-weight:500;color:#1e293b">{{ $task->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Actualizada</span>
                        <span style="font-weight:500;color:#1e293b">{{ $task->updated_at->diffForHumans() }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:#94a3b8">Asignados</span>
                        <span style="font-weight:500;color:#1e293b">{{ $task->assignees->count() }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div style="margin-top:20px">
        <a href="{{ route('tasks.index') }}" class="btn-secondary" style="font-size:13px">
            <i data-lucide="arrow-left" style="width:14px;height:14px"></i> Volver a tareas
        </a>
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
