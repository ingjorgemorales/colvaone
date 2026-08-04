<x-layouts.app title="Editar tarea | {{ config('app.name') }}" heading="Editar tarea" subheading="Actualizar informacion de la tarea">
    <div style="max-width:700px">
        <div class="card" style="padding:28px">
            <form method="POST" action="{{ route('tasks.update', $task) }}" style="display:flex;flex-direction:column;gap:18px">
                @csrf
                @method('PUT')

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Nombre de la tarea *</label>
                    <input name="title" type="text" value="{{ old('title', $task->title) }}" required class="input-field @error('title') {{ 'error-field' }} @enderror">
                    @error('title')
                        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;padding:8px 12px;border-radius:8px;font-size:12px;color:#dc2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.1)">
                            <i data-lucide="alert-circle" style="width:14px;height:14px;flex-shrink:0"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Descripcion</label>
                    <textarea name="description" rows="3" class="input-field" style="resize:vertical">{{ old('description', $task->description) }}</textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Grupo de trabajo *</label>
                        <select name="group_id" id="groupSelect" required class="input-field @error('group_id') {{ 'error-field' }} @enderror" style="cursor:pointer" onchange="loadGroupMembers(this.value)">
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ old('group_id', $task->group_id) == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                            @endforeach
                        </select>
                        @error('group_id')
                            <div style="display:flex;align-items:center;gap:6px;margin-top:6px;padding:8px 12px;border-radius:8px;font-size:12px;color:#dc2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.1)">
                                <i data-lucide="alert-circle" style="width:14px;height:14px;flex-shrink:0"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Prioridad *</label>
                        <select name="priority" required class="input-field" style="cursor:pointer">
                            <option value="baja" {{ old('priority', $task->priority)=='baja'?'selected':'' }}>Baja</option>
                            <option value="media" {{ old('priority', $task->priority)=='media'?'selected':'' }}>Media</option>
                            <option value="alta" {{ old('priority', $task->priority)=='alta'?'selected':'' }}>Alta</option>
                            <option value="urgente" {{ old('priority', $task->priority)=='urgente'?'selected':'' }}>Urgente</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Fecha de inicio *</label>
                        <input name="start_date" type="date" value="{{ old('start_date', $task->start_date->format('Y-m-d')) }}" required class="input-field @error('start_date') {{ 'error-field' }} @enderror">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Fecha de fin *</label>
                        <input name="end_date" type="date" value="{{ old('end_date', $task->end_date->format('Y-m-d')) }}" required class="input-field @error('end_date') {{ 'error-field' }} @enderror">
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Estado *</label>
                    <select name="status" required class="input-field" style="cursor:pointer">
                        <option value="pendiente" {{ old('status', $task->status)=='pendiente'?'selected':'' }}>Pendiente</option>
                        <option value="asignada" {{ old('status', $task->status)=='asignada'?'selected':'' }}>Asignada</option>
                        <option value="en_progreso" {{ old('status', $task->status)=='en_progreso'?'selected':'' }}>En progreso</option>
                        <option value="bloqueada" {{ old('status', $task->status)=='bloqueada'?'selected':'' }}>Bloqueada</option>
                        <option value="en_revision" {{ old('status', $task->status)=='en_revision'?'selected':'' }}>En revision</option>
                        <option value="finalizada" {{ old('status', $task->status)=='finalizada'?'selected':'' }}>Finalizada</option>
                        <option value="cancelada" {{ old('status', $task->status)=='cancelada'?'selected':'' }}>Cancelada</option>
                        <option value="archivada" {{ old('status', $task->status)=='archivada'?'selected':'' }}>Archivada</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Area</label>
                    <input name="area" type="text" value="{{ old('area', $task->area) }}" class="input-field" placeholder="Ej: Administracion, TI, Finanzas">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Asignar a *</label>
                    <div id="membersContainer" style="border:1px solid rgba(18,63,110,0.12);border-radius:10px;padding:10px;max-height:180px;overflow-y:auto;background:white">
                        @foreach($users as $u)
                            <label style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;cursor:pointer;transition:background 0.15s;font-size:13px;color:#1e293b" onmouseover="this.style.background='rgba(18,63,110,0.04)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="assignees[]" value="{{ $u->id }}" {{ $task->assignees->contains($u->id) ? 'checked' : '' }} style="accent-color:#123f6e">
                                <div style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:rgba(18,63,110,0.06);font-size:11px;font-weight:600;color:#123f6e;flex-shrink:0">{{ strtoupper(substr($u->name,0,1).substr($u->last_name??'',0,1)) }}</div>
                                <div>
                                    <span style="font-weight:500">{{ $u->name }} {{ $u->last_name }}</span>
                                    <span style="font-size:11px;color:#94a3b8;margin-left:6px">{{ $u->role_label }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('assignees')
                        <div style="display:flex;align-items:center;gap:6px;margin-top:6px;padding:8px 12px;border-radius:8px;font-size:12px;color:#dc2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.1)">
                            <i data-lucide="alert-circle" style="width:14px;height:14px;flex-shrink:0"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Observaciones</label>
                    <textarea name="observations" rows="3" class="input-field" style="resize:vertical">{{ old('observations', $task->observations) }}</textarea>
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end">
                    <a href="{{ route('tasks.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <i data-lucide="save" style="width:16px;height:16px"></i> Actualizar tarea
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>.error-field { border-color: rgba(220,38,38,0.4) !important; box-shadow: 0 0 0 3px rgba(220,38,38,0.06) !important; }</style>
    <script>
    function loadGroupMembers(groupId) {
        const container = document.getElementById('membersContainer');
        if (!groupId) {
            container.innerHTML = '<p style="font-size:13px;color:#94a3b8;text-align:center;padding:16px">Selecciona un grupo para ver sus integrantes</p>';
            return;
        }
        container.innerHTML = '<p style="font-size:13px;color:#94a3b8;text-align:center;padding:16px">Cargando integrantes...</p>';

        fetch('/tasks-group-members/' + groupId)
            .then(r => r.json())
            .then(members => {
                if (members.error) {
                    container.innerHTML = '<p style="font-size:13px;color:#dc2626;text-align:center;padding:16px">' + members.error + '</p>';
                    return;
                }
                if (members.length === 0) {
                    container.innerHTML = '<p style="font-size:13px;color:#94a3b8;text-align:center;padding:16px">Este grupo no tiene integrantes</p>';
                    return;
                }
                const selected = @json($task->assignees->pluck('id')->toArray());
                let html = '';
                members.forEach(m => {
                    const checked = selected.includes(m.id) ? 'checked' : '';
                    html += '<label style="display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;cursor:pointer;transition:background 0.15s;font-size:13px;color:#1e293b" onmouseover="this.style.background=\'rgba(18,63,110,0.04)\'" onmouseout="this.style.background=\'transparent\'">';
                    html += '<input type="checkbox" name="assignees[]" value="' + m.id + '" ' + checked + ' style="accent-color:#123f6e">';
                    html += '<div style="width:28px;height:28px;border-radius:50%;display:grid;place-items:center;background:rgba(18,63,110,0.06);font-size:11px;font-weight:600;color:#123f6e;flex-shrink:0">' + m.initials + '</div>';
                    html += '<div><span style="font-weight:500">' + m.name + '</span> <span style="font-size:11px;color:#94a3b8;margin-left:6px">' + m.role + '</span></div>';
                    html += '</label>';
                });
                container.innerHTML = html;
            });
    }
    </script>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
