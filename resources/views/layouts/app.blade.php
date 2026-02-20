<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "Franklin's Key")</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Figtree', -apple-system, sans-serif;
            background: #0a0a0f;
            color: #e0e0e8;
            min-height: 100vh;
        }

        /* Animated background */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(245, 158, 11, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(245, 158, 11, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridShift 20s linear infinite;
            pointer-events: none;
            z-index: 0;
        }

        @keyframes gridShift {
            0% { transform: translate(0, 0); }
            100% { transform: translate(60px, 60px); }
        }

        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }

        .bg-orb-1 {
            width: 500px;
            height: 500px;
            background: #f59e0b;
            opacity: 0.07;
            top: -10%;
            left: -5%;
            animation: orbFloat 15s ease-in-out infinite;
        }

        .bg-orb-2 {
            width: 400px;
            height: 400px;
            background: #d97706;
            opacity: 0.05;
            bottom: -10%;
            right: -5%;
            animation: orbFloat 18s ease-in-out infinite reverse;
        }

        .bg-orb-3 {
            width: 300px;
            height: 300px;
            background: #06b6d4;
            opacity: 0.05;
            top: 40%;
            left: 50%;
            transform: translateX(-50%);
            animation: orbPulse 10s ease-in-out infinite;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0); }
            33% { transform: translate(30px, -20px); }
            66% { transform: translate(-20px, 30px); }
        }

        @keyframes orbPulse {
            0%, 100% { transform: translateX(-50%) scale(1); opacity: 0.05; }
            50% { transform: translateX(-50%) scale(1.15); opacity: 0.08; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="bg-grid"></div>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="bg-orb bg-orb-3"></div>

    <!-- Nav -->
    <nav class="relative z-10 flex items-center justify-between px-4 sm:px-6 py-4 max-w-5xl mx-auto">
        <a href="/app" class="flex items-center gap-2 text-lg font-bold">
            <span class="text-2xl">&#x1F5DD;</span>
            <span class="bg-gradient-to-r from-amber-400 to-yellow-300 bg-clip-text text-transparent">Franklin's Key</span>
        </a>
        @yield('nav-right')
    </nav>

    <main class="relative z-10">
        @yield('content')
    </main>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
