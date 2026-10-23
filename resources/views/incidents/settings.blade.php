<x-layouts.app title="Ajustes de incidentes | {{ config('app.name') }}" heading="Ajustes de incidentes" subheading="Listas desplegables">
    @include('incidents.styles')
    @php
        $types = \App\Models\IncidentOption::TYPES;
    @endphp
    <div class="incident-page" style="max-width:1100px">
        <div class="incident-toolbar"><a class="btn-secondary" href="{{ route('incidents.index') }}"><i data-lucide="arrow-left" style="width:16px;height:16px"></i> Volver a incidentes</a></div>
        @include('incidents.errors')
        <nav class="incident-tabs" aria-label="Listas de incidentes">
            @foreach($types as $type => $label)<a class="btn-secondary {{ $currentType === $type ? 'selected' : '' }}" href="{{ route('incidents.settings', ['type' => $type]) }}" @if($currentType === $type) aria-current="page" @endif>{{ $label }}</a>@endforeach
        </nav>
        <div class="card">
            <section class="incident-section">
                <h2>{{ $types[$currentType] }}</h2>
                @forelse($options->get($currentType, collect()) as $option)
                    <div style="display:flex;align-items:end;gap:12px;padding:14px 0;border-bottom:1px solid #e2e8f0;flex-wrap:wrap">
                        <form method="POST" action="{{ route('incidents.options.update', $option) }}" class="incident-option-row" style="flex:1;min-width:0">
                            @csrf @method('PUT')
                            <div><label for="name_{{ $option->id }}">Nombre <span class="incident-pill">{{ $option->is_active ? 'Activo' : 'Inactivo' }}</span></label><input class="input-field" id="name_{{ $option->id }}" name="name" required maxlength="150" value="{{ $option->name }}"></div>
                            <div><label for="order_{{ $option->id }}">Orden</label><input class="input-field" id="order_{{ $option->id }}" name="sort_order" type="number" min="0" max="9999" required value="{{ $option->sort_order }}"></div>
                            <button class="incident-icon" title="Guardar opción" aria-label="Guardar {{ $option->name }}"><i data-lucide="save"></i></button>
                        </form>
                        <form method="POST" action="{{ route('incidents.options.toggle', $option) }}" onsubmit="return confirm('¿Confirmas el cambio de actividad de esta opción?')">
                            @csrf
                            <button class="incident-icon" title="{{ $option->is_active ? 'Inactivar' : 'Reactivar' }} opción" aria-label="{{ $option->is_active ? 'Inactivar' : 'Reactivar' }} {{ $option->name }}"><i data-lucide="{{ $option->is_active ? 'power-off' : 'power' }}"></i></button>
                        </form>
                    </div>
                @empty
                    <p class="incident-muted">No hay opciones registradas.</p>
                @endforelse
            </section>
            <section class="incident-section">
                <h2>Agregar opción</h2>
                <form method="POST" action="{{ route('incidents.options.store') }}" class="incident-option-row">
                    @csrf
                    <input type="hidden" name="type" value="{{ $currentType }}">
                    <div><label for="new_name">Nombre</label><input class="input-field" id="new_name" name="name" value="{{ old('name') }}" required maxlength="150"></div>
                    <div><label for="new_order">Orden</label><input class="input-field" id="new_order" name="sort_order" type="number" min="0" max="9999" required value="{{ old('sort_order', ($options->get($currentType, collect())->max('sort_order') ?? -1) + 1) }}"></div>
                    <button class="btn-primary"><i data-lucide="plus" style="width:16px;height:16px"></i> Agregar</button>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
