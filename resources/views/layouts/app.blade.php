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
            width: 500px; height: 500px;
            background: #f59e0b; opacity: 0.07;
            top: -10%; left: -5%;
            animation: orbFloat 15s ease-in-out infinite;
        }

        .bg-orb-2 {
            width: 400px; height: 400px;
            background: #d97706; opacity: 0.05;
            bottom: -10%; right: -5%;
            animation: orbFloat 18s ease-in-out infinite reverse;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0); }
            33% { transform: translate(30px, -20px); }
            66% { transform: translate(-20px, 30px); }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="bg-grid"></div>
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>

    <!-- Nav -->
    <nav class="relative z-10 flex items-center justify-between px-4 sm:px-6 py-4 max-w-5xl mx-auto">
        <a href="{{ auth()->check() ? '/dashboard' : '/' }}" class="flex items-center gap-2 text-lg font-bold">
            <span class="text-2xl">&#x1F5DD;</span>
            <span class="bg-gradient-to-r from-amber-400 to-yellow-300 bg-clip-text text-transparent">Franklin's Key</span>
        </a>

        <div class="flex items-center gap-4">
            @yield('nav-right')

            @auth
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-2 text-sm text-gray-400 hover:text-amber-400 transition">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" alt="" class="w-7 h-7 rounded-full border border-white/10">
                    @else
                        <div class="w-7 h-7 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400 text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 rounded-lg border border-white/10 bg-[#111827] shadow-xl py-1 z-50">
                    <a href="/dashboard" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-amber-400 transition">Dashboard</a>
                    <a href="/app" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-amber-400 transition">Circuit Assistant</a>
                    <div class="border-t border-white/5 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-400 hover:bg-white/5 hover:text-red-400 transition">Sign Out</button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('auth.google') }}" class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-900 bg-white rounded-lg hover:bg-gray-100 transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Sign in with Google
            </a>
            @endauth
        </div>
    </nav>

    <main class="relative z-10">
        @yield('content')
    </main>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('scripts')
</body>
</html>
