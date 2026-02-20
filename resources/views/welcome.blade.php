<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franklin's Key — AI-Powered Circuit Building</title>
    <meta name="description" content="Franklin's Key. Coming soon.">

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

        .coming-soon {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--electric-bright);
            margin-bottom: 2rem;
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

        <div class="coming-soon">Coming Soon</div>
    </div>

    <footer>
        <p>&copy; {{ date('Y') }} Franklin's Key &middot; <a href="mailto:hello@franklinskey.ai">hello@franklinskey.ai</a></p>
    </footer>


</body>
</html>
