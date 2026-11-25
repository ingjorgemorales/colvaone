<x-layouts.app title="Operaciones Recíprocas | {{ config('app.name') }}" heading="Financiera" subheading="Módulo de Operaciones Recíprocas">
    <style>
        .reciprocal-page { width: 100%; min-width: 0; max-width: 1440px; margin: 0 auto; color: #1e293b; }
        
        /* Institutional Header Block matching official CGN file */
        .cgn-institutional-header {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(18, 63, 110, 0.12);
            box-shadow: 0 4px 18px rgba(18, 63, 110, 0.04);
            margin-bottom: 22px;
            padding: 20px 24px 16px;
            position: relative;
        }
        .cgn-header-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .cgn-header-body {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px 28px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #e2e8f0;
        }
        @media (max-width: 768px) {
            .cgn-header-body {
                grid-template-columns: 1fr;
            }
        }
        .cgn-row-item {
            display: flex;
            align-items: baseline;
            gap: 10px;
            font-size: 13.5px;
            line-height: 1.4;
        }
        .cgn-row-item.full-width {
            grid-column: 1 / -1;
        }
        .cgn-field-label {
            font-weight: 700;
            color: #1e293b;
            font-size: 12.5px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            min-width: 135px;
            flex-shrink: 0;
        }
        .cgn-field-value {
            color: #334155;
            font-weight: 500;
            word-break: break-word;
        }
        .cgn-field-value.highlight {
            font-weight: 700;
            color: #123f6e;
        }
        .cgn-format-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            font-weight: 700;
            color: #123f6e;
            background: rgba(18, 63, 110, 0.06);
            padding: 6px 14px;
            border-radius: 9999px;
            border: 1px solid rgba(18, 63, 110, 0.16);
            letter-spacing: 0.02em;
        }
        .cgn-header-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: 10px;
        }
        .cgn-cifras-note {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            font-style: italic;
        }

        /* Toolbar and Filter bar */
        .reciprocal-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }
        .reciprocal-filters {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            flex: 1;
            min-width: 0;
        }
        .filter-pill-wrap {
            display: inline-flex;
            align-items: center;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            border-radius: 9999px;
            min-height: 44px;
            transition: all 0.2s ease;
        }
        .filter-pill-wrap:focus-within {
            background: #ffffff;
            border-color: #123f6e;
            box-shadow: 0 0 0 3px rgba(18, 63, 110, 0.12);
        }
        .filter-search-wrap {
            position: relative;
            padding: 0 16px;
            min-width: 220px;
            max-width: 320px;
            flex: 1;
        }
        .filter-search-wrap i {
            color: #64748b;
            margin-right: 8px;
            flex-shrink: 0;
        }
        .filter-search-input {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            font-size: 13.5px;
            color: #1e293b;
            padding: 10px 0;
        }
        .filter-search-input::placeholder {
            color: #64748b;
        }
        .filter-date-wrap {
            padding: 0 16px;
            gap: 8px;
        }
        .filter-date-wrap label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin: 0;
            white-space: nowrap;
        }
        .filter-date-input {
            background: transparent;
            border: none;
            outline: none;
            font-size: 13px;
            color: #1e293b;
            padding: 8px 0;
            cursor: pointer;
        }
        .filter-select-wrap {
            padding: 0 16px;
            gap: 8px;
        }
        .filter-select-wrap label {
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin: 0;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .filter-select-input {
            background: transparent;
            border: none;
            outline: none;
            font-size: 13px;
            font-weight: 600;
            color: #123f6e;
            padding: 8px 0;
            cursor: pointer;
        }
        .filter-select-input option {
            font-weight: 500;
            color: #1e293b;
            background: #ffffff;
        }
        .btn-filter-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: 9999px;
            background: #ffffff;
            color: #123f6e;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid rgba(18, 63, 110, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 44px;
        }
        .btn-filter-action:hover {
            background: rgba(18, 63, 110, 0.05);
            border-color: #123f6e;
        }
        .btn-filter-clear {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 16px;
            border-radius: 9999px;
            background: #ffffff;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid rgba(220, 38, 38, 0.3);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 44px;
        }
        .btn-filter-clear:hover {
            background: rgba(220, 38, 38, 0.05);
        }

        .reciprocal-actions-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-pill-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: 9999px;
            background: #123f6e;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(18, 63, 110, 0.18);
            transition: all 0.2s ease;
            white-space: nowrap;
            min-height: 44px;
        }
        .btn-pill-primary:hover {
            background: #0d2e52;
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(18, 63, 110, 0.25);
            color: #ffffff;
        }
        .btn-pill-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 9999px;
            background: #ffffff;
            color: #123f6e;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(18, 63, 110, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 44px;
        }
        .btn-pill-secondary:hover {
            background: rgba(18, 63, 110, 0.05);
            border-color: #123f6e;
        }

        /* Table Card matching Mockup */
        .reciprocal-table-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(18, 63, 110, 0.1);
            box-shadow: 0 4px 20px rgba(18, 63, 110, 0.05);
            overflow: hidden;
            margin-bottom: 24px;
        }
        .reciprocal-table-wrap {
            width: 100%;
            overflow-x: auto;
        }
        .reciprocal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13.5px;
            text-align: left;
        }
        .reciprocal-table thead th {
            background: #ffffff;
            color: #334155;
            font-weight: 600;
            font-size: 13px;
            padding: 16px 18px;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .reciprocal-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.18s ease;
        }
        .reciprocal-table tbody tr:hover {
            background: rgba(18, 63, 110, 0.02);
        }
        .reciprocal-table td {
            padding: 14px 18px;
            vertical-align: middle;
            color: #334155;
        }
        .badge-code {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12.5px;
            font-weight: 600;
            color: #123f6e;
            background: rgba(18, 63, 110, 0.07);
            padding: 3px 8px;
            border-radius: 6px;
        }
        .cell-currency {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            text-align: right;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(18, 63, 110, 0.05);
            color: #123f6e;
            text-decoration: none;
            border: 1px solid rgba(18, 63, 110, 0.08);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .action-btn:hover {
            background: #123f6e;
            color: #ffffff;
            border-color: #123f6e;
            transform: scale(1.05);
        }
    </style>

    <div class="reciprocal-page">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; color: #065f46; background: rgba(5, 150, 105, 0.1); border: 1px solid rgba(5, 150, 105, 0.25); display: flex; align-items: center; gap: 10px">
                <i data-lucide="check-circle-2" style="width: 18px; height: 18px; flex-shrink: 0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; color: #991b1b; background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.25); display: flex; align-items: center; gap: 10px">
                <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- Institutional Header Block matching official CGN file --}}
        <div class="cgn-institutional-header">
            <div class="cgn-header-top-row">
                <div class="cgn-row-item">
                    <span class="cgn-field-label">DEPARTAMENTO</span>
                    <span class="cgn-field-value">{{ $settings->departamento }}</span>
                </div>
                <div class="cgn-format-pill">
                    <i data-lucide="file-spreadsheet" style="width: 15px; height: 15px"></i>
                    <span>{{ $settings->formato }}</span>
                </div>
            </div>

            <div class="cgn-header-body">
                <div class="cgn-row-item">
                    <span class="cgn-field-label">MUNICIPIO:</span>
                    <span class="cgn-field-value">{{ $settings->municipio }}</span>
                </div>

                <div class="cgn-row-item full-width">
                    <span class="cgn-field-label">ENTIDAD:</span>
                    <span class="cgn-field-value highlight">{{ $settings->entidad }}</span>
                </div>

                <div class="cgn-row-item">
                    <span class="cgn-field-label">CODIGO:</span>
                    <span class="cgn-field-value badge-code">{{ $settings->codigo }}</span>
                </div>

                <div class="cgn-row-item">
                    <span class="cgn-field-label">FECHA DE CORTE:</span>
                    <span class="cgn-field-value" style="font-weight: 600; color: #123f6e">{{ $fechaCorte ?? $settings->fecha_corte }}</span>
                </div>
            </div>

            <div class="cgn-header-footer">
                <span class="cgn-cifras-note">{{ $settings->cifras }}</span>
            </div>
        </div>

        {{-- Filters and Actions Toolbar matching Image 1 --}}
        <form method="GET" action="{{ route('financial.reciprocals.index') }}" class="reciprocal-toolbar">
            <div class="reciprocal-filters">
                {{-- Search Pill --}}
                <div class="filter-pill-wrap filter-search-wrap">
                    <i data-lucide="search" style="width: 16px; height: 16px"></i>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Buscar....."
                           class="filter-search-input"
                           aria-label="Buscar operaciones recíprocas">
                </div>

                {{-- Trimestre Pill Selector --}}
                <div class="filter-pill-wrap filter-select-wrap">
                    <label for="select_trimestre">
                        <i data-lucide="calendar-range" style="width: 14px; height: 14px; color: #123f6e"></i>
                        <span>Trimestre:</span>
                    </label>
                    <select id="select_trimestre" name="trimestre" class="filter-select-input" aria-label="Seleccionar trimestre oficial">
                        <option value="">Personalizado / Todos</option>
                        <option value="1" {{ ($trimestre ?? '') == '1' ? 'selected' : '' }}>1er Trimestre (1 Ene - 31 Mar)</option>
                        <option value="2" {{ ($trimestre ?? '') == '2' ? 'selected' : '' }}>2do Trimestre (1 Abr - 30 Jun)</option>
                        <option value="3" {{ ($trimestre ?? '') == '3' ? 'selected' : '' }}>3er Trimestre (1 Jul - 30 Sep)</option>
                        <option value="4" {{ ($trimestre ?? '') == '4' ? 'selected' : '' }}>4to Trimestre (1 Oct - 31 Dic)</option>
                    </select>
                </div>

                {{-- Año Pill Selector --}}
                <div class="filter-pill-wrap filter-select-wrap">
                    <label for="filter_year">
                        <i data-lucide="calendar" style="width: 14px; height: 14px; color: #123f6e"></i>
                        <span>Año:</span>
                    </label>
                    <input type="number"
                           id="filter_year"
                           name="year"
                           value="{{ $selectedYear ?? now()->year }}"
                           min="2000"
                           max="2099"
                           class="filter-select-input"
                           style="width: 72px; text-align: center"
                           aria-label="Año oficial">
                </div>

                {{-- Date Start Pill --}}
                <div class="filter-pill-wrap filter-date-wrap">
                    <label for="fecha_inicio">Fecha inicio:</label>
                    <input type="date"
                           id="fecha_inicio"
                           name="fecha_inicio"
                           value="{{ $fechaInicio }}"
                           class="filter-date-input">
                </div>

                {{-- Date End Pill --}}
                <div class="filter-pill-wrap filter-date-wrap">
                    <label for="fecha_fin">Fecha fin:</label>
                    <input type="date"
                           id="fecha_fin"
                           name="fecha_fin"
                           value="{{ $fechaFin }}"
                           class="filter-date-input">
                </div>

                <button type="submit" class="btn-filter-action" title="Buscar operaciones recíprocas">
                    <i data-lucide="search" style="width: 15px; height: 15px"></i>
                    <span>Buscar</span>
                </button>

                @if(filled($search) || filled($fechaInicio) || filled($fechaFin) || filled($trimestre) || (filled($selectedYear) && $selectedYear != now()->year))
                    <a href="{{ route('financial.reciprocals.index') }}" class="btn-filter-clear" title="Limpiar filtros">
                        <i data-lucide="x" style="width: 15px; height: 15px"></i>
                        <span>Limpiar</span>
                    </a>
                @endif
            </div>

            {{-- Right Action Buttons: Exportar, Ajustes y Agregar --}}
            <div class="reciprocal-actions-group">
                <a href="{{ route('financial.reciprocals.export', request()->query()) }}"
                   class="btn-pill-primary"
                   download
                   data-no-spinner="true"
                   title="Exportar reporte a Excel en formato oficial CGN">
                    <i data-lucide="download" style="width: 16px; height: 16px"></i>
                    <span>Exportar</span>
                </a>

                <a href="{{ route('financial.reciprocals.settings') }}" class="btn-pill-secondary" title="Ajustes institucionales del formato CGN">
                    <i data-lucide="settings" style="width: 16px; height: 16px"></i>
                    <span>Ajustes</span>
                </a>

                @if(auth()->user()->hasPermission('reciprocals.create') || auth()->user()->role === 'superadmin')
                    <a href="{{ route('financial.reciprocals.create') }}" class="btn-pill-primary" title="Agregar nueva operación recíproca">
                        <i data-lucide="plus" style="width: 16px; height: 16px"></i>
                        <span>Agregar</span>
                    </a>
                @endif
            </div>
        </form>

        {{-- Table Container matching Image 1 & 2 (Sin botón de eliminar) --}}
        <div class="reciprocal-table-card">
            <div class="reciprocal-table-wrap">
                <table class="reciprocal-table">
                    <thead>
                        <tr>
                            <th>Codigo contable subcuenta</th>
                            <th>Nombre subcuenta</th>
                            <th>Codigo Entidad reciproca</th>
                            <th>Nombre entidad reciproca</th>
                            <th style="text-align: right">Valor corriente</th>
                            <th style="text-align: right">Valor No Corriente</th>
                            <th style="text-align: center">Fecha</th>
                            <th style="text-align: right; min-width: 90px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $rec)
                            <tr>
                                <td>
                                    <span class="badge-code">{{ $rec->codigo_contable_subcuenta }}</span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b">{{ $rec->nombre_subcuenta }}</div>
                                </td>
                                <td>
                                    <span class="badge-code">{{ $rec->codigo_entidad_reciproca }}</span>
                                </td>
                                <td>
                                    <div style="color: #334155">{{ $rec->nombre_entidad_reciproca }}</div>
                                </td>
                                <td class="cell-currency" style="color: #059669">
                                    {{ $rec->valor_corriente_formateado }}
                                </td>
                                <td class="cell-currency" style="color: #2563eb">
                                    {{ $rec->valor_no_corriente_formateado }}
                                </td>
                                <td style="text-align: center; white-space: nowrap">
                                    <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 13px; color: #475569">
                                        <i data-lucide="calendar" style="width: 14px; height: 14px; color: #94a3b8"></i>
                                        {{ $rec->fecha_formateada }}
                                    </span>
                                </td>
                                <td style="text-align: right">
                                    <div style="display: inline-flex; align-items: center; gap: 6px; justify-content: flex-end">
                                        <a href="{{ route('financial.reciprocals.show', $rec) }}" class="action-btn" title="Ver detalles">
                                            <i data-lucide="eye" style="width: 15px; height: 15px"></i>
                                        </a>

                                        @if(auth()->user()->hasPermission('reciprocals.edit') || auth()->user()->role === 'superadmin')
                                            <a href="{{ route('financial.reciprocals.edit', $rec) }}" class="action-btn" title="Editar">
                                                <i data-lucide="pencil" style="width: 15px; height: 15px"></i>
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasPermission('reciprocals.toggle') || auth()->user()->role === 'superadmin')
                                            <form method="POST" action="{{ route('financial.reciprocals.toggle', $rec) }}" style="display:inline; margin:0">
                                                @csrf
                                                @if($rec->is_active)
                                                    <button type="submit" class="action-btn" title="Desactivar"
                                                        style="color: #16a34a; border: none; background: none; cursor: pointer; padding: 0"
                                                        onclick="return confirm('¿Desactivar esta operación recíproca?')">
                                                        <i data-lucide="toggle-right" style="width: 17px; height: 17px"></i>
                                                    </button>
                                                @else
                                                    <button type="submit" class="action-btn" title="Activar"
                                                        style="color: #dc2626; border: none; background: none; cursor: pointer; padding: 0"
                                                        onclick="return confirm('¿Activar esta operación recíproca?')">
                                                        <i data-lucide="toggle-left" style="width: 17px; height: 17px"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 48px 24px; text-align: center; color: #64748b">
                                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #f1f5f9; display: grid; place-items: center; margin: 0 auto 12px; color: #94a3b8">
                                        <i data-lucide="inbox" style="width: 24px; height: 24px"></i>
                                    </div>
                                    <p style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0 0 6px">No se encontraron operaciones recíprocas</p>
                                    <p style="font-size: 13px; color: #94a3b8; margin: 0">Prueba ajustando los filtros de búsqueda o agrega un nuevo registro.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination matching Image 1 --}}
            <div style="padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #fafbfc">
                {{ $records->links('vendor.pagination.colvaone') }}
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const selectTrimestre = document.getElementById('select_trimestre');
            const inputYear = document.getElementById('filter_year');
            const inputInicio = document.getElementById('fecha_inicio');
            const inputFin = document.getElementById('fecha_fin');

            if (!selectTrimestre || !inputInicio || !inputFin) return;

            const getYear = () => {
                if (inputYear && inputYear.value) return inputYear.value;
                if (inputFin.value) return inputFin.value.split('-')[0];
                if (inputInicio.value) return inputInicio.value.split('-')[0];
                return '{{ $selectedYear ?: 2026 }}';
            };

            const updateQuarterDates = () => {
                const val = selectTrimestre.value;
                if (!val) return;
                const y = getYear();
                if (val === '1') {
                    inputInicio.value = `${y}-01-01`;
                    inputFin.value = `${y}-03-31`;
                } else if (val === '2') {
                    inputInicio.value = `${y}-04-01`;
                    inputFin.value = `${y}-06-30`;
                } else if (val === '3') {
                    inputInicio.value = `${y}-07-01`;
                    inputFin.value = `${y}-09-30`;
                } else if (val === '4') {
                    inputInicio.value = `${y}-10-01`;
                    inputFin.value = `${y}-12-31`;
                }
            };

            selectTrimestre.addEventListener('change', updateQuarterDates);
            if (inputYear) {
                inputYear.addEventListener('input', () => {
                    if (selectTrimestre.value) {
                        updateQuarterDates();
                    }
                });
            }

            const checkQuarterMatch = () => {
                const ini = inputInicio.value;
                const fin = inputFin.value;
                if (!ini || !fin) return;
                const mIni = ini.substring(5, 10);
                const mFin = fin.substring(5, 10);
                if (mIni === '01-01' && mFin === '03-31') selectTrimestre.value = '1';
                else if (mIni === '04-01' && mFin === '06-30') selectTrimestre.value = '2';
                else if (mIni === '07-01' && mFin === '09-30') selectTrimestre.value = '3';
                else if (mIni === '10-01' && mFin === '12-31') selectTrimestre.value = '4';
                else selectTrimestre.value = '';
            };

            inputInicio.addEventListener('change', checkQuarterMatch);
            inputFin.addEventListener('change', checkQuarterMatch);
        });
    </script>
</x-layouts.app>