<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-bg { position: fixed; inset: 0; overflow: hidden; z-index: 0; pointer-events: none; }
        .error-grid {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(18,63,110,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(18,63,110,0.015) 1px, transparent 1px);
            background-size: 60px 60px;
        }
        .error-orb { position: absolute; border-radius: 50%; filter: blur(100px); }
        .error-orb-1 { width: 400px; height: 400px; background: rgba(18,63,110,0.06); top: -12%; left: -6%; }
        .error-orb-2 { width: 300px; height: 300px; background: rgba(5,150,105,0.05); bottom: -12%; right: -6%; }

        .error-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 640px;
            background: white;
            border-radius: 16px;
            padding: 48px 40px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(18,63,110,0.08), 0 0 0 1px rgba(18,63,110,0.04);
        }

        .error-logo { max-height: 56px; width: auto; margin: 0 auto 26px; display: block; }

        .error-icon {
            width: 72px; height: 72px; border-radius: 50%;
            display: grid; place-items: center;
            margin: 0 auto 20px;
            background: rgba(245,158,11,0.12);
            color: #d97706;
        }

        .error-code {
            font-size: 12px; font-weight: 700; letter-spacing: 0.12em;
            text-transform: uppercase; color: #94a3b8; margin-bottom: 8px;
        }

        .error-title {
            font-size: 30px; font-weight: 700; color: #123f6e;
            letter-spacing: -0.5px; line-height: 1.2;
        }

        .error-text {
            font-size: 15px; color: #64748b; line-height: 1.7;
            max-width: 480px; margin: 14px auto 0;
        }

        .error-actions {
            display: flex; flex-wrap: wrap; gap: 12px;
            align-items: center; justify-content: center;
            margin-top: 28px;
        }

        .error-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 22px; border-radius: 10px;
            font-size: 15px; font-weight: 600; font-family: inherit;
            text-decoration: none; cursor: pointer; border: 1px solid transparent;
            transition: all 0.2s;
        }
        .error-btn-primary { background: #123f6e; color: white; }
        .error-btn-primary:hover { background: #0e3159; }
        .error-btn-secondary { background: white; color: #1e293b; border-color: #e2e8f0; }
        .error-btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; }

        .error-link {
            display: inline-block; margin-top: 22px;
            font-size: 13px; font-weight: 600; color: #64748b;
            text-decoration: none; transition: color 0.2s;
        }
        .error-link:hover { color: #123f6e; text-decoration: underline; }

        @media (max-width: 520px) {
            .error-card { padding: 36px 24px; }
            .error-title { font-size: 24px; }
            .error-text { font-size: 14px; }
            .error-btn { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="error-bg">
        <div class="error-grid"></div>
        <div class="error-orb error-orb-1"></div>
        <div class="error-orb error-orb-2"></div>
    </div>

    <main class="error-card">
        <img src="{{ asset('images/logo-login.png') }}" alt="Colvatel" class="error-logo">

        <div class="error-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                <path d="M12 8v4"/>
                <path d="M12 16h.01"/>
            </svg>
        </div>

        <p class="error-code">Error {{ $code }}</p>
        <h1 class="error-title">{{ $title }}</h1>
        <p class="error-text">{{ $message }}</p>

        <div class="error-actions">
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="error-btn error-btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/>
                    <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                </svg>
                {{ auth()->check() ? 'Ir al dashboard' : 'Ir al inicio de sesion' }}
            </a>

            <button type="button" onclick="if (document.referrer &amp;&amp; history.length > 1) { history.back(); } else { window.location.href = this.previousElementSibling.href; }" class="error-btn error-btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m12 19-7-7 7-7"/>
                    <path d="M19 12H5"/>
                </svg>
                Volver
            </button>
        </div>

        @auth
            <a href="{{ route('profile.edit') }}" class="error-link">Revisar mi perfil y rol</a>
        @endauth
    </main>
</body>
</html>
