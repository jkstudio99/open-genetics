<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * Marketplace — Genetic Marketplace Registry (OpenGenetics v2.0)
 *
 * A lightweight community hub for discovering, publishing, and installing
 * OpenGenetics modules, themes, and plugins. Backed by a JSON registry
 * (local or remote).
 *
 * Usage:
 *   // List available packages
 *   Marketplace::list();
 *
 *   // Search for packages
 *   $results = Marketplace::search('auth');
 *
 *   // Install a package
 *   Marketplace::install('og/jwt-refresh', $root);
 *
 *   // Publish a module to the registry
 *   Marketplace::publish([
 *       'name'        => 'og/notifications',
 *       'version'     => '1.0.0',
 *       'description' => 'Push notifications for OpenGenetics',
 *       'author'      => 'jkstudio99',
 *       'type'        => 'module',
 *       'url'         => 'https://github.com/jkstudio99/og-notifications',
 *   ]);
 *
 * CLI Commands:
 *   php add/genetics market:list
 *   php add/genetics market:search notifications
 *   php add/genetics market:install og/notifications
 */
final class Marketplace
{
    /** Remote registry URL */
    private const REGISTRY_URL = 'https://raw.githubusercontent.com/jkstudio99/open-genetics/main/marketplace/registry.json';

    /** Local cache for registry */
    private const CACHE_KEY    = 'og_marketplace_registry';
    private const CACHE_TTL    = 3600; // 1 hour

    // ── Registry ──────────────────────────────────────────

    /**
     * Fetch the package registry (local cache or remote).
     * @return array List of all packages
     */
    public static function fetch(bool $fresh = false): array
    {
        if (!$fresh) {
            $cached = self::localCache();
            if ($cached !== null) return $cached;
        }

        $registry = self::fetchRemote();
        if ($registry !== null) {
            self::writeLocalCache($registry);
            return $registry;
        }

        // Fallback to built-in packages
        return self::builtInPackages();
    }

    /**
     * List all available packages with optional type filter.
     * @param string|null $type Filter: 'module', 'middleware', 'theme', 'sdk'
     * @return array
     */
    public static function list(?string $type = null): array
    {
        $packages = self::fetch();
        if ($type !== null) {
            $packages = array_filter($packages, fn($p) => ($p['type'] ?? '') === $type);
        }
        return array_values($packages);
    }

    /**
     * Search packages by keyword.
     * @param string $query Keyword to search in name, description, tags
     * @return array
     */
    public static function search(string $query): array
    {
        $q        = mb_strtolower(trim($query));
        $packages = self::fetch();

        return array_values(array_filter($packages, function (array $p) use ($q) {
            return str_contains(mb_strtolower($p['name'] ?? ''), $q)
                || str_contains(mb_strtolower($p['description'] ?? ''), $q)
                || in_array($q, array_map('strtolower', $p['tags'] ?? []), true);
        }));
    }

    /**
     * Get details of a specific package by name.
     * @param string $package Package name e.g. 'og/notifications'
     * @return array|null
     */
    public static function get(string $package): ?array
    {
        $packages = self::fetch();
        foreach ($packages as $p) {
            if (($p['name'] ?? '') === $package) {
                return $p;
            }
        }
        return null;
    }

    // ── Install ───────────────────────────────────────────

    /**
     * Install a marketplace package into the project.
     *
     * Simple packages (single-file modules) are installed directly.
     * Complex packages require Composer and output instructions.
     *
     * @param string $package Package name e.g. 'og/notifications'
     * @param string $root    Project root path
     * @return array Result: ['success' => bool, 'message' => string, 'actions' => array]
     */
    public static function install(string $package, string $root): array
    {
        $meta = self::get($package);

        if ($meta === null) {
            return ['success' => false, 'message' => "Package '{$package}' not found in registry."];
        }

        $actions = [];

        // Simple single-file module
        if (($meta['install_type'] ?? '') === 'single-file' && isset($meta['file_url'])) {
            $content  = @file_get_contents($meta['file_url']);
            if ($content === false) {
                return ['success' => false, 'message' => "Failed to download package."];
            }

            $outDir  = $root . '/modules/' . str_replace('og/', '', $package);
            if (!is_dir($outDir)) mkdir($outDir, 0755, true);

            $outFile = $outDir . '/' . basename($meta['file_url']);
            file_put_contents($outFile, $content);

            $actions[] = "Downloaded: {$outFile}";
            $actions[] = "Register in index.php: ModuleLoader::register(require '{$outFile}');";
        }
        // Composer package
        elseif (isset($meta['composer'])) {
            $actions[] = "Run: composer require {$meta['composer']}";
        }
        // GitHub clone
        elseif (isset($meta['url'])) {
            $actions[] = "Clone: git clone {$meta['url']} modules/{$package}";
        }

        return [
            'success' => true,
            'package' => $meta,
            'message' => "Package '{$package}' ready.",
            'actions' => $actions,
        ];
    }

