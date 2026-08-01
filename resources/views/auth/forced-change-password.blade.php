<x-layouts.auth title="Cambiar contrasena | {{ config('app.name') }}">
    <div style="text-align:center;margin-bottom:28px">
        <img src="{{ asset('images/logo-login.png') }}" alt="Logo {{ config('app.name') }}" style="max-height:60px;width:auto;margin:0 auto 16px;display:block">
        <h1 style="font-size:20px;font-weight:700;color:#123f6e;margin-bottom:4px">ColvaOne</h1>
        <p style="font-size:12px;color:#64748b">Actualizacion obligatoria de seguridad</p>
    </div>

    <div style="margin-bottom:20px;padding:14px 16px;border-radius:10px;font-size:13px;color:#123f6e;background:rgba(18,63,110,0.04);border:1px solid rgba(18,63,110,0.1)">
        <div style="display:flex;align-items:flex-start;gap:8px">
            <i data-lucide="info" style="width:16px;height:16px;flex-shrink:0;margin-top:2px"></i>
            <span>Debes definir una nueva contrasena para continuar. Minimo 12 caracteres con mayusculas, minusculas, numeros y simbolos.</span>
        </div>
    </div>

    <form method="POST" action="{{ route('password.change.update') }}" style="display:flex;flex-direction:column;gap:16px" x-data="{ showNew: false, showConfirm: false }">
        @csrf
        @method('PUT')

        <div>
            <label class="auth-label">Nueva contrasena</label>
            <div style="position:relative">
                <input id="password" name="password" :type="showNew ? 'text' : 'password'" required class="auth-input @error('password') error-field @enderror" placeholder="Ingresa la nueva contrasena" style="padding-right:44px">
                <button type="button" @click="showNew = !showNew" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px">
                    <svg x-show="!showNew" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="showNew" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                </button>
            </div>
            @error('password')
                <div style="display:flex;align-items:center;gap:6px;margin-top:6px;padding:8px 12px;border-radius:8px;font-size:12px;color:#dc2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.1)">
                    <i data-lucide="alert-circle" style="width:14px;height:14px;flex-shrink:0"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div>
            <label class="auth-label">Confirmar contrasena</label>
            <div style="position:relative">
                <input id="password_confirmation" name="password_confirmation" :type="showConfirm ? 'text' : 'password'" required class="auth-input" placeholder="Confirma la nueva contrasena" style="padding-right:44px">
                <button type="button" @click="showConfirm = !showConfirm" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px">
                    <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="showConfirm" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                </button>
            </div>
            @error('password_confirmation')
                <div style="display:flex;align-items:center;gap:6px;margin-top:6px;padding:8px 12px;border-radius:8px;font-size:12px;color:#dc2626;background:rgba(220,38,38,0.06);border:1px solid rgba(220,38,38,0.1)">
                    <i data-lucide="alert-circle" style="width:14px;height:14px;flex-shrink:0"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="auth-btn" style="margin-top:4px">
            <span>Actualizar contrasena</span>
        </button>
    </form>

    <style>.error-field { border-color: rgba(220,38,38,0.4) !important; box-shadow: 0 0 0 3px rgba(220,38,38,0.06) !important; }</style>
    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.auth>
