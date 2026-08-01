<x-layouts.app heading="Dashboard" subheading="Vista general del sistema">
    <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <p style="font-size:13px;color:#94a3b8;margin:0">Usuarios</p>
                    <p style="font-size:28px;font-weight:700;color:#1e293b;margin:4px 0 0">{{ \App\Models\User::count() }}</p>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.06)">
                    <i data-lucide="users" style="width:20px;height:20px;color:#123f6e"></i>
                </div>
            </div>
        </div>

        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <p style="font-size:13px;color:#94a3b8;margin:0">Sesiones activas</p>
                    <p style="font-size:28px;font-weight:700;color:#1e293b;margin:4px 0 0">{{ \App\Models\User::where('last_login_at', '>', now()->subMinutes(30))->count() }}</p>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(5,150,105,0.06)">
                    <i data-lucide="activity" style="width:20px;height:20px;color:#059669"></i>
                </div>
            </div>
        </div>

        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <p style="font-size:13px;color:#94a3b8;margin:0">Eventos hoy</p>
                    <p style="font-size:28px;font-weight:700;color:#1e293b;margin:4px 0 0">{{ \App\Models\AuthEvent::where('occurred_at', '>=', now()->startOfDay())->count() }}</p>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(99,102,241,0.06)">
                    <i data-lucide="bar-chart-3" style="width:20px;height:20px;color:#6366f1"></i>
                </div>
            </div>
        </div>

        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <p style="font-size:13px;color:#94a3b8;margin:0">Seguridad</p>
                    <p style="font-size:28px;font-weight:700;color:#059669;margin:4px 0 0">Activa</p>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(16,185,129,0.06)">
                    <i data-lucide="shield-check" style="width:20px;height:20px;color:#10b981"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:24px;padding:24px">
        <div style="display:flex;align-items:flex-start;gap:16px">
            <div style="width:48px;height:48px;border-radius:14px;display:grid;place-items:center;background:linear-gradient(135deg,#123f6e,#059669);flex-shrink:0">
                <i data-lucide="sparkles" style="width:24px;height:24px;color:white"></i>
            </div>
            <div style="flex:1;min-width:0">
                <h2 style="font-size:18px;font-weight:600;color:#1e293b;margin:0">Bienvenido, {{ auth()->user()->name }}</h2>
                <p style="font-size:14px;color:#94a3b8;margin:6px 0 0">ColvaOne. Sistema de gestion, control y trazabilidad.</p>
                <div style="margin-top:16px;display:flex;flex-wrap:wrap;gap:12px">
                    <a href="{{ route('profile.edit') }}" class="btn-primary">
                        <i data-lucide="user" style="width:16px;height:16px"></i> Mi perfil
                    </a>
                    <a href="{{ route('users.index') }}" class="btn-secondary">
                        <i data-lucide="users" style="width:16px;height:16px"></i> Gestionar usuarios
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:24px;display:grid;gap:20px;grid-template-columns:repeat(auto-fit,minmax(300px,1fr))">
        <div class="card" style="padding:20px">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                <i data-lucide="info" style="width:16px;height:16px;color:#123f6e"></i>
                Informacion del sistema
            </h3>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;color:#64748b">
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:#059669;flex-shrink:0"></span> Laravel 13
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:#123f6e;flex-shrink:0"></span> PHP {{ phpversion() }}
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:#6366f1;flex-shrink:0"></span> Cloudflare Turnstile
                </div>
            </div>
        </div>

        <div class="card" style="padding:20px">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 14px;display:flex;align-items:center;gap:8px">
                <i data-lucide="clock" style="width:16px;height:16px;color:#059669"></i>
                Ultima sesion
            </h3>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:13px;color:#64748b">
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:#123f6e;flex-shrink:0"></span>
                    {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Primera sesion' }}
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:#059669;flex-shrink:0"></span>
                    {{ auth()->user()->email }}
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:#6366f1;flex-shrink:0"></span>
                    {{ auth()->user()->role_label }}
                </div>
            </div>
        </div>
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
