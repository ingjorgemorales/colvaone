<x-layouts.app title="Chat IA | {{ config('app.name') }}" heading="Chat IA" subheading="Configuracion del asistente interno">
    <div style="max-width:820px;margin:0 auto">
        @if(session('success'))
            <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
        @endif

        <div class="card" style="padding:24px">
            <form method="POST" action="{{ route('ai-chat.settings.update') }}" style="display:flex;flex-direction:column;gap:18px">
                @csrf
                @method('PUT')

                <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 16px;border-radius:12px;background:rgba(18,63,110,0.04);border:1px solid rgba(18,63,110,0.08)">
                    <div>
                        <p style="font-size:14px;font-weight:600;color:#1e293b;margin:0">Estado del chat</p>
                        <p style="font-size:12px;color:#64748b;margin:3px 0 0">{{ $settings->is_active ? 'Activo' : 'Inactivo' }}</p>
                    </div>
                    <label style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:600;color:#123f6e;cursor:pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $settings->is_active) ? 'checked' : '' }} style="accent-color:#059669;width:16px;height:16px">
                        Activar
                    </label>
                </div>

                <div style="display:grid;gap:16px;grid-template-columns:1fr 1fr">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Proveedor *</label>
                        <select name="provider" required class="input-field @error('provider') {{ 'error-field' }} @enderror">
                            <option value="openai" {{ old('provider', $settings->provider) === 'openai' ? 'selected' : '' }}>OpenAI</option>
                        </select>
                        @error('provider') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Modelo *</label>
                        <input name="model" type="text" value="{{ old('model', $settings->model) }}" required maxlength="120" class="input-field @error('model') {{ 'error-field' }} @enderror" placeholder="gpt-5">
                        @error('model') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Endpoint *</label>
                    <input name="endpoint" type="url" value="{{ old('endpoint', $settings->endpoint) }}" required maxlength="255" class="input-field @error('endpoint') {{ 'error-field' }} @enderror" placeholder="https://api.openai.com/v1/responses">
                    @error('endpoint') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">API key {{ $settings->hasApiKey() ? '' : '*' }}</label>
                    <input name="api_key" type="password" value="" {{ $settings->hasApiKey() ? '' : 'required' }} maxlength="500" class="input-field @error('api_key') {{ 'error-field' }} @enderror" placeholder="{{ $settings->hasApiKey() ? 'Clave configurada; escribe una nueva solo si deseas reemplazarla' : 'Ingresa la API key' }}">
                    @if($settings->hasApiKey())
                        <p style="font-size:12px;color:#059669;margin-top:5px">API key guardada y cifrada.</p>
                    @endif
                    @error('api_key') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Mensajes de contexto *</label>
                    <input name="max_context_messages" type="number" min="4" max="30" value="{{ old('max_context_messages', $settings->max_context_messages) }}" required class="input-field @error('max_context_messages') {{ 'error-field' }} @enderror">
                    @error('max_context_messages') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Prompt base</label>
                    <textarea name="system_prompt" rows="6" class="input-field @error('system_prompt') {{ 'error-field' }} @enderror" style="resize:vertical" placeholder="Instrucciones internas para el asistente...">{{ old('system_prompt', $settings->system_prompt) }}</textarea>
                    @error('system_prompt') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div style="display:flex;align-items:center;gap:12px;padding-top:8px">
                    <button type="submit" class="btn-primary">
                        <i data-lucide="save" style="width:16px;height:16px"></i> Guardar configuracion
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
