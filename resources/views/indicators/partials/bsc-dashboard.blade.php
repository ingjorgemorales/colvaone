@if($bsc['total'] === 0)
    <div class="card empty-dashboard">
        <i data-lucide="chart-no-axes-combined"></i>
        <p style="font-size:14px;font-weight:600;color:#64748b;margin:0 0 6px">Aún no hay indicadores BSC activos para tu usuario.</p>
        <p style="font-size:13px;margin:0;line-height:1.6">Registra los indicadores de la categoría BSC II con su perspectiva y sus resultados para verlos aquí.</p>
    </div>
@else
    @foreach($bsc['sections'] as $section)
        <section class="bsc-section" aria-labelledby="bsc-section-{{ $loop->index }}">
            <div class="bsc-section-title">
                <h2 id="bsc-section-{{ $loop->index }}">{{ $section['name'] }}</h2>
                <span>{{ count($section['tiles']) }} {{ count($section['tiles']) === 1 ? 'indicador' : 'indicadores' }}</span>
            </div>

            @foreach($section['tiles'] as $tile)
                @php
                    $annualGoalText = $tile['annual_goal_text'];
                    $annualCardGoalText = $tile['annual']['goal_text'];
                    $shouldShowMillions = ($tile['unit']['money'] ?? false);

                    if ($shouldShowMillions && $annualGoalText && ! str_contains(mb_strtolower($annualGoalText), 'millones')) {
                        $annualGoalText .= ' millones';
                    }

                    if ($shouldShowMillions && $annualCardGoalText && ! str_contains(mb_strtolower($annualCardGoalText), 'millones')) {
                        $annualCardGoalText .= ' millones';
                    }
                @endphp
                <div class="bsc-row">
                    <div class="bsc-cards">
                        {{-- Cumplimiento del rango consultado, como tarjeta igual que las del dashboard SGC. --}}
                        <div class="card metric-card bsc-summary">
                            <div style="width:100%">
                                <p class="metric-label">{{ $tile['mode'] === \App\Models\Indicator::ACCUMULATION_LAST ? 'Cumplimiento del último período' : 'Cumplimiento acumulado' }}</p>
                                <p class="metric-value">{{ $tile['display_text'] ?? '—' }}</p>

                                @if($tile['display'] !== null)
                                    <div class="bsc-progress" aria-hidden="true"><span style="width:{{ $tile['display'] }}%"></span></div>
                                @endif

                                @if($tile['compliance'] !== null && $tile['compliance'] !== $tile['display'])
                                    <p class="metric-subtitle">Calculado: <strong style="color:#475569">{{ $tile['compliance_text'] }}</strong>. El tablero lo lee de 0% a 100%.</p>
                                @endif

                                @if($tile['figures'])
                                    <dl class="bsc-figures">
                                        @foreach($tile['figures'] as $figure)
                                            <div>
                                                <dt>{{ $figure['label'] }}</dt>
                                                <dd>{{ $figure['value'] }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                @endif

                                <p class="metric-subtitle">{{ $tile['reference'] ?? 'Sin resultados en el rango consultado.' }}</p>

                                @if($tile['notice'])
                                    <p class="bsc-notice" role="status">{{ $tile['notice'] }}</p>
                                @endif
                            </div>
                            <div class="metric-icon"><i data-lucide="gauge"></i></div>
                        </div>

                        {{-- Lo acumulado frente a la meta de todo el año: la barra "Avance 2026" del Excel. --}}
                        <div class="card metric-card bsc-summary">
                            <div style="width:100%">
                                <p class="metric-label">Avance frente a la meta del año</p>

                                @if($tile['annual']['goal_text'] === null)
                                    <p class="metric-value" style="color:#cbd5e1">—</p>
                                    <p class="metric-subtitle">Registra la meta del año en la ficha técnica del indicador para ver este avance.</p>
                                @else
                                    <p class="metric-value">{{ $tile['annual']['display_text'] ?? '—' }}</p>

                                    @if($tile['annual']['display'] !== null)
                                        <div class="bsc-progress" aria-hidden="true"><span style="width:{{ $tile['annual']['display'] }}%"></span></div>
                                    @endif

                                    @if($tile['annual']['compliance'] !== null && $tile['annual']['compliance'] !== $tile['annual']['display'])
                                        <p class="metric-subtitle">Calculado: <strong style="color:#475569">{{ $tile['annual']['compliance_text'] }}</strong>. El tablero lo lee de 0% a 100%.</p>
                                    @endif

                                    <dl class="bsc-figures">
                                        <div>
                                            <dt>Meta del año ({{ $tile['unit']['label'] ?? $tile['unit']['caption'] }})</dt>
                                            <dd>{{ $annualCardGoalText }}</dd>
                                        </div>
                                    </dl>

                                    @if($tile['annual']['notice'])
                                        <p class="bsc-notice" role="status">{{ $tile['annual']['notice'] }}</p>
                                    @endif
                                @endif
                            </div>
                            <div class="metric-icon"><i data-lucide="flag"></i></div>
                        </div>
                    </div>

                    {{-- Real vs presupuestado por período, con la tabla de datos debajo como en el Excel. --}}
                    <div class="card chart-card bsc-chart-card">
                        <div class="chart-title">
                            <div>
                                <h3><a href="{{ route('indicators.show', ['indicator' => $tile['id'], 'tab' => 'resultados']) }}">{{ $tile['name'] }}</a></h3>
                                @if($annualGoalText)
                                    <p class="bsc-chart-goal">Meta del año: {{ $annualGoalText }} <span style="color:#64748b;font-weight:600">({{ $tile['unit']['label'] ?? $tile['unit']['caption'] }})</span></p>
                                @endif
                            </div>
                            <span>{{ $tile['unit']['caption'] ?? ($tile['labels']['actual'] . ' vs ' . mb_strtolower($tile['labels']['target']) . ' por período') }}</span>
                        </div>

                        @if($tile['count'] > 0)
                            <div class="bsc-chart-scroll">
                                <div class="bsc-chart-inner" style="--bsc-columns: {{ $tile['count'] }}">
                                    <div class="chart-wrap">
                                        <canvas id="bsc-chart-{{ $tile['id'] }}" role="img"
                                            aria-label="{{ $tile['name'] }}: {{ mb_strtolower($tile['labels']['target']) }} y {{ mb_strtolower($tile['labels']['actual']) }} por período, con el porcentaje de cumplimiento. Los valores están en la tabla de datos debajo."></canvas>
                                    </div>

                                    <table class="bsc-data-table">
                                        <caption class="bsc-sr-only">Datos de {{ $tile['name'] }} por período</caption>
                                        <thead>
                                            <tr>
                                                <th scope="col"><span class="bsc-sr-only">Serie</span></th>
                                                @foreach($tile['periods'] as $period)
                                                    <th scope="col" title="{{ $period['range'] }}">{{ $period['label'] }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th scope="row"><i class="bsc-key" style="background:#8db4e2"></i>{{ $tile['labels']['target'] }}</th>
                                                @foreach($tile['periods'] as $period)
                                                    <td>{{ $period['target_text'] }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th scope="row"><i class="bsc-key" style="background:#123f6e"></i>{{ $tile['labels']['actual'] }}</th>
                                                @foreach($tile['periods'] as $period)
                                                    <td>{{ $period['actual_text'] }}</td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <th scope="row"><i class="bsc-key bsc-key-line"></i>Cumplimiento</th>
                                                @foreach($tile['periods'] as $period)
                                                    <td>{{ $period['compliance_text'] }}</td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="empty-dashboard">
                                <i data-lucide="calendar-search"></i>
                                <p style="font-size:14px;font-weight:600;color:#64748b;margin:0 0 6px">No hay resultados activos en este rango.</p>
                                <p style="font-size:13px;margin:0;line-height:1.6">Ajusta las fechas o registra los resultados del indicador.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </section>
    @endforeach
@endif
