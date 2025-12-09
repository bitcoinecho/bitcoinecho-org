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
    <title><?= htmlspecialchars($title) ?> — Bitcoin Echo</title>
    <meta name="description" content="<?= htmlspecialchars($title) ?> — Bitcoin Echo: The Last Implementation">
    <meta name="theme-color" content="#0a0a0a">
    <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16" />

    <!-- Open Graph -->
    <meta property="og:title" content="Bitcoin Echo — The Last Implementation">
    <meta property="og:description"
        content="A complete Bitcoin protocol implementation in pure C, designed to freeze forever upon completion. Zero dependencies. Built for permanence, not continued development. bitcoinecho.org">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://bitcoinecho.org">
    <meta property="og:site_name" content="Bitcoin Echo">
    <meta property="og:locale" content="en_US">
    <meta property="og:image" content="https://bitcoinecho.org/bitcoin-echo-og-image.png">
    <meta property="og:image:width" content="960">
    <meta property="og:image:height" content="960">
    <meta property="og:image:alt" content="Bitcoin Echo symbol — a stylized B within a circuit-like design">
    <meta property="og:image:type" content="image/jpeg">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Bitcoin Echo — The Last Implementation">
    <meta name="twitter:description"
        content="A complete Bitcoin protocol implementation in pure C, designed to freeze forever upon completion. Zero dependencies. Built for permanence, not continued development.">
    <meta name="twitter:image" content="https://bitcoinecho.org/bitcoin-echo-og-image.png">
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
            --color-nav-solid: rgba(10, 10, 10, 0.95);
            --color-code-bg: #161616;
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
            --color-nav-solid: rgba(248, 246, 243, 0.95);
            --color-code-bg: #e8e5e0;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: var(--font-serif);
            background-color: var(--color-bg);
            color: var(--color-text);
            line-height: 1.8;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color 0.3s ease, color 0.3s ease;
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

        /* Navigation */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 100;
            padding: 1.5rem 0;
            background: var(--color-nav-solid);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--color-border);
        }

        .nav-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 900px;
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

        .nav-links {
            display: flex;
            gap: 2rem;
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

        /* Main Content */
        main {
            position: relative;
            z-index: 1;
            max-width: 750px;
            margin: 0 auto;
            padding: 8rem 2rem 4rem;
        }

        /* Document Header */
        .doc-header {
            text-align: center;
            margin-bottom: 4rem;
            padding-bottom: 3rem;
            border-bottom: 1px solid var(--color-border);
        }

        .doc-label {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--color-text-dim);
            margin-bottom: 1rem;
            display: block;
        }

        .doc-header h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 400;
            line-height: 1.2;
            margin-bottom: 1rem;
        }

        .doc-subtitle {
            font-style: italic;
            color: var(--color-text-muted);
            font-size: 1.125rem;
        }

        /* Article Content — Markdown Styling */
        .doc-content {
            font-size: 1.0625rem;
        }

        .doc-content h1,
        .doc-content h2,
        .doc-content h3,
        .doc-content h4,
        .doc-content h5,
        .doc-content h6 {
            position: relative;
            scroll-margin-top: 6rem;
        }

        .doc-content h1 {
            font-size: 2rem;
            font-weight: 400;
            margin: 3rem 0 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--color-border);
        }

        .doc-content h2 {
            font-size: 1.5rem;
            font-weight: 400;
            margin: 2.5rem 0 1rem;
            color: var(--color-text);
        }

        .doc-content h3 {
            font-size: 1.25rem;
            font-weight: 400;
            margin: 2rem 0 0.75rem;
            color: var(--color-text);
        }

        .doc-content h4 {
            font-size: 1rem;
            font-weight: 700;
            margin: 1.5rem 0 0.5rem;
        }

        /* Header link icon on hover */
        .doc-content h1::before,
        .doc-content h2::before,
        .doc-content h3::before,
        .doc-content h4::before,
        .doc-content h5::before,
        .doc-content h6::before {
            content: '🔗';
            position: absolute;
            left: -1.5rem;
            top: 0;
            opacity: 0;
            transition: opacity 0.2s ease;
            font-size: 0.875em;
            line-height: inherit;
            text-decoration: none;
            user-select: none;
        }

        .doc-content h1:hover::before,
        .doc-content h2:hover::before,
        .doc-content h3:hover::before,
        .doc-content h4:hover::before,
        .doc-content h5:hover::before,
        .doc-content h6:hover::before {
            opacity: 0.5;
        }

        .doc-content h1[id]:hover,
        .doc-content h2[id]:hover,
        .doc-content h3[id]:hover,
        .doc-content h4[id]:hover,
        .doc-content h5[id]:hover,
        .doc-content h6[id]:hover {
            padding-left: 0.5rem;
            margin-left: -0.5rem;
        }

        @media (max-width: 600px) {
            .doc-content h1::before,
            .doc-content h2::before,
            .doc-content h3::before,
            .doc-content h4::before,
            .doc-content h5::before,
            .doc-content h6::before {
                left: -1.25rem;
                font-size: 0.75em;
            }
        }

        .doc-content p {
            margin-bottom: 1.5rem;
            color: var(--color-text-muted);
        }

        .doc-content p:first-of-type {
            font-size: 1.125rem;
            color: var(--color-text);
        }

        .doc-content strong {
            color: var(--color-text);
            font-weight: 700;
        }

        .doc-content em {
            font-style: italic;
        }

        .doc-content a {
            color: var(--color-accent);
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .doc-content a:hover {
            text-decoration: none;
        }

        .doc-content ul,
        .doc-content ol {
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
            color: var(--color-text-muted);
        }

        .doc-content li {
            margin-bottom: 0.5rem;
        }

        .doc-content li > ul,
        .doc-content li > ol {
            margin-top: 0.5rem;
            margin-bottom: 0;
        }

        .doc-content blockquote {
            margin: 2rem 0;
            padding: 1.5rem 2rem;
            border-left: 3px solid var(--color-accent);
            background: linear-gradient(to right, var(--color-glow), transparent);
            font-style: italic;
            font-size: 1.125rem;
            color: var(--color-text);
        }

        .doc-content blockquote p {
            margin-bottom: 0;
            color: var(--color-text);
        }

        .doc-content hr {
            border: none;
            border-top: 1px solid var(--color-border);
            margin: 3rem 0;
        }

        .doc-content code {
            font-family: var(--font-mono);
            font-size: 0.875em;
            background: var(--color-code-bg);
            padding: 0.2em 0.4em;
            border-radius: 3px;
        }

        .doc-content pre {
            background: var(--color-code-bg);
            border: 1px solid var(--color-border);
            padding: 1.5rem;
            overflow-x: auto;
            margin: 1.5rem 0;
            border-radius: 4px;
        }

        .doc-content pre code {
            background: none;
            padding: 0;
            font-size: 0.8125rem;
            line-height: 1.6;
        }

        .doc-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 0.9375rem;
        }

        .doc-content th,
        .doc-content td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid var(--color-border);
        }

        .doc-content th {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--color-text-dim);
            font-weight: 400;
        }

        .doc-content td {
            color: var(--color-text-muted);
        }

        /* Footer */
        footer {
            max-width: 900px;
            margin: 0 auto;
            padding: 3rem 2rem;
            border-top: 1px solid var(--color-border);
            margin-top: 4rem;
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

        /* Responsive */
        @media (max-width: 600px) {
            main {
                padding: 7rem 1.5rem 3rem;
            }

            .nav-links {
                display: none;
            }

            .footer-inner {
                flex-direction: column;
                text-align: center;
            }
        }

        ::selection {
            background: rgba(255, 255, 255, 0.2);
            color: var(--color-accent);
        }
    </style>
</head>

<body>
    <div class="bg-pattern"></div>

    <nav>
        <div class="nav-inner">
            <a href="/" class="nav-logo">Bitcoin Echo</a>
            <ul class="nav-links">
                <li><a href="/docs/manifesto" <?= $title === 'Manifesto' ? 'class="active"' : '' ?>>Manifesto</a></li>
                <li><a href="/docs/whitepaper" <?= $title === 'Whitepaper' ? 'class="active"' : '' ?>>Whitepaper</a></li>
                <li><a href="/docs/primer" <?= $title === 'Bitcoin Primer' ? 'class="active"' : '' ?>>Primer</a></li>
                <li><a href="/docs/building" <?= $title === 'Building in the Future' ? 'class="active"' : '' ?>>Building</a></li>
            </ul>
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
    </nav>

    <main>
        <article class="doc-content">
            <?= $content ?>
        </article>
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
            </div>
        </div>
    </footer>

    <script>
        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // Evergreen year
        document.getElementById('year').textContent = new Date().getFullYear();

        // Header link functionality
        document.querySelectorAll('.doc-content h1[id], .doc-content h2[id], .doc-content h3[id], .doc-content h4[id], .doc-content h5[id], .doc-content h6[id]').forEach(header => {
            header.style.cursor = 'pointer';

            header.addEventListener('click', function(e) {
                // Don't trigger if clicking on a link inside the header
                if (e.target.tagName === 'A' || e.target.closest('a')) {
                    return;
                }

                const id = this.getAttribute('id');
                if (id) {
                    const url = window.location.origin + window.location.pathname + '#' + id;

                    // Copy to clipboard
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(() => {
                            // Visual feedback
                            const originalOpacity = this.style.opacity;
                            this.style.opacity = '0.7';
                            setTimeout(() => {
                                this.style.opacity = originalOpacity || '';
                            }, 200);
                        }).catch(() => {
                            // Fallback: navigate to the anchor
                            window.location.hash = id;
                        });
                    } else {
                        // Fallback for browsers without clipboard API
                        window.location.hash = id;
                    }
                }
            });
        });
    </script>
</body>

</html>
