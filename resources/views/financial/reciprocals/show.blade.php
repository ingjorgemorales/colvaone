<x-layouts.app title="Detalle de Operación Recíproca | {{ config('app.name') }}" heading="Financiera" subheading="Detalle de operación recíproca #{{ $reciprocal->id }}">
    <style>
        .reciprocal-show-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
        }
        .reciprocal-show-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 16px;
            flex-wrap: wrap;
        }
        .reciprocal-show-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(18, 63, 110, 0.1);
            box-shadow: 0 4px 20px rgba(18, 63, 110, 0.05);
            padding: 32px 36px;
            margin-bottom: 24px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
        }
        @media (max-width: 680px) {
            .detail-grid { grid-template-columns: 1fr; }
        }
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .detail-label {
            font-size: 12.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }
        .detail-value {
            font-size: 15px;
            font-weight: 500;
            color: #1e293b;
        }
        .detail-badge {
            display: inline-block;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 14px;
            font-weight: 600;
            color: #123f6e;
            background: rgba(18, 63, 110, 0.08);
            padding: 4px 10px;
            border-radius: 6px;
            width: fit-content;
        }
        .currency-highlight {
            font-size: 20px;
            font-weight: 700;
        }
        .btn-pill-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 9999px;
            background: #123f6e;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(18, 63, 110, 0.18);
            transition: all 0.2s ease;
        }
        .btn-pill-primary:hover {
            background: #0d2e52;
            color: #ffffff;
            transform: translateY(-1px);
        }
    </style>

    <div class="reciprocal-show-container">
        {{-- Header con SOLO UN botón para volver y SIN botón de editar --}}
        <div class="reciprocal-show-header">
            <div>
                <h2 style="font-size: 22px; font-weight: 700; color: #123f6e; margin: 0">Operación Recíproca #{{ $reciprocal->id }}</h2>
                <p style="font-size: 13px; color: #64748b; margin: 4px 0 0">Detalle de información contable e institucional</p>
            </div>

            <div>
                <a href="{{ route('financial.reciprocals.index') }}" class="btn-pill-primary">
                    <i data-lucide="arrow-left" style="width: 16px; height: 16px"></i>
                    <span>Volver al listado</span>
                </a>
            </div>
        </div>

        {{-- Vista de solo lectura --}}
        <div class="reciprocal-show-card">
            <h3 style="font-size: 16px; font-weight: 700; color: #123f6e; margin: 0 0 20px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0">
                Información Contable e Institucional
            </h3>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Código Contable Subcuenta</span>
                    <span class="detail-badge">{{ $reciprocal->codigo_contable_subcuenta }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Nombre de la Subcuenta</span>
                    <span class="detail-value" style="font-weight: 600">{{ $reciprocal->nombre_subcuenta }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Código Entidad Recíproca</span>
                    <span class="detail-badge">{{ $reciprocal->codigo_entidad_reciproca }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Nombre Entidad Recíproca</span>
                    <span class="detail-value" style="font-weight: 600">{{ $reciprocal->nombre_entidad_reciproca }}</span>
                </div>
            </div>

            <h3 style="font-size: 16px; font-weight: 700; color: #123f6e; margin: 28px 0 20px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0">
                Valores Financieros y Fecha
            </h3>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Valor Corriente</span>
                    <span class="detail-value currency-highlight" style="color: #059669">
                        {{ $reciprocal->valor_corriente_formateado }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Valor No Corriente</span>
                    <span class="detail-value currency-highlight" style="color: #2563eb">
                        {{ $reciprocal->valor_no_corriente_formateado }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Fecha de Transacción</span>
                    <span class="detail-value" style="display: flex; align-items: center; gap: 6px">
                        <i data-lucide="calendar" style="width: 16px; height: 16px; color: #64748b"></i>
                        {{ $reciprocal->fecha_formateada }}
                    </span>
                </div>
            </div>

            <h3 style="font-size: 16px; font-weight: 700; color: #123f6e; margin: 28px 0 20px; padding-bottom: 12px; border-bottom: 1px solid #e2e8f0">
                Trazabilidad y Auditoría
            </h3>

            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Registrado por</span>
                    <span class="detail-value">{{ $reciprocal->creator ? trim($reciprocal->creator->name . ' ' . $reciprocal->creator->last_name) : 'Sistema' }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Fecha de creación</span>
                    <span class="detail-value">{{ $reciprocal->created_at ? $reciprocal->created_at->format('d/m/Y H:i') : '-' }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Última actualización</span>
                    <span class="detail-value">{{ $reciprocal->updated_at ? $reciprocal->updated_at->format('d/m/Y H:i') : '-' }}</span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Estado</span>
                    <span class="detail-value" style="color: {{ $reciprocal->is_active ? '#059669' : '#dc2626' }}; font-weight: 600">
                        {{ $reciprocal->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>