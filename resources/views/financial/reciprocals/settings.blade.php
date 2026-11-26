<x-layouts.app title="Ajustes de Recíprocas | {{ config('app.name') }}" heading="Financiera" subheading="Ajustes institucionales y catálogo de entidades recíprocas">
    <style>
        .reciprocal-settings-container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
        }
        .reciprocal-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 16px;
            flex-wrap: wrap;
        }
        .btn-pill-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 9999px;
            background: #123f6e;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(18, 63, 110, 0.18);
            transition: all 0.2s ease;
            min-height: 42px;
            white-space: nowrap;
        }
        .btn-pill-primary:hover {
            background: #0d2e52;
            color: #ffffff;
            transform: translateY(-1px);
        }
        .btn-pill-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 9999px;
            background: #ffffff;
            color: #123f6e;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(18, 63, 110, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 42px;
        }
        .btn-pill-cancel:hover {
            background: rgba(18, 63, 110, 0.05);
            border-color: #123f6e;
        }
        
        /* Tabs navigation matching Incident settings */
        .settings-tabs {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 2px;
            flex-wrap: wrap;
        }
        .tab-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 9999px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            color: #64748b;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        .tab-link:hover {
            color: #123f6e;
            background: #e2e8f0;
        }
        .tab-link.active {
            background: #123f6e;
            color: #ffffff;
            border-color: #123f6e;
            box-shadow: 0 3px 10px rgba(18, 63, 110, 0.18);
        }

        .settings-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(18, 63, 110, 0.1);
            box-shadow: 0 4px 20px rgba(18, 63, 110, 0.04);
            padding: 32px 36px;
            margin-bottom: 24px;
        }
        @media (max-width: 680px) {
            .settings-card { padding: 22px 20px; }
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px 26px;
        }
        @media (max-width: 680px) {
            .settings-grid { grid-template-columns: 1fr; }
            .settings-grid > div[style*="grid-column: span 2"] { grid-column: span 1 !important; }
        }
        .settings-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .settings-label {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #1e293b;
        }
        .settings-input {
            width: 100%;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            color: #1e293b;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        .settings-input:focus {
            background: #ffffff;
            border-color: #123f6e;
            box-shadow: 0 0 0 3px rgba(18, 63, 110, 0.12);
        }
        .settings-hint {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Entity list rows matching incident options */
        .entity-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }
        .entity-edit-form {
            display: grid;
            grid-template-columns: 180px 1fr auto;
            gap: 12px;
            align-items: center;
            flex: 1;
            min-width: 0;
        }
        @media (max-width: 768px) {
            .entity-edit-form {
                grid-template-columns: 1fr;
            }
        }

        /* Formulario "Agregar nueva entidad recíproca": en pantallas angostas los campos y el botón van uno debajo del otro. */
        .entity-add-form { display: grid; grid-template-columns: 220px minmax(0, 1fr) auto; gap: 16px; align-items: end; }
        @media (max-width: 768px) {
            .entity-add-form { grid-template-columns: minmax(0, 1fr); }
            .entity-add-form .btn-pill-primary { width: 100%; justify-content: center; }
        }
        .badge-entity-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 600;
            color: #123f6e;
        }
        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(18, 63, 110, 0.06);
            color: #123f6e;
            border: 1px solid rgba(18, 63, 110, 0.12);
            cursor: pointer;
            transition: all 0.18s ease;
            flex-shrink: 0;
        }
        .icon-btn:hover {
            background: #123f6e;
            color: #ffffff;
            transform: scale(1.05);
        }
        .icon-btn-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: all 0.18s ease;
            flex-shrink: 0;
        }
        .icon-btn-toggle.active {
            background: rgba(5, 150, 105, 0.1);
            color: #059669;
            border-color: rgba(5, 150, 105, 0.25);
        }
        .icon-btn-toggle.active:hover {
            background: #059669;
            color: #ffffff;
        }
        .icon-btn-toggle.inactive {
            background: rgba(220, 38, 38, 0.08);
            color: #dc2626;
            border-color: rgba(220, 38, 38, 0.2);
        }
        .icon-btn-toggle.inactive:hover {
            background: #dc2626;
            color: #ffffff;
        }
        .pill-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .pill-status.active {
            background: rgba(5, 150, 105, 0.1);
            color: #059669;
        }
        .pill-status.inactive {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
        }

        /* Search input pill */
        .filter-search-box {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 9999px;
            padding: 0 16px;
            min-height: 42px;
            flex: 1;
            max-width: 400px;
        }
        .filter-search-box input {
            border: none;
            background: transparent;
            outline: none;
            font-size: 13.5px;
            color: #1e293b;
            width: 100%;
            padding: 8px 0;
        }
    </style>

    <div class="reciprocal-settings-container">
        {{-- Toolbar superior --}}
        <div class="reciprocal-toolbar">
            <div>
                <a class="btn-pill-cancel" href="{{ route('financial.reciprocals.index') }}">
                    <i data-lucide="arrow-left" style="width:16px;height:16px"></i>
                    <span>Volver a recíprocas</span>
                </a>
            </div>
            <div>
                <span style="font-size: 13px; color: #64748b">
                    @if($settings->updated_at)
                        Última modificación: {{ $settings->updated_at->format('d/m/Y H:i') }}
                    @endif
                </span>
            </div>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
            <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; font-size: 14px; font-weight: 500; color: #065f46; background: rgba(5, 150, 105, 0.1); border: 1px solid rgba(5, 150, 105, 0.25); display: flex; align-items: center; gap: 10px">
                <i data-lucide="check-circle-2" style="width: 18px; height: 18px; flex-shrink: 0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div style="margin-bottom: 24px; padding: 14px 18px; border-radius: 10px; background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.25); color: #991b1b; font-size: 13.5px">
                <div style="font-weight: 600; margin-bottom: 6px; display: flex; align-items: center; gap: 8px">
                    <i data-lucide="alert-circle" style="width: 16px; height: 16px"></i>
                    <span>Por favor corrige los siguientes errores:</span>
                </div>
                <ul style="margin: 0; padding-left: 20px">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Navigation Tabs matching Incident settings --}}
        <nav class="settings-tabs" aria-label="Ajustes de recíprocas">
            <a class="tab-link {{ $tab === 'institucional' ? 'active' : '' }}"
               href="{{ route('financial.reciprocals.settings', ['tab' => 'institucional']) }}">
                <i data-lucide="building-2" style="width: 16px; height: 16px"></i>
                <span>Parámetros Institucionales</span>
            </a>
            <a class="tab-link {{ $tab === 'entidades' ? 'active' : '' }}"
               href="{{ route('financial.reciprocals.settings', ['tab' => 'entidades']) }}">
                <i data-lucide="network" style="width: 16px; height: 16px"></i>
                <span>Entidades Recíprocas ({{ $totalEntities }})</span>
            </a>
        </nav>

        {{-- TAB 1: PARÁMETROS INSTITUCIONALES CGN2005_002 --}}
        @if($tab === 'institucional')
            <form method="POST" action="{{ route('financial.reciprocals.settings.update') }}" class="settings-card">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #123f6e; margin: 0">Parámetros Institucionales CGN2005_002</h2>
                        <p style="font-size: 13px; color: #64748b; margin: 4px 0 0">Estos valores se utilizarán automáticamente en el encabezado oficial de las descargas y exportaciones de Excel.</p>
                    </div>
                    <span style="font-size: 12px; font-weight: 600; color: #123f6e; background: rgba(18, 63, 110, 0.07); padding: 4px 10px; border-radius: 8px">
                        CGN2005_002
                    </span>
                </div>

                <div class="settings-grid">
                    <div class="settings-field">
                        <label for="departamento" class="settings-label">DEPARTAMENTO</label>
                        <input type="text"
                               id="departamento"
                               name="departamento"
                               value="{{ old('departamento', $settings->departamento) }}"
                               required
                               maxlength="150"
                               class="settings-input">
                        <span class="settings-hint">Ej: CUNDINAMARCA</span>
                    </div>

                    <div class="settings-field">
                        <label for="municipio" class="settings-label">MUNICIPIO:</label>
                        <input type="text"
                               id="municipio"
                               name="municipio"
                               value="{{ old('municipio', $settings->municipio) }}"
                               required
                               maxlength="150"
                               class="settings-input">
                        <span class="settings-hint">Ej: BOGOTA DISTRITO CAPITAL</span>
                    </div>

                    <div class="settings-field" style="grid-column: span 2">
                        <label for="entidad" class="settings-label">ENTIDAD:</label>
                        <input type="text"
                               id="entidad"
                               name="entidad"
                               value="{{ old('entidad', $settings->entidad) }}"
                               required
                               maxlength="255"
                               class="settings-input">
                        <span class="settings-hint">Razón social institucional completa</span>
                    </div>

                    <div class="settings-field">
                        <label for="codigo" class="settings-label">CODIGO:</label>
                        <input type="text"
                               id="codigo"
                               name="codigo"
                               value="{{ old('codigo', $settings->codigo) }}"
                               required
                               maxlength="50"
                               class="settings-input">
                        <span class="settings-hint">Código asignado por la Contaduría General de la Nación (Ej: 269411001)</span>
                    </div>

                    <div class="settings-field">
                        <label for="fecha_corte" class="settings-label">FECHA DE CORTE:</label>
                        <input type="text"
                               id="fecha_corte"
                               name="fecha_corte"
                               list="trimestres_corte_list"
                               value="{{ old('fecha_corte', $settings->fecha_corte) }}"
                               required
                               maxlength="150"
                               class="settings-input">
                        <span class="settings-hint">Período oficial de corte trimestral CGN (Ej: 1 a 30 de Septiembre 2026)</span>
                        <datalist id="trimestres_corte_list">
                            <option value="1 a 31 de Marzo 2026">1er Trimestre (1 Ene - 31 Mar 2026)</option>
                            <option value="1 a 30 de Junio 2026">2do Trimestre (1 Abr - 30 Jun 2026)</option>
                            <option value="1 a 30 de Septiembre 2026">3er Trimestre (1 Jul - 30 Sep 2026)</option>
                            <option value="1 a 31 de Diciembre 2026">4to Trimestre (1 Oct - 31 Dic 2026)</option>
                        </datalist>
                    </div>

                    <div class="settings-field">
                        <label for="formato" class="settings-label">FORMATO CGN</label>
                        <input type="text"
                               id="formato"
                               name="formato"
                               value="{{ old('formato', $settings->formato) }}"
                               required
                               maxlength="150"
                               class="settings-input">
                        <span class="settings-hint">Identificador del reporte contable</span>
                    </div>

                    <div class="settings-field">
                        <label for="cifras" class="settings-label">EXPRESIÓN DE CIFRAS</label>
                        <input type="text"
                               id="cifras"
                               name="cifras"
                               value="{{ old('cifras', $settings->cifras) }}"
                               required
                               maxlength="100"
                               class="settings-input">
                        <span class="settings-hint">Ej: Cifras en Miles de Pesos</span>
                    </div>
                </div>

                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap">
                    <button type="submit" class="btn-pill-primary">
                        <i data-lucide="check" style="width:16px;height:16px"></i>
                        <span>Guardar ajustes</span>
                    </button>

                    <a href="{{ route('financial.reciprocals.index') }}" class="btn-pill-cancel">
                        <i data-lucide="arrow-left" style="width:16px;height:16px"></i>
                        <span>Volver a recíprocas</span>
                    </a>
                </div>
            </form>

        {{-- TAB 2: ENTIDADES RECÍPROCAS (CATÁLOGO DE CÓDIGO Y NOMBRE) --}}
        @else
            {{-- Sección: Listado y edición de entidades --}}
            <div class="settings-card">
                <div style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #123f6e; margin: 0">Catálogo de Entidades Recíprocas</h2>
                        <p style="font-size: 13px; color: #64748b; margin: 4px 0 0">
                            Administra los códigos y nombres de las entidades recíprocas utilizadas para el autocompletado y búsqueda contable.
                        </p>
                    </div>

                    {{-- Buscador de entidades --}}
                    <form method="GET" action="{{ route('financial.reciprocals.settings') }}" style="display: flex; align-items: center; gap: 8px">
                        <input type="hidden" name="tab" value="entidades">
                        <div class="filter-search-box">
                            <i data-lucide="search" style="width: 15px; height: 15px; color: #64748b; margin-right: 8px"></i>
                            <input type="text"
                                   name="buscar_entidad"
                                   value="{{ $searchEntity }}"
                                   placeholder="Buscar por código o nombre...">
                        </div>
                        <button type="submit" class="btn-pill-primary" style="min-height: 40px; padding: 8px 16px">
                            <span>Buscar</span>
                        </button>
                        @if(!empty($searchEntity))
                            <a href="{{ route('financial.reciprocals.settings', ['tab' => 'entidades']) }}" class="btn-pill-cancel" style="min-height: 40px; padding: 8px 14px" title="Limpiar búsqueda">
                                <i data-lucide="x" style="width: 15px; height: 15px"></i>
                            </a>
                        @endif
                    </form>
                </div>

                {{-- Listado de entidades existentes --}}
                @forelse($entities as $entity)
                    <div class="entity-row">
                        <form method="POST" action="{{ route('financial.reciprocals.entities.update', $entity) }}" class="entity-edit-form">
                            @csrf
                            @method('PUT')

                            {{-- Código --}}
                            <div>
                                <label style="display: block; font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 4px">
                                    Código Entidad
                                </label>
                                <input type="text"
                                       name="codigo_entidad_reciproca"
                                       value="{{ $entity->codigo_entidad_reciproca }}"
                                       required
                                       maxlength="50"
                                       class="settings-input badge-entity-code"
                                       style="padding: 8px 12px; font-size: 13.5px">
                            </div>

                            {{-- Nombre --}}
                            <div>
                                <label style="display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; font-weight: 600; text-transform: uppercase; color: #64748b; margin-bottom: 4px">
                                    <span>Nombre Entidad Recíproca</span>
                                    <span class="pill-status {{ $entity->is_active ? 'active' : 'inactive' }}">
                                        {{ $entity->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </label>
                                <input type="text"
                                       name="nombre_entidad_reciproca"
                                       value="{{ $entity->nombre_entidad_reciproca }}"
                                       required
                                       maxlength="255"
                                       class="settings-input"
                                       style="padding: 8px 12px; font-size: 13.5px">
                            </div>

                            {{-- Botón Guardar --}}
                            <div style="padding-top: 18px">
                                <button type="submit" class="icon-btn" title="Guardar cambios de {{ $entity->nombre_entidad_reciproca }}" aria-label="Guardar {{ $entity->nombre_entidad_reciproca }}">
                                    <i data-lucide="save" style="width: 17px; height: 17px"></i>
                                </button>
                            </div>
                        </form>

                        {{-- Botón Activar / Inactivar (toggle) --}}
                        <div style="padding-top: 18px">
                            <form method="POST" action="{{ route('financial.reciprocals.entities.toggle', $entity) }}" onsubmit="return confirm('¿Confirmas el cambio de estado de la entidad {{ $entity->nombre_entidad_reciproca }}?')">
                                @csrf
                                <button type="submit"
                                        class="icon-btn-toggle {{ $entity->is_active ? 'active' : 'inactive' }}"
                                        title="{{ $entity->is_active ? 'Inactivar entidad' : 'Reactivar entidad' }}"
                                        aria-label="{{ $entity->is_active ? 'Inactivar' : 'Reactivar' }} {{ $entity->nombre_entidad_reciproca }}">
                                    <i data-lucide="{{ $entity->is_active ? 'power-off' : 'power' }}" style="width: 17px; height: 17px"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div style="padding: 32px 16px; text-align: center; color: #64748b">
                        <i data-lucide="info" style="width: 32px; height: 32px; margin-bottom: 8px; stroke-width: 1.5"></i>
                        <p style="margin: 0; font-size: 14px">No se encontraron entidades recíprocas registradas{{ !empty($searchEntity) ? ' para la búsqueda actual.' : '.' }}</p>
                    </div>
                @endforelse

                {{-- Paginación --}}
                @if($entities->hasPages())
                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0">
                        {{ $entities->links() }}
                    </div>
                @endif
            </div>

            {{-- Sección: Agregar nueva entidad recíproca --}}
            <div class="settings-card">
                <div style="margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0">
                    <h3 style="font-size: 16px; font-weight: 700; color: #123f6e; margin: 0">Agregar Nueva Entidad Recíproca</h3>
                    <p style="font-size: 13px; color: #64748b; margin: 4px 0 0">
                        Ingresa el código oficial y la razón social institucional de la nueva entidad recíproca.
                    </p>
                </div>

                <form method="POST" action="{{ route('financial.reciprocals.entities.store') }}" class="entity-add-form">
                    @csrf
                    <div class="settings-field">
                        <label for="new_codigo" class="settings-label">Código Entidad <span style="color: #ef4444">*</span></label>
                        <input type="text"
                               id="new_codigo"
                               name="codigo_entidad_reciproca"
                               value="{{ old('codigo_entidad_reciproca') }}"
                               required
                               maxlength="50"
                               placeholder="Ej: 229911001"
                               class="settings-input badge-entity-code">
                    </div>

                    <div class="settings-field">
                        <label for="new_nombre" class="settings-label">Nombre Entidad Recíproca <span style="color: #ef4444">*</span></label>
                        <input type="text"
                               id="new_nombre"
                               name="nombre_entidad_reciproca"
                               value="{{ old('nombre_entidad_reciproca') }}"
                               required
                               maxlength="255"
                               placeholder="Ej: AGUAS DE BOGOTA S.A. E.S.P."
                               class="settings-input">
                    </div>

                    <div>
                        <button type="submit" class="btn-pill-primary" style="min-height: 46px">
                            <i data-lucide="plus" style="width: 17px; height: 17px"></i>
                            <span>Agregar entidad</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-layouts.app>
