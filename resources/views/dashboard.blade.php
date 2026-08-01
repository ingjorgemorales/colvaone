<x-layouts.app heading="Dashboard" subheading="Vista general del sistema">
    <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
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
                    <p style="font-size:13px;color:#94a3b8;margin:0">Roles</p>
                    <p style="font-size:28px;font-weight:700;color:#1e293b;margin:4px 0 0">{{ \App\Models\Role::count() }}</p>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(99,102,241,0.06)">
                    <i data-lucide="shield-check" style="width:20px;height:20px;color:#6366f1"></i>
                </div>
            </div>
        </div>

        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <p style="font-size:13px;color:#94a3b8;margin:0">Eventos hoy</p>
                    <p style="font-size:28px;font-weight:700;color:#1e293b;margin:4px 0 0">{{ \App\Models\AuthEvent::where('occurred_at', '>=', now()->startOfDay())->count() }}</p>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(5,150,105,0.06)">
                    <i data-lucide="activity" style="width:20px;height:20px;color:#059669"></i>
                </div>
            </div>
        </div>

        <div class="card" style="padding:20px">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <p style="font-size:13px;color:#94a3b8;margin:0">Registro auditoria</p>
                    <p style="font-size:28px;font-weight:700;color:#1e293b;margin:4px 0 0">{{ \App\Models\AuthEvent::count() }}</p>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(245,158,11,0.06)">
                    <i data-lucide="scan-search" style="width:20px;height:20px;color:#f59e0b"></i>
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
                <p style="font-size:14px;color:#94a3b8;margin:6px 0 0">ColvaOne. Sistema de gestion, control y trazabilidad corporativa.</p>
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

    <h3 style="font-size:16px;font-weight:600;color:#1e293b;margin:28px 0 16px;display:flex;align-items:center;gap:8px">
        <i data-lucide="blocks" style="width:18px;height:18px;color:#123f6e"></i>
        Modulos del sistema
    </h3>

    <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(18,63,110,0.06)">
                    <i data-lucide="list-checks" style="width:20px;height:20px;color:#123f6e"></i>
                </div>
                <div>
                    <h4 style="font-size:14px;font-weight:600;color:#1e293b;margin:0">Tareas</h4>
                    <span style="font-size:11px;color:#94a3b8">Proximamente</span>
                </div>
            </div>
            <p style="font-size:13px;color:#64748b;margin:0;line-height:1.5">Gestion y seguimiento de tareas asignadas por area, con seguimiento de estado y prioridades.</p>
            <div style="margin-top:auto;padding-top:12px;border-top:1px solid rgba(18,63,110,0.06)">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;color:#f59e0b;background:rgba(245,158,11,0.08)">
                    <i data-lucide="clock" style="width:12px;height:12px"></i> En desarrollo
                </span>
            </div>
        </div>

        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(5,150,105,0.06)">
                    <i data-lucide="wallet-cards" style="width:20px;height:20px;color:#059669"></i>
                </div>
                <div>
                    <h4 style="font-size:14px;font-weight:600;color:#1e293b;margin:0">Presupuesto</h4>
                    <span style="font-size:11px;color:#94a3b8">Proximamente</span>
                </div>
            </div>
            <p style="font-size:13px;color:#64748b;margin:0;line-height:1.5">Control presupuestal por area, seguimiento de ejecucion y alertas de gasto.</p>
            <div style="margin-top:auto;padding-top:12px;border-top:1px solid rgba(18,63,110,0.06)">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;color:#f59e0b;background:rgba(245,158,11,0.08)">
                    <i data-lucide="clock" style="width:12px;height:12px"></i> En desarrollo
                </span>
            </div>
        </div>

        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(99,102,241,0.06)">
                    <i data-lucide="chart-no-axes-combined" style="width:20px;height:20px;color:#6366f1"></i>
                </div>
                <div>
                    <h4 style="font-size:14px;font-weight:600;color:#1e293b;margin:0">Indicadores</h4>
                    <span style="font-size:11px;color:#94a3b8">Proximamente</span>
                </div>
            </div>
            <p style="font-size:13px;color:#64748b;margin:0;line-height:1.5">Tablero de indicadores clave de desempeño (KPIs) por area y periodo.</p>
            <div style="margin-top:auto;padding-top:12px;border-top:1px solid rgba(18,63,110,0.06)">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;color:#f59e0b;background:rgba(245,158,11,0.08)">
                    <i data-lucide="clock" style="width:12px;height:12px"></i> En desarrollo
                </span>
            </div>
        </div>

        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(245,158,11,0.06)">
                    <i data-lucide="piggy-bank" style="width:20px;height:20px;color:#f59e0b"></i>
                </div>
                <div>
                    <h4 style="font-size:14px;font-weight:600;color:#1e293b;margin:0">Ahorros</h4>
                    <span style="font-size:11px;color:#94a3b8">Proximamente</span>
                </div>
            </div>
            <p style="font-size:13px;color:#64748b;margin:0;line-height:1.5">Gestion de ahorros corporativos, metas por empleado y reportes de acumulacion.</p>
            <div style="margin-top:auto;padding-top:12px;border-top:1px solid rgba(18,63,110,0.06)">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;color:#f59e0b;background:rgba(245,158,11,0.08)">
                    <i data-lucide="clock" style="width:12px;height:12px"></i> En desarrollo
                </span>
            </div>
        </div>

        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(168,85,247,0.06)">
                    <i data-lucide="blocks" style="width:20px;height:20px;color:#a855f7"></i>
                </div>
                <div>
                    <h4 style="font-size:14px;font-weight:600;color:#1e293b;margin:0">Aplicativos</h4>
                    <span style="font-size:11px;color:#94a3b8">Proximamente</span>
                </div>
            </div>
            <p style="font-size:13px;color:#64748b;margin:0;line-height:1.5">Inventario de aplicativos corporativos, responsables y estado de licenciamiento.</p>
            <div style="margin-top:auto;padding-top:12px;border-top:1px solid rgba(18,63,110,0.06)">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;color:#f59e0b;background:rgba(245,158,11,0.08)">
                    <i data-lucide="clock" style="width:12px;height:12px"></i> En desarrollo
                </span>
            </div>
        </div>

        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(20,184,166,0.06)">
                    <i data-lucide="file-signature" style="width:20px;height:20px;color:#14b8a6"></i>
                </div>
                <div>
                    <h4 style="font-size:14px;font-weight:600;color:#1e293b;margin:0">Contratos</h4>
                    <span style="font-size:11px;color:#94a3b8">Proximamente</span>
                </div>
            </div>
            <p style="font-size:13px;color:#64748b;margin:0;line-height:1.5">Administracion de contratos, fechas de vencimiento y seguimiento documental.</p>
            <div style="margin-top:auto;padding-top:12px;border-top:1px solid rgba(18,63,110,0.06)">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;color:#f59e0b;background:rgba(245,158,11,0.08)">
                    <i data-lucide="clock" style="width:12px;height:12px"></i> En desarrollo
                </span>
            </div>
        </div>

        <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:40px;border-radius:10px;display:grid;place-items:center;background:rgba(239,68,68,0.06)">
                    <i data-lucide="users-round" style="width:20px;height:20px;color:#ef4444"></i>
                </div>
                <div>
                    <h4 style="font-size:14px;font-weight:600;color:#1e293b;margin:0">Comites</h4>
                    <span style="font-size:11px;color:#94a3b8">Proximamente</span>
                </div>
            </div>
            <p style="font-size:13px;color:#64748b;margin:0;line-height:1.5">Gestion de comites, participantes, actas y seguimiento de decisiones.</p>
            <div style="margin-top:auto;padding-top:12px;border-top:1px solid rgba(18,63,110,0.06)">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:500;color:#f59e0b;background:rgba(245,158,11,0.08)">
                    <i data-lucide="clock" style="width:12px;height:12px"></i> En desarrollo
                </span>
            </div>
        </div>
    </div>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
