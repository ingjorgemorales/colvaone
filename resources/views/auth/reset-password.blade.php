<x-layouts.auth title="Restablecer contrasena | {{ config('app.name') }}">
    <div x-data="resetTimer()" x-init="start()">
        <div style="text-align:center;margin-bottom:32px">
            <img src="{{ asset('images/logo-login.png') }}" alt="Logo {{ config('app.name') }}" style="max-height:64px;width:auto;margin:0 auto 20px;display:block">
            <h1 style="font-size:22px;font-weight:700;color:#123f6e;letter-spacing:-0.5px">Restablecer contrasena</h1>
            <p style="font-size:13px;color:#64748b;margin-top:6px">Define una nueva contrasena para tu cuenta.</p>
        </div>

        <!-- Timer -->
        <div style="text-align:center;margin-bottom:24px" x-show="remaining > 0">
            <div style="display:inline-flex;align-items:center;gap:10px;padding:10px 20px;border-radius:12px;background:rgba(255,255,255,0.6);border:1px solid rgba(18,63,110,0.08);box-shadow:0 2px 12px rgba(18,63,110,0.04)">
                <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center" :style="remaining > 60 ? 'background:rgba(18,63,110,0.08)' : 'background:rgba(220,38,38,0.08)'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" :stroke="remaining > 60 ? '#123f6e' : '#dc2626'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <span style="font-family:'JetBrains Mono',monospace;font-size:22px;font-weight:700;letter-spacing:2px" :style="remaining > 60 ? 'color:#123f6e' : 'color:#dc2626'" x-text="formatted">03:00</span>
            </div>
        </div>

        <!-- Expired -->
        <div x-show="remaining <= 0" x-cloak style="text-align:center;margin-bottom:24px;padding:20px;border-radius:12px;background:rgba(220,38,38,0.04);border:1px solid rgba(220,38,38,0.1)">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 10px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <p style="font-size:14px;color:#dc2626;font-weight:600;margin-bottom:4px">Tiempo expirado</p>
            <p style="font-size:12px;color:#94a3b8">El enlace ha caducado. Solicita uno nuevo.</p>
            <a href="{{ route('password.request') }}" style="display:inline-block;margin-top:14px;padding:10px 24px;border-radius:10px;background:#123f6e;color:white;font-size:13px;font-weight:600;text-decoration:none;transition:all 0.3s ease" onmouseover="this.style.background='#0d3158'" onmouseout="this.style.background='#123f6e'">Solicitar nuevo enlace</a>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('password.store') }}" class="space-y-6" x-show="remaining > 0" x-cloak x-data="{ showPassword: false, showConfirm: false, password: '', passwordConfirm: '' }">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <input type="hidden" name="email" value="{{ $request->email }}">

            <div>
                <label class="auth-label">Nueva contrasena</label>
                <div style="position:relative">
                    <input id="password" name="password" x-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Ingrese su nueva contrasena" required class="auth-input" style="padding-right:44px" autocomplete="new-password">
                    <button type="button" @click="showPassword = !showPassword" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px">
                        <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                    </button>
                </div>
                @error('password') <p style="margin-top:4px;font-size:12px;color:#dc2626">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="auth-label">Confirmar contrasena</label>
                <div style="position:relative">
                    <input id="password_confirmation" name="password_confirmation" x-model="passwordConfirm" :type="showConfirm ? 'text' : 'password'" placeholder="Repita la contrasena" required class="auth-input" style="padding-right:44px" autocomplete="new-password">
                    <button type="button" @click="showConfirm = !showConfirm" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px">
                        <svg x-show="!showConfirm" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg x-show="showConfirm" x-cloak xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/><path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/><path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/><path d="m2 2 20 20"/></svg>
                    </button>
                </div>
                <p x-show="passwordConfirm && password !== passwordConfirm" x-transition style="margin-top:4px;font-size:12px;color:#dc2626">Las contrasenas no coinciden</p>
                <p x-show="passwordConfirm && password === passwordConfirm && password.length > 0" x-transition style="margin-top:4px;font-size:12px;color:#059669">Las contrasenas coinciden</p>
            </div>

            <button type="submit" class="auth-btn" :disabled="password.length === 0 || password !== passwordConfirm" :style="password.length === 0 || password !== passwordConfirm ? 'opacity:0.5;cursor:not-allowed' : ''">
                <span>Guardar nueva contrasena</span>
            </button>
        </form>

        <a href="{{ route('login') }}" class="auth-link" style="display:block;text-align:center;margin-top:16px">Volver al login</a>
    </div>

    <script>
        function resetTimer() {
            return {
                remaining: 180,
                formatted: '03:00',
                interval: null,
                start() {
                    this.updateFormatted();
                    this.interval = setInterval(() => {
                        if (this.remaining > 0) {
                            this.remaining--;
                            this.updateFormatted();
                        } else {
                            clearInterval(this.interval);
                        }
                    }, 1000);
                },
                updateFormatted() {
                    const m = Math.floor(this.remaining / 60);
                    const s = this.remaining % 60;
                    this.formatted = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                }
            };
        }
    </script>
</x-layouts.auth>
