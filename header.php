<?php
/**
 * Bitcoin Echo — Reusable Header
 *
 * Expected variables:
 * - $page_title: Page title for <title> and OG tags (default: "Bitcoin Echo — Build once. Build right. Stop.")
 * - $page_description: Meta description (default: site description)
 * - $active_nav: Active navigation item ("docs", "writings", or null for homepage)
 */

// Set defaults if not provided
$page_title = $page_title ?? 'Bitcoin Echo — Build once. Build right. Stop.';
$page_description = $page_description ?? 'A faithful implementation of the Bitcoin protocol, built not for continued development—but for permanence.';
$active_nav = $active_nav ?? null;
?>
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
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="theme-color" content="#0a0a0a">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16" />

    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://bitcoinecho.org">
    <meta property="og:site_name" content="Bitcoin Echo">
    <meta property="og:locale" content="en_US">
    <meta property="og:image" content="https://bitcoinecho.org/bitcoin-echo-og-image.png?v=2">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="628">
    <meta property="og:image:alt" content="Bitcoin Echo symbol — a stylized B within a circuit-like design">
    <meta property="og:image:type" content="image/jpeg">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta name="twitter:image" content="https://bitcoinecho.org/bitcoin-echo-og-image.png?v=2">
    <meta name="twitter:image:alt" content="Bitcoin Echo symbol — a stylized B within a circuit-like design">
    <meta name="twitter:site" content="@bitcoinechoorg">

    <!-- Early theme detection to prevent flash -->
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
            --ease-in-out: cubic-bezier(0.65, 0, 0.35, 1);
        }

        /* Dark theme (default) */
        [data-theme="dark"] {
            --color-bg: #0a0a0a;
            --color-bg-elevated: #111111;
            --color-surface: #1a1a1a;
            --color-border: #2a2a2a;
            --color-text: #e8e8e8;
            --color-text-muted: #888888;
            --color-text-dim: #555555;
            --color-accent: #ffffff;
            --color-glow: rgba(255, 255, 255, 0.03);
            --color-symbol-invert: invert(1);
            --color-nav-gradient: linear-gradient(to bottom, #0a0a0a 0%, transparent 100%);
            --color-nav-solid: rgba(10, 10, 10, 0.95);
            --color-echo-ring: rgba(255, 255, 255, 0.18);
        }

        /* Light theme */
        [data-theme="light"] {
            --color-bg: #f8f6f3;
            --color-bg-elevated: #ffffff;
            --color-surface: #eeebe6;
            --color-border: #d4d0c8;
            --color-text: #1a1a1a;
            --color-text-muted: #5a5a5a;
            --color-text-dim: #8a8a8a;
            --color-accent: #0a0a0a;
            --color-glow: rgba(0, 0, 0, 0.03);
            --color-symbol-invert: invert(0);
            --color-nav-gradient: linear-gradient(to bottom, #f8f6f3 0%, transparent 100%);
            --color-nav-solid: rgba(248, 246, 243, 0.95);
            --color-echo-ring: rgba(0, 0, 0, 0.22);
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        * {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
            overflow-x: hidden;
        }

        body {
            font-family: var(--font-serif);
            background-color: var(--color-bg);
            color: var(--color-text);
            line-height: 1.7;
            overflow-x: hidden;
            max-width: 100vw;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color 0.3s ease, color 0.3s ease;
            user-select: text;
        }

        /* Background pattern */
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

        /* Typography */
        h1, h2, h3 {
            font-weight: 400;
            letter-spacing: -0.01em;
        }

        h1 {
            font-size: clamp(2.5rem, 8vw, 5rem);
            line-height: 1.1;
        }

        h2 {
            font-size: clamp(1.5rem, 4vw, 2.25rem);
            line-height: 1.3;
        }

        h3 {
            font-size: 1.125rem;
            font-family: var(--font-mono);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: var(--color-text-muted);
            font-weight: 400;
        }

        p {
            max-width: 65ch;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        /* Layout */
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        section {
            position: relative;
            z-index: 1;
            overflow-x: hidden;
            user-select: text;
        }

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            padding: 1.5rem 0;
            background: var(--color-nav-gradient);
            transition: background 0.3s ease;
        }

        nav.scrolled {
            background: var(--color-nav-solid);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .nav-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .nav-logo {
            font-family: var(--font-mono);
            font-size: 0.875rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .nav-logo:hover {
            opacity: 1;
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

        .nav-links > li {
            position: relative;
        }

        .nav-links a {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--color-text-muted);
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-links > li > a::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 1px;
            background: var(--color-accent);
            transition: width 0.3s var(--ease-out);
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--color-accent);
        }

        .nav-links > li > a:hover::after {
            width: 100%;
        }

        /* Dropdown menus */
        .nav-dropdown {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 1rem;
            background: var(--color-bg-elevated);
            border: 1px solid var(--color-border);
            border-radius: 4px;
            min-width: 220px;
            padding: 0.5rem 0;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s var(--ease-out), visibility 0.2s, margin-top 0.2s var(--ease-out);
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }

        .nav-links > li:hover .nav-dropdown {
            opacity: 1;
            visibility: visible;
            margin-top: 0.75rem;
        }

        .nav-dropdown a {
            display: block;
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: none;
            color: var(--color-text-muted);
            transition: all 0.2s ease;
            border-left: 2px solid transparent;
        }

        .nav-dropdown a:hover {
            color: var(--color-accent);
            background: var(--color-surface);
            border-left-color: var(--color-accent);
        }

        .nav-dropdown-label {
            display: block;
            padding: 0.5rem 1.5rem 0.25rem;
            font-family: var(--font-mono);
            font-size: 0.625rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--color-text-dim);
            font-weight: 400;
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
            max-height: 500px;
        }

        .mobile-menu-links {
            list-style: none;
            padding: 1rem 2rem;
        }

        .mobile-menu-links > li {
            border-bottom: 1px solid var(--color-border);
        }

        .mobile-menu-links > li:last-child {
            border-bottom: none;
        }

        .mobile-menu-links > li > a {
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

        .mobile-menu-links > li > a:hover {
            color: var(--color-accent);
        }

        /* Mobile submenu items */
        .mobile-submenu {
            list-style: none;
            padding: 0;
            margin: 0.5rem 0 0 0;
        }

        .mobile-submenu li {
            border-top: 1px solid var(--color-border);
        }

        .mobile-submenu li:first-child {
            border-top: none;
        }

        .mobile-submenu a {
            display: block;
            padding: 0.75rem 0 0.75rem 1.5rem;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: none;
            color: var(--color-text-muted);
            text-decoration: none;
            transition: color 0.3s ease;
            border-left: 2px solid transparent;
            margin-left: 1rem;
        }

        .mobile-submenu a:hover {
            color: var(--color-accent);
            border-left-color: var(--color-accent);
        }

        /* Theme Toggle */
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
            transform: scale(1.05);
        }

        .theme-btn svg {
            width: 18px;
            height: 18px;
            transition: transform 0.3s var(--ease-out);
        }

        .theme-btn:hover svg {
            transform: rotate(15deg);
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

        /* Responsive */
        @media (max-width: 900px) {
            .nav-links {
                display: none;
            }

            .hamburger-btn {
                display: flex;
                order: 2;
            }

            .theme-btn {
                order: 1;
            }

            .nav-right {
                gap: 0.75rem;
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 0 1.5rem;
            }
        }

        /* Selection */
        [data-theme="dark"] ::selection {
            background: rgba(255, 255, 255, 0.2);
            color: var(--color-accent);
        }

        [data-theme="light"] ::selection {
            background: rgba(0, 0, 0, 0.4);
            color: var(--color-accent);
        }
    </style>
</head>

<body>
    <div class="bg-pattern"></div>

    <nav id="nav">
        <div class="container nav-inner">
            <a href="/" class="nav-logo">Bitcoin Echo</a>
            <div class="nav-right">
                <ul class="nav-links">
                    <li>
                        <a href="/docs"<?php echo $active_nav === 'docs' ? ' class="active"' : ''; ?>>Docs</a>
                        <div class="nav-dropdown">
                            <a href="/docs/manifesto">Manifesto</a>
                            <a href="/docs/whitepaper">Whitepaper</a>
                            <a href="/docs/primer">Bitcoin Primer</a>
                            <a href="/docs/building">Building Guide</a>
                        </div>
                    </li>
                    <li>
                        <a href="/writings"<?php echo $active_nav === 'writings' ? ' class="active"' : ''; ?>>Writings</a>
                        <div class="nav-dropdown">
                            <!-- <a href="/writings/ibd-architecture">IBD Architecture</a> -->
                            <a href="/writings/policy-vs-consensus">Policy vs. Consensus</a>
                        </div>
                    </li>
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
            <li>
                <a href="/docs">Docs</a>
                <ul class="mobile-submenu">
                    <li><a href="/docs/manifesto">Manifesto</a></li>
                    <li><a href="/docs/whitepaper">Whitepaper</a></li>
                    <li><a href="/docs/primer">Bitcoin Primer</a></li>
                    <li><a href="/docs/building">Building Guide</a></li>
                </ul>
            </li>
            <li>
                <a href="/writings">Writings</a>
                <ul class="mobile-submenu">
                    <li><a href="/writings/ibd-architecture">IBD Architecture</a></li>
                    <li><a href="/writings/policy-vs-consensus">Policy vs. Consensus</a></li>
                </ul>
            </li>
        </ul>
    </div>
