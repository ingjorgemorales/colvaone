<?php

return [
    'items' => [
        ['name' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard', 'permission' => 'dashboard.view', 'order' => 10, 'enabled' => true],
        ['name' => 'Tareas', 'icon' => 'list-checks', 'route' => 'tasks.index', 'permission' => 'tasks.view', 'order' => 20, 'enabled' => true],
        ['name' => 'Presupuesto', 'icon' => 'wallet-cards', 'route' => null, 'permission' => 'budgets.view', 'order' => 30, 'enabled' => true],
        ['name' => 'Indicadores', 'icon' => 'chart-no-axes-combined', 'route' => null, 'permission' => 'indicators.view', 'order' => 40, 'enabled' => true],
        ['name' => 'Ahorros', 'icon' => 'piggy-bank', 'route' => null, 'permission' => 'savings.view', 'order' => 50, 'enabled' => true],
        ['name' => 'Aplicativos', 'icon' => 'blocks', 'route' => null, 'permission' => 'applications.view', 'order' => 60, 'enabled' => true],
        ['name' => 'Contratos', 'icon' => 'file-signature', 'route' => null, 'permission' => 'contracts.view', 'order' => 70, 'enabled' => true],
        ['name' => 'Comites', 'icon' => 'users-round', 'route' => null, 'permission' => 'committees.view', 'order' => 80, 'enabled' => true],
        ['name' => 'Usuarios', 'icon' => 'user-cog', 'route' => 'users.index', 'permission' => 'users.view', 'order' => 90, 'enabled' => true],
        ['name' => 'Roles y permisos', 'icon' => 'shield-check', 'route' => 'roles.index', 'permission' => 'roles.view', 'order' => 100, 'enabled' => true],
        ['name' => 'Auditoria', 'icon' => 'scan-search', 'route' => 'audit.index', 'permission' => 'audit.view', 'order' => 110, 'enabled' => true],
        ['name' => 'Configuracion', 'icon' => 'settings', 'route' => null, 'permission' => 'settings.view', 'order' => 120, 'enabled' => true],
        ['name' => 'Perfil', 'icon' => 'circle-user-round', 'route' => 'profile.edit', 'permission' => null, 'order' => 130, 'enabled' => true],
    ],
];
