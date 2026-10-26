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
                    @include('incidents.select', ['field' => 'zone', 'label' => 'Piso o zona', 'required' => false])
                    <div><label for="location">Ubicación exacta <span class="incident-required">*</span></label><input id="location" name="location" required maxlength="500" class="input-field" value="{{ old('location', $incident->location) }}"></div>
                </div>
            </section>
            <section class="incident-section">
                <h2>Detalles del incidente</h2>
                <div class="incident-grid">
                    @include('incidents.select', ['field' => 'failure', 'label' => 'Tipo de falla', 'required' => true])
                    @include('incidents.select', ['field' => 'impact', 'label' => '¿El incidente afecta la operación?', 'required' => true])
                    <div class="incident-full"><label for="description">Descripción del problema <span class="incident-required">*</span></label><textarea id="description" name="description" required maxlength="5000" rows="5" class="input-field">{{ old('description', $incident->description) }}</textarea></div>
                    <div class="incident-full"><label for="affected_assets">Equipos o activos afectados</label><input id="affected_assets" name="affected_assets" maxlength="500" class="input-field" value="{{ old('affected_assets', $incident->affected_assets) }}"></div>
                    <div class="incident-full">@include('incidents.upload')
                        @if($incident->exists) @include('incidents.evidence', ['editable' => true]) @endif
                    </div>
                </div>
            </section>
            <section class="incident-section">
                <h2>Prioridad{{ $incident->exists ? ' y seguimiento' : ' del incidente' }}</h2>
                <div class="incident-grid">
                    @include('incidents.select', ['field' => 'priority', 'label' => 'Prioridad sugerida', 'required' => true])
                    @if($incident->exists)
                        <div><label for="status">Estado de atención <span class="incident-required">*</span></label><select id="status" name="status" required class="input-field">
                            @foreach(\App\Models\Incident::STATUSES as $value => $label)<option value="{{ $value }}" @selected(old('status', $incident->status) === $value)>{{ $label }}</option>@endforeach
                        </select></div>
                    @endif
                </div>
            </section>
            <div class="incident-section incident-buttons" style="justify-content:flex-end">
                <a class="btn-secondary" href="{{ $incident->exists ? route('incidents.show', $incident) : route('incidents.index') }}">Cancelar</a>
                <button class="btn-primary" @disabled($profileIncomplete)><i data-lucide="save" style="width:16px;height:16px"></i> {{ $incident->exists ? 'Guardar cambios' : 'Registrar incidente' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
