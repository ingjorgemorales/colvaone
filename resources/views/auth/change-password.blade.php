<x-layouts.app title="Cambiar contrasena | {{ config('app.name') }}" heading="Cambiar contrasena" subheading="Actualizacion obligatoria de seguridad">
    <div style="max-width:500px;margin:0 auto">
        <div class="card" style="padding:28px">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid rgba(18,63,110,0.06)">
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.06)">
                    <i data-lucide="shield-check" style="width:22px;height:22px;color:#123f6e"></i>
                </div>
                <div>
                    <h2 style="font-size:16px;font-weight:600;color:#1e293b;margin:0">Define una nueva contrasena</h2>
                    <p style="font-size:13px;color:#94a3b8;margin:4px 0 0">Minimo 12 caracteres, mayusculas, minusculas, numeros y simbolos.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.change.update') }}" style="display:flex;flex-direction:column;gap:18px">
                @csrf
                @method('PUT')

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Contrasena actual</label>
                    <input name="current_password" type="password" required class="input-field" placeholder="Ingresa tu contrasena actual">
                    @error('current_password') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Nueva contrasena</label>
                    <input id="password" name="password" type="password" required class="input-field" placeholder="Ingresa la nueva contrasena">
                    @error('password') <p style="font-size:12px;color:#dc2626;margin-top:4px">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:6px">Confirmar nueva contrasena</label>
                    <input name="password_confirmation" type="password" required class="input-field" placeholder="Confirma la nueva contrasena">
                </div>

                <div style="padding:12px 16px;border-radius:10px;font-size:13px;color:#123f6e;background:rgba(18,63,110,0.04);border:1px solid rgba(18,63,110,0.08)">
                    Despues de cambiar tu contrasena, deberas iniciar sesion nuevamente en todos tus dispositivos.
                </div>

                <button type="submit" class="btn-primary" style="width:100%;justify-content:center">
                    <i data-lucide="shield-check" style="width:16px;height:16px"></i> Actualizar contrasena
                </button>
            </form>
        </div>
    </div>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
