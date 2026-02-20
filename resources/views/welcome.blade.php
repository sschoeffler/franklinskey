<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franklin's Key — AI-Powered Circuit Building</title>
    <meta name="description" content="Franklin's Key — AI-powered circuit building assistant for Arduino and ESP32 beginners.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Figtree', -apple-system, sans-serif;
            background: #0a0a0f;
            color: #e0e0e8;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
        }

        /* Animated grid */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(245, 158, 11, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(245, 158, 11, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
            animation: gridShift 20s linear infinite;
            pointer-events: none;
        }

        @keyframes gridShift {
            0% { transform: translate(0, 0); }
            100% { transform: translate(60px, 60px); }
        }

        /* Gradient orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
        }

        .orb-1 {
            width: 600px;
            height: 600px;
            background: #f59e0b;
            opacity: 0.08;
            top: -15%;
            left: -10%;
            animation: orbFloat 15s ease-in-out infinite;
        }

        .orb-2 {
            width: 500px;
            height: 500px;
            background: #d97706;
            opacity: 0.06;
            bottom: -15%;
            right: -10%;
            animation: orbFloat 18s ease-in-out infinite reverse;
        }

        .orb-3 {
            width: 400px;
            height: 400px;
            background: #06b6d4;
            opacity: 0.05;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: orbPulse 10s ease-in-out infinite;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0); }
            33% { transform: translate(30px, -25px); }
            66% { transform: translate(-20px, 35px); }
        }

        @keyframes orbPulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.05; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0.09; }
        }

        /* Content */
        .container {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }

        .painting {
            width: clamp(220px, 50vw, 320px);
            border-radius: 12px;
            border: 1px solid rgba(245, 158, 11, 0.15);
            box-shadow: 0 0 40px rgba(245, 158, 11, 0.1);
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .painting-credit {
            font-size: 0.65rem;
            color: #475569;
            margin-top: -1rem;
            margin-bottom: 1.25rem;
        }

        .key-icon {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            animation: keyFloat 4s ease-in-out infinite;
            display: inline-block;
        }

        @keyframes keyFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .site-name {
            font-size: clamp(2.5rem, 7vw, 4rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.25rem;
            background: linear-gradient(135deg, #f59e0b, #fbbf24, #fcd34d);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .tagline {
            font-size: 1.3rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 1.5rem;
        }

        .tagline em {
            color: #fbbf24;
            font-style: normal;
            font-weight: 700;
        }

        /* Coming Soon badge */
        .badge {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: #fbbf24;
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.15);
            border-radius: 100px;
            animation: badgePulse 3s ease-in-out infinite;
        }

        @keyframes badgePulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
            50% { box-shadow: 0 0 20px 0 rgba(245, 158, 11, 0.15); }
        }

        /* Circuit decoration */
        .circuit-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            width: 240px;
            margin: 2.5rem auto 0;
        }

        .circuit-node {
            width: 100%;
            aspect-ratio: 1;
            border-radius: 4px;
            background: rgba(245, 158, 11, 0.03);
            border: 1px solid rgba(245, 158, 11, 0.06);
            animation: nodeGlow var(--cycle, 4s) ease-in-out infinite;
            animation-delay: var(--delay, 0s);
        }

        @keyframes nodeGlow {
            0%, 100% { background: rgba(245, 158, 11, 0.03); border-color: rgba(245, 158, 11, 0.06); }
            50% { background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.25); }
        }

        /* Footer */
        footer {
            position: fixed;
            bottom: 1.5rem;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.8rem;
            color: #475569;
            z-index: 1;
        }

        footer a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.3s;
        }

        footer a:hover {
            color: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="container">
        <div class="key-icon">&#x1F5DD;</div>

        <h1 class="site-name">Franklin's Key</h1>

        <img src="/images/franklin-kite.jpg" alt="Benjamin Franklin Drawing Electricity from the Sky, by Benjamin West, c. 1816" class="painting">
        <p class="painting-credit">Benjamin West, c. 1816 &middot; Public domain</p>

        <p class="tagline"><em>Unlock Electricity</em></p>

        <div class="badge">Coming Soon</div>

        <!-- Circuit decoration grid -->
        <div class="circuit-grid">
            <script>
                // Generate 36 nodes with random timing
                document.currentScript.parentElement.innerHTML += Array.from({length: 36}, (_, i) => {
                    const cycle = (2 + Math.random() * 6).toFixed(1);
                    const delay = (Math.random() * 4).toFixed(1);
                    return `<div class="circuit-node" style="--cycle:${cycle}s;--delay:${delay}s"></div>`;
                }).join('');
            </script>
        </div>
    </div>

    <footer>
        <p>&copy; {{ date('Y') }} Franklin's Key &middot; <a href="mailto:hello@franklinskey.ai">hello@franklinskey.ai</a></p>
    </footer>
</body>
</html>
