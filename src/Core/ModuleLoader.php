<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * ModuleLoader — Genetic Modules / Plugin System (OpenGenetics v2.0)
 *
 * Allows extending the framework via self-contained modules that hook into
 * the application lifecycle without modifying core files.
 *
 * Module contract (implement GeneticModule interface):
 *   - boot()        : executed on framework bootstrap
 *   - routes()      : returns additional route definitions
 *   - middleware()  : returns middleware aliases to register
 *   - commands()    : returns CLI commands to register
 *   - config()      : returns default configuration values
 *
 * Registering modules in public/index.php:
 *   ModuleLoader::register(new AuthLogModule());
 *   ModuleLoader::register(new NotificationsModule());
 *   ModuleLoader::boot(); // Call after all modules registered
 *
 * Creating a module in modules/AuthLog/AuthLogModule.php:
 *   class AuthLogModule implements GeneticModule {
 *       public function boot(): void { ... }
 *       public function middleware(): array { return ['authlog' => AuthLogMiddleware::class]; }
 *       ...
 *   }
 */

// ── Module Interface ──────────────────────────────────────────────────────────

interface GeneticModule
{
    /**
     * Boot the module — register services, listeners, etc.
     * Called once during framework bootstrap.
     */
    public function boot(): void;

    /**
     * Return middleware aliases this module provides.
     * @return array<string, class-string>  ['alias' => Middleware::class]
     */
    public function middleware(): array;

    /**
     * Return CLI command handlers this module provides.
     * @return array<string, callable>  ['command:name' => fn($root, $arg) => void]
     */
    public function commands(): array;

    /**
     * Return default configuration values for this module.
     * @return array<string, mixed>
     */
    public function config(): array;

    /**
     * Human-readable module name.
     */
    public function name(): string;

    /**
     * Semantic version string, e.g. '1.0.0'.
     */
    public function version(): string;
}

// ── Abstract Base Module (convenience) ───────────────────────────────────────

abstract class GeneticModuleBase implements GeneticModule
{
    public function boot(): void {}
    public function middleware(): array { return []; }
    public function commands(): array { return []; }
    public function config(): array { return []; }
    public function version(): string { return '1.0.0'; }
}

// ── ModuleLoader ──────────────────────────────────────────────────────────────

final class ModuleLoader
{
    /** @var GeneticModule[] */
    private static array $modules = [];

    /** @var array<string, mixed> Merged config from all modules */
    private static array $config  = [];

    /** @var bool Whether boot() has been called */
    private static bool $booted   = false;

    // ── Registration ─────────────────────────────────────

    /**
     * Register one or more modules.
     *
     * ModuleLoader::register(new AuthLogModule(), new NotificationsModule());
     */
    public static function register(GeneticModule ...$modules): void
    {
        foreach ($modules as $module) {
            self::$modules[$module->name()] = $module;
            // Merge config defaults
            self::$config = array_merge($module->config(), self::$config);
        }
    }

    /**
     * Auto-discover modules from a directory.
     * Scans modules/ folder for *Module.php files implementing GeneticModule.
     */
    public static function discover(string $modulesDir): void
    {
        if (!is_dir($modulesDir)) {
            return;
        }

        foreach (glob($modulesDir . '/*/*.php') as $file) {
            require_once $file;
        }

        foreach (get_declared_classes() as $class) {
            if (
                in_array(GeneticModule::class, class_implements($class) ?: [], true) &&
                !str_contains($class, 'GeneticModuleBase')
            ) {
                $module = new $class();
                self::$modules[$module->name()] = $module;
                self::$config = array_merge($module->config(), self::$config);
            }
        }
    }

    // ── Lifecycle ─────────────────────────────────────────

    /**
     * Boot all registered modules.
     * Also registers middleware aliases into the Pipeline.
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        foreach (self::$modules as $module) {
            $module->boot();

            // Register middleware aliases
            foreach ($module->middleware() as $alias => $class) {
                Pipeline::alias($alias, $class);
            }
        }

        self::$booted = true;
    }

    // ── Access ────────────────────────────────────────────

    /**
     * Get a registered module by name.
     */
    public static function get(string $name): ?GeneticModule
    {
        return self::$modules[$name] ?? null;
    }

    /**
     * Get all registered modules.
     * @return GeneticModule[]
     */
    public static function all(): array
    {
        return self::$modules;
    }

    /**
     * Check if a module is registered.
     */
    public static function has(string $name): bool
    {
        return isset(self::$modules[$name]);
    }

    /**
     * Get merged config value from all modules.
     */
    public static function config(string $key, mixed $default = null): mixed
    {
        return self::$config[$key] ?? $default;
    }

    /**
     * Get all CLI commands from all modules.
     * @return array<string, callable>
     */
    public static function commands(): array
    {
        $all = [];
        foreach (self::$modules as $module) {
            $all = array_merge($all, $module->commands());
        }
        return $all;
    }

    /**
     * Status summary for CLI/debug.
     */
    public static function status(): array
    {
        return array_map(fn(GeneticModule $m) => [
            'name'       => $m->name(),
            'version'    => $m->version(),
            'middleware' => array_keys($m->middleware()),
            'commands'   => array_keys($m->commands()),
        ], self::$modules);
    }
}
