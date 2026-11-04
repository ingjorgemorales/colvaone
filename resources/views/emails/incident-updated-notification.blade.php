@php
    $isRisk = ($incident->failure_type === 'risk');
    $title = $isRisk ? 'Actualización de riesgo priorizado' : 'Actualización de incidente';
    $classification = $isRisk ? 'Riesgo priorizado' : 'Incidente general';
    $editorName = trim($editor->name.' '.$editor->last_name);
    $editorRole = ($editor->id === $incident->created_by) ? 'Reportante' : (($editor->id === $incident->responsible_user_id) ? 'Responsable asignado' : 'Gestor');
    $fontFamily = "'Inter', Arial, Helvetica, sans-serif";
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} {{ $incident->ticket }} | ColvaOne</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body, table, td, p, a, div, span, strong {
            font-family: 'Inter', Arial, Helvetica, sans-serif !important;
        }
        a.email-btn,
        a.email-btn:link,
        a.email-btn:visited,
        a.email-btn:hover,
        a.email-btn:active {
            color: #ffffff !important;
            background-color: #0b3967 !important;
            text-decoration: none !important;
        }
        a.email-btn span {
            color: #ffffff !important;
            text-decoration: none !important;
        }
        a.email-fallback-link,
        a.email-fallback-link:visited {
            color: #0b3967 !important;
            text-decoration: underline !important;
        }
    </style>
    <!--[if mso]>
    <style type="text/css">
        body, table, td, p, a, div, span, strong {
            font-family: Arial, Helvetica, sans-serif !important;
        }
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: {{ $fontFamily }}; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f6f9; padding: 32px 16px; font-family: {{ $fontFamily }};">
        <tr>
            <td align="center" style="font-family: {{ $fontFamily }};">
                <table role="presentation" width="680" cellpadding="0" cellspacing="0" style="max-width: 680px; width: 100%; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; font-family: {{ $fontFamily }};">
                    
                    <!-- Header with Colvatel logo left-aligned -->
                    <tr>
                        <td style="padding: 24px 36px; border-bottom: 1px solid #eef2f6; background: #ffffff; font-family: {{ $fontFamily }};">
                            <img src="{{ config('app.url') }}/images/logo-login.png" alt="Colvatel" style="max-height: 48px; width: auto; display: block; border: 0;">
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 32px 36px 36px; background: #ffffff; font-family: {{ $fontFamily }};">
                            
                            <!-- Category tag -->
                            <p style="margin: 0 0 8px; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; font-family: {{ $fontFamily }};">
                                COLVAONE &middot; GESTI&Oacute;N DE INCIDENTES Y RIESGOS
                            </p>

                            <!-- Title -->
                            <h1 style="margin: 0 0 6px; font-size: 22px; font-weight: 700; color: #0b3967; line-height: 1.3; font-family: {{ $fontFamily }};">
                                Reporte {{ $incident->ticket }} actualizado
                            </h1>

                            <!-- Classification -->
                            <p style="margin: 0 0 20px; font-size: 14px; color: #475569; font-family: {{ $fontFamily }};">
                                Clasificaci&oacute;n: <strong style="color: {{ $isRisk ? '#b45309' : '#0b3967' }};">{{ $classification }}</strong> &middot; <strong style="color: #0b3967;">{{ $incident->failure?->name }}</strong>
                            </p>

                            <!-- Greeting & Notice -->
                            <p style="margin: 0 0 10px; font-size: 14px; color: #1e293b; line-height: 1.6; font-family: {{ $fontFamily }};">
                                Hola <strong>{{ $recipient->name }}</strong>,
                            </p>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #334155; line-height: 1.6; font-family: {{ $fontFamily }};">
                                <strong>{{ $editorName }}</strong> ({{ $editorRole }}) ha realizado una actualizaci&oacute;n en la informaci&oacute;n del reporte <strong>{{ $incident->ticket }}</strong>.
                            </p>

                            <!-- Changes Registered Card (if available) -->
                            @if(!empty($changes))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width: 100%; border: 1px solid #bfdbfe; background: #f0f7ff; border-left: 4px solid #0b3967; border-radius: 6px; margin-bottom: 24px; font-family: {{ $fontFamily }};">
                                    <tr>
                                        <td style="padding: 16px 20px; font-family: {{ $fontFamily }};">
                                            <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #0b3967; letter-spacing: 0.5px; margin-bottom: 10px; font-family: {{ $fontFamily }};">
                                                MODIFICACIONES REALIZADAS
                                            </div>
                                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family: {{ $fontFamily }}; font-size: 14px; line-height: 1.5;">
                                                @foreach($changes as $ch)
                                                    <tr>
                                                        <td style="padding: 5px 0; color: #1e293b; font-family: {{ $fontFamily }};">
                                                            <strong style="color: #0b3967;">&bull; {{ $ch['field'] }}:</strong>
                                                            @if(!empty($ch['old']))
                                                                <span style="color: #64748b; text-decoration: line-through; margin: 0 6px;">{{ $ch['old'] }}</span>
                                                                <span style="color: #047857; font-weight: 600;">&rarr; {{ $ch['new'] }}</span>
                                                            @else
                                                                <span style="color: #047857; font-weight: 600; margin-left: 6px;">{{ $ch['new'] }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <!-- Structured Information Grid -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; background: #ffffff; margin-bottom: 24px; border-radius: 6px; overflow: hidden; font-family: {{ $fontFamily }};">
                                <tr>
                                    <td width="50%" style="padding: 12px 16px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; vertical-align: top; font-family: {{ $fontFamily }};">
                                        <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-bottom: 4px; font-family: {{ $fontFamily }};">
                                            N&Uacute;MERO DE TICKET
                                        </div>
                                        <div style="font-size: 14px; font-weight: 700; color: #0b3967; font-family: {{ $fontFamily }};">
                                            {{ $incident->ticket }}
                                        </div>
                                    </td>
                                    <td width="50%" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; vertical-align: top; font-family: {{ $fontFamily }};">
                                        <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-bottom: 4px; font-family: {{ $fontFamily }};">
                                            ESTADO ACTUAL
                                        </div>
                                        <div style="font-size: 14px; font-weight: 600; color: #1e293b; font-family: {{ $fontFamily }};">
                                            {{ \App\Models\Incident::STATUSES[$incident->status] ?? ucfirst($incident->status) }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding: 12px 16px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; vertical-align: top; font-family: {{ $fontFamily }};">
                                        <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-bottom: 4px; font-family: {{ $fontFamily }};">
                                            SEDE / UBICACI&Oacute;N
                                        </div>
                                        <div style="font-size: 14px; font-weight: 600; color: #334155; font-family: {{ $fontFamily }};">
                                            {{ $incident->zone?->name ?? 'Sin sede' }} &ndash; {{ $incident->location }}
                                        </div>
                                    </td>
                                    <td width="50%" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; vertical-align: top; font-family: {{ $fontFamily }};">
                                        <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-bottom: 4px; font-family: {{ $fontFamily }};">
                                            PRIORIDAD / AFECTACI&Oacute;N
                                        </div>
                                        <div style="font-size: 14px; font-weight: 600; color: #334155; font-family: {{ $fontFamily }};">
                                            Prioridad {{ $incident->priority?->name ?? 'Normal' }} &middot; {{ $incident->impact?->name ?? 'Sin impacto' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" style="padding: 12px 16px; border-right: 1px solid #e2e8f0; vertical-align: top; font-family: {{ $fontFamily }};">
                                        <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-bottom: 4px; font-family: {{ $fontFamily }};">
                                            REPORTADO POR
                                        </div>
                                        <div style="font-size: 14px; font-weight: 600; color: #334155; font-family: {{ $fontFamily }};">
                                            {{ $incident->reporter_name }} <span style="font-size: 13px; color: #64748b; font-weight: 400;">({{ $incident->reporter_position }})</span>
                                        </div>
                                    </td>
                                    <td width="50%" style="padding: 12px 16px; vertical-align: top; font-family: {{ $fontFamily }};">
                                        <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-bottom: 4px; font-family: {{ $fontFamily }};">
                                            RESPONSABLE ASIGNADO
                                        </div>
                                        <div style="font-size: 14px; font-weight: 600; color: #334155; font-family: {{ $fontFamily }};">
                                            {{ $incident->responsibleUser ? ($incident->responsibleUser->name.' '.$incident->responsibleUser->last_name) : ($incident->responsible_name ?: 'Sin asignar') }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Description Card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="width: 100%; border: 1px solid #dbeafe; background: #f8fafc; border-radius: 6px; margin-bottom: 24px; font-family: {{ $fontFamily }};">
                                <tr>
                                    <td style="padding: 16px 20px; font-family: {{ $fontFamily }};">
                                        <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #475569; letter-spacing: 0.5px; margin-bottom: 8px; font-family: {{ $fontFamily }};">
                                            DESCRIPCI&Oacute;N DEL REPORTE
                                        </div>
                                        <div style="font-size: 14px; color: #1e293b; line-height: 1.6; white-space: pre-wrap; font-family: {{ $fontFamily }};">{{ $incident->description }}</div>
                                        @if(!empty($incident->affected_assets))
                                            <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 14px; color: #64748b; font-family: {{ $fontFamily }};">
                                                <strong style="color: #334155;">Equipos o activos afectados:</strong> {{ $incident->affected_assets }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- Action button (14px) -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 16px 0;">
                                <tr>
                                    <td align="left" bgcolor="#0b3967" style="border-radius: 6px; background-color: #0b3967;">
                                        <a href="{{ route('incidents.show', $incident) }}"
                                           target="_blank"
                                           class="email-btn"
                                           style="display: inline-block; background-color: #0b3967; color: #ffffff !important; padding: 14px 28px; border-radius: 6px; font-size: 14px; font-weight: 600; text-decoration: none !important; font-family: {{ $fontFamily }}; border: 1px solid #0b3967; line-height: 1.2; -webkit-text-size-adjust: none;">
                                            <span style="color: #ffffff !important; text-decoration: none !important; font-family: {{ $fontFamily }}; font-size: 14px; font-weight: 600; line-height: 1.2;">
                                                Ver detalle del reporte en ColvaOne &rarr;
                                            </span>
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 24px; font-size: 12px; color: #94a3b8; line-height: 1.5; font-family: {{ $fontFamily }};">
                                Si el bot&oacute;n no abre directamente, copia y pega este enlace en tu navegador:<br>
                                <a href="{{ route('incidents.show', $incident) }}" class="email-fallback-link" style="color: #0b3967 !important; text-decoration: underline !important; word-break: break-all; font-family: {{ $fontFamily }}; font-size: 12px;">
                                    {{ route('incidents.show', $incident) }}
                                </a>
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 36px; background-color: #f8fafc; border-top: 1px solid #eef2f6; text-align: center; font-family: {{ $fontFamily }};">
                            <p style="margin: 0 0 4px; font-size: 13px; color: #64748b; font-family: {{ $fontFamily }};">
                                Este es un correo autom&aacute;tico generado por el Sistema de Gesti&oacute;n de Incidentes de ColvaOne.
                            </p>
                            <p style="margin: 0; font-size: 13px; color: #94a3b8; font-family: {{ $fontFamily }};">
                                &copy; {{ date('Y') }} Colvatel. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

