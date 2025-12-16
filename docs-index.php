<?php
/**
 * Bitcoin Echo — Docs Index
 *
 * Table of contents page for foundational documents.
 */

// Set page metadata for header
$page_title = 'Documentation — Bitcoin Echo';
$page_description = 'Foundational documents defining Bitcoin Echo\'s philosophy, technical specification, and purpose.';
$active_nav = 'docs';
$show_donation = false; // Hide donation on TOC pages

// Include header
include __DIR__ . '/header.php';
?>

<style>
    /* TOC-specific styles */
    main {
        position: relative;
        z-index: 1;
        max-width: 900px;
        margin: 0 auto;
        padding: 8rem 2rem 4rem;
    }

    .page-header {
        text-align: center;
        margin-bottom: 4rem;
        padding-bottom: 3rem;
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
        margin-bottom: 3rem;
    }

    .doc-card {
        padding: 2rem;
        border: 1px solid var(--color-border);
        background: var(--color-surface);
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
        margin-bottom: 0.5rem;
        display: block;
    }

    .doc-card-title {
        font-family: var(--font-serif);
        font-size: 1.5rem;
        font-weight: 400;
        color: var(--color-text);
        margin-bottom: 0.75rem;
    }

    .doc-card-description {
        font-size: 0.9375rem;
        color: var(--color-text-muted);
        line-height: 1.6;
    }

    @media (max-width: 600px) {
        main {
            padding: 7rem 1.5rem 3rem;
        }
    }
</style>

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
            <h2 class="doc-card-title">Bitcoin Echo Manifesto</h2>
            <p class="doc-card-description">
                Why we build software that stops. The case for permanence over perpetual development.
            </p>
        </a>

        <a href="/docs/whitepaper" class="doc-card">
            <span class="doc-card-label">Technical Specification</span>
            <h2 class="doc-card-title">Bitcoin Echo Whitepaper</h2>
            <p class="doc-card-description">
                Complete technical architecture, consensus rules, and implementation roadmap.
            </p>
        </a>

        <a href="/docs/primer" class="doc-card">
            <span class="doc-card-label">Educational</span>
            <h2 class="doc-card-title">Bitcoin Primer</h2>
            <p class="doc-card-description">
                A comprehensive introduction to Bitcoin's core concepts and protocol mechanics.
            </p>
        </a>

        <a href="/docs/building" class="doc-card">
            <span class="doc-card-label">Guide</span>
            <h2 class="doc-card-title">Building in the Future</h2>
            <p class="doc-card-description">
                How to build software designed to last decades, not quarters.
            </p>
        </a>
    </div>
</main>

<?php
// Include footer
include __DIR__ . '/footer.php';
?>