    // ── Publish ───────────────────────────────────────────

    /**
     * Register a package in the local marketplace registry.
     * (For publishing to the remote registry, a GitHub PR would be required.)
     *
     * @param array $meta Package metadata
     */
    public static function publish(array $meta): void
    {
        $localFile = self::localRegistryPath();
        $existing  = self::localRegistry();

        // Remove if already exists (update)
        $existing = array_filter($existing, fn($p) => ($p['name'] ?? '') !== ($meta['name'] ?? ''));

        $meta['published_at'] = date('Y-m-d');
        $existing[] = $meta;

        file_put_contents($localFile, json_encode(array_values($existing), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // ── Built-in packages (fallback) ──────────────────────

    private static function builtInPackages(): array
    {
        return [
            [
                'name'         => 'og/jwt-refresh',
                'version'      => '1.0.0',
                'description'  => 'JWT Refresh Token support for OpenGenetics',
                'author'       => 'jkstudio99',
                'type'         => 'module',
                'tags'         => ['jwt', 'auth', 'token', 'refresh'],
                'url'          => 'https://github.com/jkstudio99/og-jwt-refresh',
                'install_type' => 'github',
            ],
            [
                'name'         => 'og/notifications',
                'version'      => '1.0.0',
                'description'  => 'Real-time push notifications via SSE + database queue',
                'author'       => 'jkstudio99',
                'type'         => 'module',
                'tags'         => ['notification', 'realtime', 'sse', 'push'],
                'url'          => 'https://github.com/jkstudio99/og-notifications',
                'install_type' => 'github',
            ],
            [
                'name'         => 'og/file-upload',
                'version'      => '1.0.0',
                'description'  => 'Secure file upload middleware with MIME validation',
                'author'       => 'jkstudio99',
                'type'         => 'middleware',
                'tags'         => ['upload', 'file', 'image', 'storage'],
                'url'          => 'https://github.com/jkstudio99/og-file-upload',
                'install_type' => 'github',
            ],
            [
                'name'         => 'og/two-factor',
                'version'      => '1.0.0',
                'description'  => 'TOTP-based Two-Factor Authentication for OpenGenetics',
                'author'       => 'jkstudio99',
                'type'         => 'module',
                'tags'         => ['2fa', 'totp', 'security', 'mfa'],
                'url'          => 'https://github.com/jkstudio99/og-two-factor',
                'install_type' => 'github',
            ],
            [
                'name'         => 'og/graphql-lite',
                'version'      => '2.0.0',
                'description'  => 'Full GraphQL-lite query engine built on FieldSelector',
                'author'       => 'jkstudio99',
                'type'         => 'module',
                'tags'         => ['graphql', 'query', 'fields', 'sparse'],
                'url'          => 'https://github.com/jkstudio99/og-graphql-lite',
                'install_type' => 'github',
            ],
        ];
    }

    // ── Cache helpers ─────────────────────────────────────

    private static function localRegistryPath(): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/marketplace';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        return $dir . '/registry.json';
    }

    private static function localRegistry(): array
    {
        $file = self::localRegistryPath();
        if (!file_exists($file)) return [];
        return json_decode(file_get_contents($file), true) ?? [];
    }

    private static function localCache(): ?array
    {
        $file = self::localRegistryPath() . '.cache';
        if (!file_exists($file)) return null;
        $mtime = filemtime($file);
        if (time() - $mtime > self::CACHE_TTL) return null;
        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : null;
    }

    private static function writeLocalCache(array $data): void
    {
        file_put_contents(self::localRegistryPath() . '.cache', json_encode($data));
    }

    private static function fetchRemote(): ?array
    {
        $ctx = stream_context_create(['http' => ['timeout' => 5]]);
        $raw = @file_get_contents(self::REGISTRY_URL, false, $ctx);
        if ($raw === false) return null;
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }
}
