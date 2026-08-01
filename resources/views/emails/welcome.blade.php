@extends('emails.layout')
@section('content')
<h2 style="margin:0 0 16px;color:#123f6e;font-size:20px">Bienvenido a ColvaOne</h2>
<p style="margin:0 0 12px">Hola <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 12px">Se ha creado una cuenta para ti en el sistema <strong>ColvaOne</strong>.</p>
<p style="margin:0 0 8px">Inicia sesion con estas credenciales temporales:</p>
<table role="presentation" style="margin:16px 0;padding:16px;background-color:#f8fafc;border-radius:6px;font-size:14px">
<tr><td style="padding:4px 12px 4px 0;font-weight:600;color:#64748b">Correo:</td><td style="padding:4px 0;font-weight:700;color:#0f172a">{{ $email }}</td></tr>
<tr><td style="padding:4px 12px 4px 0;font-weight:600;color:#64748b">Contrasena temporal:</td><td style="padding:4px 0;font-weight:800;color:#123f6e">{{ $password }}</td></tr>
</table>
<p style="margin:0 0 12px;padding:12px;background-color:#fef3c7;border-radius:6px;font-size:13px;color:#92400e"><strong>Importante:</strong> Al ingresar por primera vez, deberas cambiar esta contrasena por una que solo tu conozcas.</p>
<p style="margin:0">Si no solicitaste esta cuenta, contacta al administrador del sistema.</p>
<p style="margin:16px 0 0">Atentamente,<br><strong>Equipo ColvaOne</strong></p>
@endsection
