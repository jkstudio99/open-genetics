<?php

/**
 * 🧬 OpenGenetics — Shared Bootstrap
 * 
 * Common autoloader and environment setup used by both
 * public/index.php and the genetics CLI tool.
 */

declare(strict_types=1);

// Composer autoloader
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Manual PSR-4 fallback (when Composer is not available)
spl_autoload_register(function (string $class) {
    $prefix  = 'OpenGenetics\\';
    $baseDir = __DIR__ . '/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
