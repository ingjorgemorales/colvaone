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
                                <option value="{{ $option->id }}" data-position="{{ $option->responsible_position }}" data-user-id="{{ $failureMeta[$option->id]['user_id'] ?? '' }}" @selected(old('failure_id', $incident->failure_id) == $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactivo)' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="risk_failure_container" style="display:none">
                        <label for="risk_failure_id">Riesgo priorizado <span class="incident-required">*</span></label>
                        <select id="risk_failure_id" name="failure_id" class="input-field" disabled>
                            <option value="">Seleccionar riesgo...</option>
                            @foreach($options->get('prioritized_risk', collect()) as $option)
                                <option value="{{ $option->id }}" data-position="{{ $option->responsible_position }}" data-user-id="{{ $failureMeta[$option->id]['user_id'] ?? '' }}" @selected(old('failure_id', $incident->failure_id) == $option->id)>{{ $option->name }}{{ $option->is_active ? '' : ' (inactivo)' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="risk_responsible_section" class="incident-full" style="background:rgba(18,63,110,0.03);border:1px solid rgba(18,63,110,0.1);border-radius:8px;padding:16px 20px">
                        <div style="font-weight:600;font-size:13px;color:#123f6e;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                            <div style="display:flex;align-items:center;gap:6px">
                                <i data-lucide="user-check" style="width:16px;height:16px;color:#123f6e"></i> Responsable asignado para notificación <span class="incident-required">*</span>
                            </div>
                            <span id="responsible_auto_badge" style="display:none;font-size:11px;color:#0284c7;background:#e0f2fe;border:1px solid #bae6fd;padding:2px 8px;border-radius:12px;font-weight:500;align-items:center;gap:4px">
                                <i data-lucide="sparkles" style="width:12px;height:12px"></i> Asignado automáticamente
                            </span>
                        </div>
                        <input type="hidden" id="responsible_position_hidden" name="responsible_position" value="{{ old('responsible_position', $incident->responsible_position) }}">
                        <input type="hidden" id="responsible_name_hidden" name="responsible_name" value="{{ old('responsible_name', $incident->responsible_name) }}">
                        
                        <div style="margin-bottom:12px;position:relative" id="responsible_combobox_container">
                            <label for="responsible_combobox_input" style="font-weight:500;font-size:13px;color:#475569;display:block;margin-bottom:6px">Persona responsable <span class="incident-required">*</span></label>
                            <div style="position:relative;display:flex;align-items:center">
                                <input type="text"
                                       id="responsible_combobox_input"
                                       class="input-field"
                                       placeholder="Buscar o seleccionar persona responsable..."
                                       autocomplete="off"
                                       required
                                       style="cursor:pointer;padding-right:60px"
                                       aria-autocomplete="list"
                                       aria-expanded="false">
                                <div style="position:absolute;right:8px;display:flex;align-items:center;gap:2px">
                                    <button type="button" id="responsible_clear_btn" style="display:none;background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;border-radius:4px;line-height:1" title="Limpiar selección">
                                        <i data-lucide="x" style="width:14px;height:14px"></i>
                                    </button>
                                    <button type="button" id="responsible_toggle_btn" style="background:none;border:none;color:#64748b;cursor:pointer;padding:4px;border-radius:4px;line-height:1" title="Mostrar lista">
                                        <i data-lucide="chevron-down" style="width:16px;height:16px"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Native select kept in sync for form submission and validation -->
                            <select id="responsible_user_id" name="responsible_user_id" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;width:1px;height:1px;margin:0;padding:0">
                                <option value="">-- Selecciona el responsable asignado * --</option>
                                @foreach($activeUsers ?? [] as $u)
                                    <option value="{{ $u->id }}"
                                        data-name="{{ trim($u->name.' '.$u->last_name) }}"
                                        data-position="{{ $u->position ?? '' }}"
                                        data-email="{{ $u->email }}"
                                        data-search="{{ mb_strtolower(trim($u->name.' '.$u->last_name.' '.($u->position ?? '').' '.$u->email.' '.($u->document_number ?? ''))) }}"
                                        @selected(old('responsible_user_id', $incident->responsible_user_id ?? $incident->responsibleUser?->id) == $u->id)>
                                        {{ $u->name }} {{ $u->last_name }} — {{ $u->position ?: 'Sin cargo' }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>

                            <!-- Floating dropdown list with active users -->
                            <div id="responsible_dropdown" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:60;margin-top:4px;background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);max-height:250px;overflow-y:auto">
                                @foreach($activeUsers ?? [] as $u)
                                    <div class="responsible-option"
                                         data-id="{{ $u->id }}"
                                         data-name="{{ trim($u->name.' '.$u->last_name) }}"
                                         data-position="{{ $u->position ?? '' }}"
                                         data-email="{{ $u->email }}"
                                         data-search="{{ mb_strtolower(trim($u->name.' '.$u->last_name.' '.($u->position ?? '').' '.$u->email.' '.($u->document_number ?? ''))) }}">
                                        <div style="font-weight:600;font-size:13px;color:#1e293b">{{ $u->name }} {{ $u->last_name }}</div>
                                        <div style="font-size:12px;color:#64748b;margin-top:2px;display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                            <span style="color:#123f6e;font-weight:500">{{ $u->position ?: 'Sin cargo' }}</span>
                                            <span>•</span>
                                            <span>{{ $u->email }}</span>
                                        </div>
                                    </div>
                                @endforeach
                                <div id="responsible_no_results" style="display:none;padding:14px;text-align:center;color:#94a3b8;font-size:13px">
                                    No se encontraron personas con ese criterio.
                                </div>
                            </div>

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

            const userSelect = document.getElementById('responsible_user_id');
            const comboboxInput = document.getElementById('responsible_combobox_input');
            const dropdown = document.getElementById('responsible_dropdown');
            const clearBtn = document.getElementById('responsible_clear_btn');
            const toggleBtn = document.getElementById('responsible_toggle_btn');
            const autoBadge = document.getElementById('responsible_auto_badge');
            const noResults = document.getElementById('responsible_no_results');
            const options = dropdown ? dropdown.querySelectorAll('.responsible-option') : [];

            const posDisplay = document.getElementById('responsible_position_display');
            const emailDisplay = document.getElementById('responsible_email_display');
            const posHidden = document.getElementById('responsible_position_hidden');
            const nameHidden = document.getElementById('responsible_name_hidden');

            let isAutoAssigned = false;

            function formatUserLabel(name, position, email) {
                if (!name) return '';
                let label = name;
                if (position) label += ' — ' + position;
                if (email) label += ' (' + email + ')';
                return label;
            }

            function syncResponsibleFields(userId, name, position, email, isAuto = false) {
                if (userSelect) {
                    userSelect.value = userId || '';
                }
                if (userId && name) {
                    if (comboboxInput) {
                        comboboxInput.value = formatUserLabel(name, position, email);
                        comboboxInput.setCustomValidity('');
                    }
                    if (posDisplay) posDisplay.value = position || 'Sin cargo asignado';
                    if (emailDisplay) emailDisplay.value = email || 'Sin correo asignado';
                    if (posHidden) posHidden.value = position || '';
                    if (nameHidden) nameHidden.value = name || '';
                    if (clearBtn) clearBtn.style.display = 'block';
                    isAutoAssigned = isAuto;
                    if (autoBadge) autoBadge.style.display = isAuto ? 'inline-flex' : 'none';
                } else {
                    if (comboboxInput) comboboxInput.value = '';
                    if (posDisplay) posDisplay.value = 'Sin cargo asignado';
                    if (emailDisplay) emailDisplay.value = 'Sin correo asignado';
                    if (posHidden) posHidden.value = '';
                    if (nameHidden) nameHidden.value = '';
                    if (clearBtn) clearBtn.style.display = 'none';
                    isAutoAssigned = false;
                    if (autoBadge) autoBadge.style.display = 'none';
                }

                options.forEach(opt => {
                    const selected = userId && String(opt.dataset.id) === String(userId);
                    opt.style.background = selected ? 'rgba(18,63,110,0.08)' : '';
                    opt.style.fontWeight = selected ? '600' : 'normal';
                });

                if (window.lucide) window.lucide.createIcons();
            }

            function selectUserById(userId, isAuto = false) {
                if (!userId) {
                    syncResponsibleFields('', '', '', '', false);
                    return;
                }
                const opt = Array.from(userSelect.options).find(o => String(o.value) === String(userId));
                if (opt && opt.value) {
                    syncResponsibleFields(opt.value, opt.dataset.name, opt.dataset.position, opt.dataset.email, isAuto);
                } else {
                    syncResponsibleFields('', '', '', '', false);
                }
            }

            function selectUserByPosition(position, isAuto = false) {
                if (!position) return false;
                const cleanPos = position.toLowerCase().trim();
                const opt = Array.from(userSelect.options).find(o => {
                    return o.dataset.position && o.dataset.position.toLowerCase().trim() === cleanPos;
                });
                if (opt && opt.value) {
                    syncResponsibleFields(opt.value, opt.dataset.name, opt.dataset.position, opt.dataset.email, isAuto);
                    return true;
                }
                return false;
            }

            function autoSyncResponsibleFromFailure() {
                const isRisk = failureTypeSelect && failureTypeSelect.value === 'risk';
                const activeSelect = isRisk ? riskSelect : generalSelect;
                if (!activeSelect || activeSelect.selectedIndex < 0) return;

                const selectedOption = activeSelect.options[activeSelect.selectedIndex];
                if (!selectedOption || !selectedOption.value) {
                    if (isAutoAssigned) {
                        syncResponsibleFields('', '', '', '', false);
                    }
                    return;
                }

                const targetUserId = selectedOption.dataset.userId;
                const targetPosition = selectedOption.dataset.position;

                if (targetUserId) {
                    selectUserById(targetUserId, true);
                } else if (targetPosition) {
                    const matched = selectUserByPosition(targetPosition, true);
                    if (!matched && isAutoAssigned) {
                        syncResponsibleFields('', '', '', '', false);
                    }
                } else if (isAutoAssigned) {
                    syncResponsibleFields('', '', '', '', false);
                }
            }

            if (generalSelect) {
                generalSelect.addEventListener('change', autoSyncResponsibleFromFailure);
            }
            if (riskSelect) {
                riskSelect.addEventListener('change', autoSyncResponsibleFromFailure);
            }

            function openDropdown() {
                if (!dropdown) return;
                dropdown.style.display = 'block';
                if (comboboxInput) comboboxInput.setAttribute('aria-expanded', 'true');
                filterOptions(comboboxInput ? comboboxInput.value : '');
            }

            function closeDropdown() {
                if (!dropdown) return;
                dropdown.style.display = 'none';
                if (comboboxInput) {
                    comboboxInput.setAttribute('aria-expanded', 'false');
                    if (userSelect && userSelect.value) {
                        const opt = userSelect.options[userSelect.selectedIndex];
                        if (opt && opt.value) {
                            comboboxInput.value = formatUserLabel(opt.dataset.name, opt.dataset.position, opt.dataset.email);
                        }
                    } else {
                        comboboxInput.value = '';
                    }
                }
            }

            function filterOptions(searchTerm) {
                const term = (searchTerm || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim();
                let visibleCount = 0;
                options.forEach(opt => {
                    const text = (opt.dataset.search || opt.textContent).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    const matches = !term || text.includes(term);
                    opt.style.display = matches ? 'block' : 'none';
                    if (matches) visibleCount++;
                });
                if (noResults) {
                    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
                }
            }

            if (comboboxInput) {
                comboboxInput.addEventListener('focus', function() {
                    openDropdown();
                });
                comboboxInput.addEventListener('click', function() {
                    openDropdown();
                });
                comboboxInput.addEventListener('input', function() {
                    openDropdown();
                    filterOptions(this.value);
                    if (clearBtn) clearBtn.style.display = this.value ? 'block' : 'none';
                });
                comboboxInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeDropdown();
                    } else if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (dropdown.style.display === 'none') {
                            openDropdown();
                        } else {
                            const firstVisible = Array.from(options).find(o => o.style.display !== 'none');
                            if (firstVisible) firstVisible.scrollIntoView({ block: 'nearest' });
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const firstVisible = Array.from(options).find(o => o.style.display !== 'none');
                        if (firstVisible) {
                            firstVisible.click();
                        }
                    }
                });
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (dropdown.style.display === 'none' || !dropdown.style.display) {
                        openDropdown();
                        if (comboboxInput) comboboxInput.focus();
                    } else {
                        closeDropdown();
                    }
                });
            }

            if (clearBtn) {
                clearBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    syncResponsibleFields('', '', '', '', false);
                    filterOptions('');
                    if (comboboxInput) {
                        comboboxInput.value = '';
                        comboboxInput.focus();
                    }
                    openDropdown();
                });
            }

            options.forEach(opt => {
                opt.addEventListener('click', function() {
                    const userId = this.dataset.id;
                    const name = this.dataset.name;
                    const pos = this.dataset.position;
                    const email = this.dataset.email;
                    syncResponsibleFields(userId, name, pos, email, false);
                    closeDropdown();
                });
            });

            document.addEventListener('click', function(e) {
                const container = document.getElementById('responsible_combobox_container');
                if (container && !container.contains(e.target)) {
                    closeDropdown();
                }
            });

            // Prevent submitting form if no valid responsible user was selected
            const form = userSelect ? userSelect.closest('form') : null;
            if (form && comboboxInput) {
                form.addEventListener('submit', function(e) {
                    if (!userSelect.value) {
                        e.preventDefault();
                        comboboxInput.setCustomValidity('Por favor selecciona una persona responsable de la lista.');
                        comboboxInput.reportValidity();
                        comboboxInput.focus();
                        openDropdown();
                    } else {
                        comboboxInput.setCustomValidity('');
                    }
                });
            }

            // Initialization on page load
            if (userSelect && userSelect.value) {
                const opt = userSelect.options[userSelect.selectedIndex];
                if (opt && opt.value) {
                    syncResponsibleFields(opt.value, opt.dataset.name, opt.dataset.position, opt.dataset.email, false);
                }
            } else {
                autoSyncResponsibleFromFailure();

                if (!userSelect.value && (posHidden?.value || nameHidden?.value)) {
                    const matchOpt = Array.from(userSelect.options).find(o => 
                        (nameHidden?.value && o.dataset.name === nameHidden.value) ||
                        (posHidden?.value && o.dataset.position && o.dataset.position.toLowerCase() === posHidden.value.toLowerCase())
                    );
                    if (matchOpt) {
                        syncResponsibleFields(matchOpt.value, matchOpt.dataset.name, matchOpt.dataset.position, matchOpt.dataset.email, true);
                    }
                }
            }

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
                autoSyncResponsibleFromFailure();
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
