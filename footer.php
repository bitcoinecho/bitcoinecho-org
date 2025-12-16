<?php
/**
 * Bitcoin Echo — Reusable Footer
 *
 * Expected variables:
 * - $show_donation: Boolean to show/hide donation section (default: false)
 */

$show_donation = $show_donation ?? false;
?>

<style>
    /* Footer */
    footer {
        padding: 4rem 0;
        border-top: 1px solid var(--color-border);
    }

    .footer-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .footer-brand {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .footer-logo {
        width: 32px;
        height: 32px;
        opacity: 0.5;
    }

    .footer-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        filter: var(--color-symbol-invert);
        transition: filter 0.3s ease;
    }

    .footer-text {
        font-family: var(--font-mono);
        font-size: 0.75rem;
        color: var(--color-text-dim);
    }

    .footer-links {
        display: flex;
        gap: 2rem;
    }

    .footer-link {
        font-family: var(--font-mono);
        font-size: 0.75rem;
        color: var(--color-text-muted);
        transition: color 0.3s ease;
    }

    .footer-link:hover {
        color: var(--color-accent);
    }

    /* Donation Section */
    .donation {
        padding: 2rem 0;
        text-align: center;
    }

    .donation-header {
        margin-bottom: 1rem;
    }

    .donation-header h3 {
        margin-bottom: 0.75rem;
    }

    .donation-message {
        max-width: 500px;
        margin: 0 auto 1.5rem;
        color: var(--color-text-muted);
        font-size: 0.9375rem;
        line-height: 1.6;
    }

    .donation-content {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        flex-wrap: wrap;
        max-width: 700px;
        margin: 0 auto;
    }

    .donation-qr {
        width: 120px;
        height: 120px;
        border: 1px solid var(--color-border);
        padding: 0.5rem;
        background: var(--color-surface);
        transition: border-color 0.3s ease;
    }

    .donation-qr img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        display: block;
    }

    .donation-address-wrapper {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }

    .donation-address-label {
        font-family: var(--font-mono);
        font-size: 0.75rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--color-text-dim);
    }

    .donation-address-text {
        font-family: var(--font-mono);
        font-size: 0.875rem;
        color: var(--color-text);
        padding: 0.75rem 1rem;
        background: var(--color-surface);
        border: 1px solid var(--color-border);
        border-radius: 4px;
        cursor: pointer;
        user-select: all;
        transition: all 0.3s ease;
        position: relative;
        max-width: 100%;
        word-break: break-all;
        min-width: 280px;
    }

    .donation-address-text:hover {
        border-color: var(--color-accent);
        background: var(--color-bg-elevated);
        transform: translateY(-2px);
    }

    .donation-address-text.copied {
        border-color: var(--color-accent);
        background: var(--color-glow);
    }

    .donation-copy-hint {
        font-family: var(--font-mono);
        font-size: 0.6875rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--color-text-dim);
    }

    @media (max-width: 600px) {
        .footer-inner {
            flex-direction: column;
            text-align: center;
        }

        .donation-content {
            flex-direction: column;
        }

        .donation-address-text {
            min-width: auto;
            font-size: 0.8125rem;
        }
    }
</style>

<?php if ($show_donation): ?>
<section class="donation" id="donation">
    <div class="container">
        <div class="donation-header">
            <h3>Support the Mission</h3>
        </div>
        <p class="donation-message">
            Support Bitcoin's permanence through a frozen reference&nbsp;implementation.
        </p>
        <div class="donation-content">
            <div class="donation-qr">
                <img src="/bitcoin-echo-btc-qr.png" alt="Bitcoin QR Code">
            </div>
            <div class="donation-address-wrapper">
                <div class="donation-address-label">Bitcoin Address</div>
                <div class="donation-address-text" id="btc-address" data-address="bc1q6lxs3kmjwya43p5l278fydcagxaawaateq7gse">
                    bc1q6lxs3kmjwya43p5l278fydcagxaawaateq7gse
                </div>
                <div class="donation-copy-hint">Click to copy</div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<footer>
    <div class="container footer-inner">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="/bitcoin-echo-symbol.jpg" alt="Bitcoin Echo">
            </div>
            <span class="footer-text">© <span id="year"></span> Bitcoin Echo. The last implementation.</span>
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

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            html.setAttribute('data-theme', e.matches ? 'dark' : 'light');
        }
    });

    // Evergreen year
    document.getElementById('year').textContent = new Date().getFullYear();

    // Navigation scroll effect
    const nav = document.getElementById('nav');
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        if (currentScroll > 100) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
        lastScroll = currentScroll;
    });

    <?php if ($show_donation): ?>
    // BTC Address Copy Functionality
    const btcAddress = document.getElementById('btc-address');
    if (btcAddress) {
        btcAddress.addEventListener('click', async function () {
            const address = this.getAttribute('data-address');

            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(address);
                    this.classList.add('copied');
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';

                    setTimeout(() => {
                        this.classList.remove('copied');
                        this.textContent = originalText;
                    }, 2000);
                } else {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = address;
                    textArea.style.position = 'fixed';
                    textArea.style.opacity = '0';
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);

                    this.classList.add('copied');
                    const originalText = this.textContent;
                    this.textContent = 'Copied!';

                    setTimeout(() => {
                        this.classList.remove('copied');
                        this.textContent = originalText;
                    }, 2000);
                }
            } catch (err) {
                console.error('Failed to copy address:', err);
            }
        });
    }
    <?php endif; ?>
</script>
</body>
</html>
