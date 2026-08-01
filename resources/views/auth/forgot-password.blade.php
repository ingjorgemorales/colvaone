<x-layouts.auth title="Recuperar contrasena | {{ config('app.name') }}">
    <div style="text-align:center;margin-bottom:32px">
        <img src="{{ asset('images/logo-login.png') }}" alt="Logo {{ config('app.name') }}" style="max-height:64px;width:auto;margin:0 auto 20px;display:block">
        <h1 style="font-size:22px;font-weight:700;color:#123f6e;letter-spacing:-0.5px">Recuperar contrasena</h1>
        <p style="font-size:13px;color:#64748b;margin-top:6px">Enviaremos un codigo de 6 digitos a tu correo.</p>
    </div>

    @if (session('status'))
        <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf
        <div>
            <label class="auth-label">Correo electronico</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="correo@colvatel.com" required class="auth-input">
            @error('email') <p style="margin-top:4px;font-size:12px;color:#dc2626">{{ $message }}</p> @enderror
        </div>

        @if (config('app.env') !== 'local')
        <div style="display:flex;justify-content:center;margin-top:8px;min-height:65px">
            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site') }}" data-theme="light"></div>
        </div>
        @endif

        <button type="submit" class="auth-btn">
            <span>Enviar codigo</span>
        </button>

        <a href="{{ route('login') }}" class="auth-link" style="display:block;text-align:center;margin-top:8px">Volver al login</a>
    </form>
</x-layouts.auth>
