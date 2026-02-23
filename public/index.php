<?php

/**
 * 🧬 OpenGenetics — Public Entry Point (v2.0 with Middleware Pipeline)
 *
 * All API requests are routed through this file via .htaccess.
 * Initializes environment, autoloading, middleware, and dispatches routing.
 */

// Shared autoloader bootstrap
require_once __DIR__ . '/src/bootstrap.php';

use OpenGenetics\Core\Env;
use OpenGenetics\Core\ErrorHandler;
use OpenGenetics\Core\Pipeline;
use OpenGenetics\Core\Router;
use OpenGenetics\Middleware\CorsMiddleware;
use OpenGenetics\I18n\I18n;

// Load environment
Env::load(__DIR__);

// Register global error handler (must be after Env::load for APP_DEBUG)
ErrorHandler::register();

// Register global middleware (runs on EVERY request)
Pipeline::addGlobal(CorsMiddleware::class);

// Initialize i18n only when locale header/param is present (lazy-load)
if (isset($_SERVER['HTTP_X_LOCALE']) || isset($_GET['lang']) || str_contains($_SERVER['REQUEST_URI'] ?? '', '/i18n')) {
    I18n::init(__DIR__ . '/locales', 'en');
}

// Set timezone
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Bangkok'));

// Dispatch router
$router = new Router(__DIR__, 'api');
$router->dispatch();
