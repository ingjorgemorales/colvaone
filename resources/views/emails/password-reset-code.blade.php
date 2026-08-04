<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Codigo de recuperacion</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:32px 16px">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#123f6e 0%,#0d3158 50%,#059669 100%);border-radius:16px 16px 0 0;padding:32px 40px;text-align:center">
                            <img src="{{ config('app.url') }}/images/logo-login.png" alt="ColvaOne" width="180" style="display:block;margin:0 auto 12px;border:0">
                            <p style="color:rgba(255,255,255,0.7);font-size:13px;margin:0">Gestion corporativa, control y trazabilidad</p>
                        </td>
                    </tr>

                    <!-- Badge -->
                    <tr>
                        <td style="background:white;padding:0 40px">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:-24px">
                                <tr>
                                    <td align="center">
                                        <div style="background:white;border-radius:50%;width:56px;height:56px;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
                                            <span style="font-size:24px">🔐</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background:white;padding:8px 40px 32px">
                            <h1 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 8px;text-align:center">Codigo de recuperacion</h1>
                            <p style="font-size:14px;color:#64748b;margin:0 0 24px;text-align:center">Hola <strong style="color:#1e293b">{{ $name }}</strong>, recibimos una solicitud para restablecer tu contrasena.</p>

                            <!-- Code card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid rgba(18,63,110,0.08);border-radius:12px;margin-bottom:20px">
                                <tr>
                                    <td style="padding:24px;text-align:center">
                                        <p style="font-size:12px;color:#94a3b8;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Tu codigo de verificacion</p>
                                        <div style="font-size:36px;font-weight:800;color:#123f6e;letter-spacing:10px;font-family:'Courier New',monospace;background:white;padding:16px 24px;border-radius:10px;border:2px dashed rgba(18,63,110,0.15);display:inline-block">{{ $code }}</div>
                                        <p style="font-size:13px;color:#64748b;margin:16px 0 0">Este codigo vence en <strong style="color:#1e293b">{{ $expireMinutes }} minutos</strong></p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px">
                                <tr>
                                    <td style="padding:14px 16px;background:rgba(18,63,110,0.03);border-radius:10px;border-left:3px solid #123f6e">
                                        <p style="font-size:13px;color:#475569;margin:0;line-height:1.5">Ingresa este codigo en la pantalla de verificacion para restablecer tu contrasena.</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:12px;color:#94a3b8;margin:0;text-align:center">Si no solicitaste este cambio, ignora este correo.</p>
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
