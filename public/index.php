<?php

/**
 * 🧬 OpenGenetics — Public Entry Point (v2.0 with Middleware Pipeline)
 *
 * All API requests are routed through this file via .htaccess.
 * Initializes environment, autoloading, middleware, and dispatches routing.
 */

// Shared autoloader bootstrap
$appRoot = dirname(__DIR__);
require_once $appRoot . '/src/bootstrap.php';

use OpenGenetics\Core\Env;
use OpenGenetics\Core\ErrorHandler;
use OpenGenetics\Core\Pipeline;
use OpenGenetics\Core\Router;
use OpenGenetics\Middleware\CorsMiddleware;
use OpenGenetics\I18n\I18n;

// Load environment
Env::load($appRoot);

// Register global error handler
ErrorHandler::register();

// Register global middleware
Pipeline::addGlobal(CorsMiddleware::class);

// Initialize i18n only when locale header/param is present
if (isset($_SERVER['HTTP_X_LOCALE']) || isset($_GET['lang']) || str_contains($_SERVER['REQUEST_URI'] ?? '', '/i18n')) {
    I18n::init($appRoot . '/locales', 'en');
}

// Set timezone
date_default_timezone_set(Env::get('APP_TIMEZONE', 'Asia/Bangkok'));

// Welcome Page Intercept (Serve HTML for root path)
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($requestUri === '/' || $requestUri === '') {
    require_once __DIR__ . '/welcome.php';
    exit;
}

// Dispatch router
$router = new Router($appRoot, 'api');
$router->dispatch();
