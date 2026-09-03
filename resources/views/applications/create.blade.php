<x-layouts.app title="Nuevo aplicativo | {{ config('app.name') }}" heading="Nuevo aplicativo" subheading="Registra una aplicacion o plataforma de Colvatel">
    <div style="max-width:820px">
        <div class="card" style="padding:28px">
            <form method="POST" action="{{ route('applications.store') }}" style="display:flex;flex-direction:column;gap:18px">
                @csrf

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Nombre del aplicativo *</label>
                    <input name="name" type="text" value="{{ old('name') }}" required maxlength="255" class="input-field @error('name') {{ 'error-field' }} @enderror" placeholder="Ej: Mesa de servicio">
                    @error('name') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Descripcion</label>
                    <textarea name="description" rows="4" class="input-field @error('description') {{ 'error-field' }} @enderror" style="resize:vertical" placeholder="Para que sirve el aplicativo y quien lo usa...">{{ old('description') }}</textarea>
                    @error('description') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Ruta o URL *</label>
                    <input name="url" type="url" value="{{ old('url') }}" required maxlength="2048" class="input-field @error('url') {{ 'error-field' }} @enderror" placeholder="https://aplicativo.colvatel.com">
                    <p style="font-size:11px;color:#94a3b8;margin-top:4px">Debe iniciar con http:// o https://. Al hacer clic, el usuario se redirige a este sitio en una pestana nueva.</p>
                    @error('url') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div style="display:flex;gap:12px;justify-content:flex-end">
                    <a href="{{ route('applications.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <i data-lucide="plus" style="width:16px;height:16px"></i> Crear aplicativo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .error-field { border-color: rgba(220,38,38,0.4) !important; box-shadow: 0 0 0 3px rgba(220,38,38,0.06) !important; }
    </style>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
