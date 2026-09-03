<x-layouts.app title="Nuevo indicador | {{ config('app.name') }}" heading="Nuevo indicador" subheading="Registra la ficha tecnica del indicador">
    @include('indicators.partials.form-styles')

    <div style="max-width:1080px">
        <div class="card" style="padding:26px">
            <form method="POST" action="{{ route('indicators.store') }}">
                @csrf

                @include('indicators.partials.form-fields', ['indicator' => null])

                <div class="form-actions">
                    <a href="{{ route('indicators.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <i data-lucide="plus" style="width:16px;height:16px"></i> Guardar
                    </button>
                </div>
            </form>
        </div>

        <p style="font-size:12px;color:#94a3b8;margin:14px 2px 0;display:flex;align-items:center;gap:6px">
            <i data-lucide="info" style="width:13px;height:13px"></i>
            Los resultados por periodo se registran despues de crear el indicador, en la pestana Resultados.
        </p>
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
