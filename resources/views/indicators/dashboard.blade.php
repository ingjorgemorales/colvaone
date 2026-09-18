<x-layouts.app title="Dashboard de indicadores | {{ config('app.name') }}" heading="Indicadores" subheading="Dashboard de cumplimiento y estado">
    <style>
        .indicator-dashboard { display:flex; flex-direction:column; gap:18px; }
        .dashboard-toolbar {
            display:flex; align-items:end; justify-content:space-between; gap:16px; flex-wrap:wrap;
        }
        .dashboard-filters {
            display:grid; grid-template-columns:repeat(2, minmax(160px, 1fr)) auto auto;
            gap:10px; align-items:end;
        }
        .dashboard-filter-field label {
            display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:5px;
        }
        .metric-grid {
            display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px;
        }
        .metric-card {
            padding:20px; display:flex; align-items:center; justify-content:space-between; gap:16px;
        }
        .metric-icon {
            width:44px; height:44px; border-radius:12px; display:grid; place-items:center;
            background:rgba(18,63,110,0.08); color:#123f6e; flex-shrink:0;
        }
        .metric-label {
            margin:0 0 8px; font-size:12px; font-weight:700; letter-spacing:0.06em;
            color:#94a3b8; text-transform:uppercase;
        }
        .metric-value { margin:0; font-size:34px; line-height:1; font-weight:800; color:#1e293b; }
        .metric-subtitle { margin:8px 0 0; font-size:12px; color:#94a3b8; }
        .chart-grid {
            display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px;
        }
        .chart-card { padding:18px; min-height:360px; }
        .chart-card.wide { grid-column:1 / -1; }
        .chart-title {
            display:flex; align-items:center; justify-content:space-between; gap:12px;
            margin-bottom:14px;
        }
        .chart-title h3 { margin:0; font-size:15px; font-weight:700; color:#1e293b; }
        .chart-title span { font-size:12px; color:#94a3b8; }
        .chart-wrap { position:relative; min-height:280px; }
        .chart-wrap.compact { min-height:250px; }
        .chart-wrap.tall { min-height:420px; }
        .empty-dashboard {
            padding:44px 20px; text-align:center; color:#94a3b8;
        }
        .empty-dashboard i { width:34px; height:34px; margin-bottom:10px; color:#cbd5e1; }
        @media (max-width: 900px) {
            .metric-grid, .chart-grid { grid-template-columns:1fr; }
            .chart-card.wide { grid-column:auto; }
            .dashboard-filters { grid-template-columns:1fr; width:100%; }
            .dashboard-toolbar { align-items:stretch; }
        }
    </style>

    <div class="indicator-dashboard">
        @if($errors->any())
            <div style="padding:12px 16px;border-radius:10px;font-size:13px;color:#991b1b;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.15)">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="dashboard-toolbar">
            <form method="GET" action="{{ route('indicators.index') }}" class="dashboard-filters">
                <div class="dashboard-filter-field">
                    <label for="from">Fecha inicio</label>
                    <input id="from" type="date" name="from" value="{{ $filters['from'] }}" class="input-field">
                </div>
                <div class="dashboard-filter-field">
                    <label for="to">Fecha fin</label>
                    <input id="to" type="date" name="to" value="{{ $filters['to'] }}" class="input-field">
                </div>
                <button type="submit" class="btn-primary" style="padding:10px 16px">
                    <i data-lucide="filter" style="width:16px;height:16px"></i> Filtrar
                </button>
                @if($filters['from'] || $filters['to'])
                    <a href="{{ route('indicators.index') }}" class="btn-secondary" style="padding:10px 16px;color:#dc2626">
                        <i data-lucide="x" style="width:16px;height:16px"></i>
                    </a>
                @endif
            </form>

            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <a href="{{ route('indicators.list') }}" class="btn-secondary">
                    <i data-lucide="list" style="width:16px;height:16px"></i> Ver listado
                </a>
                @if(auth()->user()->hasPermission('indicators.create'))
                    <a href="{{ route('indicators.create') }}" class="btn-primary">
                        <i data-lucide="plus" style="width:16px;height:16px"></i> Nuevo indicador
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px;border-radius:10px;font-size:13px;color:#065f46;background:rgba(5,150,105,0.08);border:1px solid rgba(5,150,105,0.15)">{{ session('success') }}</div>
        @endif

        <div class="metric-grid">
            <div class="card metric-card">
                <div>
                    <p class="metric-label">Total indicadores</p>
                    <p class="metric-value">{{ $dashboard['total'] }}</p>
                    <p class="metric-subtitle">Indicadores visibles para tu usuario.</p>
                </div>
                <div class="metric-icon"><i data-lucide="chart-no-axes-combined"></i></div>
            </div>

            <div class="card metric-card">
                <div>
                    <p class="metric-label">Cumplimiento promedio</p>
                    <p class="metric-value">{{ number_format($dashboard['average'], 2, ',', '.') }}%</p>
                    <p class="metric-subtitle">Promedio del resultado vigente en el rango.</p>
                </div>
                <div class="metric-icon"><i data-lucide="gauge"></i></div>
            </div>
        </div>

        @if($dashboard['total'] === 0)
            <div class="card empty-dashboard">
                <i data-lucide="chart-no-axes-combined"></i>
                <p style="font-size:14px;font-weight:600;color:#64748b;margin:0 0 4px">No hay indicadores para graficar.</p>
                <p style="font-size:13px;margin:0">Cuando registres indicadores, el tablero se alimentara automaticamente.</p>
            </div>
        @else
            <div class="chart-grid">
                <div class="card chart-card">
                    <div class="chart-title">
                        <h3>Estado de indicadores</h3>
                        <span>{{ $dashboard['total'] }} indicadores</span>
                    </div>
                    <div class="chart-wrap compact">
                        <canvas id="indicatorStateChart"></canvas>
                    </div>
                </div>

                <div class="card chart-card">
                    <div class="chart-title">
                        <h3>Cumplimiento de objetivos de calidad</h3>
                        <span>Cantidad y cumplimiento</span>
                    </div>
                    <div class="chart-wrap compact">
                        <canvas id="qualityComplianceChart"></canvas>
                    </div>
                </div>

                <div class="card chart-card wide">
                    <div class="chart-title">
                        <h3>Estado de objetivos de calidad</h3>
                        <span>Distribucion porcentual</span>
                    </div>
                    <div class="chart-wrap compact">
                        <canvas id="qualityStatusChart"></canvas>
                    </div>
                </div>

                <div class="card chart-card wide">
                    <div class="chart-title">
                        <h3>Cumplimiento de subprocesos</h3>
                        <span>Promedio por subproceso</span>
                    </div>
                    <div class="chart-wrap tall">
                        <canvas id="subprocessComplianceChart"></canvas>
                    </div>
                </div>

                <div class="card chart-card wide">
                    <div class="chart-title">
                        <h3>Estado de subprocesos</h3>
                        <span>Distribucion porcentual</span>
                    </div>
                    <div class="chart-wrap tall">
                        <canvas id="subprocessStatusChart"></canvas>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($dashboard['total'] > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const dashboard = @json($dashboard);
                const gridColor = 'rgba(18,63,110,0.08)';
                const textColor = '#475569';
                const countAxisMax = (counts) => {
                    const highest = Math.max(0, ...counts.map((value) => Number(value || 0)));
                    return Math.max(5, Math.ceil(highest * 1.2));
                };
                const percentAxisMax = (values, minimum = 100) => {
                    const highest = Math.max(0, ...values.map((value) => Number(value || 0)));
                    return Math.max(minimum, Math.ceil((highest * 1.15) / 10) * 10);
                };
                const fitChartHeight = (elementId, count, minHeight, pixelsPerItem) => {
                    const canvas = document.getElementById(elementId);
                    const wrapper = canvas?.closest('.chart-wrap');
                    if (!wrapper) return;

                    wrapper.style.minHeight = `${Math.max(minHeight, 110 + (count * pixelsPerItem))}px`;
                };
                const shortTick = function(value) {
                    const label = String(this.getLabelForValue(value));
                    return label.length > 38 ? `${label.slice(0, 35)}...` : label;
                };
                const labelPlugin = {
                    id: 'colvaoneValueLabels',
                    afterDatasetsDraw(chart) {
                        const { ctx } = chart;
                        ctx.save();
                        ctx.font = '600 10px Inter, sans-serif';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        chart.data.datasets.forEach((dataset, datasetIndex) => {
                            const meta = chart.getDatasetMeta(datasetIndex);
                            if (meta.hidden) return;

                            meta.data.forEach((element, index) => {
                                const rawValue = dataset.data[index];
                                if (rawValue === null || rawValue === undefined) return;

                                const value = Number(rawValue || 0);
                                const count = Number(dataset.countData?.[index] ?? value);
                                if (value <= 0 && count <= 0) return;

                                const position = element.tooltipPosition();

                                if (dataset.type === 'line') {
                                    ctx.fillStyle = '#1e293b';
                                    ctx.fillText(`${Math.round(value)}%`, position.x, position.y - 14);
                                    return;
                                }

                                if (chart.options.indexAxis === 'y') {
                                    if (value < 8) return;
                                    ctx.fillStyle = dataset.backgroundColor === '#ffff00' ? '#1e293b' : '#ffffff';
                                    ctx.fillText(String(count), position.x, position.y);
                                    return;
                                }

                                ctx.fillStyle = '#1e293b';
                                ctx.fillText(String(count), position.x, position.y - 10);
                            });
                        });

                        ctx.restore();
                    },
                };

                fitChartHeight('qualityComplianceChart', dashboard.qualityObjectives.labels.length, 250, 44);
                fitChartHeight('qualityStatusChart', dashboard.qualityObjectives.labels.length, 250, 36);
                fitChartHeight('subprocessComplianceChart', dashboard.subprocesses.labels.length, 420, 34);
                fitChartHeight('subprocessStatusChart', dashboard.subprocesses.labels.length, 420, 34);

                const sharedOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: textColor, boxWidth: 12, boxHeight: 12, usePointStyle: true },
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 10,
                            cornerRadius: 8,
                        },
                    },
                };

                new Chart(document.getElementById('indicatorStateChart'), {
                    type: 'doughnut',
                    data: {
                        labels: dashboard.stateChart.labels,
                        datasets: [{
                            data: dashboard.stateChart.data,
                            backgroundColor: dashboard.stateChart.colors,
                            borderColor: '#ffffff',
                            borderWidth: 3,
                        }],
                    },
                    options: {
                        ...sharedOptions,
                        cutout: '64%',
                    },
                });

                const qualityCompliance = () => new Chart(document.getElementById('qualityComplianceChart'), {
                    type: 'bar',
                    data: {
                        labels: dashboard.qualityObjectives.labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Cantidad',
                                data: dashboard.qualityObjectives.counts,
                                backgroundColor: '#5b9bd5',
                                borderRadius: 6,
                                yAxisID: 'y',
                                order: 2,
                                barPercentage: 0.6,
                                categoryPercentage: 0.7,
                            },
                            {
                                type: 'line',
                                label: 'Cumplimiento',
                                data: dashboard.qualityObjectives.compliance,
                                borderColor: '#ed7d31',
                                backgroundColor: '#ed7d31',
                                yAxisID: 'y1',
                                order: 1,
                                tension: 0.25,
                                pointRadius: 4,
                                pointHoverRadius: 5,
                                borderWidth: 3,
                                pointBackgroundColor: '#ed7d31',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                spanGaps: false,
                                clip: false,
                            },
                        ],
                    },
                    plugins: [labelPlugin],
                    options: {
                        ...sharedOptions,
                        scales: {
                            x: {
                                ticks: { color: textColor, callback: shortTick, maxRotation: 0, minRotation: 0 },
                                grid: { display: false },
                            },
                            y: {
                                beginAtZero: true,
                                max: countAxisMax(dashboard.qualityObjectives.counts),
                                ticks: { color: textColor, precision: 0, stepSize: 1 },
                                grid: { color: gridColor },
                                title: { display: true, text: 'Cantidad', color: textColor },
                            },
                            y1: {
                                beginAtZero: true,
                                max: percentAxisMax(dashboard.qualityObjectives.compliance, 120),
                                position: 'right',
                                ticks: { color: textColor, callback: value => `${value}%` },
                                grid: { drawOnChartArea: false },
                                title: { display: true, text: 'Cumplimiento', color: textColor },
                            },
                        },
                        plugins: {
                            ...sharedOptions.plugins,
                            tooltip: {
                                ...sharedOptions.plugins.tooltip,
                                callbacks: {
                                    label: context => context.dataset.type === 'line'
                                        ? ` Cumplimiento: ${context.raw === null ? 'Sin resultado' : `${Number(context.raw || 0).toFixed(2)}%`}`
                                        : ` Cantidad: ${context.raw}`,
                                },
                            },
                        },
                    },
                });

                const horizontalCompliance = (elementId, labels, data) => new Chart(document.getElementById(elementId), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Cumplimiento',
                            data,
                            backgroundColor: '#1d5f99',
                            borderRadius: 7,
                            barPercentage: 0.7,
                            categoryPercentage: 0.72,
                        }],
                    },
                    options: {
                        ...sharedOptions,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: percentAxisMax(data),
                                ticks: { color: textColor, callback: value => `${value}%` },
                                grid: { color: gridColor },
                            },
                            y: {
                                ticks: { color: textColor, autoSkip: false, callback: shortTick },
                                grid: { display: false },
                            },
                        },
                        plugins: {
                            ...sharedOptions.plugins,
                            legend: { display: false },
                            tooltip: {
                                ...sharedOptions.plugins.tooltip,
                                callbacks: {
                                    label: context => context.raw === null
                                        ? ' Sin resultado'
                                        : ` ${Number(context.raw || 0).toFixed(2)}%`,
                                },
                            },
                        },
                    },
                });

                const stackedStatus = (elementId, labels, datasets) => new Chart(document.getElementById(elementId), {
                    type: 'bar',
                    data: { labels, datasets },
                    plugins: [labelPlugin],
                    options: {
                        ...sharedOptions,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                stacked: true,
                                beginAtZero: true,
                                max: 100,
                                ticks: { color: textColor, precision: 0, callback: value => `${value}%` },
                                grid: { color: gridColor },
                            },
                            y: {
                                stacked: true,
                                ticks: { color: textColor, autoSkip: false, callback: shortTick },
                                grid: { display: false },
                            },
                        },
                        plugins: {
                            ...sharedOptions.plugins,
                            tooltip: {
                                ...sharedOptions.plugins.tooltip,
                                callbacks: {
                                    label: context => {
                                        const count = context.dataset.countData?.[context.dataIndex] ?? 0;
                                        return ` ${context.dataset.label}: ${count} (${Number(context.raw || 0).toFixed(2)}%)`;
                                    },
                                },
                            },
                        },
                    },
                });

                qualityCompliance();
                stackedStatus('qualityStatusChart', dashboard.qualityObjectives.labels, dashboard.qualityObjectives.statusDatasets);
                horizontalCompliance('subprocessComplianceChart', dashboard.subprocesses.labels, dashboard.subprocesses.compliance);
                stackedStatus('subprocessStatusChart', dashboard.subprocesses.labels, dashboard.subprocesses.statusDatasets);

                setTimeout(() => lucide.createIcons(), 200);
            });
        </script>
    @endif
</x-layouts.app>
