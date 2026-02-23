<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * EndpointAI — AI-Powered Endpoint Scaffolder (OpenGenetics v2.0)
 *
 * Generates intelligent, production-ready API endpoints from natural-language
 * descriptions by parsing intent from the description and applying best
 * practices automatically (RBAC, validation, pagination, audit logging).
 *
 * This is a developer-time code generator, NOT a runtime AI feature.
 * It uses pattern matching and template intelligence to scaffold code.
 *
 * Usage via CLI:
 *   php genetics make:endpoint-ai "Products CRUD with auth and pagination"
 *   php genetics make:endpoint-ai "User profile endpoint that requires admin role"
 *
 * Or directly:
 *   EndpointAI::scaffold('products', 'List products with search and pagination', [
 *       'auth'       => true,
 *       'role'       => 'ADMIN',
 *       'paginate'   => true,
 *       'search'     => true,
 *       'audit'      => true,
 *       'table'      => 'products',
 *   ]);
 */
final class EndpointAI
{
    // ── Pattern Detection ─────────────────────────────────

    /**
     * Analyze a natural-language description and extract intent.
     *
     * @param string $description   User description of what the endpoint should do
     * @return array Detected features to scaffold
     */
    public static function analyze(string $description): array
    {
        $desc = mb_strtolower($description);

        return [
            'needs_auth'     => self::matches($desc, ['auth', 'login', 'protect', 'secure', 'token', 'jwt', 'authenticated']),
            'needs_admin'    => self::matches($desc, ['admin', 'manager', 'rbac', 'role', 'permission', 'only admin']),
            'needs_paginate' => self::matches($desc, ['list', 'paginate', 'all', 'page', 'per_page', 'index', 'search']),
            'needs_search'   => self::matches($desc, ['search', 'filter', 'find', 'query', 'lookup']),
            'needs_crud'     => self::matches($desc, ['crud', 'create', 'read', 'update', 'delete', 'manage', 'resource']),
            'needs_audit'    => self::matches($desc, ['audit', 'log', 'track', 'history', 'activity']),
            'needs_validate' => self::matches($desc, ['validate', 'required', 'email', 'form', 'input']),
            'needs_cache'    => self::matches($desc, ['cache', 'fast', 'performance', 'speed']),
            'needs_sse'      => self::matches($desc, ['realtime', 'real-time', 'stream', 'live', 'push', 'event']),
            'is_upload'      => self::matches($desc, ['upload', 'file', 'image', 'photo', 'attachment']),
        ];
    }

    /**
     * Scaffold an endpoint file with AI-detected features.
     *
     * @param string $name        Endpoint name (e.g. 'products')
     * @param string $description Natural-language description
     * @param array  $opts        Override any analyzed options
     * @param string $root        Project root
     * @return string Path to generated file
     */
    public static function scaffold(
        string $name,
        string $description = '',
        array  $opts = [],
        string $root = ''
    ): string {
        if ($root === '') {
            $root = dirname(__DIR__, 2);
        }

        $features   = array_merge(self::analyze($description), $opts);
        $className  = ucfirst(strtolower($name));
        $table      = $opts['table'] ?? strtolower($name);
        $filePath   = $root . '/api/' . strtolower($name) . '.php';

        if (file_exists($filePath)) {
            $filePath = $root . '/api/' . strtolower($name) . '_' . time() . '.php';
        }

        $summary = self::featureSummary($features);
        $code = self::buildCode($className, $table, $description, $features, $summary);
        file_put_contents($filePath, $code);

        return $filePath;
    }

    // ── Code Builder ──────────────────────────────────────

    private static function buildCode(
        string $className,
        string $table,
        string $description,
        array  $f,
        string $summary = ''
    ): string {
        $middlewares = [];
        if ($f['needs_auth'])  {
            $role = $f['needs_admin'] ? 'ADMIN' : '';
            $middlewares[] = $role ? "auth:{$role}" : 'auth';
        }
        if ($f['needs_cache']) {
            $middlewares[] = 'rate:60,60';
        }

        $middlewareAttr = '';
        if ($middlewares) {
            $mw = implode("', '", $middlewares);
            $middlewareAttr = "\n#[\\OpenGenetics\\Core\\Middleware('{$mw}')]";
        }

        $useStatements = self::buildUseStatements($f);
        $getMethod     = self::buildGetMethod($table, $f);
        $postMethod    = $f['needs_crud'] ? self::buildPostMethod($table, $f) : '';
        $putMethod     = $f['needs_crud'] ? self::buildPutMethod($table, $f) : '';
        $deleteMethod  = $f['needs_crud'] ? self::buildDeleteMethod($table, $f) : '';

        $timestamp = date('Y-m-d H:i:s');

        return <<<PHP
<?php
/**
 * {$className} Endpoint
 * Description: {$description}
 * Generated:   {$timestamp} by EndpointAI
 *
 * Detected features: {$summary}
 */

{$useStatements}
{$middlewareAttr}
class {$className}
{
{$getMethod}
{$postMethod}
{$putMethod}
{$deleteMethod}
}

PHP;
    }

    private static function buildUseStatements(array $f): string
    {
        $uses = ['use OpenGenetics\\Core\\{Database, Response};'];
        if ($f['needs_validate'])  $uses[] = 'use OpenGenetics\\Core\\Validator;';
        if ($f['needs_audit'])     $uses[] = 'use OpenGenetics\\Core\\AuditLog;';
        if ($f['needs_auth'])      $uses[] = 'use OpenGenetics\\Auth\\Guard;';
        if ($f['needs_cache'])     $uses[] = 'use OpenGenetics\\Core\\Cache;';
        if ($f['needs_sse'])       $uses[] = 'use OpenGenetics\\Core\\Pulse;';
        return implode("\n", $uses);
    }

