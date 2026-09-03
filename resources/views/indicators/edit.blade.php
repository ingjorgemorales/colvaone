<x-layouts.app title="Editar indicador | {{ config('app.name') }}" heading="Editar indicador" subheading="{{ $indicator->name }}">
    @include('indicators.partials.form-styles')

    <div style="max-width:1080px">
        <div class="card" style="padding:26px">
            <form method="POST" action="{{ route('indicators.update', $indicator) }}">
                @csrf
                @method('PUT')

                @include('indicators.partials.form-fields')

                <div class="form-actions">
                    <a href="{{ route('indicators.show', $indicator) }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <i data-lucide="save" style="width:16px;height:16px"></i> Guardar
                    </button>
                </div>
            </form>
        </div>

        @if($indicator->results()->exists())
            <p style="font-size:12px;color:#64748b;margin:14px 2px 0;display:flex;align-items:flex-start;gap:6px;line-height:1.55">
                <i data-lucide="triangle-alert" style="width:13px;height:13px;flex-shrink:0;margin-top:2px;color:#f59e0b"></i>
                Si cambias los rangos de evaluacion, los resultados ya registrados se reclasifican automaticamente con los nuevos umbrales.
            </p>
        @endif
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
