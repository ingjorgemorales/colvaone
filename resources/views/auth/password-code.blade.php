<x-layouts.auth title="Verificar codigo | {{ config('app.name') }}">
    <div x-data="countdown(120)" x-init="start()">
        <div style="text-align:center;margin-bottom:32px">
            <img src="{{ asset('images/logo-login.png') }}" alt="Logo {{ config('app.name') }}" style="max-height:64px;width:auto;margin:0 auto 20px;display:block">
            <h1 style="font-size:22px;font-weight:700;color:#123f6e;letter-spacing:-0.5px">Verificar codigo</h1>
            <p style="font-size:13px;color:#64748b;margin-top:6px">Ingresa el codigo de 6 digitos enviado a tu correo.</p>
        </div>

        @if (session('success'))
            <div style="margin-bottom:16px;padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
        @endif

        <!-- Timer -->
        <div style="text-align:center;margin-bottom:24px" x-show="remaining > 0">
            <div style="display:inline-flex;align-items:center;gap:10px;padding:10px 20px;border-radius:12px;background:rgba(255,255,255,0.6);border:1px solid rgba(18,63,110,0.08);box-shadow:0 2px 12px rgba(18,63,110,0.04)">
                <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center" :style="remaining > 30 ? 'background:rgba(18,63,110,0.08)' : 'background:rgba(220,38,38,0.08)'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" :stroke="remaining > 30 ? '#123f6e' : '#dc2626'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <span style="font-family:'JetBrains Mono',monospace;font-size:22px;font-weight:700;letter-spacing:2px" :style="remaining > 30 ? 'color:#123f6e' : 'color:#dc2626'" x-text="formatted">02:00</span>
            </div>
        </div>

        <!-- Expired message -->
        <div x-show="remaining <= 0" x-cloak style="text-align:center;margin-bottom:24px;padding:16px;border-radius:12px;background:rgba(220,38,38,0.04);border:1px solid rgba(220,38,38,0.1)">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 8px"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <p style="font-size:13px;color:#dc2626;font-weight:500">El codigo ha expirado</p>
        </div>

        <!-- Code input form -->
        <form method="POST" action="{{ route('password.code.verify') }}" x-show="remaining > 0" x-cloak style="display:flex;flex-direction:column;gap:24px">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">

            <div style="margin-bottom:20px">
                <div style="display:flex;gap:10px;justify-content:center">
                    <template x-for="(digit, i) in digits" :key="i">
                        <input :id="'code-' + i" type="text" inputmode="numeric" pattern="[0-9]{1}" maxlength="1"
                            x-model="digits[i]"
                            @keypress="if(!/[0-9]/.test($event.key)) $event.preventDefault()"
                            @input="$event.target.value = $event.target.value.replace(/[^0-9]/g, ''); digits[i] = $event.target.value; if($event.target.value && i < 5) document.getElementById('code-' + (i+1)).focus()"
                            @keydown.backspace="if(!digits[i] && i > 0) document.getElementById('code-' + (i-1)).focus()"
                            style="width:48px;height:56px;border-radius:12px;border:1px solid rgba(18,63,110,0.12);background:rgba(255,255,255,0.7);color:#1e293b;font-size:22px;font-family:'JetBrains Mono',monospace;font-weight:600;text-align:center;outline:none;transition:all 0.2s ease"
                            onfocus="this.style.borderColor='rgba(18,63,110,0.4)';this.style.boxShadow='0 0 0 3px rgba(18,63,110,0.06)'"
                            onblur="this.style.borderColor='rgba(18,63,110,0.12)';this.style.boxShadow='none'"
                            :disabled="remaining <= 0">
                    </template>
                </div>
                <input type="hidden" name="code" :value="digits.join('')">
                @error('code') <p style="margin-top:8px;font-size:12px;color:#dc2626;text-align:center">{{ $message }}</p> @enderror
            </div>

            @if (config('app.env') !== 'local')
            <div style="display:flex;justify-content:center;min-height:65px">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site') }}" data-theme="light"></div>
            </div>
            @endif

            <button type="submit" class="auth-btn" :disabled="remaining <= 0 || digits.join('').length < 6" :style="digits.join('').length < 6 ? 'opacity:0.5;cursor:not-allowed' : ''">
                <span>Verificar codigo</span>
            </button>
        </form>

        <!-- Resend form -->
        <div x-show="remaining <= 0" x-cloak>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <button type="submit" class="auth-btn">
                    <span>Reenviar codigo</span>
                </button>
            </form>
        </div>

        <a href="{{ route('password.request') }}" class="auth-link" style="display:block;text-align:center;margin-top:16px">Volver al login</a>
    </div>

    <script>
        function countdown(seconds) {
            return {
                remaining: seconds,
                formatted: '02:00',
                digits: ['', '', '', '', '', ''],
                interval: null,
                start() {
                    this.remaining = seconds;
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
                    if (this.remaining <= 0) {
                        setTimeout(() => { window.location.href = '{{ route("login") }}'; }, 2000);
                    }
                }
            };
        }
    </script>
</x-layouts.auth>
