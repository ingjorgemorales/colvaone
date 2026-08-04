<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a ColvaOne</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Arial,Helvetica,sans-serif">
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
                                        <div style="background:white;border-radius:50%;width:56px;height:56px;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
                                            <span style="font-size:24px">👤</span>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="background:white;padding:8px 40px 32px">
                            <h1 style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 8px;text-align:center">Bienvenido a ColvaOne</h1>
                            <p style="font-size:14px;color:#64748b;margin:0 0 24px;text-align:center">Hola <strong style="color:#1e293b">{{ $name }}</strong>, se ha creado tu cuenta en el sistema.</p>

                            <!-- Credentials card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid rgba(18,63,110,0.08);border-radius:12px;margin-bottom:20px">
                                <tr>
                                    <td style="padding:20px">
                                        <p style="font-size:12px;color:#94a3b8;margin:0 0 12px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600">Tus credenciales de acceso</p>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:10px 0;border-bottom:1px solid rgba(18,63,110,0.06)">
                                                    <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px">Correo electronico</p>
                                                    <p style="font-size:14px;font-weight:600;color:#1e293b;margin:0">{{ $email }}</p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 0">
                                                    <p style="font-size:11px;color:#94a3b8;margin:0 0 2px;text-transform:uppercase;letter-spacing:0.3px">Contrasena temporal</p>
                                                    <p style="font-size:16px;font-weight:800;color:#123f6e;margin:0;font-family:'Courier New',monospace;background:rgba(18,63,110,0.04);padding:8px 12px;border-radius:8px;display:inline-block">{{ $password }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Warning -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px">
                                <tr>
                                    <td style="padding:14px 16px;background:rgba(245,158,11,0.06);border-radius:10px;border-left:3px solid #f59e0b">
                                        <p style="font-size:13px;color:#92400e;margin:0;line-height:1.5"><strong>Importante:</strong> Al ingresar por primera vez, deberas cambiar esta contrasena por una que solo tu conozcas. Tu contrasena debe tener minimo 12 caracteres con mayusculas, minusculas, numeros y simbolos.</p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Button -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:24px">
                                <tr>
                                    <td align="center">
                                        <a href="{{ config('app.url') }}" style="display:inline-block;background:linear-gradient(135deg,#123f6e,#059669);color:white;font-weight:600;font-size:14px;padding:14px 32px;border-radius:10px;text-decoration:none;box-shadow:0 4px 12px rgba(18,63,110,0.2)">Iniciar sesion en ColvaOne</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:12px;color:#94a3b8;margin:24px 0 0;text-align:center">Si no solicitaste esta cuenta, contacta al administrador del sistema.</p>
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