    private static function buildGetMethod(string $table, array $f): string
    {
        $lines = ["    public static function get(array \$body): void\n    {"];

        if ($f['needs_search']) {
            $lines[] = "        \$search  = \$_GET['q'] ?? '';";
        }
        if ($f['needs_paginate']) {
            $lines[] = "        \$page    = max(1, (int)(\$_GET['page']     ?? 1));";
            $lines[] = "        \$perPage = min((int)(\$_GET['per_page']  ?? 20), 100);";
            $lines[] = "        \$offset  = (\$page - 1) * \$perPage;";
        }

        if ($f['needs_cache']) {
            $lines[] = "";
            $lines[] = "        \$cacheKey = 'endpoint:{$table}:' . http_build_query(\$_GET);";
            $lines[] = "        \$cached   = Cache::get(\$cacheKey);";
            $lines[] = "        if (\$cached !== null) { Response::success(\$cached); return; }";
        }

        $lines[] = "";
        if ($f['needs_search'] && $f['needs_paginate']) {
            $lines[] = "        \$where  = \$search ? \"WHERE name LIKE :q\" : '1=1';";
            $lines[] = "        \$total  = Database::queryOne(\"SELECT COUNT(*) as c FROM `{$table}` WHERE \$where\", \$search ? ['q' => \"%\$search%\"] : [])['c'] ?? 0;";
            $lines[] = "        \$rows   = Database::query(\"SELECT * FROM `{$table}` WHERE \$where LIMIT :l OFFSET :o\", array_merge(\$search ? ['q' => \"%\$search%\"] : [], ['l' => \$perPage, 'o' => \$offset]));";
        } elseif ($f['needs_paginate']) {
            $lines[] = "        \$total  = Database::queryOne(\"SELECT COUNT(*) as c FROM `{$table}`\")['c'] ?? 0;";
            $lines[] = "        \$rows   = Database::query(\"SELECT * FROM `{$table}` LIMIT :l OFFSET :o\", ['l' => \$perPage, 'o' => \$offset]);";
        } else {
            $lines[] = "        \$rows   = Database::query(\"SELECT * FROM `{$table}`\");";
        }

        if ($f['needs_cache']) {
            $lines[] = "        Cache::put(\$cacheKey, \$rows, 300);";
        }

        $lines[] = "";
        if ($f['needs_paginate']) {
            $lines[] = "        Response::paginated(\$rows, (int)\$total, \$page, \$perPage);";
        } else {
            $lines[] = "        Response::success(\$rows);";
        }

        $lines[] = "    }";
        return implode("\n", $lines);
    }

    private static function buildPostMethod(string $table, array $f): string
    {
        $lines = ["\n    public static function post(array \$body): void\n    {"];
        if ($f['needs_validate']) {
            $lines[] = "        Validator::check(\$body, [\n            'name' => 'required|min:2',\n        ]);";
        }
        $lines[] = "        // TODO: Map \$body fields to columns";
        $lines[] = "        Database::execute(\"INSERT INTO `{$table}` (name) VALUES (:name)\", ['name' => \$body['name']]);";
        if ($f['needs_audit']) {
            $lines[] = "        AuditLog::log(AuditLog::CREATE, '{$table}', \$body);";
        }
        if ($f['needs_cache']) {
            $lines[] = "        Cache::flush('{$table}');";
        }
        $lines[] = "        Response::success(null, 'Created', 201);";
        $lines[] = "    }";
        return implode("\n", $lines);
    }

    private static function buildPutMethod(string $table, array $f): string
    {
        $lines = ["\n    public static function put(array \$body): void\n    {"];
        $lines[] = "        \$id = (int)(\$_GET['id'] ?? 0);";
        $lines[] = "        Database::execute(\"UPDATE `{$table}` SET name = :name WHERE id = :id\", ['name' => \$body['name'], 'id' => \$id]);";
        if ($f['needs_audit']) {
            $lines[] = "        AuditLog::log(AuditLog::UPDATE, '{$table}', ['id' => \$id]);";
        }
        if ($f['needs_cache']) {
            $lines[] = "        Cache::flush('{$table}');";
        }
        $lines[] = "        Response::success(null, 'Updated');";
        $lines[] = "    }";
        return implode("\n", $lines);
    }

    private static function buildDeleteMethod(string $table, array $f): string
    {
        $lines = ["\n    public static function delete(array \$body): void\n    {"];
        $lines[] = "        \$id = (int)(\$_GET['id'] ?? 0);";
        $lines[] = "        Database::execute(\"DELETE FROM `{$table}` WHERE id = :id\", ['id' => \$id]);";
        if ($f['needs_audit']) {
            $lines[] = "        AuditLog::log(AuditLog::DELETE, '{$table}', ['id' => \$id]);";
        }
        if ($f['needs_cache']) {
            $lines[] = "        Cache::flush('{$table}');";
        }
        $lines[] = "        Response::success(null, 'Deleted');";
        $lines[] = "    }";
        return implode("\n", $lines);
    }

    // ── Helpers ───────────────────────────────────────────

    private static function matches(string $text, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (str_contains($text, $kw)) return true;
        }
        return false;
    }

    private static function featureSummary(array $f): string
    {
        $active = array_keys(array_filter($f));
        return implode(', ', array_map(fn($k) => str_replace('needs_', '', $k), $active)) ?: 'basic';
    }
}
