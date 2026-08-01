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
                    <p style="font-size:13px;color:#94a3b8;margin:0">Auditoria</p>
                    <p style="font-size:28px;font-weight:700;color:#1e293b;margin:4px 0 0">{{ \App\Models\AuthEvent::count() }}</p>
                </div>
                <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(245,158,11,0.06)">
                    <i data-lucide="scan-search" style="width:20px;height:20px;color:#f59e0b"></i>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:24px;display:grid;gap:20px;grid-template-columns:2fr 1fr">
        <div class="card" style="padding:24px">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                <i data-lucide="trending-up" style="width:16px;height:16px;color:#123f6e"></i>
                Ejecucion presupuestal mensual
            </h3>
            <div style="position:relative;height:260px">
                <canvas id="chartPresupuesto"></canvas>
            </div>
        </div>

        <div class="card" style="padding:24px">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                <i data-lucide="pie-chart" style="width:16px;height:16px;color:#059669"></i>
                Distribucion por area
            </h3>
            <div style="position:relative;height:260px">
                <canvas id="chartAreas"></canvas>
            </div>
        </div>
    </div>

    <div style="margin-top:20px;display:grid;gap:20px;grid-template-columns:1fr 1fr">
        <div class="card" style="padding:24px">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                <i data-lucide="bar-chart-3" style="width:16px;height:16px;color:#6366f1"></i>
                Tareas por estado
            </h3>
            <div style="position:relative;height:220px">
                <canvas id="chartTareas"></canvas>
            </div>
        </div>

        <div class="card" style="padding:24px">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                <i data-lucide="line-chart" style="width:16px;height:16px;color:#f59e0b"></i>
                Ahorros acumulados
            </h3>
            <div style="position:relative;height:220px">
                <canvas id="chartAhorros"></canvas>
            </div>
        </div>
    </div>

    <div style="margin-top:20px;display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(280px,1fr))">
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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const primary = '#123f6e';
        const green = '#059669';
        const purple = '#6366f1';
        const amber = '#f59e0b';
        const red = '#ef4444';
        const teal = '#14b8a6';
        const pink = '#ec4899';

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#94a3b8';

        // Ejecucion presupuestal mensual
        new Chart(document.getElementById('chartPresupuesto'), {
            type: 'bar',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [
                    {
                        label: 'Presupuesto',
                        data: [120, 135, 142, 128, 155, 148, 162, 170, 158, 175, 168, 180],
                        backgroundColor: 'rgba(18,63,110,0.15)',
                        borderColor: primary,
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Ejecutado',
                        data: [98, 112, 130, 115, 140, 132, 150, 155, 142, 160, 152, 0],
                        backgroundColor: 'rgba(5,150,105,0.2)',
                        borderColor: green,
                        borderWidth: 2,
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'circle', padding: 16 } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(18,63,110,0.04)' }, ticks: { callback: v => '$' + v + 'M' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Distribucion por area (donut)
        new Chart(document.getElementById('chartAreas'), {
            type: 'doughnut',
            data: {
                labels: ['Tecnologia', 'Finanzas', 'Operaciones', 'Gerencia', 'RRHH', 'Legal'],
                datasets: [{
                    data: [28, 22, 20, 15, 10, 5],
                    backgroundColor: [primary, green, purple, amber, teal, pink],
                    borderWidth: 0,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 11 } } }
                }
            }
        });

        // Tareas por estado (horizontal bar)
        new Chart(document.getElementById('chartTareas'), {
            type: 'bar',
            data: {
                labels: ['Pendiente', 'En progreso', 'Completada', 'Vencida', 'Cancelada'],
                datasets: [{
                    data: [18, 24, 42, 8, 3],
                    backgroundColor: [amber, purple, green, red, '#94a3b8'],
                    borderRadius: 6,
                    barThickness: 22,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(18,63,110,0.04)' } },
                    y: { grid: { display: false } }
                }
            }
        });

        // Ahorros acumulados (line)
        new Chart(document.getElementById('chartAhorros'), {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Acumulado',
                    data: [45, 92, 148, 210, 285, 360, 438, 520, 605, 695, 790, 0],
                    borderColor: amber,
                    backgroundColor: 'rgba(245,158,11,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: amber,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(18,63,110,0.04)' }, ticks: { callback: v => '$' + v + 'K' } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
    </script>

    <style>
        @media (max-width: 768px) {
            div[style*="grid-template-columns:2fr 1fr"] { grid-template-columns: 1fr !important; }
            div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
        }
    </style>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
