<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franklin's Key — AI-Powered Circuit Building</title>
    <meta name="description" content="Describe what you want to build. We handle the wiring and the code. Arduino and ESP32 projects made effortless.">

    <!-- Open Graph -->
    <meta property="og:title" content="Franklin's Key">
    <meta property="og:description" content="Describe what you want to build. We handle the wiring and the code.">
    <meta property="og:url" content="https://franklinskey.ai">
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|playfair-display:700,800" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --midnight: #0a0e1a;
            --deep-blue: #0f1629;
            --electric: #7dd3fc;
            --electric-bright: #38bdf8;
            --lightning: #fbbf24;
            --lightning-bright: #fcd34d;
            --white: #f8fafc;
            --gray: #94a3b8;
            --dim: #475569;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--midnight);
            color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        @keyframes pulse-glow {
            0%, 100% { text-shadow: 0 0 20px rgba(251, 191, 36, 0.3); }
            50% { text-shadow: 0 0 40px rgba(251, 191, 36, 0.6), 0 0 80px rgba(251, 191, 36, 0.2); }
        }

        @keyframes subtle-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        .bg-glow {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 50% 40%, rgba(251, 191, 36, 0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 50% 90%, rgba(125, 211, 252, 0.03) 0%, transparent 50%);
            pointer-events: none;
        }

        .container {
            position: relative;
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }

        /* The key icon — 0--m.com visual */
        .key-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            animation: subtle-float 4s ease-in-out infinite;
            color: var(--lightning);
            animation: pulse-glow 3s infinite, subtle-float 4s ease-in-out infinite;
        }

        .site-name {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 7vw, 4rem);
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.1;
        }

        .site-name .highlight {
            color: var(--lightning);
        }

        .tagline {
            font-size: 1.3rem;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }

        .sub-tagline {
            font-size: 1rem;
            color: var(--dim);
            margin-bottom: 3rem;
        }

        .coming-soon {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--electric-bright);
            margin-bottom: 2rem;
        }

        /* Email signup */
        .signup-form {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            max-width: 440px;
            margin: 0 auto 1rem;
        }

        .signup-form input {
            flex: 1;
            padding: 0.9rem 1.25rem;
            background: rgba(15, 22, 41, 0.8);
            border: 1px solid var(--dim);
            border-radius: 8px;
            color: var(--white);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
        }

        .signup-form input::placeholder {
            color: var(--dim);
        }

        .signup-form input:focus {
            outline: none;
            border-color: var(--lightning);
        }

        .signup-form button {
            padding: 0.9rem 1.75rem;
            background: var(--lightning);
            color: var(--midnight);
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .signup-form button:hover {
            background: var(--lightning-bright);
            transform: translateY(-1px);
        }

        .signup-note {
            font-size: 0.8rem;
            color: var(--dim);
        }

        footer {
            position: fixed;
            bottom: 1.5rem;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.8rem;
            color: var(--dim);
        }

        footer a {
            color: var(--gray);
            text-decoration: none;
        }

        footer a:hover {
            color: var(--lightning);
        }

        @media (max-width: 500px) {
            .signup-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>

    <div class="container">
        <div class="key-icon">&#x1F5DD;</div>

        <h1 class="site-name">Franklin's <span class="highlight">Key</span></h1>

        <p class="tagline">Describe it. Connect it. It turns on.</p>
        <p class="sub-tagline">AI-powered circuit building for Arduino &amp; ESP32</p>

        <div class="coming-soon">Coming Soon</div>

        <form class="signup-form" method="POST" action="#" onsubmit="return handleSignup(event)">
            <input type="email" name="email" placeholder="you@example.com" required>
            <button type="submit">Notify Me</button>
        </form>
        <p class="signup-note">We'll let you know when we launch. No spam.</p>
    </div>

    <footer>
        <p>&copy; {{ date('Y') }} Franklin's Key &middot; <a href="mailto:hello@franklinskey.ai">hello@franklinskey.ai</a></p>
    </footer>

    <script>
        function handleSignup(e) {
            e.preventDefault();
            const form = e.target;
            const button = form.querySelector('button');

            button.textContent = 'You\'re in!';
            button.style.background = '#22c55e';
            form.querySelector('input').value = '';

            setTimeout(() => {
                button.textContent = 'Notify Me';
                button.style.background = '';
            }, 3000);

            return false;
        }
    </script>
</body>
</html>
