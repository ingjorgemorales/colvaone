<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $actionLabel }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    </style>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Inter',Arial,Helvetica,sans-serif">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:32px 16px">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">
                    <!-- Header -->
                    <tr>
                        <td style="background:white;border-radius:16px 16px 0 0;padding:32px 40px;text-align:center">
                            <img src="{{ config('app.url') }}/images/logo-login.png" alt="ColvaOne" width="180" style="display:block;margin:0 auto;border:0">
                        </td>
                    </tr>

                    <!-- Badge -->
                    <tr>
                        <td style="background:white;padding:0 40px">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:-24px">
                                <tr>
                                    <td align="center">
                                        @php
                                            $badgeIcons = [
                                                'assigned' => '📋',
                                                'progress' => '📊',
                                                'comment' => '💬',
                                                'status' => '🔄',
                                                'created' => '✨',
                                            ];
                                            $badgeColors = [
                                                'assigned' => '#123f6e',
                                                'progress' => '#6366f1',
                                                'comment' => '#059669',
                                                'status' => '#f59e0b',
                                                'created' => '#123f6e',
                                            ];
                                        @endphp
                                        <div style="background:white;border-radius:50%;width:56px;height:56px;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 4px 1px rgba(0,0,0,0.08)">
                                            <span style="font-size:24px">{{ $badgeIcons[$action] ?? '📋' }}</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background:white;padding:8px 40px 32px">
                            <h1 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 8px;text-align:center">{{ $actionLabel }}</h1>
                            <p style="font-size:14px;color:#64748b;margin:0 0 24px;text-align:center">Hola <strong style="color:#1e293b">{{ $user->name }}</strong>, {{ $actionDetail }}</p>

                            <!-- Task card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid rgba(18,63,110,0.08);border-radius:12px;margin-bottom:20px">
                                <tr>
                                    <td style="padding:20px">
                                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Tarea</p>
                                        <p style="font-size:16px;font-weight:700;color:#123f6e;margin:0 0 16px">{{ $task->title }}</p>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="50%" style="padding:8px 0;vertical-align:top">
                                                    <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px">Creada por</p>
                                                    <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0">{{ $task->creator->name }}</p>
                                                </td>
                                                <td width="50%" style="padding:8px 0;vertical-align:top">
                                                    <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px">Grupo</p>
                                                    <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0">{{ $task->group->name ?? '—' }}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%" style="padding:8px 0;vertical-align:top">
                                                    <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px">Prioridad</p>
                                                    @php
                                                        $priorityColors = ['baja' => '#94a3b8', 'media' => '#f59e0b', 'alta' => '#f97316', 'urgente' => '#ef4444'];
                                                        $priorityBg = ['baja' => 'rgba(148,163,184,0.1)', 'media' => 'rgba(245,158,11,0.1)', 'alta' => 'rgba(249,115,22,0.1)', 'urgente' => 'rgba(239,68,68,0.1)'];
                                                    @endphp
                                                    <p style="margin:0">
                                                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;color:{{ $priorityColors[$task->priority] ?? '#94a3b8' }};background:{{ $priorityBg[$task->priority] ?? 'rgba(148,163,184,0.1)' }}">{{ ucfirst($task->priority) }}</span>
                                                    </p>
                                                </td>
                                                <td width="50%" style="padding:8px 0;vertical-align:top">
                                                    <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px">Estado</p>
                                                    @php
                                                        $statusLabels = ['pendiente'=>'Pendiente','asignada'=>'Asignada','en_progreso'=>'En progreso','bloqueada'=>'Bloqueada','en_revision'=>'En revision','finalizada'=>'Finalizada','cancelada'=>'Cancelada','archivada'=>'Archivada'];
                                                        $statusColors = ['pendiente'=>'#f59e0b','asignada'=>'#8b5cf6','en_progreso'=>'#6366f1','bloqueada'=>'#ef4444','en_revision'=>'#f97316','finalizada'=>'#059669','cancelada'=>'#94a3b8','archivada'=>'#64748b'];
                                                    @endphp
                                                    <p style="margin:0">
                                                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;color:white;background:{{ $statusColors[$task->status] ?? '#94a3b8' }}">{{ $statusLabels[$task->status] ?? $task->status }}</span>
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%" style="padding:8px 0;vertical-align:top">
                                                    <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px">Fecha fin</p>
                                                    <p style="font-size:13px;font-weight:600;color:{{ $task->end_date->isPast() && !in_array($task->status, ['finalizada','cancelada','archivada']) ? '#ef4444' : '#1e293b' }};margin:0">{{ $task->end_date->format('d/m/Y') }}</p>
                                                </td>
                                                <td width="50%" style="padding:8px 0;vertical-align:top">
                                                    <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px">Progreso</p>
                                                    <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0">{{ $task->progress }}%</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Action detail box -->
                            @if($extra)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px">
                                <tr>
                                    <td style="padding:14px 16px;background:{{ $action === 'comment' ? 'rgba(5,150,105,0.04)' : 'rgba(99,102,241,0.04)' }};border-radius:10px;border-left:3px solid {{ $action === 'comment' ? '#059669' : '#6366f1' }}">
                                        <p style="font-size:11px;color:#94a3b8;margin:0 0 4px;text-transform:uppercase;letter-spacing:0.3px;font-weight:600">{{ $action === 'comment' ? 'Comentario' : 'Detalle' }}</p>
                                        <p style="font-size:13px;color:#475569;margin:0;line-height:1.6">{!! nl2br(e($extra)) !!}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Actor info -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px">
                                <tr>
                                    <td style="padding:12px 16px;background:rgba(18,63,110,0.03);border-radius:10px;text-align:center">
                                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px">Realizado por</p>
                                        <p style="font-size:14px;font-weight:600;color:#123f6e;margin:0">{{ $actedBy->name }} {{ $actedBy->last_name ?? '' }}</p>
                                        <p style="font-size:11px;color:#94a3b8;margin:4px 0 0">{{ now()->format('d/m/Y H:i') }}</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('tasks.show', $task) }}" style="display:inline-block;background:linear-gradient(135deg,#123f6e,#059669);color:white;font-weight:600;font-size:14px;padding:14px 32px;border-radius:10px;text-decoration:none;box-shadow:0 4px 12px rgba(18,63,110,0.2)">Ver tarea en ColvaOne</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:12px;color:#94a3b8;margin:24px 0 0;text-align:center">Si el boton no funciona, copia y pega este enlace en tu navegador:<br><a href="{{ route('tasks.show', $task) }}" style="color:#123f6e;word-break:break-all">{{ route('tasks.show', $task) }}</a></p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8fafc;border-radius:0 0 16px 16px;padding:24px 40px;border-top:1px solid rgba(18,63,110,0.06)">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <p style="font-size:12px;color:#94a3b8;margin:0 0 4px">{{ config('app.name') }} &mdash; Sistema de gestion corporativa</p>
                                        <p style="font-size:11px;color:#cbd5e1;margin:0">&copy; {{ date('Y') }} Colvatel. Todos los derechos reservados.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
