<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="preload" href="{{ asset('images/logo-login.png') }}" as="image">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
            background: #f8fafc;
        }

        /* === NEURAL NETWORK BACKGROUND === */
        .neural-bg {
            position: fixed;
            inset: 0;
            overflow: hidden;
            z-index: 0;
        }

        /* Circuit board grid */
        .circuit-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(18, 63, 110, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(18, 63, 110, 0.03) 1px, transparent 1px),
                linear-gradient(rgba(18, 63, 110, 0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(18, 63, 110, 0.015) 1px, transparent 1px);
            background-size: 100px 100px, 100px 100px, 20px 20px, 20px 20px;
        }

        /* Animated data streams - vertical */
        .data-stream {
            position: absolute;
            width: 1px;
            background: linear-gradient(180deg, transparent, rgba(18, 63, 110, 0.08), transparent);
            animation: streamFlow linear infinite;
        }
        .data-stream--1 { height: 200px; left: 10%; animation-duration: 8s; animation-delay: 0s; }
        .data-stream--2 { height: 150px; left: 25%; animation-duration: 6s; animation-delay: 1s; }
        .data-stream--3 { height: 180px; left: 40%; animation-duration: 9s; animation-delay: 2s; }
        .data-stream--4 { height: 120px; left: 55%; animation-duration: 7s; animation-delay: 0.5s; }
        .data-stream--5 { height: 200px; left: 70%; animation-duration: 10s; animation-delay: 3s; }
        .data-stream--6 { height: 160px; left: 85%; animation-duration: 8s; animation-delay: 1.5s; }
        .data-stream--7 { height: 140px; left: 15%; animation-duration: 7s; animation-delay: 4s; }
        .data-stream--8 { height: 190px; left: 60%; animation-duration: 11s; animation-delay: 2.5s; }
        .data-stream--9 { height: 130px; left: 90%; animation-duration: 6s; animation-delay: 0.8s; }
        .data-stream--10 { height: 170px; left: 35%; animation-duration: 9s; animation-delay: 3.5s; }

        @keyframes streamFlow {
            0% { transform: translateY(-100vh); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }

        /* Neural nodes */
        .neural-node {
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(18, 63, 110, 0.15);
            animation: nodePulse 4s ease-in-out infinite;
        }
        .neural-node::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 1px solid rgba(18, 63, 110, 0.08);
            animation: nodeRing 4s ease-in-out infinite;
        }

        .neural-node--1 { top: 12%; left: 8%; animation-delay: 0s; }
        .neural-node--2 { top: 25%; left: 18%; animation-delay: 0.5s; }
        .neural-node--3 { top: 15%; left: 35%; animation-delay: 1s; }
        .neural-node--4 { top: 30%; left: 50%; animation-delay: 1.5s; }
        .neural-node--5 { top: 18%; left: 65%; animation-delay: 2s; }
        .neural-node--6 { top: 35%; left: 80%; animation-delay: 2.5s; }
        .neural-node--7 { top: 50%; left: 12%; animation-delay: 3s; }
        .neural-node--8 { top: 55%; left: 45%; animation-delay: 0.8s; }
        .neural-node--9 { top: 60%; left: 72%; animation-delay: 1.8s; }
        .neural-node--10 { top: 70%; left: 20%; animation-delay: 2.8s; }
        .neural-node--11 { top: 75%; left: 55%; animation-delay: 3.5s; }
        .neural-node--12 { top: 80%; left: 85%; animation-delay: 0.3s; }
        .neural-node--13 { top: 42%; left: 92%; animation-delay: 1.3s; }
        .neural-node--14 { top: 88%; left: 38%; animation-delay: 2.3s; }
        .neural-node--15 { top: 5%; left: 50%; animation-delay: 3.3s; }

        @keyframes nodePulse {
            0%, 100% { transform: scale(1); opacity: 0.4; background: rgba(18, 63, 110, 0.15); }
            50% { transform: scale(1.8); opacity: 1; background: rgba(18, 63, 110, 0.35); }
        }
        @keyframes nodeRing {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(2.5); opacity: 0; }
        }

        /* Neural connections - animated lines between nodes */
        .neural-connection {
            position: absolute;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(18, 63, 110, 0.1), transparent);
            transform-origin: left center;
            animation: connectionPulse 5s ease-in-out infinite;
        }
        .neural-connection--1 { top: 15%; left: 10%; width: 120px; transform: rotate(20deg); animation-delay: 0s; }
        .neural-connection--2 { top: 20%; left: 30%; width: 150px; transform: rotate(-10deg); animation-delay: 1s; }
        .neural-connection--3 { top: 28%; left: 55%; width: 100px; transform: rotate(15deg); animation-delay: 2s; }
        .neural-connection--4 { top: 50%; left: 15%; width: 180px; transform: rotate(5deg); animation-delay: 0.5s; }
        .neural-connection--5 { top: 58%; left: 50%; width: 130px; transform: rotate(-8deg); animation-delay: 1.5s; }
        .neural-connection--6 { top: 72%; left: 25%; width: 160px; transform: rotate(12deg); animation-delay: 2.5s; }
        .neural-connection--7 { top: 78%; left: 60%; width: 110px; transform: rotate(-5deg); animation-delay: 3s; }
        .neural-connection--8 { top: 35%; left: 75%; width: 90px; transform: rotate(25deg); animation-delay: 0.8s; }

        @keyframes connectionPulse {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 1; }
        }

        /* Binary/hex floating text */
        .binary-float {
            position: absolute;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            color: rgba(18, 63, 110, 0.06);
            white-space: nowrap;
            animation: binaryDrift linear infinite;
            user-select: none;
        }
        .binary-float--1 { top: 8%; left: 5%; animation-duration: 20s; }
        .binary-float--2 { top: 20%; left: 75%; animation-duration: 25s; animation-delay: 3s; }
        .binary-float--3 { top: 40%; left: 3%; animation-duration: 22s; animation-delay: 6s; }
        .binary-float--4 { top: 65%; left: 88%; animation-duration: 18s; animation-delay: 1s; }
        .binary-float--5 { top: 85%; left: 40%; animation-duration: 24s; animation-delay: 4s; }
        .binary-float--6 { top: 55%; left: 92%; animation-duration: 21s; animation-delay: 7s; }
        .binary-float--7 { top: 30%; left: 60%; animation-duration: 19s; animation-delay: 2s; }

        @keyframes binaryDrift {
            0% { transform: translateY(0) translateX(0); opacity: 0.4; }
            25% { transform: translateY(-15px) translateX(10px); opacity: 0.7; }
            50% { transform: translateY(5px) translateX(-5px); opacity: 0.3; }
            75% { transform: translateY(-10px) translateX(8px); opacity: 0.6; }
            100% { transform: translateY(0) translateX(0); opacity: 0.4; }
        }

        /* Hexagon accents */
        .hex-accent {
            position: absolute;
            width: 40px;
            height: 46px;
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            background: rgba(18, 63, 110, 0.03);
            border: 1px solid rgba(18, 63, 110, 0.04);
            animation: hexFloat 8s ease-in-out infinite;
        }
        .hex-accent--1 { top: 10%; left: 92%; animation-delay: 0s; }
        .hex-accent--2 { top: 30%; left: 2%; animation-delay: 2s; width: 30px; height: 35px; }
        .hex-accent--3 { top: 70%; left: 90%; animation-delay: 4s; width: 50px; height: 58px; }
        .hex-accent--4 { top: 85%; left: 8%; animation-delay: 1s; width: 35px; height: 40px; }

        @keyframes hexFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.4; }
            50% { transform: translateY(-20px) rotate(10deg); opacity: 0.8; }
        }

        /* Scanning beam */
        .scan-beam {
            position: absolute;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(18, 63, 110, 0.06), rgba(5, 150, 105, 0.06), transparent);
            animation: scanBeam 8s linear infinite;
        }

        @keyframes scanBeam {
            0% { top: -2px; opacity: 0; }
            5% { opacity: 1; }
            95% { opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        /* Gradient mesh - subtle */
        .gradient-mesh {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 600px 400px at 15% 20%, rgba(18, 63, 110, 0.04) 0%, transparent 70%),
                radial-gradient(ellipse 500px 500px at 85% 80%, rgba(5, 150, 105, 0.03) 0%, transparent 70%),
                radial-gradient(ellipse 400px 300px at 50% 50%, rgba(99, 102, 241, 0.02) 0%, transparent 70%);
        }

        /* === GLASSMORPHISM CARD === */
        .auth-card {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 20px;
            box-shadow:
                0 4px 6px rgba(0, 0, 0, 0.02),
                0 12px 40px rgba(18, 63, 110, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
            position: relative;
            overflow: hidden;
        }

        .auth-card::before {
            display: none;
        }

        /* Turnstile widget */
        .cf-turnstile {
            min-height: 65px;
        }
        .cf-turnstile iframe {
            min-height: 65px;
        }
        .cf-turnstile .cf-turnstile-banner,
        .cf-turnstile [class*="banner"] {
            display: none !important;
        }

        /* Input styles */
        .auth-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid rgba(18, 63, 110, 0.12);
            background: rgba(255, 255, 255, 0.7);
            color: #1e293b;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
        }
        .auth-input::placeholder { color: #94a3b8; }
        .auth-input:focus {
            border-color: rgba(18, 63, 110, 0.35);
            box-shadow: 0 0 0 3px rgba(18, 63, 110, 0.06), 0 0 20px rgba(18, 63, 110, 0.04);
            background: rgba(255, 255, 255, 0.9);
        }

        .auth-btn {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 15px;
            color: white;
            cursor: pointer;
            background: linear-gradient(135deg, #123f6e 0%, #0d3158 50%, #059669 100%);
            background-size: 200% 200%;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        .auth-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #059669 0%, #123f6e 100%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .auth-btn:hover::before { opacity: 1; }
        .auth-btn span { position: relative; z-index: 1; }
        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(18, 63, 110, 0.2);
        }
        .auth-btn:active { transform: translateY(0); }

        .auth-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            margin-bottom: 6px;
        }

        .auth-link {
            color: #123f6e;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .auth-link:hover { color: #059669; }

        .auth-footer {
            color: #94a3b8;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Neural Network Background -->
    <div class="neural-bg">
        <div class="gradient-mesh"></div>
        <div class="circuit-grid"></div>
        <div class="scan-beam"></div>

        <!-- Data streams -->
        <div class="data-stream data-stream--1"></div>
        <div class="data-stream data-stream--2"></div>
        <div class="data-stream data-stream--3"></div>
        <div class="data-stream data-stream--4"></div>
        <div class="data-stream data-stream--5"></div>
        <div class="data-stream data-stream--6"></div>
        <div class="data-stream data-stream--7"></div>
        <div class="data-stream data-stream--8"></div>
        <div class="data-stream data-stream--9"></div>
        <div class="data-stream data-stream--10"></div>

        <!-- Neural nodes -->
        <div class="neural-node neural-node--1"></div>
        <div class="neural-node neural-node--2"></div>
        <div class="neural-node neural-node--3"></div>
        <div class="neural-node neural-node--4"></div>
        <div class="neural-node neural-node--5"></div>
        <div class="neural-node neural-node--6"></div>
        <div class="neural-node neural-node--7"></div>
        <div class="neural-node neural-node--8"></div>
        <div class="neural-node neural-node--9"></div>
        <div class="neural-node neural-node--10"></div>
        <div class="neural-node neural-node--11"></div>
        <div class="neural-node neural-node--12"></div>
        <div class="neural-node neural-node--13"></div>
        <div class="neural-node neural-node--14"></div>
        <div class="neural-node neural-node--15"></div>

        <!-- Neural connections -->
        <div class="neural-connection neural-connection--1"></div>
        <div class="neural-connection neural-connection--2"></div>
        <div class="neural-connection neural-connection--3"></div>
        <div class="neural-connection neural-connection--4"></div>
        <div class="neural-connection neural-connection--5"></div>
        <div class="neural-connection neural-connection--6"></div>
        <div class="neural-connection neural-connection--7"></div>
        <div class="neural-connection neural-connection--8"></div>

        <!-- Binary/hex text -->
        <div class="binary-float binary-float--1">01101001 10110010 01010101</div>
        <div class="binary-float binary-float--2">0x4F 0xA3 0x7B 0x1E</div>
        <div class="binary-float binary-float--3">11010011 00101101</div>
        <div class="binary-float binary-float--4">0x8C 0xF2 0x5D</div>
        <div class="binary-float binary-float--5">01001011 11010010 10010110</div>
        <div class="binary-float binary-float--6">0xE7 0x3A 0x91</div>
        <div class="binary-float binary-float--7">10100110 01101001</div>

        <!-- Hexagon accents -->
        <div class="hex-accent hex-accent--1"></div>
        <div class="hex-accent hex-accent--2"></div>
        <div class="hex-accent hex-accent--3"></div>
        <div class="hex-accent hex-accent--4"></div>
    </div>

    <!-- Content -->
    <main style="position:relative;z-index:10;display:grid;min-height:100vh;place-items:center;padding:24px">
        <div class="auth-card" style="width:100%;max-width:420px;padding:40px 36px">
            {{ $slot }}
        </div>
    </main>
</body>
</html>
