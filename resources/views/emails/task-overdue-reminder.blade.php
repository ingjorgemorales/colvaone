<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarea retrasada</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:32px 16px">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:white;border-radius:16px;overflow:hidden">
                    <tr>
                        <td style="padding:28px 40px;text-align:center;border-bottom:1px solid #e2e8f0">
                            <img src="{{ config('app.url') }}/images/logo-login.png" alt="ColvaOne" width="170" style="display:block;margin:0 auto;border:0">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 40px">
                            <p style="margin:0 0 8px;color:#ef4444;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.4px">Tarea retrasada</p>
                            <h1 style="font-size:22px;line-height:1.3;color:#1e293b;margin:0 0 12px">{{ $task->title }}</h1>
                            <p style="font-size:14px;line-height:1.6;color:#475569;margin:0 0 22px">
                                Hola <strong>{{ $user->name }}</strong>,
                                @if($isCreator)
                                    la actividad que creaste esta retrasada.
                                @else
                                    tienes una actividad asignada que esta retrasada.
                                @endif
                                Este recordatorio se enviara cada 24 horas mientras la tarea siga sin finalizar.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:24px">
                                <tr>
                                    <td style="padding:18px">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:7px 0;color:#94a3b8;font-size:12px">Dias de retraso</td>
                                                <td align="right" style="padding:7px 0;color:#ef4444;font-size:14px;font-weight:700">{{ $daysOverdue }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0;color:#94a3b8;font-size:12px">Fecha fin</td>
                                                <td align="right" style="padding:7px 0;color:#1e293b;font-size:14px;font-weight:600">{{ $task->end_date->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0;color:#94a3b8;font-size:12px">Progreso</td>
                                                <td align="right" style="padding:7px 0;color:#1e293b;font-size:14px;font-weight:600">{{ $task->progress }}%</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0;color:#94a3b8;font-size:12px">Grupo</td>
                                                <td align="right" style="padding:7px 0;color:#1e293b;font-size:14px;font-weight:600">{{ $task->group->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0;color:#94a3b8;font-size:12px">Creada por</td>
                                                <td align="right" style="padding:7px 0;color:#1e293b;font-size:14px;font-weight:600">{{ $task->creator->name ?? '-' }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('tasks.show', $task) }}" style="display:inline-block;background:#123f6e;color:white;text-decoration:none;font-size:14px;font-weight:700;padding:14px 28px;border-radius:10px">Ver tarea</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:12px;color:#94a3b8;margin:24px 0 0;text-align:center">
                                Si el boton no funciona, copia este enlace:<br>
                                <a href="{{ route('tasks.show', $task) }}" style="color:#123f6e;word-break:break-all">{{ route('tasks.show', $task) }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc;padding:18px 40px;text-align:center;border-top:1px solid #e2e8f0">
                            <p style="font-size:12px;color:#94a3b8;margin:0">{{ config('app.name') }} - Sistema de gestion corporativa</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
