<x-layouts.app title="{{ $incident->exists ? 'Editar incidente' : 'Nuevo incidente' }} | {{ config('app.name') }}" heading="{{ $incident->exists ? 'Editar '.$incident->ticket : 'Nuevo incidente' }}" subheading="Información del reporte">
    @include('incidents.styles')
    <div class="incident-page" style="max-width:1100px">
        @include('incidents.errors')
        @if($profileIncomplete)
            <div class="incident-profile-notice" role="alert">Falta información en tu perfil: nombre, cargo o área. <a href="{{ route('profile.edit') }}">Actualizar perfil</a></div>
        @endif
        <form method="POST" action="{{ $incident->exists ? route('incidents.update', $incident) : route('incidents.store') }}" enctype="multipart/form-data" class="card">
            @csrf
            @if($incident->exists) @method('PUT') @endif
            <section class="incident-section">
                <h2>Información de quien reporta</h2>
                <div class="incident-grid three">
                    <div><label for="reporter_name">Nombre completo</label><input id="reporter_name" name="reporter_name" readonly aria-readonly="true" class="input-field" value="{{ $reporter['reporter_name'] }}"></div>
                    <div><label for="reporter_position">Cargo</label><input id="reporter_position" name="reporter_position" readonly aria-readonly="true" class="input-field" value="{{ $reporter['reporter_position'] }}"></div>
                    <div><label for="reporter_area">Área / dependencia</label><input id="reporter_area" name="reporter_area" readonly aria-readonly="true" class="input-field" value="{{ $reporter['reporter_area'] }}"></div>
                </div>
            </section>
            <section class="incident-section">
                <h2>Ubicación del incidente</h2>
                <div class="incident-grid">
                    @include('incidents.select', ['field' => 'zone', 'label' => 'Sede', 'required' => false])
                    <div><label for="location">Ubicación exacta <span class="incident-required">*</span></label><input id="location" name="location" required maxlength="500" class="input-field" value="{{ old('location', $incident->location) }}"></div>
                </div>
            </section>
            <section class="incident-section">
                <h2>Detalles de Incidente o Riesgo</h2>
                <div class="incident-grid">
                    <div>
                        <label for="failure_type">Tipo de falla <span class="incident-required">*</span></label>
                        @php
                            $defaultType = old('failure_type', $incident->failure_type ?? ($incident->failure?->type === 'prioritized_risk' ? 'risk' : 'general'));
                        @endphp
                        <select id="failure_type" name="failure_type" class="input-field" required>
                            <option value="general" @selected($defaultType === 'general')>Incidentes generales</option>
                            <option value="risk" @selected($defaultType === 'risk')>Riesgo priorizado</option>
                        </select>
                    </div>

                    <div id="general_failure_container">
                        <label for="general_failure_id">Incidente general / Falla <span class="incident-required">*</span></label>
                        <select id="general_failure_id" name="failure_id" class="input-field" required>
                            <option value="">Seleccionar falla...</option>
                            @foreach($options->get('failure', collect()) as $option)
                                <option value="{{ $option->id }}" data-position="{{ $option->responsible_position }}" @selected(old('failure_id', $incident->failure_id) == $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactivo)' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="risk_failure_container" style="display:none">
                        <label for="risk_failure_id">Riesgo priorizado <span class="incident-required">*</span></label>
                        <select id="risk_failure_id" name="failure_id" class="input-field" disabled>
                            <option value="">Seleccionar riesgo...</option>
                            @foreach($options->get('prioritized_risk', collect()) as $option)
                                <option value="{{ $option->id }}" data-position="{{ $option->responsible_position }}" @selected(old('failure_id', $incident->failure_id) == $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactivo)' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="risk_responsible_section" class="incident-full" style="background:rgba(18,63,110,0.03);border:1px solid rgba(18,63,110,0.1);border-radius:8px;padding:16px 20px">
                        <div style="font-weight:600;font-size:13px;color:#123f6e;margin-bottom:12px;display:flex;align-items:center;gap:6px">
                            <i data-lucide="user-check" style="width:16px;height:16px;color:#123f6e"></i> Responsable asignado para notificación <span class="incident-required">*</span>
                        </div>
                        <input type="hidden" id="responsible_position_hidden" name="responsible_position" value="{{ old('responsible_position', $incident->responsible_position) }}">
                        <input type="hidden" id="responsible_name_hidden" name="responsible_name" value="{{ old('responsible_name', $incident->responsible_name) }}">
                        
                        <div style="margin-bottom:12px">
                            <label for="responsible_search" style="font-weight:500;font-size:13px;color:#475569;display:block;margin-bottom:6px">Buscar persona responsable</label>
                            <input type="text" id="responsible_search" placeholder="Escribe el nombre, cargo o correo para filtrar..." class="input-field" style="margin-bottom:8px">
                            <label for="responsible_user_id" style="font-weight:500;font-size:13px;color:#475569;display:block;margin-bottom:6px">Persona responsable <span class="incident-required">*</span></label>
                            <select id="responsible_user_id" name="responsible_user_id" required class="input-field" style="cursor:pointer">
                                <option value="">-- Selecciona el responsable asignado * --</option>
                                @foreach($activeUsers ?? [] as $u)
                                    <option value="{{ $u->id }}"
                                        data-name="{{ trim($u->name.' '.$u->last_name) }}"
                                        data-position="{{ $u->position ?? '' }}"
                                        data-email="{{ $u->email }}"
                                        data-search="{{ $u->name }} {{ $u->last_name }} {{ $u->position }} {{ $u->email }} {{ $u->document_number }}"
                                        @selected(old('responsible_user_id', $incident->responsible_user_id ?? $incident->responsibleUser?->id) == $u->id)>
                                        {{ $u->name }} {{ $u->last_name }} — {{ $u->position ?: 'Sin cargo' }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('responsible_user_id')
                                <span class="incident-error" style="color:#ef4444;font-size:12px;display:block;margin-top:4px">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="incident-grid">
                            <div>
                                <label for="responsible_position_display">Cargo del responsable</label>
                                <input id="responsible_position_display" class="input-field" readonly aria-readonly="true" value="{{ old('responsible_position', $incident->responsible_position) ?: 'Sin cargo asignado' }}" placeholder="Selecciona una persona...">
                            </div>
                            <div>
                                <label for="responsible_email_display">Correo para notificación</label>
                                <input id="responsible_email_display" class="input-field" readonly aria-readonly="true" value="{{ $incident->responsibleUser?->email ?: 'Sin correo asignado' }}" placeholder="Selecciona una persona...">
                            </div>
                        </div>
                        <p class="incident-muted" style="margin-top:10px">Al registrar este reporte, se enviará una notificación automática por correo electrónico a la persona seleccionada.</p>
                    </div>

                    @include('incidents.select', ['field' => 'impact', 'label' => '¿El incidente afecta la operación?', 'required' => true])
                    <div class="incident-full"><label for="description">Descripción del problema <span class="incident-required">*</span></label><textarea id="description" name="description" required maxlength="5000" rows="5" class="input-field">{{ old('description', $incident->description) }}</textarea></div>
                    <div class="incident-full"><label for="affected_assets">Equipos o activos afectados</label><input id="affected_assets" name="affected_assets" maxlength="500" class="input-field" value="{{ old('affected_assets', $incident->affected_assets) }}"></div>
                    <div class="incident-full">@include('incidents.upload')
                        @if($incident->exists) @include('incidents.evidence', ['editable' => true]) @endif
                    </div>
                </div>
            </section>
            <section class="incident-section">
                <h2>Prioridad del incidente</h2>
                <div class="incident-grid">
                    @include('incidents.select', ['field' => 'priority', 'label' => 'Prioridad sugerida', 'required' => true])
                </div>
            </section>
            <div class="incident-section incident-buttons" style="justify-content:flex-end">
                <a class="btn-secondary" href="{{ $incident->exists ? route('incidents.show', $incident) : route('incidents.index') }}">Cancelar</a>
                <button class="btn-primary" @disabled($profileIncomplete)><i data-lucide="save" style="width:16px;height:16px"></i> {{ $incident->exists ? 'Guardar cambios' : 'Registrar incidente' }}</button>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const failureTypeSelect = document.getElementById('failure_type');
            const generalContainer = document.getElementById('general_failure_container');
            const riskContainer = document.getElementById('risk_failure_container');
            const generalSelect = document.getElementById('general_failure_id');
            const riskSelect = document.getElementById('risk_failure_id');

            const userSearch = document.getElementById('responsible_search');
            const userSelect = document.getElementById('responsible_user_id');
            const posDisplay = document.getElementById('responsible_position_display');
            const emailDisplay = document.getElementById('responsible_email_display');
            const posHidden = document.getElementById('responsible_position_hidden');
            const nameHidden = document.getElementById('responsible_name_hidden');

            function syncResponsibleUser() {
                if (!userSelect) return;
                const opt = userSelect.selectedIndex >= 0 ? userSelect.options[userSelect.selectedIndex] : null;
                if (opt && opt.value) {
                    const name = opt.dataset.name || '';
                    const pos = opt.dataset.position || '';
                    const email = opt.dataset.email || '';
                    if (posDisplay) posDisplay.value = pos || 'Sin cargo asignado';
                    if (emailDisplay) emailDisplay.value = email || 'Sin correo asignado';
                    if (posHidden) posHidden.value = pos;
                    if (nameHidden) nameHidden.value = name;
                } else {
                    if (posDisplay) posDisplay.value = 'Sin cargo asignado';
                    if (emailDisplay) emailDisplay.value = 'Sin correo asignado';
                    if (posHidden) posHidden.value = '';
                    if (nameHidden) nameHidden.value = '';
                }
            }

            if (userSelect) {
                userSelect.addEventListener('change', syncResponsibleUser);
            }

            if (userSearch && userSelect) {
                userSearch.addEventListener('input', function() {
                    const term = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
                    userSelect.querySelectorAll('option').forEach(option => {
                        if (!option.value) return;
                        const text = (option.dataset.search || option.textContent).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                        option.hidden = !text.includes(term);
                    });
                });
            }

            // If userSelect is not yet explicitly set but hidden has an existing position/name
            if (userSelect && !userSelect.value && (posHidden?.value || nameHidden?.value)) {
                const matchOpt = Array.from(userSelect.options).find(o => 
                    (nameHidden?.value && o.dataset.name === nameHidden.value) ||
                    (posHidden?.value && o.dataset.position && o.dataset.position.toLowerCase() === posHidden.value.toLowerCase())
                );
                if (matchOpt) {
                    userSelect.value = matchOpt.value;
                }
            }
            syncResponsibleUser();

            function syncType() {
                const isRisk = failureTypeSelect && failureTypeSelect.value === 'risk';
                if (isRisk) {
                    if (generalContainer) generalContainer.style.display = 'none';
                    if (generalSelect) {
                        generalSelect.disabled = true;
                        generalSelect.required = false;
                    }
                    if (riskContainer) riskContainer.style.display = 'block';
                    if (riskSelect) {
                        riskSelect.disabled = false;
                        riskSelect.required = true;
                    }
                } else {
                    if (riskContainer) riskContainer.style.display = 'none';
                    if (riskSelect) {
                        riskSelect.disabled = true;
                        riskSelect.required = false;
                    }
                    if (generalContainer) generalContainer.style.display = 'block';
                    if (generalSelect) {
                        generalSelect.disabled = false;
                        generalSelect.required = true;
                    }
                }
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }

            if (failureTypeSelect) {
                failureTypeSelect.addEventListener('change', syncType);
                syncType();
            }
        });
    </script>
</x-layouts.app>
