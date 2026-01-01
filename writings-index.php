<?php
/**
 * Bitcoin Echo — Writings Index
 *
 * Table of contents page for essays and analysis.
 */

// Set page metadata for header
$page_title = 'Writings — Bitcoin Echo';
$page_description = 'Essays and analysis on Bitcoin, software permanence, and protocol design.';
$active_nav = 'writings';
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
        margin-bottom: 0.75rem;
    }

    .doc-card-meta {
        font-family: var(--font-mono);
        font-size: 0.75rem;
        color: var(--color-text-dim);
        letter-spacing: 0.05em;
    }

    @media (max-width: 600px) {
        main {
            padding: 7rem 1.5rem 3rem;
        }
    }
</style>

<main>
    <div class="page-header">
        <span class="page-label">Writings</span>
        <h1>Essays & Analysis</h1>
        <p class="page-description">
            Essays and analysis on Bitcoin, software permanence, and&nbsp;protocol&nbsp;design.
        </p>
    </div>

    <div class="doc-grid">
        <!-- <a href="/writings/ibd-architecture" class="doc-card">
            <span class="doc-card-label">Technical Deep-Dive</span>
            <h2 class="doc-card-title">IBD Architecture</h2>
            <p class="doc-card-description">
                A comprehensive look at Bitcoin Echo's Initial Block Download system—headers-first sync, PULL-based work distribution, sticky batch racing, and adaptive peer management.
            </p>
            <div class="doc-card-meta">Published December 31, 2025</div>
        </a> -->
        <a href="/writings/policy-vs-consensus" class="doc-card">
            <span class="doc-card-label">Position Paper</span>
            <h2 class="doc-card-title">Policy vs. Consensus</h2>
            <p class="doc-card-description">
                Why Core and Knots are both right (and both wrong). Understanding the critical distinction between Bitcoin's consensus rules and policy rules—and why configuration transparency matters.
            </p>
            <div class="doc-card-meta">Published December 15, 2025</div>
        </a>
    </div>
</main>

<?php
// Include footer
include __DIR__ . '/footer.php';
?>
