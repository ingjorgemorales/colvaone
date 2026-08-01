@extends('emails.layout')
@section('content')
<h2 style="margin:0 0 16px;color:#123f6e;font-size:20px">Codigo de recuperacion</h2>
<p style="margin:0 0 12px">Hola <strong>{{ $name }}</strong>,</p>
<p style="margin:0 0 12px">Recibimos una solicitud para restablecer la contrasena de tu cuenta en <strong>ColvaOne</strong>.</p>
<p style="margin:0 0 12px">Ingresa este codigo en la pantalla de verificacion:</p>
<div style="margin:18px 0;padding:18px;text-align:center;background-color:#eef2f7;border-radius:8px">
  <div style="font-size:32px;letter-spacing:8px;font-weight:800;color:#123f6e">{{ $code }}</div>
</div>
<p style="margin:0 0 12px;font-size:13px;color:#64748b">Este codigo vence en {{ $expireMinutes }} minutos.</p>
<p style="margin:0 0 12px;font-size:13px;color:#64748b">Si no solicitaste este cambio, ignora este correo.</p>
<p style="margin:16px 0 0">Atentamente,<br><strong>Equipo ColvaOne</strong></p>
@endsection
