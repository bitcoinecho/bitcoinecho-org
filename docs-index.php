<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-CHQTPHCD7L"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-CHQTPHCD7L');
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation — Bitcoin Echo</title>
    <meta name="description" content="Foundational documents for Bitcoin Echo: manifesto, whitepaper, and guides.">
    <meta name="theme-color" content="#0a0a0a">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16" />

    <!-- Open Graph -->
    <meta property="og:title" content="Documentation — Bitcoin Echo">
    <meta property="og:description" content="Foundational documents for Bitcoin Echo: manifesto, whitepaper, and guides.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://bitcoinecho.org/docs">
    <meta property="og:image" content="https://bitcoinecho.org/bitcoin-echo-og-image.png?v=2">

    <!-- Early theme detection -->
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            } else {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
            }
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <style>
        :root {
            --font-serif: 'Libre Baskerville', Georgia, serif;
            --font-mono: 'Courier Prime', 'Courier New', monospace;
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
        }

        [data-theme="dark"] {
            --color-bg: #0a0a0a;
            --color-bg-elevated: #111111;
            --color-surface: #1a1a1a;
            --color-border: #2a2a2a;
            --color-text: #e8e8e8;
            --color-text-muted: #888888;
            --color-text-dim: #555555;
            --color-accent: #ffffff;
            --color-symbol-invert: invert(1);
            --color-nav-solid: rgba(10, 10, 10, 0.95);
        }

        [data-theme="light"] {
            --color-bg: #f8f6f3;
            --color-bg-elevated: #ffffff;
            --color-surface: #eeebe6;
            --color-border: #d4d0c8;
            --color-text: #1a1a1a;
            --color-text-muted: #5a5a5a;
            --color-text-dim: #8a8a8a;
            --color-accent: #0a0a0a;
            --color-symbol-invert: invert(0);
            --color-nav-solid: rgba(248, 246, 243, 0.95);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        * {
            -webkit-user-select: text;
            -moz-user-select: text;
            user-select: text;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: var(--font-serif);
            background-color: var(--color-bg);
            color: var(--color-text);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            opacity: 0.4;
            background-image:
                radial-gradient(ellipse at 50% 0%, rgba(255, 255, 255, 0.02) 0%, transparent 50%),
                repeating-linear-gradient(0deg, transparent, transparent 100px, rgba(255, 255, 255, 0.008) 100px, rgba(255, 255, 255, 0.008) 101px),
                repeating-linear-gradient(90deg, transparent, transparent 100px, rgba(255, 255, 255, 0.008) 100px, rgba(255, 255, 255, 0.008) 101px);
        }

        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            padding: 1.5rem 0;
            background: var(--color-nav-solid);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--color-border);
        }

        .nav-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .nav-logo {
            font-family: var(--font-mono);
            font-size: 0.875rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--color-text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-logo:hover {
            color: var(--color-text);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 2.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2.5rem;
            list-style: none;
        }

        .nav-links a {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--color-text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--color-accent);
        }

        /* Hamburger Menu Button */
        .hamburger-btn {
            background: none;
            border: 1px solid var(--color-border);
            cursor: pointer;
            padding: 0.5rem;
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            transition: all 0.3s var(--ease-out);
            color: var(--color-text-muted);
        }

        .hamburger-btn:hover {
            border-color: var(--color-text-dim);
            color: var(--color-text);
        }

        .hamburger-btn svg {
            width: 18px;
            height: 18px;
        }

        .hamburger-btn .icon-hamburger,
        .hamburger-btn .icon-close {
            display: none;
        }

        .hamburger-btn .icon-hamburger {
            display: block;
        }

        .hamburger-btn.active .icon-hamburger {
            display: none;
        }

        .hamburger-btn.active .icon-close {
            display: block;
        }

        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 85px;
            left: 0;
            right: 0;
            background: var(--color-bg-elevated);
            border-bottom: 1px solid var(--color-border);
            z-index: 99;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s var(--ease-out);
        }

        .mobile-menu.active {
            max-height: 300px;
        }

        .mobile-menu-links {
            list-style: none;
            padding: 1rem 2rem;
        }

        .mobile-menu-links li {
            border-bottom: 1px solid var(--color-border);
        }

        .mobile-menu-links li:last-child {
            border-bottom: none;
        }

        .mobile-menu-links a {
            display: block;
            padding: 1rem 0;
            font-family: var(--font-mono);
            font-size: 0.875rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--color-text);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .mobile-menu-links a:hover {
            color: var(--color-accent);
        }

        .theme-btn {
            background: none;
            border: 1px solid var(--color-border);
            cursor: pointer;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            transition: all 0.3s var(--ease-out);
            color: var(--color-text-muted);
        }

        .theme-btn:hover {
            border-color: var(--color-text-dim);
            color: var(--color-text);
        }

        .theme-btn svg {
            width: 18px;
            height: 18px;
        }

        .theme-btn .icon-sun,
        .theme-btn .icon-moon {
            display: none;
        }

        [data-theme="dark"] .theme-btn .icon-sun {
            display: block;
        }

        [data-theme="light"] .theme-btn .icon-moon {
            display: block;
        }

        main {
            position: relative;
            z-index: 1;
            max-width: 1000px;
            margin: 0 auto;
            padding: 8rem 2rem 4rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 4rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--color-border);
        }

        .page-label {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--color-text-dim);
            margin-bottom: 1rem;
            display: block;
        }

        .page-header h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 400;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        .page-description {
            font-style: italic;
            color: var(--color-text-muted);
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .doc-grid {
            display: grid;
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .doc-card {
            border: 1px solid var(--color-border);
            background: var(--color-surface);
            padding: 2rem;
            transition: all 0.3s var(--ease-out);
            text-decoration: none;
            display: block;
        }

        .doc-card:hover {
            border-color: var(--color-accent);
            background: var(--color-bg-elevated);
            transform: translateY(-2px);
        }

        .doc-card-label {
            font-family: var(--font-mono);
            font-size: 0.625rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--color-text-dim);
            margin-bottom: 0.75rem;
            display: block;
        }

        .doc-card-title {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 0.75rem;
            color: var(--color-text);
        }

        .doc-card-description {
            color: var(--color-text-muted);
            font-size: 0.9375rem;
            line-height: 1.6;
        }

        footer {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
            border-top: 1px solid var(--color-border);
        }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .footer-logo {
            width: 24px;
            height: 24px;
            opacity: 0.5;
        }

        .footer-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: var(--color-symbol-invert);
        }

        .footer-text {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--color-text-dim);
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-link {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            color: var(--color-text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-link:hover {
            color: var(--color-accent);
        }

        @media (max-width: 600px) {
            main {
                padding: 7rem 1.5rem 3rem;
            }

            .nav-links {
                display: none;
            }

            .hamburger-btn {
                display: flex;
            }

            .footer-inner {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="bg-pattern"></div>

    <nav>
        <div class="nav-inner">
            <a href="/" class="nav-logo">Bitcoin Echo</a>
            <div class="nav-right">
                <ul class="nav-links">
                    <li><a href="/docs" class="active">Docs</a></li>
                    <li><a href="/writings">Writings</a></li>
                </ul>
                <button class="hamburger-btn" id="hamburger-toggle" aria-label="Toggle menu">
                    <svg class="icon-hamburger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                    <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
                <button class="theme-btn" id="theme-toggle" aria-label="Toggle theme">
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5" />
                        <line x1="12" y1="1" x2="12" y2="3" />
                        <line x1="12" y1="21" x2="12" y2="23" />
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                        <line x1="1" y1="12" x2="3" y2="12" />
                        <line x1="21" y1="12" x2="23" y2="12" />
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                    </svg>
                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobile-menu">
        <ul class="mobile-menu-links">
            <li><a href="/docs">Docs</a></li>
            <li><a href="/writings">Writings</a></li>
        </ul>
    </div>

    <main>
        <div class="page-header">
            <span class="page-label">Documentation</span>
            <h1>Foundational Documents</h1>
            <p class="page-description">
                The core texts defining Bitcoin Echo's philosophy, technical specification, and purpose.
            </p>
        </div>

        <div class="doc-grid">
            <a href="/docs/manifesto" class="doc-card">
                <span class="doc-card-label">Philosophy</span>
                <h2 class="doc-card-title">Manifesto</h2>
                <p class="doc-card-description">
                    Why Bitcoin Echo freezes. A manifesto for permanent software, built to outlast its creators.
                </p>
            </a>

            <a href="/docs/whitepaper" class="doc-card">
                <span class="doc-card-label">Technical Specification</span>
                <h2 class="doc-card-title">Whitepaper</h2>
                <p class="doc-card-description">
                    Complete technical specification of Bitcoin Echo's architecture, consensus rules, and design principles.
                </p>
            </a>

            <a href="/docs/primer" class="doc-card">
                <span class="doc-card-label">Educational</span>
                <h2 class="doc-card-title">Bitcoin Primer</h2>
                <p class="doc-card-description">
                    A comprehensive introduction to Bitcoin's protocol, history, and fundamental concepts.
                </p>
            </a>

            <a href="/docs/building" class="doc-card">
                <span class="doc-card-label">Guide</span>
                <h2 class="doc-card-title">Building in the Future</h2>
                <p class="doc-card-description">
                    Principles for building software that lasts decades, not quarters. Lessons for permanent systems.
                </p>
            </a>
        </div>
    </main>

    <footer>
        <div class="footer-inner">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="/bitcoin-echo-symbol.jpg" alt="Bitcoin Echo">
                </div>
                <span class="footer-text">&copy; <span id="year"></span> Bitcoin Echo. The last implementation.</span>
            </div>
            <div class="footer-links">
                <a href="mailto:echo@bitcoinecho.org" class="footer-link">echo@bitcoinecho.org</a>
                <a href="https://github.com/bitcoinecho" target="_blank" rel="noopener noreferrer" class="footer-link">GitHub</a>
                <a href="https://x.com/bitcoinechoorg" target="_blank" rel="noopener noreferrer" class="footer-link">𝕏</a>
                <a href="/funding" class="footer-link" style="margin-left: 1rem;">Funding</a>
            </div>
        </div>
    </footer>

    <script>
        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        const hamburgerToggle = document.getElementById('hamburger-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        const html = document.documentElement;

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // Mobile menu toggle
        hamburgerToggle.addEventListener('click', () => {
            hamburgerToggle.classList.toggle('active');
            mobileMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking a link
        const mobileMenuLinks = mobileMenu.querySelectorAll('a');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', () => {
                hamburgerToggle.classList.remove('active');
                mobileMenu.classList.remove('active');
            });
        });

        // Evergreen year
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>

</html>
