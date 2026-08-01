# Fase 2: Autenticacion

## Objetivo
Implementar acceso seguro sin registro publico, con usuarios creados por administracion, recuperacion de contrasena, verificacion de correo, cambio obligatorio de contrasena, bloqueo de usuarios inactivos y trazabilidad de eventos.

## Alcance implementado
- Login y logout.
- Recuperacion y restablecimiento de contrasena mediante broker de Laravel.
- Verificacion de correo con rutas firmadas.
- Dashboard protegido con `auth`, `verified` y `password.changed`.
- Cambio obligatorio de contrasena para usuarios con contrasena temporal.
- Rate limiting de login por correo e IP.
- Bloqueo de login para usuarios inactivos.
- Aceptacion de politica de tratamiento de datos en login.
- Eventos de autenticacion en tabla `auth_events`.
- Politica vigente en `data_policies` y aceptaciones en `data_policy_acceptances`.

## Base de datos local
La estructura esta en MySQL del sistema (servicio MySQL80) para inspeccion desde Workbench.

Conexion:
- Host: `127.0.0.1`
- Puerto: `3306`
- Base: `crm_administrativo`
- Usuario: `crm_app`
- Contrasena local: `crm_app_local_change_me`

Tablas principales:
- `users`
- `password_reset_tokens`
- `sessions`
- `data_policies`
- `data_policy_acceptances`
- `auth_events`
- `cache`
- `jobs`

## Usuario inicial
- Correo: `admin@crm.test`
- Contrasena temporal: `TempPass!2026`
- Estado: activo
- Correo: verificado
- Debe cambiar contrasena: si

## Reglas
- No existe registro publico.
- Todo dashboard requiere usuario autenticado y correo verificado.
- Un usuario inactivo no puede iniciar sesion.
- El usuario con `must_change_password = 1` debe cambiar su contrasena antes de usar el dashboard.
- La aceptacion de politica se registra con usuario, IP, User-Agent y fecha.
- Los eventos de login, fallo, logout, recuperacion y verificacion quedan auditados en `auth_events`.

## Validaciones
- Login: correo valido, contrasena obligatoria, aceptacion de politica.
- Reset/cambio de contrasena: minimo 12 caracteres, mayusculas, minusculas, numeros y simbolos.
- Cambio de contrasena autenticado: requiere contrasena actual.

## Riesgos pendientes
- MFA queda para una iteracion siguiente de la Fase 2.
- Cierre remoto de otras sesiones queda para gestion de perfil/usuarios.
- Las notificaciones de correo estan en `MAIL_MAILER=log` hasta configurar SMTP real.
- El cliente Redis local usa `predis`; en produccion puede cambiarse a `phpredis` si la extension esta instalada.

## Pruebas
Comandos ejecutados:

```bash
C:\laragon\bin\php\php-8.5.4-Win32-vs17-x64\php.exe artisan migrate --force
C:\laragon\bin\php\php-8.5.4-Win32-vs17-x64\php.exe artisan db:seed --force
C:\laragon\bin\php\php-8.5.4-Win32-vs17-x64\php.exe artisan test
npm.cmd run build
```

Resultado: pruebas y build correctos.
