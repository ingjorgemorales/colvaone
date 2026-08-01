# CRM Administrativo Corporativo

Base Laravel 13 para un CRM administrativo modular, seguro y responsivo.

## Estado actual
- Fase 1 iniciada.
- Laravel 13 instalado.
- Livewire, Tailwind, Vite, Alpine y Lucide configurados.
- Layout administrativo responsivo y menu modular base.
- Fase 2 de autenticacion implementada: login, logout, recuperacion, verificacion de correo, cambio obligatorio de contrasena y auditoria de eventos.
- Documentacion inicial en `docs/`.

## Requisito importante
El proyecto exige PHP `^8.5`. Se instalo PHP `8.5.4` en `C:\laragon\bin\php\php-8.5.4-Win32-vs17-x64` y el perfil de Laragon apunta a esa version.

## MySQL Workbench
Conexion local (MySQL del sistema - servicio MySQL80):
- Host: `127.0.0.1`
- Puerto: `3306`
- Base: `crm_administrativo`
- Usuario: `crm_app`
- Contrasena: `crm_app_local_change_me`

Usuario inicial del CRM:
- Correo: `admin@crm.test`
- Contrasena temporal: `TempPass!2026`

## Comandos locales
```bash
npm install
npm run build
php artisan serve
```

Para base de datos MySQL, configura `.env` desde `.env.example` y ejecuta:

```bash
php artisan migrate
```
