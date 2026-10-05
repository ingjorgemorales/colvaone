<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const charts = {{ Illuminate\Support\Js::from($bsc['charts']) }};
        // Un decimal, como el Excel de la junta: 104,1%.
        const percent = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Porcentaje encima de cada punto de la linea.
        const complianceLabels = periods => ({
            id: 'bscComplianceLabels',
            afterDatasetsDraw(chart) {
                if (!chart.isDatasetVisible(2)) return;
                const { ctx } = chart;
                ctx.save();
                ctx.font = '600 11px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillStyle = '#1e293b';
                chart.getDatasetMeta(2).data.forEach((point, index) => {
                    if (periods[index].compliance === null) return;
                    ctx.fillText(`${percent.format(periods[index].compliance)}%`, point.x, point.y - 12);
                });
                ctx.restore();
            },
        });

        charts.forEach(({ id, labels, unit, periods }) => {
            const canvas = document.getElementById(`bsc-chart-${id}`);
            if (!canvas) return;

            const amount = new Intl.NumberFormat('es-CO', { maximumFractionDigits: unit.decimals });
            const valueText = value => `${unit.prefix}${amount.format(value)}${unit.suffix}`;
            // La tabla de datos va debajo; el area de barras se ajusta a sus columnas de meses
            // para que cada barra quede justo sobre su mes, como en el Excel. Se vuelve a medir
            // cuando la pagina termina de acomodarse o cambia de tamano.
            const months = [...(canvas.closest('.bsc-chart-inner')?.querySelectorAll('.bsc-data-table thead th') ?? [])].slice(1);
            const fitToTable = chart => {
                if (!months.length) return;
                const box = chart.canvas.getBoundingClientRect();
                const left = Math.max(0, months[0].getBoundingClientRect().left - box.left);
                const right = Math.max(0, box.right - months[months.length - 1].getBoundingClientRect().right);
                const padding = chart.options.layout.padding;
                if (Math.abs(padding.left - left) < 0.5 && Math.abs(padding.right - right) < 0.5) return;
                padding.left = left;
                padding.right = right;
                chart.update('none');
            };

            const bar = (label, key, color) => ({
                label,
                data: periods.map(period => period[key]),
                backgroundColor: color,
                borderRadius: 5,
                maxBarThickness: 30,
                order: 2,
                yAxisID: 'y',
            });

            const chart = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: periods.map(period => period.label),
                    datasets: [
                        bar(labels.target, 'target', '#8db4e2'),
                        bar(labels.actual, 'actual', '#123f6e'),
                        {
                            type: 'line',
                            label: 'Cumplimiento',
                            data: periods.map(period => period.compliance),
                            borderColor: '#7cc142',
                            backgroundColor: '#ffffff',
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#7cc142',
                            pointBorderWidth: 3,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointHoverRadius: 6,
                            tension: 0.25,
                            spanGaps: false,
                            order: 1,
                            yAxisID: 'y1',
                        },
                    ],
                },
                plugins: [complianceLabels(periods)],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: reducedMotion ? false : undefined,
                    interaction: { mode: 'index', intersect: false },
                    // Sin el relleno automatico de Chart.js, que corria las barras unos pixeles por mes.
                    layout: { autoPadding: false, padding: { top: 24, bottom: 0, left: 0, right: 0 } },
                    onResize: chart => requestAnimationFrame(() => fitToTable(chart)),
                    plugins: {
                        // La leyenda va en la tabla de datos, con el color de cada serie.
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                title: items => items.length ? periods[items[0].dataIndex].range : '',
                                label: context => context.raw === null
                                    ? ` ${context.dataset.label}: sin cálculo`
                                    : context.dataset.type === 'line'
                                        ? ` ${context.dataset.label}: ${percent.format(context.raw)}%`
                                        : ` ${context.dataset.label}: ${valueText(context.raw)}`,
                            },
                        },
                    },
                    // Sin ejes: los valores se leen en la tabla de datos, como en el Excel.
                    scales: {
                        x: { display: false },
                        // Holgura arriba para que la linea de cumplimiento flote sobre las barras.
                        // Sin minimo fijo en cero: EBITDA y similares pueden ir en negativo.
                        y: { display: false, beginAtZero: true, grace: '70%' },
                        y1: { display: false, beginAtZero: true, suggestedMax: 100, grace: '10%' },
                    },
                },
            });

            requestAnimationFrame(() => fitToTable(chart));
            window.addEventListener('load', () => fitToTable(chart));
        });
    });
</script>
