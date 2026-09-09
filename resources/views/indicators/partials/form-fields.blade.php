@php
    $indicator = $indicator ?? null;
    $max = \App\Models\Indicator::SCALE_MAX;
@endphp

<div class="sheet-block">
    <h3 class="sheet-title">Ficha tecnica</h3>

    <div class="sheet-grid">
        {{-- Fila 1: clasificacion y procesos --}}
        <div class="field">
            <label class="field-label">Categoria</label>
            <select name="category" class="input-field @error('category') error-field @enderror">
                <option value="">Sin clasificar</option>
                @foreach(\App\Models\Indicator::CATEGORIES as $value => $label)
                    <option value="{{ $value }}" {{ old('category', $indicator->category ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <p class="field-hint">Define en cual sub-menu de Indicadores aparece.</p>
            @error('category') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label class="field-label">Proceso</label>
            <select name="process_id" class="input-field @error('process_id') error-field @enderror">
                <option value="">Seleccionar...</option>
                @foreach($processes as $process)
                    <option value="{{ $process->id }}" {{ (int) old('process_id', $indicator->process_id ?? 0) === $process->id ? 'selected' : '' }}>{{ $process->name }}</option>
                @endforeach
            </select>
            @error('process_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label class="field-label">Subproceso</label>
            <select name="subprocess_id" class="input-field @error('subprocess_id') error-field @enderror">
                <option value="">Seleccionar...</option>
                @foreach($subprocesses as $subprocess)
                    <option value="{{ $subprocess->id }}" {{ (int) old('subprocess_id', $indicator->subprocess_id ?? 0) === $subprocess->id ? 'selected' : '' }}>{{ $subprocess->full_name }}</option>
                @endforeach
            </select>
            @error('subprocess_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        {{-- Fila 2: identificacion, responsable y unidad --}}
        <div class="field">
            <label class="field-label">Nombre <span class="req">*</span></label>
            <input name="name" type="text" maxlength="255" required
                value="{{ old('name', $indicator->name ?? '') }}"
                class="input-field @error('name') error-field @enderror"
                placeholder="Ej: Cumplimiento de ANS en soporte">
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label class="field-label">Responsable <span class="req">*</span></label>
            <select name="responsible_user_id" required class="input-field @error('responsible_user_id') error-field @enderror">
                <option value="">Seleccionar...</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ (int) old('responsible_user_id', $indicator->responsible_user_id ?? 0) === $u->id ? 'selected' : '' }}>
                        {{ $u->name }} {{ $u->last_name }}
                    </option>
                @endforeach
            </select>
            @error('responsible_user_id') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label class="field-label">Unidad de medicion <span class="req">*</span></label>
            <select name="measurement_unit" required class="input-field @error('measurement_unit') error-field @enderror">
                <option value="">Seleccionar...</option>
                @foreach(\App\Models\Indicator::MEASUREMENT_UNITS as $value => $label)
                    <option value="{{ $value }}" {{ old('measurement_unit', $indicator->measurement_unit ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('measurement_unit') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        {{-- Fila 3: medicion --}}
        <div class="field">
            <label class="field-label">Frecuencia <span class="req">*</span></label>
            <select name="frequency" required class="input-field @error('frequency') error-field @enderror">
                <option value="">Seleccionar...</option>
                @foreach(\App\Models\Indicator::FREQUENCIES as $value => $label)
                    <option value="{{ $value }}" {{ old('frequency', $indicator->frequency ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('frequency') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label class="field-label">Tipo <span class="req">*</span></label>
            <select name="type" required class="input-field @error('type') error-field @enderror">
                <option value="">Seleccionar...</option>
                @foreach(\App\Models\Indicator::TYPES as $value => $label)
                    <option value="{{ $value }}" {{ old('type', $indicator->type ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('type') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label class="field-label">Meta <span class="req">*</span></label>
            <select name="goal" required class="input-field @error('goal') error-field @enderror">
                @for($i = 1; $i <= $max; $i++)
                    <option value="{{ $i }}" {{ (int) old('goal', $indicator->goal ?? 100) === $i ? 'selected' : '' }}>{{ $i }}%</option>
                @endfor
            </select>
            <p class="field-hint">Desplegable de 1 a {{ $max }}%. El limite es {{ $max }}%.</p>
            @error('goal') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        {{-- Campos amplios: ocupan la fila completa --}}
        <div class="field field-full">
            <label class="field-label">Objetivo del indicador <span class="req">*</span></label>
            <textarea name="objective" rows="4" required
                class="input-field @error('objective') error-field @enderror"
                placeholder="Que busca medir este indicador...">{{ old('objective', $indicator->objective ?? '') }}</textarea>
            @error('objective') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field field-full">
            <label class="field-label">Formula <span class="req">*</span></label>
            <input name="formula" type="text" maxlength="255" required
                value="{{ old('formula', $indicator->formula ?? '') }}"
                class="input-field @error('formula') error-field @enderror"
                placeholder="Ej: (Campo 2 / Campo 1) x 100">
            @error('formula') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field field-full">
            <label class="field-label">Aspectos metodologicos</label>
            <textarea name="methodological_aspects" rows="5"
                class="input-field @error('methodological_aspects') error-field @enderror"
                placeholder="Fuente de datos, supuestos, exclusiones...">{{ old('methodological_aspects', $indicator->methodological_aspects ?? '') }}</textarea>
            @error('methodological_aspects') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

<div class="sheet-block" style="margin-top:18px"
    x-data="{
        acceptable: {{ (int) old('threshold_acceptable', $indicator->threshold_acceptable ?? 80) }},
        satisfactory: {{ (int) old('threshold_satisfactory', $indicator->threshold_satisfactory ?? 95) }},
        clamp() {
            this.acceptable = Math.min({{ $max }}, Math.max(1, parseInt(this.acceptable) || 1));
            this.satisfactory = Math.min({{ $max }}, Math.max(1, parseInt(this.satisfactory) || 1));
        }
    }">
    <h3 class="sheet-title">Rango de evaluacion</h3>

    <div class="range-grid">
        <div class="range-card range-bad">
            <p class="range-name">Insatisfactorio</p>
            <p class="range-value">&lt; <span x-text="acceptable"></span>%</p>
        </div>
        <div class="range-card range-mid">
            <p class="range-name">Aceptable</p>
            <p class="range-value">&ge; <span x-text="acceptable"></span>% y &lt; <span x-text="satisfactory"></span>%</p>
        </div>
        <div class="range-card range-good">
            <p class="range-name">Satisfactorio</p>
            <p class="range-value">&ge; <span x-text="satisfactory"></span>%</p>
        </div>
    </div>

    <div class="range-inputs">
        <div class="field">
            <label class="field-label">Desde donde es aceptable <span class="req">*</span></label>
            <input name="threshold_acceptable" type="number" min="1" max="{{ $max }}" required
                x-model.number="acceptable" @input="clamp()"
                class="input-field @error('threshold_acceptable') error-field @enderror">
            @error('threshold_acceptable') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <div class="field">
            <label class="field-label">Desde donde es satisfactorio <span class="req">*</span></label>
            <input name="threshold_satisfactory" type="number" min="1" max="{{ $max }}" required
                x-model.number="satisfactory" @input="clamp()"
                class="input-field @error('threshold_satisfactory') error-field @enderror">
            @error('threshold_satisfactory') <p class="field-error">{{ $message }}</p> @enderror
        </div>
        <p class="range-hint">
            <i data-lucide="info" style="width:13px;height:13px"></i>
            Ambos van de 1 a {{ $max }}%, y el satisfactorio debe ser mayor que el aceptable. Con estos rangos se clasifica y colorea cada resultado automaticamente.
        </p>
    </div>
</div>
