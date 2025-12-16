<?php
/**
 * Bitcoin Echo — Homepage
 */

// Set page metadata for header
$page_title = 'Bitcoin Echo — Build once. Build right. Stop.';
$page_description = 'A faithful implementation of the Bitcoin protocol, built not for continued development—but for permanence.';
$active_nav = null; // No active nav on homepage
$show_donation = true; // Show donation section on homepage

// Include header
include __DIR__ . '/header.php';
?>

<style>
    /* Homepage-specific styles */

    /* Hero Section */
    .hero {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 8rem 2rem 4rem;
        position: relative;
        overflow: hidden;
    }

    .hero-symbol {
        width: min(300px, 50vw);
        height: min(300px, 50vw);
        margin-bottom: 3rem;
        position: relative;
        animation: fadeInUp 1.2s var(--ease-out) both;
        overflow: visible;
    }

    .hero-symbol img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: var(--color-symbol-invert);
        transition: filter 0.3s ease;
    }

    /* Echo rings animation */
    .echo-rings {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    .echo-ring {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 2px solid var(--color-echo-ring);
        border-radius: 50%;
        animation: echoExpand 4s var(--ease-out) infinite;
    }

    .echo-ring:nth-child(1) { animation-delay: 0s; }
    .echo-ring:nth-child(2) { animation-delay: 1s; }
    .echo-ring:nth-child(3) { animation-delay: 2s; }
    .echo-ring:nth-child(4) { animation-delay: 3s; }

    @keyframes echoExpand {
        0% {
            width: 100%;
            height: 100%;
            opacity: 0.7;
        }
        100% {
            width: 250%;
            height: 250%;
            opacity: 0;
        }
    }

    .hero-title {
        animation: fadeInUp 1.2s var(--ease-out) 0.2s both;
    }

    .hero-title span {
        display: block;
        font-style: italic;
        font-size: clamp(1rem, 3vw, 1.5rem);
        color: var(--color-text-muted);
        margin-top: 0.5rem;
        font-weight: 400;
    }

    .hero-description {
        max-width: 600px;
        margin: 2rem auto;
        color: var(--color-text-muted);
        font-size: 1.125rem;
        animation: fadeInUp 1.2s var(--ease-out) 0.4s both;
    }

    .scroll-indicator {
        position: absolute;
        bottom: 3rem;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        animation: fadeInUp 1.2s var(--ease-out) 0.8s both;
    }

    .scroll-indicator span {
        font-family: var(--font-mono);
        font-size: 0.625rem;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--color-text-dim);
    }

    .scroll-line {
        width: 1px;
        height: 40px;
        background: linear-gradient(to bottom, var(--color-text-dim), transparent);
        animation: scrollPulse 2s ease-in-out infinite;
    }

    @keyframes scrollPulse {
        0%, 100% {
            opacity: 0.3;
            transform: scaleY(1);
        }
        50% {
            opacity: 0.8;
            transform: scaleY(1.2);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Philosophy Section */
    .philosophy {
        padding: 8rem 0;
        background: linear-gradient(to bottom, var(--color-bg) 0%, var(--color-bg-elevated) 50%, var(--color-bg) 100%);
    }

    .philosophy-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 2rem;
    }

    .philosophy-header {
        grid-column: 1 / -1;
        margin-bottom: 4rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .philosophy-header h3 {
        margin-bottom: 1rem;
    }

    .divider {
        width: 60px;
        height: 1px;
        background: var(--color-border);
        margin: 2rem 0;
    }

    .philosophy-content {
        grid-column: 2 / 12;
        display: grid;
        gap: 4rem;
    }

    .philosophy-block {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: start;
    }

    .philosophy-block.reverse {
        direction: rtl;
    }

    .philosophy-block.reverse > * {
        direction: ltr;
    }

    .quote-large {
        font-size: clamp(1.25rem, 3vw, 1.75rem);
        font-style: italic;
        line-height: 1.6;
        position: relative;
        padding-left: 2rem;
        border-left: 2px solid var(--color-border);
    }

    .quote-large cite {
        display: block;
        margin-top: 1.5rem;
        font-size: 0.875rem;
        font-style: normal;
        font-family: var(--font-mono);
        color: var(--color-text-muted);
    }

    .philosophy-text {
        color: var(--color-text-muted);
    }

    .philosophy-text p + p {
        margin-top: 1.5rem;
    }

    /* Principles Section */
    .principles {
        padding: 8rem 0;
        position: relative;
    }

    .principles::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 1px;
        height: 100%;
        background: linear-gradient(to bottom, transparent 0%, var(--color-border) 10%, var(--color-border) 90%, transparent 100%);
    }

    .principles-header {
        text-align: center;
        margin-bottom: 6rem;
    }

    .principles-header h3 {
        margin-bottom: 1rem;
    }

    .principles-list {
        display: grid;
        gap: 4rem;
        max-width: 900px;
        margin: 0 auto;
    }

    .principle {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 2rem;
        align-items: start;
        padding: 2rem;
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        position: relative;
        transition: all 0.4s var(--ease-out);
    }

    .principle:hover {
        border-color: var(--color-text-dim);
        transform: translateY(-2px);
        box-shadow: 0 20px 40px -20px rgba(0, 0, 0, 0.5);
    }

    .principle-number {
        font-family: var(--font-mono);
        font-size: 3rem;
        line-height: 1;
        color: var(--color-text-dim);
        opacity: 0.5;
    }

    .principle-content h4 {
        font-size: 1.25rem;
        margin-bottom: 0.75rem;
        font-weight: 400;
    }

    .principle-content p {
        color: var(--color-text-muted);
        font-size: 0.9375rem;
    }

    /* Architecture Section */
    .architecture {
        padding: 8rem 0;
        background: var(--color-bg-elevated);
    }

    .architecture-header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .architecture-header h3 {
        margin-bottom: 1rem;
    }

    .architecture-diagram {
        max-width: 700px;
        margin: 0 auto 4rem;
        font-family: var(--font-mono);
        font-size: 0.8125rem;
        background: var(--color-bg);
        border: 1px solid var(--color-border);
        padding: 2rem;
        overflow-x: auto;
    }

    .layer {
        border: 1px solid var(--color-border);
        padding: 1rem 1.5rem;
        text-align: center;
        margin-bottom: -1px;
        background: var(--color-surface);
        transition: all 0.3s ease;
        position: relative;
    }

    .layer:hover {
        background: var(--color-bg-elevated);
        border-color: var(--color-text-dim);
        z-index: 1;
    }

    .layer-name {
        color: var(--color-text);
        font-weight: 700;
        display: block;
        margin-bottom: 0.25rem;
    }

    .layer-desc {
        color: var(--color-text-dim);
        font-size: 0.75rem;
    }

    .layer.consensus {
        border-color: var(--color-accent);
        background: rgba(255, 255, 255, 0.02);
    }

    .layer.consensus:hover {
        background: rgba(255, 255, 255, 0.05);
    }

    .architecture-note {
        text-align: center;
        color: var(--color-text-muted);
        font-style: italic;
        max-width: 500px;
        margin: 0 auto;
    }

    /* Manifesto Section */
    .manifesto {
        padding: 8rem 0;
    }

    .manifesto-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .manifesto-header h3 {
        margin-bottom: 1rem;
    }

    .document-links {
        display: flex;
        justify-content: center;
        gap: 2rem;
        margin-bottom: 4rem;
        flex-wrap: wrap;
    }

    .doc-link {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
        padding: 1.5rem 2.5rem;
        border: 1px solid var(--color-border);
        background: var(--color-surface);
        transition: all 0.3s var(--ease-out);
        text-align: center;
        position: relative;
    }

    .doc-link:hover {
        border-color: var(--color-accent);
        background: var(--color-bg-elevated);
        transform: translateY(-2px);
    }

    .doc-label {
        font-family: var(--font-mono);
        font-size: 0.625rem;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: var(--color-text-dim);
    }

    .doc-title {
        font-family: var(--font-serif);
        font-size: 1.25rem;
        font-style: italic;
        color: var(--color-text);
    }

    .manifesto-content {
        max-width: 700px;
        margin: 0 auto;
    }

    .manifesto-excerpt {
        font-size: 1.125rem;
        line-height: 1.8;
    }

    .manifesto-excerpt p {
        margin-bottom: 1.5rem;
    }

    .manifesto-excerpt .emphasis {
        font-style: italic;
        color: var(--color-accent);
    }

    .manifesto-excerpt .highlight {
        display: block;
        font-size: 1.375rem;
        padding: 2rem;
        margin: 3rem 0;
        border-left: 3px solid var(--color-accent);
        background: linear-gradient(to right, var(--color-glow), transparent);
    }

    /* CTA Section */
    .cta {
        padding: 8rem 0;
        text-align: center;
        background: linear-gradient(to bottom, var(--color-bg) 0%, var(--color-bg-elevated) 100%);
        position: relative;
    }

    .cta-symbol {
        width: 80px;
        height: 80px;
        margin: 0 auto 2rem;
        opacity: 0.3;
    }

    .cta-symbol img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: var(--color-symbol-invert);
        transition: filter 0.3s ease;
    }

    .cta h2 {
        margin-bottom: 1.5rem;
    }

    .cta-description {
        max-width: 550px;
        margin: 0 auto 3rem;
        color: var(--color-text-muted);
    }

    .cta-links {
        display: flex;
        justify-content: center;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .cta-link {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        font-family: var(--font-mono);
        font-size: 0.875rem;
        letter-spacing: 0.05em;
        padding: 1rem 2rem;
        border: 1px solid var(--color-border);
        background: transparent;
        transition: all 0.3s var(--ease-out);
        position: relative;
        overflow: hidden;
    }

    .cta-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--color-accent);
        transform: translateX(-100%);
        transition: transform 0.3s var(--ease-out);
        z-index: -1;
    }

    .cta-link:hover {
        border-color: var(--color-accent);
        color: var(--color-bg);
    }

    .cta-link:hover::before {
        transform: translateX(0);
    }

    .cta-link svg {
        width: 18px;
        height: 18px;
    }

    /* Reveal animations */
    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: opacity 0.8s var(--ease-out), transform 0.8s var(--ease-out);
    }

    .reveal.visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* Responsive */
    @media (max-width: 900px) {
        .philosophy-content {
            grid-column: 1 / -1;
        }

        .philosophy-block {
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        .philosophy-block.reverse {
            direction: ltr;
        }
    }

    @media (max-width: 600px) {
        .hero {
            padding: 6rem 1.5rem 4rem;
        }

        .principle {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .principle-number {
            font-size: 2rem;
        }

        .cta-links {
            flex-direction: column;
            align-items: center;
        }
    }
</style>

<section class="hero">
    <div class="hero-symbol">
        <img src="/bitcoin-echo-symbol.jpg" alt="Bitcoin Echo Symbol">
        <div class="echo-rings">
            <div class="echo-ring"></div>
            <div class="echo-ring"></div>
            <div class="echo-ring"></div>
            <div class="echo-ring"></div>
        </div>
    </div>
    <h1 class="hero-title">
        Bitcoin Echo
        <span>Build once. Build right. Stop.</span>
    </h1>
    <p class="hero-description">
        A faithful implementation of the Bitcoin protocol, built not for continued development—but for permanence.
    </p>
    <div class="scroll-indicator">
        <span>Explore</span>
        <div class="scroll-line"></div>
    </div>
</section>

<section class="philosophy" id="philosophy">
    <div class="container">
        <div class="philosophy-grid">
            <div class="philosophy-header">
                <h3>Philosophy</h3>
                <h2>Build Once. Build Right. Stop.</h2>
                <div class="divider"></div>
            </div>
            <div class="philosophy-content">
                <div class="philosophy-block reveal">
                    <blockquote class="quote-large">
                        "The rules are stable. The software is not. We believe this is a problem."
                        <cite>— Bitcoin Echo Manifesto</cite>
                    </blockquote>
                    <div class="philosophy-text">
                        <p>The Bitcoin protocol hasn't changed since 2008. But the software implementing it never stops changing. Every update is a risk. Every refactor is a chance for divergence.</p>
                        <p>What if we built software the way Bitcoin builds its chain? Append-only. Immutable. Final.</p>
                    </div>
                </div>
                <div class="philosophy-block reverse reveal">
                    <blockquote class="quote-large">
                        "An echo does not editorialize. It faithfully reproduces what was said."
                    </blockquote>
                    <div class="philosophy-text">
                        <p>Bitcoin Echo is not a new interpretation. It's a faithful transcription of Satoshi's protocol—built to outlast its creators, designed to be compiled and run by engineers in 2125 who have never heard of us.</p>
                        <p>We are not building something new. We are preserving something important.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="principles" id="principles">
    <div class="container">
        <div class="principles-header">
            <h3>Core Principles</h3>
            <h2>Designed for Century-Scale Operation</h2>
        </div>
        <div class="principles-list">
            <article class="principle reveal">
                <div class="principle-number">01</div>
                <div class="principle-content">
                    <h4>Zero External Dependencies</h4>
                    <p>Every library is a liability. Every package manager is a bet on someone else's decisions. Bitcoin Echo depends on a C compiler and nothing else. There is no npm install. There is no supply chain.</p>
                </div>
            </article>
            <article class="principle reveal">
                <div class="principle-number">02</div>
                <div class="principle-content">
                    <h4>Consensus Separated from Platform</h4>
                    <p>The consensus engine makes no system calls. It is pure computation—bytes in, validity out. Operating systems change. The consensus engine does not.</p>
                </div>
            </article>
            <article class="principle reveal">
                <div class="principle-number">03</div>
                <div class="principle-content">
                    <h4>Small Enough to Audit</h4>
                    <p>A single skilled programmer should be able to read and understand every line. We target 20,000 lines of heavily-commented C. No abstraction for its own sake. No cleverness.</p>
                </div>
            </article>
            <article class="principle reveal">
                <div class="principle-number">04</div>
                <div class="principle-content">
                    <h4>Pure C for Longevity</h4>
                    <p>Not because it's the best language, but because it will outlive us. C compilers will exist as long as computers do. The Linux kernel proves that C can last for decades. So will Bitcoin Echo.</p>
                </div>
            </article>
            <article class="principle reveal">
                <div class="principle-number">05</div>
                <div class="principle-content">
                    <h4>Doors That Close</h4>
                    <p>Upon completion, the repository will be archived. The signing keys will be published. Not compromised—intentionally released. So that no one can falsely claim authority to issue updates. The project will be finished.</p>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="architecture" id="architecture">
    <div class="container">
        <div class="architecture-header">
            <h3>Architecture</h3>
            <h2>Four Layers, One Frozen Core</h2>
        </div>
        <div class="architecture-diagram">
            <div class="layer">
                <span class="layer-name">Application Layer</span>
                <span class="layer-desc">Node operation, RPC interface, logging</span>
            </div>
            <div class="layer">
                <span class="layer-name">Protocol Layer</span>
                <span class="layer-desc">P2P messages, peer management, mempool</span>
            </div>
            <div class="layer consensus">
                <span class="layer-name">⬡ Consensus Engine ⬡</span>
                <span class="layer-desc">Block validation, tx validation, chain selection — FROZEN</span>
            </div>
            <div class="layer">
                <span class="layer-name">Platform Abstraction Layer</span>
                <span class="layer-desc">Sockets, threads, files, time, entropy</span>
            </div>
        </div>
        <p class="architecture-note reveal">
            The consensus engine is pure computation. It can be extracted, compiled independently, and verified against any specification—forever.
        </p>
    </div>
</section>

<section class="manifesto" id="manifesto">
    <div class="container">
        <div class="manifesto-header">
            <h3>From the Manifesto</h3>
            <h2>Why Permanence Matters</h2>
        </div>
        <div class="document-links reveal">
            <a href="/docs/manifesto" class="doc-link">
                <span class="doc-label">Read the Full</span>
                <span class="doc-title">Manifesto</span>
            </a>
            <a href="/docs/whitepaper" class="doc-link">
                <span class="doc-label">Read the Full</span>
                <span class="doc-title">Whitepaper</span>
            </a>
            <a href="/docs/primer" class="doc-link">
                <span class="doc-label">Read the</span>
                <span class="doc-title">Bitcoin Primer</span>
            </a>
            <a href="/docs/building" class="doc-link">
                <span class="doc-label">Read</span>
                <span class="doc-title">Building Guide</span>
            </a>
        </div>
        <div class="manifesto-content">
            <div class="manifesto-excerpt reveal">
                <p>Bitcoin asks us to trust mathematics, not institutions. To verify, not believe. To run our own nodes and check the chain ourselves.</p>

                <p>But what good is verification if the verifier changes every six months?</p>

                <p class="highlight">
                    We want verification without expiration. We want software that exists the way the protocol exists: unchanging, reliable, permanent.
                </p>

                <p>We are building something else: a <span class="emphasis">frozen reference</span>. A verification artifact. Software that someone in 2125 can compile and run to check the chain, without trusting that a century of developers made compatible choices.</p>

                <p>This is not abandonment. <span class="emphasis">This is completion.</span></p>

                <p class="highlight">
                    An echo does not editorialize. It does not improve upon the original. It faithfully reproduces what was said. And when the sound travels further than expected, a new echo carries it forward—faithful to the last, as the last was faithful to the first.
                </p>
            </div>
        </div>
    </div>
</section>

<section class="cta" id="connect">
    <div class="container">
        <div class="cta-symbol">
            <img src="/bitcoin-echo-symbol.jpg" alt="Bitcoin Echo">
        </div>
        <h2>Help Us Build the Last Implementation</h2>
        <p class="cta-description">
            If this resonates with you—if you believe Bitcoin deserves software as permanent as its protocol—we want to hear from you. Developers. Auditors. Funders. Critics.
        </p>
        <div class="cta-links">
            <a href="mailto:echo@bitcoinecho.org" class="cta-link">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                    <polyline points="22,6 12,13 2,6" />
                </svg>
                Email Us
            </a>
            <a href="https://github.com/bitcoinecho" target="_blank" rel="noopener noreferrer" class="cta-link">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                </svg>
                GitHub
            </a>
            <a href="https://x.com/bitcoinechoorg" target="_blank" rel="noopener noreferrer" class="cta-link">
                <span style="font-size: 1.25rem; line-height: 1;">𝕏</span>
                Follow
            </a>
        </div>
    </div>
</section>

<script>
    // Reveal animations on scroll
    const revealElements = document.querySelectorAll('.reveal');

    const revealOnScroll = () => {
        const windowHeight = window.innerHeight;
        revealElements.forEach(element => {
            const elementTop = element.getBoundingClientRect().top;
            const revealPoint = windowHeight - 100;

            if (elementTop < revealPoint) {
                element.classList.add('visible');
            }
        });
    };

    window.addEventListener('scroll', revealOnScroll);
    window.addEventListener('load', revealOnScroll);

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Pause echo animation when not visible
    const heroSymbol = document.querySelector('.hero-symbol');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            const rings = entry.target.querySelectorAll('.echo-ring');
            rings.forEach(ring => {
                ring.style.animationPlayState = entry.isIntersecting ? 'running' : 'paused';
            });
        });
    }, { threshold: 0.1 });

    observer.observe(heroSymbol);
</script>

<?php
// Include footer
include __DIR__ . '/footer.php';
?>
