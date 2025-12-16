<?php
/**
 * Bitcoin Echo — Document Template
 *
 * Template for rendering markdown documents.
 * Variables provided by index.php:
 * - $title: Document title
 * - $content: Parsed HTML content from markdown
 */

// Set page metadata for header
$page_title = htmlspecialchars($title) . ' — Bitcoin Echo';
$page_description = htmlspecialchars($title) . ' — Bitcoin Echo: Build once. Build right. Stop.';
$active_nav = null; // Don't highlight nav on document pages
$show_donation = true; // Show donation section on document pages

// Include header
include __DIR__ . '/header.php';
?>

<style>
    /* Document-specific styles */
    main {
        position: relative;
        z-index: 1;
        max-width: 750px;
        margin: 0 auto;
        padding: 8rem 2rem 4rem;
        user-select: text;
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

    /* Copy feedback notification */
    .header-copy-feedback {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        margin-left: 0.75rem;
        font-family: var(--font-mono);
        font-size: 0.75rem;
        color: var(--color-text-muted);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        vertical-align: middle;
    }

    .header-copy-feedback.show {
        opacity: 1;
    }

    .header-copy-feedback::before {
        content: '✓';
        font-size: 0.875rem;
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

    /* Dark theme code backgrounds */
    [data-theme="dark"] {
        --color-code-bg: #161616;
    }

    /* Light theme code backgrounds */
    [data-theme="light"] {
        --color-code-bg: #e8e5e0;
    }

    /* Responsive */
    @media (max-width: 600px) {
        main {
            padding: 7rem 1.5rem 3rem;
        }

        .doc-content h1::before,
        .doc-content h2::before,
        .doc-content h3::before,
        .doc-content h4::before,
        .doc-content h5::before,
        .doc-content h6::before {
            left: -1.25rem;
            font-size: 0.75em;
        }

        .header-copy-feedback {
            font-size: 0.6875rem;
            margin-left: 0.5rem;
        }
    }
</style>

<main>
    <article class="doc-content">
        <?= $content ?>
    </article>
</main>

<script>
    // Header link functionality
    document.querySelectorAll('.doc-content h1[id], .doc-content h2[id], .doc-content h3[id], .doc-content h4[id], .doc-content h5[id], .doc-content h6[id]').forEach(header => {
        header.style.cursor = 'pointer';
        header.style.position = 'relative';

        // Create feedback element
        const feedback = document.createElement('span');
        feedback.className = 'header-copy-feedback';
        feedback.textContent = 'Link Copied';
        header.appendChild(feedback);

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
                        // Show feedback
                        const feedbackEl = this.querySelector('.header-copy-feedback');
                        if (feedbackEl) {
                            feedbackEl.classList.add('show');
                            setTimeout(() => {
                                feedbackEl.classList.remove('show');
                            }, 1200);
                        }
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

<?php
// Include footer
include __DIR__ . '/footer.php';
?>
