# Fase 1: Base del CRM

## Objetivo
Dejar una aplicacion Laravel 13 funcional como punto de partida modular, seguro y responsivo.

## Alcance
Incluye instalacion base, dependencias frontend, Livewire, Alpine, Tailwind, Vite, layout administrativo, menu modular, vistas legales publicas, paginas de error de acceso y documentacion inicial. No incluye autenticacion avanzada, CRUD de usuarios ni modulos de negocio.

## Reglas de negocio
No hay registro publico. Los menus deben asociarse a permisos. Los modulos sin ruta quedan visibles como planificacion hasta que su backend exista. Las politicas legales deben poder abrirse sin iniciar sesion.

## Entidades
En Fase 1 solo se conserva la entidad `users` generada por Laravel como base tecnica. Las entidades definitivas de usuarios, roles, auditoria, comites y negocio se implementaran en sus fases respectivas con migraciones dedicadas.

## Relaciones
Base preparada para usuarios, sesiones, roles, permisos, auditoria, politicas legales y menus. Las relaciones fisicas se crearan cuando se instale el paquete de permisos y se disenen los modulos.

## Migraciones
Laravel genero migraciones base para usuarios, sesiones, cache y jobs. La configuracion oficial apunta a MySQL en `.env.example`; el SQLite creado por el instalador queda solo como artefacto local de instalacion.

## Permisos
El archivo `config/navigation.php` asocia cada item del menu con su permiso base. En fases 2 y 3 se activara filtrado real con middleware, policies y paquete de permisos.

## Validaciones
Validacion pendiente para formularios reales. Se creo `app/Http/Requests` como ubicacion estandar.

## Riesgos
El entorno local usa PHP 8.4.23 y debe migrar a 8.5.4 para cumplir la plataforma declarada. Ejecutar `composer install` antes de cambiar PHP fallara por el requisito `^8.5`.

## Pruebas
Pruebas iniciales: `php artisan test`, `npm run build`, revision responsive en celular/tablet/escritorio y validacion manual de `/dashboard`, `/politica-tratamiento-datos`, `/terminos`, errores 402 y 403.

## Criterios de aceptacion
Laravel 13 instalado, Livewire disponible, Vite/Tailwind compilando, Alpine cargado, menu mobile funcional, dashboard responsivo, rutas legales publicas, Composer declarando PHP `^8.5` y documentacion inicial creada.
