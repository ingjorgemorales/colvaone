<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        * { font-family: 'Inter', sans-serif; box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f0f4f8; min-height: 100vh; }

        .app-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
        .app-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(18,63,110,0.015) 1px,transparent 1px),linear-gradient(90deg,rgba(18,63,110,0.015) 1px,transparent 1px); background-size: 60px 60px; }
        .app-orb { position: absolute; border-radius: 50%; filter: blur(100px); }
        .app-orb-1 { width: 400px; height: 400px; background: rgba(18,63,110,0.06); top: -10%; left: -5%; animation: orb1 25s ease-in-out infinite; }
        .app-orb-2 { width: 300px; height: 300px; background: rgba(5,150,105,0.05); bottom: -10%; right: -5%; animation: orb2 22s ease-in-out infinite; }
        @keyframes orb1 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(50px,40px)} }
        @keyframes orb2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-40px,-50px)} }

        .layout { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 260px; z-index: 50;
            background: rgba(255,255,255,0.9); backdrop-filter: blur(20px);
            border-right: 1px solid rgba(18,63,110,0.06);
            display: flex; flex-direction: column;
            transition: width 0.3s ease, transform 0.3s ease;
        }
        @media (min-width: 1024px) {
            .sidebar { transform: translateX(0) !important; }
        }
        .sidebar.mobile-closed { transform: translateX(-100%); }

        /* Collapsed sidebar */
        .sidebar.collapsed { width: 72px; overflow: hidden; }
        .sidebar.collapsed .sidebar-header .logo-text,
        .sidebar.collapsed .nav-label { display: none; }
        .sidebar.collapsed .sidebar-header { justify-content: center; padding: 16px 8px; }
        .sidebar.collapsed .sidebar-header img { height: 32px; width: 32px; object-fit: contain; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 10px; gap: 0; }
        .sidebar.collapsed .sidebar-footer { padding: 12px 8px; }

        .sidebar-header {
            display: flex; align-items: center; gap: 12px;
            padding: 16px 20px; border-bottom: 1px solid rgba(18,63,110,0.06);
            min-height: 64px;
        }
        .sidebar-header img { height: 36px; width: auto; transition: height 0.3s; }
        .sidebar-close {
            margin-left: auto; background: none; border: none; cursor: pointer;
            padding: 6px; border-radius: 6px; color: #94a3b8; display: flex; align-items: center; justify-content: center;
        }
        .sidebar-close:hover { background: rgba(0,0,0,0.05); }

        .sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; }
        .nav-link {
            display: flex; align-items: center; gap: 12px; padding: 10px 14px;
            border-radius: 10px; font-size: 14px; font-weight: 500;
            color: #475569; text-decoration: none; transition: all 0.2s;
        }
        .nav-link:hover { background: rgba(18,63,110,0.04); color: #123f6e; }
        .nav-link.active { background: rgba(18,63,110,0.08); color: #123f6e; font-weight: 600; }
        .nav-link i { width: 20px; height: 20px; flex-shrink: 0; }

        .sidebar-footer {
            padding: 16px; border-top: 1px solid rgba(18,63,110,0.06);
            position: relative; overflow: hidden;
        }
        .sidebar-footer .expand-icon {
            display: flex; align-items: center; justify-content: center;
            width: 32px; height: 32px; margin: 0 auto; border-radius: 8px;
            background: rgba(18,63,110,0.04); color: #94a3b8; cursor: pointer;
            transition: all 0.3s ease;
        }
        .sidebar-footer .expand-icon:hover { background: rgba(18,63,110,0.08); color: #123f6e; transform: scale(1.1); }
        .sidebar-footer .expand-icon:active { transform: scale(0.95); }

        /* Main area */
        .main-area { flex: 1; min-width: 0; display: flex; flex-direction: column; transition: margin-left 0.3s ease; }
        @media (min-width: 1024px) {
            .main-area { margin-left: 260px; }
            .main-area.sidebar-collapsed { margin-left: 72px; }
        }

        /* Header */
        .header {
            position: sticky; top: 0; z-index: 40;
            display: flex; align-items: center; gap: 12px;
            height: 64px; padding: 0 24px;
            background: rgba(255,255,255,0.8); backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(18,63,110,0.06);
        }
        .hamburger {
            display: flex; align-items: center; justify-content: center;
            width: 40px; height: 40px; border-radius: 10px;
            background: none; border: none; cursor: pointer;
            color: #475569; transition: background 0.2s; flex-shrink: 0;
        }
        .hamburger:hover { background: rgba(18,63,110,0.04); }
        .header-title { flex: 1; min-width: 0; }
        .header-title h1 { font-size: 17px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .header-title p { font-size: 13px; color: #94a3b8; }

        .content { flex: 1; padding: 24px; max-width: 1200px; width: 100%; margin: 0 auto; }

        .card {
            background: rgba(255,255,255,0.75); backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.6); border-radius: 16px;
            box-shadow: 0 4px 20px rgba(18,63,110,0.04);
        }

        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 10px; border: none;
            font-weight: 600; font-size: 14px; cursor: pointer; color: white;
            background: linear-gradient(135deg, #123f6e, #059669);
            text-decoration: none; transition: all 0.3s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(18,63,110,0.2); }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 10px;
            border: 1px solid rgba(18,63,110,0.12);
            font-weight: 500; font-size: 14px; cursor: pointer; color: #123f6e;
            background: rgba(18,63,110,0.04); text-decoration: none; transition: all 0.3s;
        }
        .btn-secondary:hover { background: rgba(18,63,110,0.08); }

        .avatar {
            background: linear-gradient(135deg, #123f6e, #059669);
            color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px; flex-shrink: 0;
        }

        .user-menu-trigger {
            display: flex; align-items: center; gap: 8px;
            background: none; border: none; cursor: pointer;
            padding: 4px 8px; border-radius: 10px; transition: background 0.2s;
        }
        .user-menu-trigger:hover { background: rgba(18,63,110,0.04); }
        .user-menu {
            position: absolute; right: 0; top: 100%; margin-top: 8px;
            width: 240px; border-radius: 12px; padding: 4px;
            border: 1px solid rgba(18,63,110,0.08);
            background: rgba(255,255,255,0.95); backdrop-filter: blur(20px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.08); z-index: 50;
        }
        .user-menu-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px; border-radius: 8px; font-size: 13px;
            color: #475569; text-decoration: none; transition: background 0.2s;
        }
        .user-menu-item:hover { background: rgba(18,63,110,0.04); }
        .user-menu-item i { width: 16px; height: 16px; color: #94a3b8; }

        .sidebar-overlay {
            position: fixed; inset: 0; background: rgba(18,63,110,0.3);
            backdrop-filter: blur(4px); z-index: 40;
        }

        .input-field {
            width: 100%; padding: 10px 14px; border-radius: 10px;
            border: 1px solid rgba(18,63,110,0.12); background: rgba(255,255,255,0.7);
            color: #1e293b; font-size: 14px; outline: none; transition: all 0.3s;
        }
        .input-field:focus { border-color: rgba(18,63,110,0.35); box-shadow: 0 0 0 3px rgba(18,63,110,0.06); }

        /* Tooltip for collapsed sidebar */
        .nav-tooltip {
            position: absolute; left: 100%; top: 50%; transform: translateY(-50%);
            margin-left: 8px; padding: 6px 12px; border-radius: 8px;
            background: #1e293b; color: white; font-size: 12px; font-weight: 500;
            white-space: nowrap; pointer-events: none; opacity: 0;
            transition: opacity 0.2s; z-index: 60;
        }
        .nav-link:hover .nav-tooltip { opacity: 1; }
    </style>
</head>
<body>
    <div class="app-bg">
        <div class="app-grid"></div>
        <div class="app-orb app-orb-1"></div>
        <div class="app-orb app-orb-2"></div>
    </div>

    <div x-data="{
        sidebarOpen: false,
        collapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebarCollapsed', this.collapsed);
            const sb = document.getElementById('sidebar');
            const ma = document.querySelector('.main-area');
            if (this.collapsed) { sb.classList.add('collapsed'); ma.classList.add('sidebar-collapsed'); }
            else { sb.classList.remove('collapsed'); ma.classList.remove('sidebar-collapsed'); }
            this.$nextTick(() => lucide.createIcons());
        }
    }" class="layout">
        <!-- Overlay movil -->
        <template x-if="sidebarOpen">
            <div class="sidebar-overlay" @click="sidebarOpen = false" x-transition.opacity></div>
        </template>

        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar" :class="(window.innerWidth < 1024 && !sidebarOpen) ? 'mobile-closed' : (collapsed ? 'collapsed' : '')">
            <div class="sidebar-header">
                <img :src="collapsed ? '{{ asset('images/logo_icono.png') }}' : '{{ asset('images/logo-login.png') }}'" alt="Logo" style="transition:all 0.3s ease">
                <span class="logo-text" style="font-size:16px;font-weight:700;color:#123f6e;white-space:nowrap">ONE</span>
                <button class="sidebar-close" @click="sidebarOpen = false" style="display:none" x-init="$watch('sidebarOpen', v => $el.style.display = (window.innerWidth < 1024 && v) ? 'flex' : 'none')">
                    <i data-lucide="x" style="width:20px;height:20px"></i>
                </button>
            </div>
            <nav class="sidebar-nav">
                @foreach ($navigationItems ?? collect(config('navigation.items'))->where('enabled', true)->sortBy('order') as $item)
                    @php
                        $isLinked = filled($item['route']);
                        $isActive = $isLinked && request()->routeIs($item['route']);
                    @endphp
                    <a href="{{ $isLinked ? route($item['route']) : '#' }}"
                        class="nav-link {{ $isActive ? 'active' : '' }}"
                        @click="if(window.innerWidth < 1024) sidebarOpen = false"
                        style="position:relative">
                        <i data-lucide="{{ $item['icon'] }}"></i>
                        <span class="nav-label">{{ $item['name'] }}</span>
                        <span class="nav-tooltip" x-show="collapsed && window.innerWidth >= 1024" x-cloak>{{ $item['name'] }}</span>
                    </a>
                @endforeach
            </nav>
            <div class="sidebar-footer">
                <div class="expand-icon" @click="toggleCollapse()">
                    <i data-lucide="chevrons-left" x-show="!collapsed" style="width:18px;height:18px;transition:transform 0.3s ease"></i>
                    <i data-lucide="chevrons-right" x-show="collapsed" x-cloak style="width:18px;height:18px;transition:transform 0.3s ease"></i>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <div class="main-area" :class="collapsed ? 'sidebar-collapsed' : ''">
            <header class="header">
                <button class="hamburger" @click="if(window.innerWidth < 1024) { sidebarOpen = !sidebarOpen } else { toggleCollapse() }">
                    <i data-lucide="menu" style="width:22px;height:22px"></i>
                </button>
                <div class="header-title">
                    <h1>{{ $heading ?? 'Dashboard' }}</h1>
                    @if (!empty($subheading))
                        <p>{{ $subheading }}</p>
                    @endif
                </div>
                @auth
                    <div style="position:relative" x-data="{ userMenu: false }" @click.outside="userMenu = false">
                        <button class="user-menu-trigger" @click="userMenu = !userMenu">
                            <div class="avatar" style="width:36px;height:36px">{{ auth()->user()->initials }}</div>
                            <i data-lucide="chevron-down" style="width:16px;height:16px;color:#94a3b8"></i>
                        </button>
                        <div x-cloak x-show="userMenu" x-transition class="user-menu">
                            <div style="padding:12px 14px;border-bottom:1px solid rgba(18,63,110,0.06)">
                                <p style="font-size:13px;font-weight:600;color:#1e293b">{{ auth()->user()->name }} {{ auth()->user()->last_name }}</p>
                                <p style="font-size:11px;color:#94a3b8;margin-top:2px">{{ auth()->user()->email }}</p>
                                <span style="display:inline-block;margin-top:6px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;color:white;background:linear-gradient(135deg,#123f6e,#059669)">{{ auth()->user()->role_label }}</span>
                            </div>
                            <div style="padding:4px">
                                <a href="{{ route('profile.edit') }}" class="user-menu-item">
                                    <i data-lucide="user"></i> Mi perfil
                                </a>
                                <a href="{{ route('password.change.edit') }}" class="user-menu-item">
                                    <i data-lucide="key-round"></i> Cambiar contrasena
                                </a>
                            </div>
                            <div style="border-top:1px solid rgba(18,63,110,0.06);padding:4px">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="user-menu-item" style="width:100%;border:none;background:none;cursor:pointer;color:#dc2626">
                                        <i data-lucide="log-out"></i> Cerrar sesion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth
            </header>
            <main class="content">
                {{ $slot }}
            </main>
        </div>
    </div>



    <script>
        if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth >= 1024) {
            document.getElementById('sidebar').classList.add('collapsed');
            document.querySelector('.main-area').classList.add('sidebar-collapsed');
        }
        document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
        window.addEventListener('load', () => { setTimeout(() => lucide.createIcons(), 100); });
    </script>
    @livewireScripts
</body>
</html>
