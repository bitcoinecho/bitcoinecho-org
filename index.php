<?php
/**
 * Bitcoin Echo — Front Controller
 *
 * Routes requests to appropriate handlers.
 * Single source of truth: markdown files are rendered server-side.
 */

require_once __DIR__ . '/Parsedown.php';

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Document routes — maps URLs to markdown files
$docs = [
    'docs/manifesto'  => ['file' => 'bitcoin-echo-manifesto.md',  'title' => 'Manifesto'],
    'docs/whitepaper' => ['file' => 'bitcoin-echo-whitepaper.md', 'title' => 'Whitepaper'],
    'docs/primer'     => ['file' => 'bitcoin-primer.md',          'title' => 'Bitcoin Primer'],
    'docs/building'   => ['file' => 'building-in-the-future.md',  'title' => 'Building in the Future'],
];

// Handle document requests
if (isset($docs[$uri])) {
    $doc = $docs[$uri];
    $mdPath = __DIR__ . '/' . $doc['file'];

    if (!file_exists($mdPath)) {
        http_response_code(404);
        echo '404 — Document not found';
        exit;
    }

    $markdown = file_get_contents($mdPath);
    $parsedown = new Parsedown();
    // Safe mode disabled - we trust our own markdown files
    $content = $parsedown->text($markdown);
    $title = $doc['title'];

    include __DIR__ . '/doc-template.php';
    exit;
}

// Homepage — serve the static index.html
if ($uri === '' || $uri === 'index.html') {
    include __DIR__ . '/index.html';
    exit;
}

// Let other static files pass through (handled by Nginx)
// If we reach here, it's a 404
http_response_code(404);
echo '404 — Not found';
