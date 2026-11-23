<x-layouts.app title="Editar Operación Recíproca | {{ config('app.name') }}" heading="Financiera" subheading="Editar operación recíproca #{{ $reciprocal->id }}">
    <style>
        .reciprocal-form-container {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
        }

        .reciprocal-form-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
        }
        .reciprocal-form-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: #123f6e;
            margin: 0;
        }
        .reciprocal-form-header p {
            font-size: 13px;
            color: #64748b;
            margin: 4px 0 0;
        }

        .btn-pill-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 26px;
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

        .btn-pill-cancel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 26px;
            border-radius: 9999px;
            background: #e2e8f0;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid #cbd5e1;
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 44px;
        }
        .btn-pill-cancel:hover {
            background: #cbd5e1;
            color: #0f172a;
        }

        .reciprocal-form-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(18, 63, 110, 0.1);
            box-shadow: 0 4px 20px rgba(18, 63, 110, 0.04);
            padding: 32px 36px;
        }

        .form-grid-three {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        @media (max-width: 900px) {
            .form-grid-three {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 600px) {
            .form-grid-three {
                grid-template-columns: 1fr;
            }
        }

        .form-field-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-field-label {
            font-size: 13.5px;
            font-weight: 600;
            color: #334155;
        }
        .form-field-label .required-star {
            color: #ef4444;
        }
        .form-pill-input {
            width: 100%;
            background: #e2e8f0;
            border: 1px solid #cbd5e1;
            border-radius: 9999px;
            padding: 12px 20px;
            font-size: 14px;
            color: #1e293b;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
            min-height: 46px;
        }
        .form-pill-input:focus {
            background: #ffffff;
            border-color: #123f6e;
            box-shadow: 0 0 0 3px rgba(18, 63, 110, 0.12);
        }
        .form-pill-input::placeholder {
            color: #94a3b8;
        }
        .form-field-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 2px;
        }

        .form-actions-bar {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 16px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
    </style>

    <div class="reciprocal-form-container">

        <div class="reciprocal-form-header">
            <div>
                <h2>Editar Operación Recíproca</h2>
                <p>Modifica los campos contables de la operación recíproca #{{ $reciprocal->id }}</p>
            </div>
            <div>
                <a href="{{ route('financial.reciprocals.index') }}" class="btn-pill-cancel" title="Volver al listado de operaciones recíprocas">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px"></i>
                    <span>Volver al listado</span>
                </a>
            </div>
        </div>

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

        {{-- Form matching 3-column layout (Sin campo de observaciones) --}}
        <form method="POST" action="{{ route('financial.reciprocals.update', $reciprocal) }}" class="reciprocal-form-card">
            @csrf
            @method('PUT')

            {{-- Row 1: Codigo contable subcuenta | Nombre subcuenta | Codigo Entidad reciproca --}}
            <div class="form-grid-three">
                <div class="form-field-group">
                    <label for="codigo_contable_subcuenta" class="form-field-label">
                        Codigo contable subcuenta <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="codigo_contable_subcuenta"
                           name="codigo_contable_subcuenta"
                           value="{{ old('codigo_contable_subcuenta', $reciprocal->codigo_contable_subcuenta) }}"
                           required
                           maxlength="50"
                           class="form-pill-input">
                    @error('codigo_contable_subcuenta')
                        <span class="form-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-field-group">
                    <label for="nombre_subcuenta" class="form-field-label">
                        Nombre subcuenta <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="nombre_subcuenta"
                           name="nombre_subcuenta"
                           value="{{ old('nombre_subcuenta', $reciprocal->nombre_subcuenta) }}"
                           required
                           maxlength="255"
                           class="form-pill-input">
                    @error('nombre_subcuenta')
                        <span class="form-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-field-group">
                    <label for="codigo_entidad_reciproca" class="form-field-label">
                        Codigo Entidad reciproca <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="codigo_entidad_reciproca"
                           name="codigo_entidad_reciproca"
                           list="entities_codes_list"
                           value="{{ old('codigo_entidad_reciproca', $reciprocal->codigo_entidad_reciproca) }}"
                           required
                           maxlength="50"
                           class="form-pill-input">
                    @error('codigo_entidad_reciproca')
                        <span class="form-field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Row 2: Nombre entidad reciproca | Valor corriente | Valor No Corriente --}}
            <div class="form-grid-three">
                <div class="form-field-group">
                    <label for="nombre_entidad_reciproca" class="form-field-label">
                        Nombre entidad reciproca <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="nombre_entidad_reciproca"
                           name="nombre_entidad_reciproca"
                           list="entities_names_list"
                           value="{{ old('nombre_entidad_reciproca', $reciprocal->nombre_entidad_reciproca) }}"
                           required
                           maxlength="255"
                           class="form-pill-input">
                    @error('nombre_entidad_reciproca')
                        <span class="form-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-field-group">
                    <label for="valor_corriente" class="form-field-label">
                        Valor corriente <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="valor_corriente"
                           name="valor_corriente"
                           value="{{ old('valor_corriente', $reciprocal->valor_corriente_input) }}"
                           required
                           class="form-pill-input">
                    @error('valor_corriente')
                        <span class="form-field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-field-group">
                    <label for="valor_no_corriente" class="form-field-label">
                        Valor No Corriente <span class="required-star">*</span>
                    </label>
                    <input type="text"
                           id="valor_no_corriente"
                           name="valor_no_corriente"
                           value="{{ old('valor_no_corriente', $reciprocal->valor_no_corriente_input) }}"
                           required
                           class="form-pill-input">
                    @error('valor_no_corriente')
                        <span class="form-field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Row 3: Solo fecha --}}
            <div class="form-grid-three">
                <div class="form-field-group">
                    <label for="fecha" class="form-field-label">
                        Fecha <span class="required-star">*</span>
                    </label>
                    <input type="date"
                           id="fecha"
                           name="fecha"
                           value="{{ old('fecha', $reciprocal->fecha?->format('Y-m-d')) }}"
                           required
                           class="form-pill-input">
                    @error('fecha')
                        <span class="form-field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Bottom Buttons con botón Volver --}}
            <div class="form-actions-bar">
                <button type="submit" class="btn-pill-primary">
                    <i data-lucide="check" style="width: 16px; height: 16px"></i>
                    <span>Guardar cambios</span>
                </button>

                <a href="{{ route('financial.reciprocals.index') }}" class="btn-pill-cancel">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px"></i>
                    <span>Volver al listado</span>
                </a>
            </div>
        </form>

    </div>

    {{-- Datalists for automatic lookup and autocomplete --}}
    <datalist id="entities_codes_list">
        @foreach($knownEntities ?? [] as $ent)
            <option value="{{ $ent->codigo_entidad_reciproca }}">{{ $ent->nombre_entidad_reciproca }}</option>
        @endforeach
    </datalist>

    <datalist id="entities_names_list">
        @foreach($knownEntities ?? [] as $ent)
            <option value="{{ $ent->nombre_entidad_reciproca }}">{{ $ent->codigo_entidad_reciproca }}</option>
        @endforeach
    </datalist>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const codeInput = document.getElementById('codigo_entidad_reciproca');
            const nameInput = document.getElementById('nombre_entidad_reciproca');
            if (!codeInput || !nameInput) return;

            const codeToName = {
                @foreach($knownEntities ?? [] as $ent)
                    "{{ $ent->codigo_entidad_reciproca }}": @json($ent->nombre_entidad_reciproca),
                @endforeach
            };

            const nameToCode = {
                @foreach($knownEntities ?? [] as $ent)
                    @json($ent->nombre_entidad_reciproca): "{{ $ent->codigo_entidad_reciproca }}",
                @endforeach
            };

            // When user types or selects a code, automatically fill the entity name
            codeInput.addEventListener('input', () => {
                const val = codeInput.value.trim();
                if (codeToName[val]) {
                    nameInput.value = codeToName[val];
                }
            });

            // When user types or selects an entity name, automatically fill the code
            nameInput.addEventListener('input', () => {
                const val = nameInput.value.trim();
                if (nameToCode[val]) {
                    codeInput.value = nameToCode[val];
                }
            });
        });
    </script>
</x-layouts.app>