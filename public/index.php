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

// ─── State-Aware Entry Point ───────────────────────────────────
// Automatically serve the right page based on framework state:
//   - Fresh install (no openapi.json) → Welcome page
//   - After docs:generate            → Redirect / → /api/docs
//   - /api/docs                      → Swagger UI
// ────────────────────────────────────────────────────────────────

$rawUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Calculate the public base path (e.g., /open-genetics/public or /)
$publicBase = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

// Get the relative path within the application
$relativePath = $rawUri;
if ($publicBase !== '' && str_starts_with($rawUri, $publicBase)) {
    $relativePath = substr($rawUri, strlen($publicBase));
}
$relativePath = '/' . ltrim($relativePath ?: '', '/');

// Root path: Welcome page or auto-redirect to API docs
if ($relativePath === '/' || $relativePath === '') {
    if (file_exists(__DIR__ . '/openapi.json')) {
        header('Location: ' . $publicBase . '/api/docs');
        exit;
    }
    require_once __DIR__ . '/welcome.php';
    exit;
}

// API Docs: Serve Swagger UI at /api/docs
if ($relativePath === '/api/docs' || $relativePath === '/api/docs/') {
    $openapiFile = __DIR__ . '/openapi.json';
    if (!file_exists($openapiFile)) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error'   => 'API documentation not generated yet.',
            'hint'    => 'Run: php genetics docs:generate',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
    $specUrl     = $publicBase . '/openapi.json';
    $appName     = Env::get('APP_NAME', 'OpenGenetics API');
    $appVersion  = '2.3.0';
    http_response_code(200);
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>API Docs — {$appName}</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { margin: 0; background: #0e0e1a; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .og-header {
      background: linear-gradient(135deg, #1e1b4b 0%, #0e0e1a 100%);
      border-bottom: 1px solid rgba(99,102,241,.15);
      padding: 16px 24px;
      display: flex; align-items: center; gap: 12px;
    }
    .og-header svg { width: 28px; height: 28px; color: #818cf8; }
    .og-header h1 { margin: 0; font-size: 18px; font-weight: 600; color: #e2e8f0; }
    .og-header span { font-size: 12px; color: #64748b; background: rgba(99,102,241,.12); padding: 2px 8px; border-radius: 9999px; }
    .swagger-ui { background: #fafafa; }
    .swagger-ui .topbar { display: none !important; }
  </style>
</head>
<body>
  <div class="og-header">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
    </svg>
    <h1>{$appName}</h1>
    <span>v{$appVersion}</span>
  </div>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script>
    SwaggerUIBundle({
      url: '{$specUrl}',
      dom_id: '#swagger-ui',
      deepLinking: true,
      presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
      layout: 'BaseLayout',
      defaultModelsExpandDepth: -1,
      docExpansion: 'list',
      filter: true,
      tryItOutEnabled: true,
    });
  </script>
</body>
</html>
HTML;
    exit;
}

// Dispatch router
$router = new Router($appRoot, 'api');
$router->dispatch();
