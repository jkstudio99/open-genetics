<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * 🧬 OpenGenetics — Auto OpenAPI Generator
 *
 * Scans the api/ directory, parses endpoint classes and PHPDoc comments,
 * and generates an OpenAPI 3.0 compliant JSON specification file.
 */
final class OpenApiGenerator
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

        if (str_ends_with($uri, '/index')) {
            $uri = substr($uri, 0, -6);
        } elseif ($uri === 'index') {
            $uri = '';
        }

        $endpointUri = '/api' . ($uri !== '' ? '/' . $uri : '');
        $className   = $this->resolveClassName($uri);

        if (!class_exists($className)) {
            return;
        }

        try {
            $reflection      = new \ReflectionClass($className);
            $methods         = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_STATIC);
            $classDoc        = $reflection->getDocComment();
            $classSummary    = $this->extractSummary($classDoc);
            $supportedMethods = ['get', 'post', 'put', 'patch', 'delete'];

            foreach ($methods as $method) {
                $methodName = strtolower($method->getName());
                if (!in_array($methodName, $supportedMethods)) {
                    continue;
                }

                if (!isset($this->spec['paths'][$endpointUri])) {
                    $this->spec['paths'][$endpointUri] = [];
                }

                $methodDoc     = $method->getDocComment();
                $methodSummary = $this->extractSummary($methodDoc) ?: $classSummary;
                $tag           = $this->extractTag($uri);

                $operation = [
                    'summary'     => $methodSummary ?: strtoupper($methodName) . ' ' . $endpointUri,
                    'tags'        => [$tag],
                    'security'    => [['bearerAuth' => []]],
                    'parameters'  => $this->extractQueryParams($methodDoc ?: $classDoc),
                    'responses'   => $this->buildResponses($methodDoc ?: $classDoc, $methodName),
                ];

                // Add requestBody for mutating methods
                if (in_array($methodName, ['post', 'put', 'patch', 'delete'])) {
                    $bodySchema = $this->extractBodySchema($methodDoc ?: $classDoc);
                    if (!empty($bodySchema['properties'])) {
                        $operation['requestBody'] = [
                            'required' => true,
                            'content'  => [
                                'application/json' => [
                                    'schema' => $bodySchema,
                                ],
                            ],
                        ];
                    }
                }

                // Remove empty parameters array
                if (empty($operation['parameters'])) {
                    unset($operation['parameters']);
                }

                $this->spec['paths'][$endpointUri][$methodName] = $operation;
            }
        } catch (\ReflectionException $e) {
            // Ignore classes that cannot be reflected
        }
    }

    /**
     * Parse @query tags from PHPDoc: @query name type required description
     */
    private function extractQueryParams(string|false $doc): array
    {
        if (!$doc) return [];
        $params = [];
        preg_match_all('/@query\s+(\S+)\s+(\S+)\s+(required|optional)\s*(.*)/i', $doc, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $params[] = [
                'name'        => $m[1],
                'in'          => 'query',
                'required'    => strtolower($m[3]) === 'required',
                'description' => trim($m[4]),
                'schema'      => ['type' => $this->normalizeType($m[2])],
            ];
        }
        return $params;
    }

    /**
     * Parse @body tags from PHPDoc: @body name type required description
     */
    private function extractBodySchema(string|false $doc): array
    {
        if (!$doc) return ['type' => 'object', 'properties' => []];
        $properties = [];
        $required   = [];
        preg_match_all('/@body\s+(\S+)\s+(\S+)\s+(required|optional)\s*(.*)/i', $doc, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $name = $m[1];
            $properties[$name] = [
                'type'        => $this->normalizeType($m[2]),
                'description' => trim($m[4]),
            ];
            if (strtolower($m[3]) === 'required') {
                $required[] = $name;
            }
        }
        $schema = ['type' => 'object', 'properties' => $properties];
        if (!empty($required)) {
            $schema['required'] = $required;
        }
        return $schema;
    }

    /**
     * Build responses map from @response tags or defaults.
     */
    private function buildResponses(string|false $doc, string $method): array
    {
        $responses = [];
        if ($doc) {
            preg_match_all('/@response\s+(\d+)\s*(.*)/i', $doc, $matches, PREG_SET_ORDER);
            foreach ($matches as $m) {
                $responses[$m[1]] = ['description' => trim($m[2]) ?: $this->httpMessage((int) $m[1])];
            }
        }

        if (empty($responses)) {
            $default = $method === 'post' ? '201' : '200';
            $responses[$default] = ['description' => 'Successful operation'];
        }

        // Always add 401 and 422 for documented endpoints
        $responses['401'] = ['description' => 'Unauthorized'];
        $responses['422'] = ['description' => 'Validation error'];

        return $responses;
    }

    /**
     * Normalize PHP type hints to OpenAPI types.
     */
    private function normalizeType(string $type): string
    {
        return match (strtolower($type)) {
            'int', 'integer' => 'integer',
            'bool', 'boolean' => 'boolean',
            'float', 'double', 'number' => 'number',
            'array' => 'array',
            default => 'string',
        };
    }

    /**
     * Map HTTP status code to message.
     */
    private function httpMessage(int $code): string
    {
        return match ($code) {
            200 => 'OK', 201 => 'Created', 400 => 'Bad Request',
            401 => 'Unauthorized', 403 => 'Forbidden', 404 => 'Not Found',
            409 => 'Conflict', 422 => 'Unprocessable Entity',
            429 => 'Too Many Requests', default => 'Internal Server Error',
        };
    }

    /**
     * Extract the first meaningful sentence from a PHPDoc block.
     */
    private function extractSummary(string|false $docComment): string
    {
        if (!$docComment) {
            return '';
        }

        $clean = preg_replace('/^\s*\/?\**\/?/m', '', $docComment);
        $lines = explode("\n", $clean);

        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && !str_starts_with($line, '@') && !preg_match('/^(GET|POST|PUT|PATCH|DELETE)\s+/i', $line)) {
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
