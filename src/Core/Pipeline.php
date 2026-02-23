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
     */
    public function then(callable $destination): void
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes),
            fn(callable $next, string|callable $pipe) => function (array $passable) use ($next, $pipe) {
                if (is_string($pipe) && class_exists($pipe)) {
                    // Parse middleware:param1,param2 syntax
                    $params = [];
                    if (str_contains($pipe, ':')) {
                        [$pipe, $paramStr] = explode(':', $pipe, 2);
                        $params = explode(',', $paramStr);
                    }
                    $middleware = new $pipe();
                    if (method_exists($middleware, 'handle')) {
                        $middleware->handle($passable, $next, ...$params);
                        return;
                    }
                }

                if (is_callable($pipe)) {
                    $pipe($passable, $next);
                    return;
                }

                $next($passable);
            },
            $destination
        );

        $pipeline($this->passable);
    }

    // ─── Global Middleware Registry ───────────────────

    /** @var array<class-string> Global middleware that runs on every request */
    private static array $globalMiddleware = [];

    /** @var array<string, class-string> Named middleware aliases */
    private static array $namedMiddleware = [];

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
     * Resolve a middleware name/alias to a class name.
     * Supports "name:param1,param2" syntax.
     */
    public static function resolve(string $name): string
    {
        // Extract params part
        $baseName = str_contains($name, ':') ? explode(':', $name, 2)[0] : $name;
        $params = str_contains($name, ':') ? ':' . explode(':', $name, 2)[1] : '';

        // Check alias first
        if (isset(self::$namedMiddleware[$baseName])) {
            return self::$namedMiddleware[$baseName] . $params;
        }

        // Return as-is (assume fully qualified class name)
        return $name;
    }
}
