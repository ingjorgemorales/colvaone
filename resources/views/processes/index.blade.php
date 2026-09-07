<x-layouts.app title="Procesos y subprocesos | {{ config('app.name') }}" heading="Procesos y subprocesos" subheading="Maestro del mapa de procesos que alimenta la ficha tecnica de indicadores">
    <style>
        .tabs { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; align-items: center; }
        .tab-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 18px; border-radius: 10px; border: 1px solid rgba(18,63,110,0.10);
            background: white; color: #475569; font-size: 13px; font-weight: 600;
            cursor: pointer; white-space: nowrap; transition: all 0.2s; font-family: inherit;
        }
        .tab-btn:hover { background: rgba(18,63,110,0.04); color: #123f6e; }
        .tab-btn.is-active { background: #123f6e; border-color: #123f6e; color: white; }
        .tab-count { padding: 1px 7px; border-radius: 20px; font-size: 11px; background: rgba(18,63,110,0.10); color: #123f6e; }
        .tab-btn.is-active .tab-count { background: rgba(255,255,255,0.2); color: white; }

        .proc-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 760px; }
        .proc-table th {
            padding: 11px 14px; text-align: left; font-size: 11px; font-weight: 700;
            letter-spacing: 0.05em; text-transform: uppercase; color: #94a3b8;
            border-bottom: 1px solid rgba(18,63,110,0.06); white-space: nowrap;
        }
        .proc-table td { padding: 12px 14px; border-bottom: 1px solid rgba(18,63,110,0.04); vertical-align: middle; color: #475569; }
        .proc-table tr:last-child td { border-bottom: none; }
        .proc-table tr.is-inactive td:not(.actions-cell) { opacity: 0.5; }

        .code-chip {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 42px; height: 24px; padding: 0 8px; border-radius: 6px;
            font-size: 11px; font-weight: 700; color: #123f6e; background: rgba(18,63,110,0.06);
        }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; white-space: nowrap; }
        .use-count { font-size: 12px; color: #94a3b8; white-space: nowrap; }

        .row-action {
            display: flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 8px; border: none;
            background: rgba(18,63,110,0.04); cursor: pointer; transition: background 0.15s;
        }
        .row-action:hover { background: rgba(18,63,110,0.10); }
        .row-action:disabled { opacity: 0.3; cursor: not-allowed; }
        .actions-cell { white-space: nowrap; }

        .edit-panel { border: 1px dashed rgba(18,63,110,0.18); border-radius: 12px; padding: 18px; background: rgba(18,63,110,0.02); margin-top: 16px; }
        .edit-grid { display: grid; grid-template-columns: 120px minmax(0, 1fr); gap: 14px; }
        @media (max-width: 560px) { .edit-grid { grid-template-columns: 1fr; } }
        .panel-title { font-size: 13px; font-weight: 700; color: #123f6e; margin: 0 0 14px; display: flex; align-items: center; gap: 7px; }
        .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 16px; flex-wrap: wrap; }
        .field { display: flex; flex-direction: column; min-width: 0; }
        .field-label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px; }
        .field-label .req { color: #dc2626; font-weight: 700; }
        .field-error { font-size: 12px; color: #dc2626; margin: 4px 0 0; }
    </style>

    @php
        $tab = request('tab') === 'subprocesos' ? 'subprocesos' : 'procesos';
        $canCreate = auth()->user()->hasPermission('processes.create');
        $canEdit = auth()->user()->hasPermission('processes.edit');
        $canToggle = auth()->user()->hasPermission('processes.toggle');
    @endphp

    <div x-data="{
        tab: '{{ $tab }}',
        panel: null,
        editing: null,
        form: { code: '', name: '' },
        openCreate(type) { this.form = { code: '', name: '' }; this.editing = null; this.panel = type; },
        openEdit(type, item) { this.form = { code: item.code ?? '', name: item.name }; this.editing = item.id; this.panel = type; },
        close() { this.panel = null; this.editing = null; this.form = { code: '', name: '' }; },
        action(type) {
            const base = '{{ url('configuracion/procesos') }}/' + type;
            return this.editing ? base + '/' + this.editing : base;
        }
    }">
        <div class="tabs">
            <button type="button" class="tab-btn" :class="tab === 'procesos' ? 'is-active' : ''" @click="tab = 'procesos'; close()">
                <i data-lucide="workflow" style="width:15px;height:15px"></i>
                Procesos <span class="tab-count">{{ $processes->count() }}</span>
            </button>
            <button type="button" class="tab-btn" :class="tab === 'subprocesos' ? 'is-active' : ''" @click="tab = 'subprocesos'; close()">
                <i data-lucide="git-branch" style="width:15px;height:15px"></i>
                Subprocesos <span class="tab-count">{{ $subprocesses->count() }}</span>
            </button>
        </div>

        @if(session('success'))
            <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#991b1b;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.15)">
                @foreach($errors->all() as $error)
                    <p style="margin:0">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <p style="font-size:12px;color:#94a3b8;margin:0 0 14px;display:flex;align-items:flex-start;gap:7px;line-height:1.55">
            <i data-lucide="info" style="width:13px;height:13px;flex-shrink:0;margin-top:2px;color:#123f6e"></i>
            Renombrar aqui actualiza el nombre en todos los indicadores que lo usan. En vez de borrar, se desactiva: deja de ofrecerse en la ficha tecnica pero los indicadores que ya lo tenian conservan su clasificacion.
        </p>

        {{-- ============ PROCESOS ============ --}}
        <div x-show="tab === 'procesos'" x-cloak>
            <div class="card" style="padding:24px">
                <div style="overflow-x:auto;border:1px solid rgba(18,63,110,0.06);border-radius:12px;background:white">
                    <table class="proc-table">
                        <thead>
                            <tr>
                                <th style="width:90px">Orden</th>
                                <th>Nombre</th>
                                <th style="width:130px">Indicadores</th>
                                <th style="width:110px">Estado</th>
                                @if($canEdit || $canToggle)<th style="width:120px;text-align:right">Acciones</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($processes as $i => $process)
                                <tr class="{{ $process->is_active ? '' : 'is-inactive' }}">
                                    <td>
                                        @if($canEdit)
                                            <div style="display:flex;gap:4px">
                                                <form method="POST" action="{{ route('processes.move', ['type' => 'procesos', 'id' => $process->id]) }}" style="margin:0">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="row-action" title="Subir" style="color:#64748b" {{ $i === 0 ? 'disabled' : '' }}>
                                                        <i data-lucide="chevron-up" style="width:14px;height:14px"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('processes.move', ['type' => 'procesos', 'id' => $process->id]) }}" style="margin:0">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="row-action" title="Bajar" style="color:#64748b" {{ $i === $processes->count() - 1 ? 'disabled' : '' }}>
                                                        <i data-lucide="chevron-down" style="width:14px;height:14px"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="use-count">{{ $i + 1 }}</span>
                                        @endif
                                    </td>
                                    <td style="font-weight:600;color:#1e293b">{{ $process->name }}</td>
                                    <td>
                                        <span class="use-count">
                                            {{ $process->indicators_count }} {{ $process->indicators_count === 1 ? 'indicador' : 'indicadores' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="pill" style="background:{{ $process->is_active ? 'rgba(5,150,105,0.08)' : 'rgba(148,163,184,0.12)' }};color:{{ $process->is_active ? '#059669' : '#94a3b8' }}">
                                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $process->is_active ? '#059669' : '#94a3b8' }}"></span>
                                            {{ $process->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    @if($canEdit || $canToggle)
                                        <td class="actions-cell">
                                            <div style="display:flex;gap:4px;justify-content:flex-end">
                                                @if($canEdit)
                                                    <button type="button" class="row-action" title="Renombrar" style="color:#6366f1"
                                                        @click="openEdit('procesos', { id: {{ $process->id }}, name: @js($process->name), code: null })">
                                                        <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                                    </button>
                                                @endif
                                                @if($canToggle)
                                                    <form method="POST" action="{{ route('processes.toggle', ['type' => 'procesos', 'id' => $process->id]) }}" style="margin:0">
                                                        @csrf
                                                        <button type="submit" class="row-action" title="{{ $process->is_active ? 'Inactivar' : 'Activar' }}"
                                                            style="color:{{ $process->is_active ? '#94a3b8' : '#059669' }}">
                                                            <i data-lucide="{{ $process->is_active ? 'power-off' : 'power' }}" style="width:14px;height:14px"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($canEdit || $canToggle) ? 5 : 4 }}" style="padding:40px 16px;text-align:center;border-bottom:none">
                                        <p style="font-size:13px;color:#94a3b8;margin:0">No hay procesos registrados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($canCreate || $canEdit)
                    <button type="button" class="btn-primary" style="margin-top:16px;padding:9px 16px;font-size:13px"
                        x-show="panel !== 'procesos'" @click="openCreate('procesos')">
                        <i data-lucide="plus" style="width:15px;height:15px"></i> Nuevo proceso
                    </button>

                    <div class="edit-panel" x-show="panel === 'procesos'" x-cloak x-transition>
                        <p class="panel-title">
                            <i data-lucide="square-pen" style="width:15px;height:15px"></i>
                            <span x-text="editing ? 'Renombrar proceso' : 'Nuevo proceso'"></span>
                        </p>
                        <form method="POST" :action="action('procesos')">
                            @csrf
                            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                            <div class="field">
                                <label class="field-label">Nombre del proceso <span class="req">*</span></label>
                                <input name="name" type="text" maxlength="150" required x-model="form.name"
                                    class="input-field" placeholder="Ej: GESTION DE TALENTO HUMANO">
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-secondary" @click="close()">Cancelar</button>
                                <button type="submit" class="btn-primary">
                                    <i data-lucide="save" style="width:16px;height:16px"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ SUBPROCESOS ============ --}}
        <div x-show="tab === 'subprocesos'" x-cloak>
            <div class="card" style="padding:24px">
                <div style="overflow-x:auto;border:1px solid rgba(18,63,110,0.06);border-radius:12px;background:white">
                    <table class="proc-table">
                        <thead>
                            <tr>
                                <th style="width:90px">Orden</th>
                                <th style="width:80px">Codigo</th>
                                <th>Nombre</th>
                                <th style="width:130px">Indicadores</th>
                                <th style="width:110px">Estado</th>
                                @if($canEdit || $canToggle)<th style="width:120px;text-align:right">Acciones</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subprocesses as $i => $subprocess)
                                <tr class="{{ $subprocess->is_active ? '' : 'is-inactive' }}">
                                    <td>
                                        @if($canEdit)
                                            <div style="display:flex;gap:4px">
                                                <form method="POST" action="{{ route('processes.move', ['type' => 'subprocesos', 'id' => $subprocess->id]) }}" style="margin:0">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" class="row-action" title="Subir" style="color:#64748b" {{ $i === 0 ? 'disabled' : '' }}>
                                                        <i data-lucide="chevron-up" style="width:14px;height:14px"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('processes.move', ['type' => 'subprocesos', 'id' => $subprocess->id]) }}" style="margin:0">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" class="row-action" title="Bajar" style="color:#64748b" {{ $i === $subprocesses->count() - 1 ? 'disabled' : '' }}>
                                                        <i data-lucide="chevron-down" style="width:14px;height:14px"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="use-count">{{ $i + 1 }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subprocess->code)
                                            <span class="code-chip">{{ $subprocess->code }}</span>
                                        @else
                                            <span class="use-count">-</span>
                                        @endif
                                    </td>
                                    <td style="font-weight:600;color:#1e293b">{{ $subprocess->name }}</td>
                                    <td>
                                        <span class="use-count">
                                            {{ $subprocess->indicators_count }} {{ $subprocess->indicators_count === 1 ? 'indicador' : 'indicadores' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="pill" style="background:{{ $subprocess->is_active ? 'rgba(5,150,105,0.08)' : 'rgba(148,163,184,0.12)' }};color:{{ $subprocess->is_active ? '#059669' : '#94a3b8' }}">
                                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $subprocess->is_active ? '#059669' : '#94a3b8' }}"></span>
                                            {{ $subprocess->is_active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    @if($canEdit || $canToggle)
                                        <td class="actions-cell">
                                            <div style="display:flex;gap:4px;justify-content:flex-end">
                                                @if($canEdit)
                                                    <button type="button" class="row-action" title="Editar" style="color:#6366f1"
                                                        @click="openEdit('subprocesos', { id: {{ $subprocess->id }}, name: @js($subprocess->name), code: @js($subprocess->code ?? '') })">
                                                        <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                                    </button>
                                                @endif
                                                @if($canToggle)
                                                    <form method="POST" action="{{ route('processes.toggle', ['type' => 'subprocesos', 'id' => $subprocess->id]) }}" style="margin:0">
                                                        @csrf
                                                        <button type="submit" class="row-action" title="{{ $subprocess->is_active ? 'Inactivar' : 'Activar' }}"
                                                            style="color:{{ $subprocess->is_active ? '#94a3b8' : '#059669' }}">
                                                            <i data-lucide="{{ $subprocess->is_active ? 'power-off' : 'power' }}" style="width:14px;height:14px"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($canEdit || $canToggle) ? 6 : 5 }}" style="padding:40px 16px;text-align:center;border-bottom:none">
                                        <p style="font-size:13px;color:#94a3b8;margin:0">No hay subprocesos registrados.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($canCreate || $canEdit)
                    <button type="button" class="btn-primary" style="margin-top:16px;padding:9px 16px;font-size:13px"
                        x-show="panel !== 'subprocesos'" @click="openCreate('subprocesos')">
                        <i data-lucide="plus" style="width:15px;height:15px"></i> Nuevo subproceso
                    </button>

                    <div class="edit-panel" x-show="panel === 'subprocesos'" x-cloak x-transition>
                        <p class="panel-title">
                            <i data-lucide="square-pen" style="width:15px;height:15px"></i>
                            <span x-text="editing ? 'Editar subproceso' : 'Nuevo subproceso'"></span>
                        </p>
                        <form method="POST" :action="action('subprocesos')">
                            @csrf
                            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                            <div class="edit-grid">
                                <div class="field">
                                    <label class="field-label">Codigo</label>
                                    <input name="code" type="text" maxlength="10" x-model="form.code"
                                        class="input-field" placeholder="Ej: A07">
                                </div>
                                <div class="field">
                                    <label class="field-label">Nombre del subproceso <span class="req">*</span></label>
                                    <input name="name" type="text" maxlength="200" required x-model="form.name"
                                        class="input-field" placeholder="Ej: GESTION DE COMUNICACIONES">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn-secondary" @click="close()">Cancelar</button>
                                <button type="submit" class="btn-primary">
                                    <i data-lucide="save" style="width:16px;height:16px"></i> Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
