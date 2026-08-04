<x-layouts.app heading="Dashboard" subheading="Bienvenido, {{ Auth::user()->name }}">
    @php
        $user = Auth::user();
        $isAdmin = in_array($user->role, ['superadmin', 'admin']);
        $isGerente = $user->role === 'gerente';
    @endphp

    {{-- ==================== ADMIN / SUPERADMIN ==================== --}}
    @if($isAdmin)
        <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Usuarios</p>
                        <p style="font-size:28px;font-weight:700;color:#1e293b;margin:4px 0 0">{{ $totalUsers }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.06)">
                        <i data-lucide="users" style="width:20px;height:20px;color:#123f6e"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Tareas activas</p>
                        <p style="font-size:28px;font-weight:700;color:#6366f1;margin:4px 0 0">{{ $activeTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(99,102,241,0.06)">
                        <i data-lucide="list-checks" style="width:20px;height:20px;color:#6366f1"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Grupos</p>
                        <p style="font-size:28px;font-weight:700;color:#059669;margin:4px 0 0">{{ $totalGroups }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(5,150,105,0.06)">
                        <i data-lucide="users-round" style="width:20px;height:20px;color:#059669"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Finalizadas</p>
                        <p style="font-size:28px;font-weight:700;color:#059669;margin:4px 0 0">{{ $completedTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(5,150,105,0.06)">
                        <i data-lucide="check-circle" style="width:20px;height:20px;color:#059669"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Eventos hoy</p>
                        <p style="font-size:28px;font-weight:700;color:#f59e0b;margin:4px 0 0">{{ $eventsToday }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(245,158,11,0.06)">
                        <i data-lucide="activity" style="width:20px;height:20px;color:#f59e0b"></i>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:24px;display:grid;gap:20px;grid-template-columns:1fr 1fr">
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="bar-chart-3" style="width:16px;height:16px;color:#6366f1"></i> Tareas por estado
                </h3>
                <div style="position:relative;height:220px">
                    <canvas id="chartTareas"></canvas>
                </div>
            </div>
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="pie-chart" style="width:16px;height:16px;color:#059669"></i> Tareas por grupo
                </h3>
                <div style="position:relative;height:220px">
                    <canvas id="chartGrupos"></canvas>
                </div>
            </div>
        </div>

        <div style="margin-top:20px">
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="clock" style="width:16px;height:16px;color:#123f6e"></i> Ultimas tareas
                </h3>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @forelse($recentTasks as $t)
                        <a href="{{ route('tasks.show', $t) }}" style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;text-decoration:none;transition:background 0.15s" onmouseover="this.style.background='rgba(18,63,110,0.03)'" onmouseout="this.style.background='transparent'">
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $t->priority_color }};flex-shrink:0"></span>
                            <div style="flex:1;min-width:0">
                                <span style="font-size:13px;font-weight:600;color:#1e293b">{{ $t->title }}</span>
                                <span style="font-size:11px;color:#94a3b8;margin-left:8px">{{ $t->group->name ?? '—' }}</span>
                            </div>
                            <span style="display:inline-flex;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;color:white;background:{{ $t->status_color }}">{{ $t->status_label }}</span>
                        </a>
                    @empty
                        <p style="font-size:13px;color:#94a3b8;text-align:center;padding:20px">No hay tareas recientes</p>
                    @endforelse
                </div>
            </div>
        </div>

    {{-- ==================== GERENTE ==================== --}}
    @elseif($isGerente)
        <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Mis grupos</p>
                        <p style="font-size:28px;font-weight:700;color:#059669;margin:4px 0 0">{{ $myGroups }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(5,150,105,0.06)">
                        <i data-lucide="users-round" style="width:20px;height:20px;color:#059669"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Tareas del equipo</p>
                        <p style="font-size:28px;font-weight:700;color:#6366f1;margin:4px 0 0">{{ $teamTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(99,102,241,0.06)">
                        <i data-lucide="list-checks" style="width:20px;height:20px;color:#6366f1"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Activas</p>
                        <p style="font-size:28px;font-weight:700;color:#f59e0b;margin:4px 0 0">{{ $activeTeamTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(245,158,11,0.06)">
                        <i data-lucide="clock" style="width:20px;height:20px;color:#f59e0b"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Finalizadas</p>
                        <p style="font-size:28px;font-weight:700;color:#059669;margin:4px 0 0">{{ $completedTeamTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(5,150,105,0.06)">
                        <i data-lucide="check-circle" style="width:20px;height:20px;color:#059669"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Mis creaciones</p>
                        <p style="font-size:28px;font-weight:700;color:#123f6e;margin:4px 0 0">{{ $myCreatedTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(18,63,110,0.06)">
                        <i data-lucide="plus-circle" style="width:20px;height:20px;color:#123f6e"></i>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:24px;display:grid;gap:20px;grid-template-columns:1fr 1fr">
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="bar-chart-3" style="width:16px;height:16px;color:#6366f1"></i> Tareas por estado
                </h3>
                <div style="position:relative;height:220px">
                    <canvas id="chartTareas"></canvas>
                </div>
            </div>
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="pie-chart" style="width:16px;height:16px;color:#059669"></i> Tareas por grupo
                </h3>
                <div style="position:relative;height:220px">
                    <canvas id="chartGrupos"></canvas>
                </div>
            </div>
        </div>

        <div style="margin-top:20px">
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="clock" style="width:16px;height:16px;color:#123f6e"></i> Ultimas tareas del equipo
                </h3>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @forelse($recentTasks as $t)
                        <a href="{{ route('tasks.show', $t) }}" style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;text-decoration:none;transition:background 0.15s" onmouseover="this.style.background='rgba(18,63,110,0.03)'" onmouseout="this.style.background='transparent'">
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $t->priority_color }};flex-shrink:0"></span>
                            <div style="flex:1;min-width:0">
                                <span style="font-size:13px;font-weight:600;color:#1e293b">{{ $t->title }}</span>
                                <span style="font-size:11px;color:#94a3b8;margin-left:8px">{{ $t->group->name ?? '—' }}</span>
                            </div>
                            <span style="display:inline-flex;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;color:white;background:{{ $t->status_color }}">{{ $t->status_label }}</span>
                        </a>
                    @empty
                        <p style="font-size:13px;color:#94a3b8;text-align:center;padding:20px">No hay tareas en tus grupos</p>
                    @endforelse
                </div>
            </div>
        </div>

    {{-- ==================== OPERADOR / JEFE / OTROS ==================== --}}
    @else
        <div style="display:grid;gap:20px;grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Mis tareas</p>
                        <p style="font-size:28px;font-weight:700;color:#6366f1;margin:4px 0 0">{{ $myAssignedTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(99,102,241,0.06)">
                        <i data-lucide="list-checks" style="width:20px;height:20px;color:#6366f1"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Activas</p>
                        <p style="font-size:28px;font-weight:700;color:#f59e0b;margin:4px 0 0">{{ $myActiveTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(245,158,11,0.06)">
                        <i data-lucide="clock" style="width:20px;height:20px;color:#f59e0b"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Finalizadas</p>
                        <p style="font-size:28px;font-weight:700;color:#059669;margin:4px 0 0">{{ $myCompletedTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(5,150,105,0.06)">
                        <i data-lucide="check-circle" style="width:20px;height:20px;color:#059669"></i>
                    </div>
                </div>
            </div>
            <div class="card" style="padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <p style="font-size:13px;color:#94a3b8;margin:0">Retrasadas</p>
                        <p style="font-size:28px;font-weight:700;color:#ef4444;margin:4px 0 0">{{ $myDelayedTasks }}</p>
                    </div>
                    <div style="width:44px;height:44px;border-radius:12px;display:grid;place-items:center;background:rgba(239,68,68,0.06)">
                        <i data-lucide="alert-triangle" style="width:20px;height:20px;color:#ef4444"></i>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:24px;display:grid;gap:20px;grid-template-columns:1fr 1fr">
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="bar-chart-3" style="width:16px;height:16px;color:#6366f1"></i> Mis tareas por estado
                </h3>
                <div style="position:relative;height:200px">
                    <canvas id="chartTareas"></canvas>
                </div>
            </div>
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="users-round" style="width:16px;height:16px;color:#059669"></i> Mis grupos
                </h3>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @forelse($myGroups as $g)
                        <a href="{{ route('groups.show', $g) }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;text-decoration:none;transition:background 0.15s" onmouseover="this.style.background='rgba(18,63,110,0.03)'" onmouseout="this.style.background='transparent'">
                            <div style="width:32px;height:32px;border-radius:8px;display:grid;place-items:center;background:rgba(5,150,105,0.08)">
                                <i data-lucide="users-round" style="width:14px;height:14px;color:#059669"></i>
                            </div>
                            <div style="flex:1">
                                <span style="font-size:13px;font-weight:600;color:#1e293b">{{ $g->name }}</span>
                                <span style="font-size:11px;color:#94a3b8;margin-left:6px">{{ $g->activeMembers->count() }} integrantes</span>
                            </div>
                        </a>
                    @empty
                        <p style="font-size:13px;color:#94a3b8;text-align:center;padding:20px">No perteneces a ningun grupo</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div style="margin-top:20px">
            <div class="card" style="padding:24px">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin:0 0 16px;display:flex;align-items:center;gap:8px">
                    <i data-lucide="clock" style="width:16px;height:16px;color:#123f6e"></i> Mis ultimas tareas
                </h3>
                <div style="display:flex;flex-direction:column;gap:8px">
                    @forelse($myRecentTasks as $t)
                        <a href="{{ route('tasks.show', $t) }}" style="display:flex;align-items:center;gap:12px;padding:10px 14px;border-radius:10px;text-decoration:none;transition:background 0.15s" onmouseover="this.style.background='rgba(18,63,110,0.03)'" onmouseout="this.style.background='transparent'">
                            <span style="width:8px;height:8px;border-radius:50%;background:{{ $t->priority_color }};flex-shrink:0"></span>
                            <div style="flex:1;min-width:0">
                                <span style="font-size:13px;font-weight:600;color:#1e293b">{{ $t->title }}</span>
                                <span style="font-size:11px;color:#94a3b8;margin-left:8px">{{ $t->group->name ?? '—' }}</span>
                            </div>
                            <span style="display:inline-flex;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;color:white;background:{{ $t->status_color }}">{{ $t->status_label }}</span>
                        </a>
                    @empty
                        <p style="font-size:13px;color:#94a3b8;text-align:center;padding:20px">No tienes tareas asignadas</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const primary = '#123f6e';
        const green = '#059669';
        const purple = '#6366f1';
        const amber = '#f59e0b';
        const red = '#ef4444';
        const teal = '#14b8a6';
        const gray = '#94a3b8';

        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#94a3b8';

        @if($isAdmin || $isGerente)
            const statusData = @json($tasksByStatus);
            const groupData = @json($tasksByGroup);
            const statusLabels = ['pendiente','asignada','en_progreso','bloqueada','en_revision','finalizada','completada','cancelada','archivada'];
            const statusNames = ['Pendiente','Asignada','En progreso','Bloqueada','En revision','Finalizada','Completada','Cancelada','Archivada'];
            const statusColors = [amber, purple, '#6366f1', red, '#f97316', green, green, gray, '#64748b'];

            const filteredStatuses = statusLabels.filter(s => statusData[s] > 0);

            new Chart(document.getElementById('chartTareas'), {
                type: 'bar',
                data: {
                    labels: filteredStatuses.map((s, i) => statusNames[statusLabels.indexOf(s)]),
                    datasets: [{
                        data: filteredStatuses.map(s => statusData[s]),
                        backgroundColor: filteredStatuses.map(s => statusColors[statusLabels.indexOf(s)]),
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
                        x: { beginAtZero: true, grid: { color: 'rgba(18,63,110,0.04)' }, ticks: { stepSize: 1 } },
                        y: { grid: { display: false } }
                    }
                }
            });

            const groupLabels = Object.keys(groupData);
            const groupValues = Object.values(groupData);
            const groupColors = [primary, green, purple, amber, teal, red, '#ec4899', '#8b5cf6'];

            new Chart(document.getElementById('chartGrupos'), {
                type: 'doughnut',
                data: {
                    labels: groupLabels,
                    datasets: [{
                        data: groupValues,
                        backgroundColor: groupColors.slice(0, groupLabels.length),
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
        @else
            const myStatusData = @json($myTasksByStatus);
            const myStatusLabels = ['pendiente','asignada','en_progreso','bloqueada','en_revision','finalizada','completada','cancelada','archivada'];
            const myStatusNames = ['Pendiente','Asignada','En progreso','Bloqueada','En revision','Finalizada','Completada','Cancelada','Archivada'];
            const myStatusColors = [amber, purple, '#6366f1', red, '#f97316', green, green, gray, '#64748b'];

            const myFiltered = myStatusLabels.filter(s => myStatusData[s] > 0);

            new Chart(document.getElementById('chartTareas'), {
                type: 'bar',
                data: {
                    labels: myFiltered.map(s => myStatusNames[myStatusLabels.indexOf(s)]),
                    datasets: [{
                        data: myFiltered.map(s => myStatusData[s]),
                        backgroundColor: myFiltered.map(s => myStatusColors[myStatusLabels.indexOf(s)]),
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
                        x: { beginAtZero: true, grid: { color: 'rgba(18,63,110,0.04)' }, ticks: { stepSize: 1 } },
                        y: { grid: { display: false } }
                    }
                }
            });
        @endif
    });
    </script>

    <style>
        @media (max-width: 768px) {
            div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
        }
    </style>

    <script>setTimeout(() => lucide.createIcons(), 300);</script>
</x-layouts.app>
