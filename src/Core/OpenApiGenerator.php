<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Auto OpenAPI Generator
 * 
 * Scans the api/ directory, parses endpoint classes and PHPDoc comments,
 * and generates an OpenAPI 3.0 compliant JSON specification file.
 */
class OpenApiGenerator
{
    private string $apiDir;
    private array $spec;

    public function __construct(string $apiDir)
    {
        $this->apiDir = rtrim($apiDir, '/');
        
        $this->spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'OpenGenetics API',
                'description' => 'Auto-generated API documentation for OpenGenetics framework.',
                'version' => '1.0.0'
            ],
            'servers' => [
                ['url' => Env::get('APP_URL', 'http://localhost:8080')]
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description' => 'Paste your JWT token here (without the Bearer prefix).'
                    ]
                ]
            ],
            'security' => [
                ['bearerAuth' => []]
            ],
            'paths' => []
        ];
    }

    /**
     * Generate the OpenAPI specification file.
     * 
     * @param string $outputPath Path to save the generated JSON.
     * @return bool True on success.
     */
    public function generate(string $outputPath): bool
    {
        if (!is_dir($this->apiDir)) {
            echo "  ❌ API directory not found at {$this->apiDir}\n";
            return false;
        }

        $this->scanDirectory($this->apiDir);

        $json = json_encode($this->spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $result = file_put_contents($outputPath, $json);
        return $result !== false;
    }

    /**
     * Recursively scan directory for endpoint files.
     */
    private function scanDirectory(string $dir): void
    {
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $this->scanDirectory($path);
            } elseif (is_file($path) && str_ends_with($item, '.php')) {
                $this->processEndpointFile($path);
            }
        }
    }

    /**
     * Process an individual endpoint PHP file.
     */
    private function processEndpointFile(string $filePath): void
    {
        require_once $filePath;

        // Calculate URI from file path
        $relativePath = substr($filePath, strlen($this->apiDir));
        $uri = preg_replace('/\.php$/', '', ltrim($relativePath, '/'));
        
        // Handle index.php files mapping to directory paths
        if (str_ends_with($uri, '/index')) {
            $uri = substr($uri, 0, -6);
        } elseif ($uri === 'index') {
            $uri = '';
        }

        $endpointUri = '/api' . ($uri !== '' ? '/' . $uri : '');
        $className = $this->resolveClassName($uri);

        if (!class_exists($className)) {
            return;
        }

        try {
            $reflection = new \ReflectionClass($className);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);
            
            $docComment = $reflection->getDocComment();
            $summary = $this->extractSummary($docComment);
            
            $supportedMethods = ['get', 'post', 'put', 'patch', 'delete'];
            
            foreach ($methods as $method) {
                $methodName = strtolower($method->getName());
                
                if (in_array($methodName, $supportedMethods)) {
                    if (!isset($this->spec['paths'][$endpointUri])) {
                        $this->spec['paths'][$endpointUri] = [];
                    }

                    // Extract method-level docblock if class-level is empty or generic
                    $methodDoc = $method->getDocComment();
                    $methodSummary = $this->extractSummary($methodDoc) ?: $summary;

                    $this->spec['paths'][$endpointUri][$methodName] = [
                        'summary' => $methodSummary ?: "{$methodName} {$endpointUri}",
                        'tags' => [$this->extractTag($uri)],
                        'responses' => [
                            '200' => [
                                'description' => 'Successful operation'
                            ]
                        ]
                    ];
                }
            }
        } catch (\ReflectionException $e) {
            // Ignore classes that cannot be reflected
        }
    }

    /**
     * Extract the first meaningful sentence from a PHPDoc block.
     */
    private function extractSummary(string|false $docComment): string
    {
        if (!$docComment) {
            return '';
        }

        // Remove /**, *, and */
        $clean = preg_replace('/^\s*\/?\**\/?/m', '', $docComment);
        $lines = explode("\n", $clean);
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Ignore HTTP method definitions, tags, or empty lines
            if (!empty($line) && !str_starts_with($line, '@') && !preg_match('/^(GET|POST|PUT|PATCH|DELETE)\s+/i', $line)) {
                // Remove some artifacts like emoji if they start the line
                $line = preg_replace('/^[-•🧬🚀🛡️⚙️]+\s*/u', '', $line);
                return $line;
            }
        }

        return '';
    }

    /**
     * Extract a logical grouping tag from the URI length.
     */
    private function extractTag(string $uri): string
    {
        if (empty($uri)) {
            return 'Core';
        }
        
        $parts = explode('/', $uri);
        return ucfirst(explode('-', $parts[0])[0]);
    }

    /**
     * Convert URI path to a PascalCase class name (matches Router logic).
     */
    private function resolveClassName(string $uri): string
    {
        if (empty($uri)) {
            $uri = 'index';
        }

        $parts = explode('/', $uri);
        $name  = '';

        foreach ($parts as $part) {
            $segments = explode('-', $part);
            $name .= implode('', array_map('ucfirst', $segments));
        }

        return $name;
    }
}
