<x-layouts.auth title="Iniciar sesion | {{ config('app.name') }}">
    <div style="text-align:center;margin-bottom:32px">
        <img src="{{ asset('images/logo-login.png') }}" alt="Logo {{ config('app.name') }}" style="max-height:80px;width:auto;margin:0 auto 20px;display:block">
        <h1 style="font-size:24px;font-weight:700;color:#123f6e;letter-spacing:-0.5px">CRM Administrativo</h1>
        <p style="font-size:13px;color:#64748b;margin-top:4px">Gestion corporativa, control y trazabilidad</p>
    </div>

    @if (session('status'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-6" x-data="{ showPassword: false }">
        @csrf

        <div>
            <label class="auth-label">Correo electronico</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="usuario@colvatel.com" autocomplete="username" required autofocus class="auth-input">
            @error('email') <p style="margin-top:4px;font-size:12px;color:#dc2626">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="auth-label">Contrasena</label>
            <div style="position:relative">
                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" placeholder="Ingrese su contrasena" autocomplete="current-password" required class="auth-input" style="padding-right:44px">
                <button type="button" @click="showPassword = !showPassword" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px">
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                </button>
            </div>
            @error('password') <p style="margin-top:4px;font-size:12px;color:#dc2626">{{ $message }}</p> @enderror
        </div>

        <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#64748b;cursor:pointer">
            <input type="checkbox" name="accept_policy" value="1" required style="margin-top:2px;accent-color:#123f6e;flex-shrink:0">
            <span>Acepto la <a href="https://colvatel.com.co/wp-content/uploads/2025/07/E01.D05.-Politica-de-Datos-Personales.pdf" target="_blank" rel="noopener noreferrer" style="color:#123f6e;font-weight:500;text-decoration:underline">politica de tratamiento de datos</a> y los <a href="https://colvatel.com.co/wp-content/uploads/2025/07/E01.D05.-Politica-de-Datos-Personales.pdf" target="_blank" rel="noopener noreferrer" style="color:#123f6e;font-weight:500;text-decoration:underline">terminos y condiciones</a>.</span>
        </label>
        @error('accept_policy') <p style="font-size:12px;color:#dc2626">{{ $message }}</p> @enderror

        <div style="display:flex;align-items:center;justify-content:space-between">
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#64748b;cursor:pointer">
                <input type="checkbox" name="remember" value="1" style="accent-color:#123f6e">
                Recordarme
            </label>
            <a href="{{ route('password.request') }}" class="auth-link" style="font-size:13px">Olvide mi contrasena</a>
        </div>

        @if (config('app.env') !== 'local')
        <div style="display:flex;justify-content:center;margin-top:8px;min-height:65px">
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site') }}" data-theme="light"></div>
        </div>
        @endif

        <button type="submit" class="auth-btn">
            <span>Ingresar</span>
        </button>
    </form>

    <div class="auth-footer" style="margin-top:28px">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        <p style="margin-top:2px;opacity:0.6">Desarrollado por el equipo de TI</p>
    </div>
</x-layouts.auth>
