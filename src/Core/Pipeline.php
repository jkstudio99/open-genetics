<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Middleware Pipeline
 *
 * Chain of responsibility pattern for processing HTTP requests.
 * Each middleware can inspect/modify the request before passing
 * it to the next middleware in the chain.
 *
 * Usage:
 *   Pipeline::send($request)
 *       ->through([CorsMiddleware::class, AuthMiddleware::class])
 *       ->then(fn($req) => $handler($req));
 *
 * Post-response hooks (fire after response is sent):
 *   Pipeline::after(function (\Throwable|null $error): void {
 *       // audit log, metrics, cleanup
 *   });
 */
final class Pipeline
{
    private array $passable;
    /** @var array<class-string|callable> */
    private array $pipes = [];

    private function __construct(array $passable)
    {
        $this->passable = $passable;
    }

    /**
     * Create a new pipeline with the given request data.
     */
    public static function send(array $passable): self
    {
        return new self($passable);
    }

    /**
     * Set the middleware stack to run through.
     *
     * @param array<class-string|callable> $pipes
     */
    public function through(array $pipes): self
    {
        $this->pipes = $pipes;
        return $this;
    }

    /**
     * Run the pipeline and execute the final destination.
     * After destination completes, fires all registered after() callbacks.
     */
    public function then(callable $destination): void
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            fn(callable $next, string|callable $pipe) => function (array $passable) use ($next, $pipe) {
                if (is_callable($pipe) && !is_string($pipe)) {
                    $pipe($passable, $next);
                    return;
                }

                if (is_string($pipe)) {
                    // Parse "ClassName:param1,param2" syntax
                    $params    = [];
                    $className = $pipe;
                    if (str_contains($pipe, ':')) {
                        [$className, $paramStr] = explode(':', $pipe, 2);
                        $params = explode(',', $paramStr);
                    }

                    if (class_exists($className)) {
                        $middleware = new $className();
                        if (method_exists($middleware, 'handle')) {
                            $middleware->handle($passable, $next, ...$params);
                            return;
                        }
                    }
                }

                $next($passable);
            },
            $destination
        );

        $error = null;
        try {
            $pipeline($this->passable);
        } catch (\Throwable $e) {
            $error = $e;
            throw $e;
        } finally {
            self::runAfterCallbacks($error);
        }
    }

    // ─── Global Middleware Registry ───────────────────

    /** @var array<class-string> Global middleware that runs on every request */
    private static array $globalMiddleware = [];

    /** @var array<string, class-string> Named middleware aliases */
    private static array $namedMiddleware = [];

    /** @var array<callable> Callbacks invoked after the response is sent */
    private static array $afterCallbacks = [];

    /**
     * Register global middleware (runs on every request).
     */
    public static function addGlobal(string $middleware): void
    {
        self::$globalMiddleware[] = $middleware;
    }

    /**
     * Register named middleware alias.
     * Example: Pipeline::alias('auth', AuthMiddleware::class);
     */
    public static function alias(string $name, string $middleware): void
    {
        self::$namedMiddleware[$name] = $middleware;
    }

    /**
     * Get global middleware stack.
     * @return array<class-string>
     */
    public static function getGlobal(): array
    {
        return self::$globalMiddleware;
    }

    /**
     * Register a callback to run after the pipeline completes.
     * Useful for audit logging, metrics, and cleanup.
     *
     * Pipeline::after(function (?\Throwable $error): void {
     *     AuditLog::record($_SERVER['REQUEST_URI'], $error?->getMessage());
     * });
     */
    public static function after(callable $callback): void
    {
        self::$afterCallbacks[] = $callback;
    }

    /**
     * Resolve a middleware name/alias to a class name.
     * Supports "name:param1,param2" syntax.
     */
    public static function resolve(string $name): string
    {
        $baseName = str_contains($name, ':') ? explode(':', $name, 2)[0] : $name;
        $params   = str_contains($name, ':') ? ':' . explode(':', $name, 2)[1] : '';

        if (isset(self::$namedMiddleware[$baseName])) {
            return self::$namedMiddleware[$baseName] . $params;
        }

        return $name;
    }

    /**
     * Fire all registered after-callbacks.
     * Called in the `finally` block — runs even if an exception was thrown.
     */
    private static function runAfterCallbacks(?\Throwable $error): void
    {
        foreach (self::$afterCallbacks as $callback) {
            try {
                $callback($error);
            } catch (\Throwable) {
                // After-callbacks must never crash the response
            }
        }
    }
}
