<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f3f5f8;font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f5f8;padding:24px 0">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08)">
<tr><td style="padding:32px 40px 24px;text-align:center;background-color:#ffffff">
<img src="{{ url('images/logo-login.png') }}" alt="CRM" style="max-height:60px;width:auto" />
</td></tr>
<tr><td style="padding:32px 40px;color:#243044;font-size:15px;line-height:1.6">
@yield('content')
</td></tr>
<tr><td style="padding:20px 40px;border-top:1px solid #e2e8f0;text-align:center;font-size:12px;color:#94a3b8">
CRM Administrativo &copy; {{ date('Y') }}
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
