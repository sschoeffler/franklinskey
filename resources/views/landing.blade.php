<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Franklin's Key — AI-Powered Circuit Building for Everyone</title>
    <meta name="description" content="Stop staring at that drawer full of Arduino parts. Franklin's Key turns your words into working circuits. Describe what you want to build — we handle the rest.">

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
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(251, 191, 36, 0.3); }
            50% { box-shadow: 0 0 40px rgba(251, 191, 36, 0.6), 0 0 80px rgba(251, 191, 36, 0.2); }
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 50;
            background: linear-gradient(to bottom, var(--midnight), transparent);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--lightning);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-cta {
            padding: 0.6rem 1.5rem;
            background: transparent;
            border: 1px solid var(--lightning);
            color: var(--lightning);
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .nav-cta:hover {
            background: var(--lightning);
            color: var(--midnight);
        }

        /* Hero */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 50% 30%, rgba(251, 191, 36, 0.08) 0%, transparent 60%),
                radial-gradient(ellipse at 50% 80%, rgba(125, 211, 252, 0.05) 0%, transparent 50%);
        }

        .hero-content {
            position: relative;
            max-width: 800px;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 8vw, 5.5rem);
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .hero h1 .highlight {
            color: var(--lightning);
        }

        .hero-sub {
            font-size: 1.35rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto 2.5rem;
            line-height: 1.7;
        }

        .hero-cta-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            padding: 1rem 2.5rem;
            background: var(--lightning);
            color: var(--midnight);
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            animation: pulse-glow 3s infinite;
        }

        .btn-primary:hover {
            background: var(--lightning-bright);
            transform: translateY(-2px);
        }

        .btn-secondary {
            padding: 1rem 2.5rem;
            background: transparent;
            color: var(--white);
            border: 1px solid var(--dim);
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .btn-secondary:hover {
            border-color: var(--electric);
            color: var(--electric);
        }

        /* Sections */
        .section {
            padding: 6rem 2rem;
            max-width: 1100px;
            margin: 0 auto;
        }

        .section-label {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            color: var(--electric-bright);
            margin-bottom: 1rem;
        }

        .section h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .section p.lead {
            font-size: 1.2rem;
            color: var(--gray);
            max-width: 700px;
            line-height: 1.8;
        }

        /* Problem section */
        .problem {
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }

        .problem-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-top: 3rem;
        }

        .problem-card {
            padding: 2rem;
            border-radius: 12px;
            background: rgba(15, 22, 41, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.08);
        }

        .problem-card .icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .problem-card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .problem-card p {
            color: var(--gray);
            line-height: 1.7;
        }

        /* How it works */
        .how-it-works {
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 3rem;
        }

        .step {
            text-align: center;
            padding: 2.5rem 1.5rem;
            border-radius: 12px;
            background: rgba(15, 22, 41, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.08);
            transition: all 0.3s;
        }

        .step:hover {
            border-color: rgba(251, 191, 36, 0.3);
            transform: translateY(-4px);
        }

        .step-number {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(251, 191, 36, 0.15);
            color: var(--lightning);
            font-weight: 800;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .step h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .step p {
            color: var(--gray);
            line-height: 1.7;
        }

        /* Spell demo */
        .spell-section {
            border-top: 1px solid rgba(148, 163, 184, 0.1);
            text-align: center;
        }

        .spell-demo {
            max-width: 700px;
            margin: 3rem auto 0;
            background: rgba(15, 22, 41, 0.8);
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 16px;
            overflow: hidden;
        }

        .spell-bar {
            padding: 0.75rem 1.5rem;
            background: rgba(15, 22, 41, 0.9);
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .spell-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--dim);
        }

        .spell-dot.active { background: var(--lightning); }

        .spell-body {
            padding: 2.5rem;
        }

        .spell-input {
            font-family: 'Inter', monospace;
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 2rem;
            text-align: left;
        }

        .spell-input .user-text {
            color: var(--white);
            font-weight: 500;
        }

        .spell-input .cursor {
            display: inline-block;
            width: 2px;
            height: 1.2em;
            background: var(--lightning);
            vertical-align: text-bottom;
            animation: blink 1s infinite;
        }

        .spell-response {
            text-align: left;
            padding: 1.5rem;
            background: rgba(251, 191, 36, 0.05);
            border: 1px solid rgba(251, 191, 36, 0.15);
            border-radius: 8px;
        }

        .spell-response p {
            color: var(--electric);
            font-size: 0.95rem;
            line-height: 1.8;
        }

        .spell-response .label {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--lightning);
            margin-bottom: 0.5rem;
        }

        /* Features */
        .features {
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature {
            padding: 2rem;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.08);
            transition: all 0.3s;
        }

        .feature:hover {
            border-color: rgba(125, 211, 252, 0.3);
        }

        .feature-icon {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .feature h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .feature p {
            color: var(--gray);
            font-size: 0.95rem;
            line-height: 1.7;
        }

        /* CTA section */
        .cta-section {
            text-align: center;
            padding: 8rem 2rem;
            position: relative;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at center, rgba(251, 191, 36, 0.06) 0%, transparent 70%);
        }

        .cta-content {
            position: relative;
        }

        .cta-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .cta-section p {
            font-size: 1.2rem;
            color: var(--gray);
            margin-bottom: 2.5rem;
        }

        .signup-form {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            max-width: 500px;
            margin: 0 auto;
        }

        .signup-form input {
            flex: 1;
            padding: 1rem 1.25rem;
            background: rgba(15, 22, 41, 0.8);
            border: 1px solid var(--dim);
            border-radius: 8px;
            color: var(--white);
            font-size: 1rem;
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
            padding: 1rem 2rem;
            background: var(--lightning);
            color: var(--midnight);
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .signup-form button:hover {
            background: var(--lightning-bright);
        }

        .signup-note {
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--dim);
        }

        /* Footer */
        footer {
            padding: 3rem 2rem;
            text-align: center;
            border-top: 1px solid rgba(148, 163, 184, 0.08);
            color: var(--dim);
            font-size: 0.9rem;
        }

        footer a {
            color: var(--gray);
            text-decoration: none;
        }

        footer a:hover {
            color: var(--lightning);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .problem-grid,
            .feature-grid {
                grid-template-columns: 1fr;
            }

            .steps {
                grid-template-columns: 1fr;
            }

            .signup-form {
                flex-direction: column;
            }

            nav {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav>
        <a href="/" class="logo">
            &#x1F5DD; Franklin's Key
        </a>
        <a href="#signup" class="nav-cta">Get Early Access</a>
    </nav>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-content">
            <h1>Speak. Connect.<br><span class="highlight">It turns on.</span></h1>
            <p class="hero-sub">
                Stop staring at that drawer full of Arduino parts.
                Describe what you want to build in plain English &mdash;
                Franklin's Key handles the rest.
            </p>
            <div class="hero-cta-group">
                <a href="#signup" class="btn-primary">Get Early Access</a>
                <a href="#how" class="btn-secondary">See How It Works</a>
            </div>
        </div>
    </section>

    <!-- Problem -->
    <section class="section problem">
        <div class="section-label">The Drawer Problem</div>
        <h2>You bought the kit.<br>It's still in the drawer.</h2>
        <p class="lead">
            You were excited. You ordered the Arduino starter kit, watched half a YouTube tutorial,
            hit a wall of C++ and wiring diagrams, and put everything back in the drawer. Sound familiar?
        </p>

        <div class="problem-grid">
            <div class="problem-card">
                <div class="icon">&#x1F4DA;</div>
                <h3>Tutorials assume you already know</h3>
                <p>Every guide starts simple then suddenly expects you to understand voltage dividers, pull-up resistors, and serial communication.</p>
            </div>
            <div class="problem-card">
                <div class="icon">&#x1F635;</div>
                <h3>Code that makes no sense</h3>
                <p>You don't want to learn C++. You want to make an LED blink when it's dark outside. Why does that require 47 lines of code?</p>
            </div>
            <div class="problem-card">
                <div class="icon">&#x1F50C;</div>
                <h3>Which wire goes where?</h3>
                <p>You have 200 jumper wires, a breadboard, and zero confidence. One wrong connection and you're worried you'll fry something.</p>
            </div>
            <div class="problem-card">
                <div class="icon">&#x1F5D1;</div>
                <h3>The drawer of broken dreams</h3>
                <p>Millions of Arduino and ESP32 kits sit unused in drawers worldwide. The ambition was real. The barrier was too high.</p>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="section how-it-works" id="how">
        <div class="section-label">How It Works</div>
        <h2>Describe it. Connect it. It turns on.</h2>
        <p class="lead">
            You describe what you want. Franklin's Key tells you exactly what to connect and writes
            all the code invisibly. Snap, click, it turns on.
        </p>

        <div class="steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Describe your project</h3>
                <p>"I want a light that turns on when someone walks in the room" &mdash; that's all you need to say.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Follow the connections</h3>
                <p>Clear, visual instructions show you exactly which wire goes where. No diagrams to decode. No guessing.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Upload and go</h3>
                <p>The code is written for you, uploaded to your board, and it just works. No IDE. No compiling. No debugging.</p>
            </div>
        </div>
    </section>

    <!-- Spell demo -->
    <section class="section spell-section">
        <div class="section-label">See the Magic</div>
        <h2>You speak. It happens.</h2>

        <div class="spell-demo">
            <div class="spell-bar">
                <div class="spell-dot active"></div>
                <div class="spell-dot"></div>
                <div class="spell-dot"></div>
            </div>
            <div class="spell-body">
                <div class="spell-input">
                    <span class="user-text">"I want my plant to text me when it needs water"</span><span class="cursor"></span>
                </div>
                <div class="spell-response">
                    <div class="label">Franklin's Key</div>
                    <p>
                        Got it! You'll need your ESP32, the soil moisture sensor (the one with two prongs), and 3 jumper wires.
                        <br><br>
                        <strong>Step 1:</strong> Connect the sensor's VCC pin to the 3.3V pin on your ESP32 using a red wire.
                        <br>
                        <strong>Step 2:</strong> Connect GND to GND with a black wire.
                        <br>
                        <strong>Step 3:</strong> Connect the sensor's analog output to pin 34.
                        <br><br>
                        Ready? Plug in your ESP32 and I'll upload the code. Your plant will text you when the soil gets dry.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="section features">
        <div class="section-label">Features</div>
        <h2>Everything you need. Nothing you don't.</h2>

        <div class="feature-grid">
            <div class="feature">
                <div class="feature-icon">&#x1F4F7;</div>
                <h3>Camera identification</h3>
                <p>Point your camera at a part and Franklin's Key tells you what it is, what it does, and where to connect it.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">&#x1F6AB;</div>
                <h3>No code visible</h3>
                <p>The code exists, but you never see it. Describe what you want, and it's written, compiled, and uploaded automatically.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">&#x1F9E9;</div>
                <h3>Works with what you have</h3>
                <p>Arduino Uno, ESP32, Raspberry Pi Pico &mdash; tell us what's in your kit and we'll work with it.</p>
            </div>
            <div class="feature">
                <div class="feature-icon">&#x26A1;</div>
                <h3>Safe by default</h3>
                <p>Franklin's Key checks your wiring before uploading. If something could damage your board, we'll tell you before you connect it.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section" id="signup">
        <div class="cta-content">
            <h2>Open the drawer.</h2>
            <p>Franklin's Key is coming soon. Be the first to try it.</p>

            <form class="signup-form" method="POST" action="#" onsubmit="return handleSignup(event)">
                <input type="email" name="email" placeholder="you@example.com" required>
                <button type="submit">Join Waitlist</button>
            </form>
            <p class="signup-note">No spam. Just a note when we launch.</p>
        </div>
    </section>

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
                button.textContent = 'Join Waitlist';
                button.style.background = '';
            }, 3000);

            return false;
        }
    </script>
</body>
</html>
