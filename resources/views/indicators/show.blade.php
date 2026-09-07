<x-layouts.app title="{{ $indicator->name }} | {{ config('app.name') }}" heading="{{ $indicator->name }}" subheading="Ficha tecnica y resultados del indicador">
    @include('indicators.partials.form-styles')
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

        .data-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        @media (max-width: 900px) { .data-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 620px) { .data-grid { grid-template-columns: 1fr; } }
        .data-item { min-width: 0; }
        .data-item-full { grid-column: 1 / -1; }
        .data-label { font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: #94a3b8; margin: 0 0 5px; }
        .data-value {
            font-size: 13px; color: #1e293b; margin: 0; line-height: 1.6;
            background: white; border: 1px solid rgba(18,63,110,0.08); border-radius: 9px;
            padding: 9px 12px; min-height: 40px;
            overflow-wrap: anywhere; white-space: pre-line;
        }

        .results-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 1020px; }
        .results-table th {
            padding: 11px 14px; text-align: left; font-size: 11px; font-weight: 700;
            letter-spacing: 0.05em; text-transform: uppercase; color: #94a3b8;
            border-bottom: 1px solid rgba(18,63,110,0.06); white-space: nowrap;
        }
        .results-table td { padding: 13px 14px; border-bottom: 1px solid rgba(18,63,110,0.04); vertical-align: top; color: #475569; }
        .results-table tr:last-child td { border-bottom: none; }
        .results-table tr.is-inactive td { opacity: 0.55; }
        /* La descripcion crece hacia abajo, nunca a lo ancho. */
        .desc-cell { max-width: 300px; min-width: 200px; white-space: normal; overflow-wrap: anywhere; line-height: 1.55; }
        .nowrap { white-space: nowrap; }

        .row-action {
            display: flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; border-radius: 8px; border: none;
            background: rgba(18,63,110,0.04); cursor: pointer; transition: background 0.15s;
        }
        .row-action:hover { background: rgba(18,63,110,0.10); }

        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; white-space: nowrap; }

        .add-panel { border: 1px dashed rgba(18,63,110,0.18); border-radius: 12px; padding: 18px; background: rgba(18,63,110,0.02); margin-top: 16px; }
        .add-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        @media (max-width: 900px) { .add-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 560px) { .add-grid { grid-template-columns: 1fr; } }
        .add-grid .span-2 { grid-column: span 2; }
        @media (max-width: 560px) { .add-grid .span-2 { grid-column: span 1; } }

        .preview-box { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; background: white; border: 1px solid rgba(18,63,110,0.08); border-radius: 10px; padding: 12px 14px; margin-top: 14px; }
        .preview-label { font-size: 11px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: #94a3b8; }
        .panel-title { font-size: 13px; font-weight: 700; color: #123f6e; margin: 0 0 14px; display: flex; align-items: center; gap: 7px; }

        /* Textos largos: se recortan en la ficha y se leen completos en un sub-cuadro. */
        .clamp-box { max-height: 108px; overflow: hidden; }
        .clamp-box-sm { max-height: 60px; overflow: hidden; }
        .clamp-more-btn {
            display: inline-flex; align-items: center; gap: 4px;
            margin-top: 6px; padding: 0; border: none; background: none;
            font-size: 11.5px; font-weight: 600; color: #123f6e; cursor: pointer; font-family: inherit;
        }
        .clamp-more-btn:hover { text-decoration: underline; }

        .text-modal-backdrop {
            position: fixed; inset: 0; z-index: 200;
            background: rgba(15,23,42,0.45); backdrop-filter: blur(2px);
            display: flex; align-items: center; justify-content: center; padding: 24px;
        }
        .text-modal-card {
            background: white; border-radius: 16px; padding: 22px 24px;
            width: 100%; max-width: 640px; max-height: 82vh;
            display: flex; flex-direction: column;
            box-shadow: 0 20px 60px rgba(15,23,42,0.25);
        }
        .text-modal-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 14px; }
        .text-modal-title { font-size: 15px; font-weight: 700; color: #123f6e; margin: 0; line-height: 1.4; }
        .text-modal-close {
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 32px; height: 32px; border-radius: 50%; border: none;
            background: rgba(18,63,110,0.06); color: #64748b; cursor: pointer; transition: background 0.15s;
        }
        .text-modal-close:hover { background: rgba(18,63,110,0.12); color: #123f6e; }
        .text-modal-body {
            overflow-y: auto; font-size: 13.5px; line-height: 1.75; color: #334155;
            white-space: pre-line; overflow-wrap: anywhere; padding-right: 6px;
        }
        @media (max-width: 560px) {
            .text-modal-card { padding: 18px; max-height: 88vh; }
        }
    </style>

    @php
        $tab = request('tab') === 'resultados' ? 'resultados' : 'ficha';
        $resultsBase = route('indicators.results.store', $indicator);
    @endphp

    <div x-data="{
        tab: '{{ $tab }}',
        textModal: { open: false, title: '', text: '' },
        openText(title, text) { this.textModal = { open: true, title: title, text: text }; },
        closeText() { this.textModal.open = false; }
    }">
        <div class="tabs">
            <button type="button" class="tab-btn" :class="tab === 'ficha' ? 'is-active' : ''" @click="tab = 'ficha'">
                <i data-lucide="file-text" style="width:15px;height:15px"></i> Ficha tecnica
            </button>
            <button type="button" class="tab-btn" :class="tab === 'resultados' ? 'is-active' : ''" @click="tab = 'resultados'">
                <i data-lucide="chart-no-axes-combined" style="width:15px;height:15px"></i>
                Resultados
                <span style="padding:1px 7px;border-radius:20px;font-size:11px;background:rgba(18,63,110,0.10);color:#123f6e">{{ $indicator->results->count() }}</span>
            </button>

            <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap">
                <a href="{{ route('indicators.index') }}" class="btn-secondary" style="padding:9px 16px;font-size:13px">
                    <i data-lucide="arrow-left" style="width:15px;height:15px"></i> Volver
                </a>
                @if(auth()->user()->hasPermission('indicators.edit'))
                    {{-- Editar ficha solo tiene sentido en la pestana de ficha tecnica. --}}
                    <a href="{{ route('indicators.edit', $indicator) }}" class="btn-primary" style="padding:9px 16px;font-size:13px"
                        x-show="tab === 'ficha'" x-cloak>
                        <i data-lucide="pencil" style="width:15px;height:15px"></i> Editar ficha
                    </a>
                @endif
            </div>
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

        {{-- ============ FICHA TECNICA ============ --}}
        <div x-show="tab === 'ficha'" x-cloak>
            <div class="card" style="padding:24px">
                <div class="sheet-block">
                    <h3 class="sheet-title">Ficha tecnica</h3>
                    <div class="data-grid">
                        <div class="data-item">
                            <p class="data-label">Categoria</p>
                            <p class="data-value">{{ $indicator->category_label }}</p>
                        </div>
                        <div class="data-item">
                            <p class="data-label">Proceso</p>
                            <p class="data-value">{{ $indicator->process_label }}</p>
                        </div>
                        <div class="data-item">
                            <p class="data-label">Subproceso</p>
                            <p class="data-value">{{ $indicator->subprocess_label }}</p>
                        </div>
                        <div class="data-item">
                            <p class="data-label">Nombre</p>
                            <p class="data-value">{{ $indicator->name }}</p>
                        </div>
                        <div class="data-item">
                            <p class="data-label">Responsable</p>
                            <p class="data-value">{{ $indicator->responsible->name ?? '-' }} {{ $indicator->responsible->last_name ?? '' }}</p>
                        </div>
                        <div class="data-item">
                            <p class="data-label">Unidad de medicion</p>
                            <p class="data-value">{{ $indicator->measurement_unit_label }}</p>
                        </div>
                        <div class="data-item">
                            <p class="data-label">Frecuencia</p>
                            <p class="data-value">{{ $indicator->frequency_label }}</p>
                        </div>
                        <div class="data-item">
                            <p class="data-label">Tipo</p>
                            <p class="data-value">{{ $indicator->type_label }}</p>
                        </div>
                        <div class="data-item">
                            <p class="data-label">Meta</p>
                            <p class="data-value" style="font-weight:700;color:#123f6e">{{ $indicator->goal }}%</p>
                        </div>
                        <div class="data-item data-item-full">
                            <p class="data-label">Objetivo del indicador</p>
                            @include('indicators.partials.clamped-text', ['text' => $indicator->objective, 'modalTitle' => 'Objetivo del indicador'])
                        </div>
                        <div class="data-item data-item-full">
                            <p class="data-label">Formula</p>
                            <p class="data-value">{{ $indicator->formula }}</p>
                        </div>
                        <div class="data-item data-item-full">
                            <p class="data-label">Aspectos metodologicos</p>
                            @include('indicators.partials.clamped-text', ['text' => $indicator->methodological_aspects, 'modalTitle' => 'Aspectos metodologicos'])
                        </div>
                    </div>
                </div>

                <div class="sheet-block" style="margin-top:18px">
                    <h3 class="sheet-title">Rango de evaluacion</h3>
                    <div class="range-grid">
                        <div class="range-card range-bad">
                            <p class="range-name">Insatisfactorio</p>
                            <p class="range-value">&lt; {{ $indicator->threshold_acceptable }}%</p>
                        </div>
                        <div class="range-card range-mid">
                            <p class="range-name">Aceptable</p>
                            <p class="range-value">&ge; {{ $indicator->threshold_acceptable }}% y &lt; {{ $indicator->threshold_satisfactory }}%</p>
                        </div>
                        <div class="range-card range-good">
                            <p class="range-name">Satisfactorio</p>
                            <p class="range-value">&ge; {{ $indicator->threshold_satisfactory }}%</p>
                        </div>
                    </div>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:18px;margin-top:18px;padding-top:16px;border-top:1px solid rgba(18,63,110,0.06);font-size:12px;color:#94a3b8">
                    <span>Estado: <strong style="color:{{ $indicator->status_color }}">{{ $indicator->status_label }}</strong></span>
                    <span>Creado por <strong style="color:#475569">{{ $indicator->creator->name ?? 'Sistema' }}</strong> el {{ $indicator->created_at->format('d/m/Y') }}</span>
                    @if($indicator->updater)
                        <span>Ultima edicion por <strong style="color:#475569">{{ $indicator->updater->name }}</strong> el {{ $indicator->updated_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============ RESULTADOS ============ --}}
        <div x-show="tab === 'resultados'" x-cloak>
            <div class="card" style="padding:24px"
                x-data="{
                    open: {{ $errors->any() ? 'true' : 'false' }},
                    editingId: null,
                    form: { periodStart: '', periodEnd: '', fieldOne: '', fieldTwo: '', description: '', actionNumber: '' },
                    base: '{{ $resultsBase }}',
                    acceptable: {{ $indicator->threshold_acceptable }},
                    satisfactory: {{ $indicator->threshold_satisfactory }},
                    get action() { return this.editingId ? this.base + '/' + this.editingId : this.base; },
                    get method() { return this.editingId ? 'PUT' : 'POST'; },
                    reset() {
                        this.form = { periodStart: '', periodEnd: '', fieldOne: '', fieldTwo: '', description: '', actionNumber: '' };
                        this.editingId = null;
                    },
                    openCreate() { this.reset(); this.open = true; },
                    openEdit(row) { this.form = { ...row.data }; this.editingId = row.id; this.open = true; },
                    close() { this.reset(); this.open = false; },
                    get compliance() {
                        const a = parseFloat(this.form.fieldOne);
                        const b = parseFloat(this.form.fieldTwo);
                        if (!a || isNaN(a) || isNaN(b)) return null;
                        return Math.round((b / a) * 10000) / 100;
                    },
                    get evaluation() {
                        const c = this.compliance;
                        if (c === null) return null;
                        if (c >= this.satisfactory) return { label: 'Satisfactorio', color: '#059669', bg: 'rgba(5,150,105,0.10)' };
                        if (c >= this.acceptable) return { label: 'Aceptable', color: '#f59e0b', bg: 'rgba(245,158,11,0.12)' };
                        return { label: 'Insatisfactorio', color: '#ef4444', bg: 'rgba(239,68,68,0.10)' };
                    }
                }">
                <h3 class="sheet-title">Resultados</h3>

                <div style="overflow-x:auto;border:1px solid rgba(18,63,110,0.06);border-radius:12px;background:white">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Periodo</th>
                                <th>Campo 1</th>
                                <th>Campo 2</th>
                                <th>Cumplimiento ANS</th>
                                <th>Evaluacion</th>
                                <th>Descripcion</th>
                                <th>Numero de accion</th>
                                <th>Estado</th>
                                @if($canEditResults || $canToggleResults)<th style="text-align:right">Acciones</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($indicator->results as $result)
                                <tr class="{{ $result->status === 'inactive' ? 'is-inactive' : '' }}">
                                    <td class="nowrap">
                                        <span style="font-weight:600;color:#1e293b">{{ $result->period_start->format('d/m/Y') }}</span>
                                        <p style="margin:2px 0 0;font-size:12px;color:#94a3b8">al {{ $result->period_end->format('d/m/Y') }}</p>
                                    </td>
                                    <td class="nowrap">{{ rtrim(rtrim(number_format((float) $result->field_one, 2, ',', '.'), '0'), ',') }}</td>
                                    <td class="nowrap">{{ rtrim(rtrim(number_format((float) $result->field_two, 2, ',', '.'), '0'), ',') }}</td>
                                    <td class="nowrap" style="font-weight:700;color:{{ $result->evaluation_color }}">
                                        {{ rtrim(rtrim(number_format((float) $result->compliance, 2, ',', '.'), '0'), ',') }}%
                                    </td>
                                    <td>
                                        <span class="pill" style="background:{{ $result->evaluation_background }};color:{{ $result->evaluation_color }}">
                                            <span style="width:7px;height:7px;border-radius:50%;background:{{ $result->evaluation_color }}"></span>
                                            {{ $result->evaluation_label }}
                                        </span>
                                    </td>
                                    <td class="desc-cell">
                                        @include('indicators.partials.clamped-text', ['text' => $result->description, 'modalTitle' => 'Descripcion del resultado', 'clampClass' => 'clamp-box-sm', 'threshold' => 80])
                                    </td>
                                    <td>{{ $result->action_number ?: '-' }}</td>
                                    <td>
                                        <span class="pill" style="background:{{ $result->status === 'active' ? 'rgba(5,150,105,0.08)' : 'rgba(148,163,184,0.12)' }};color:{{ $result->status === 'active' ? '#059669' : '#94a3b8' }}">
                                            <span style="width:6px;height:6px;border-radius:50%;background:{{ $result->status === 'active' ? '#059669' : '#94a3b8' }}"></span>
                                            {{ $result->status_label }}
                                        </span>
                                    </td>
                                    @if($canEditResults || $canToggleResults)
                                        <td>
                                            <div style="display:flex;align-items:center;gap:4px;justify-content:flex-end">
                                                @if($canEditResults)
                                                <button type="button" class="row-action" title="Editar resultado" style="color:#6366f1"
                                                    @click="openEdit({
                                                        id: {{ $result->id }},
                                                        data: {
                                                            periodStart: '{{ $result->period_start->format('Y-m-d') }}',
                                                            periodEnd: '{{ $result->period_end->format('Y-m-d') }}',
                                                            fieldOne: '{{ (float) $result->field_one }}',
                                                            fieldTwo: '{{ (float) $result->field_two }}',
                                                            description: @js($result->description ?? ''),
                                                            actionNumber: @js($result->action_number ?? '')
                                                        }
                                                    }); $nextTick(() => window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' }))">
                                                    <i data-lucide="pencil" style="width:14px;height:14px"></i>
                                                </button>
                                                @endif

                                                @if($canToggleResults)
                                                <form method="POST" action="{{ route('indicators.results.toggle', [$indicator, $result]) }}" style="margin:0">
                                                    @csrf
                                                    <button type="submit" class="row-action" title="{{ $result->status === 'active' ? 'Desactivar resultado' : 'Activar resultado' }}"
                                                        style="color:{{ $result->status === 'active' ? '#94a3b8' : '#059669' }}">
                                                        <i data-lucide="{{ $result->status === 'active' ? 'power-off' : 'power' }}" style="width:14px;height:14px"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($canEditResults || $canToggleResults) ? 9 : 8 }}" style="padding:40px 16px;text-align:center;border-bottom:none">
                                        <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.04);margin:0 auto 10px">
                                            <i data-lucide="calendar-range" style="width:20px;height:20px;color:#cbd5e1"></i>
                                        </div>
                                        <p style="font-size:13px;color:#94a3b8;margin:0">Aun no hay resultados registrados para este indicador.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(auth()->user()->hasPermission('indicators.results'))
                    <button type="button" class="btn-primary" style="margin-top:16px;padding:9px 16px;font-size:13px" @click="openCreate()" x-show="!open">
                        <i data-lucide="plus" style="width:15px;height:15px"></i> Agregar
                    </button>

                    <div class="add-panel" x-show="open" x-cloak x-transition>
                        <p class="panel-title">
                            <i data-lucide="square-pen" style="width:15px;height:15px"></i>
                            <span x-text="editingId ? 'Editar resultado' : 'Nuevo resultado'"></span>
                        </p>

                        <form method="POST" :action="action">
                            @csrf
                            <input type="hidden" name="_method" :value="method">

                            <div class="add-grid">
                                <div class="field">
                                    <label class="field-label">Fecha inicio <span class="req">*</span></label>
                                    <input name="period_start" type="date" required x-model="form.periodStart" class="input-field">
                                </div>
                                <div class="field">
                                    <label class="field-label">Fecha fin <span class="req">*</span></label>
                                    <input name="period_end" type="date" required x-model="form.periodEnd" class="input-field">
                                </div>
                                <div class="field">
                                    <label class="field-label">Campo 1 <span class="req">*</span></label>
                                    <input name="field_one" type="number" step="0.01" min="0" required x-model="form.fieldOne" class="input-field" placeholder="400">
                                </div>
                                <div class="field">
                                    <label class="field-label">Campo 2 <span class="req">*</span></label>
                                    <input name="field_two" type="number" step="0.01" min="0" required x-model="form.fieldTwo" class="input-field" placeholder="300">
                                </div>
                                <div class="field span-2">
                                    <label class="field-label">Descripcion</label>
                                    <textarea name="description" rows="4" x-model="form.description" class="input-field" placeholder="Se realizo..."></textarea>
                                </div>
                                <div class="field span-2">
                                    <label class="field-label">Numero de accion</label>
                                    <input name="action_number" type="text" maxlength="60" x-model="form.actionNumber" class="input-field" placeholder="Ej: ACC-2026-014">
                                </div>
                            </div>

                            <div class="preview-box">
                                <span class="preview-label">Cumplimiento ANS</span>
                                <template x-if="compliance === null">
                                    <span style="font-size:13px;color:#cbd5e1">Ingresa Campo 1 y Campo 2</span>
                                </template>
                                <template x-if="compliance !== null">
                                    <span style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                                        <span style="font-size:18px;font-weight:700" :style="`color:${evaluation.color}`" x-text="compliance + '%'"></span>
                                        <span class="pill" :style="`background:${evaluation.bg};color:${evaluation.color}`">
                                            <span style="width:7px;height:7px;border-radius:50%" :style="`background:${evaluation.color}`"></span>
                                            <span x-text="evaluation.label"></span>
                                        </span>
                                        <span style="font-size:11px;color:#94a3b8">Se calcula solo: Campo 2 &divide; Campo 1</span>
                                    </span>
                                </template>
                            </div>

                            <div class="form-actions" style="margin-top:16px">
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

        <div class="text-modal-backdrop" x-show="textModal.open" x-cloak
            @click.self="closeText()" @keydown.escape.window="closeText()" x-transition.opacity>
            <div class="text-modal-card" x-transition>
                <div class="text-modal-header">
                    <p class="text-modal-title" x-text="textModal.title"></p>
                    <button type="button" class="text-modal-close" @click="closeText()" title="Cerrar" aria-label="Cerrar">
                        <i data-lucide="x" style="width:16px;height:16px"></i>
                    </button>
                </div>
                <div class="text-modal-body" x-text="textModal.text"></div>
            </div>
        </div>
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
