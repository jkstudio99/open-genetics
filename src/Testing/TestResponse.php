<?php

declare(strict_types=1);

namespace OpenGenetics\Testing;

use PHPUnit\Framework\TestCase;

/**
 * 🧬 OpenGenetics — Fluent Test Response
 *
 * Wraps an HTTP response with chainable assertion methods.
 *
 * Usage:
 *   $response = $this->get('/api/users');
 *   $response
 *       ->assertStatus(200)
 *       ->assertJsonHas('success', true)
 *       ->assertJsonStructure(['success', 'data']);
 */
final class TestResponse
{
    private int    $status;
    private array  $json;
    private string $raw;
    private TestCase $testCase;

    public function __construct(int $status, array $json, string $raw, TestCase $testCase)
    {
        $this->status   = $status;
        $this->json     = $json;
        $this->raw      = $raw;
        $this->testCase = $testCase;
    }

    // ─── Getters ─────────────────────────────

    public function status(): int
    {
        return $this->status;
    }

    public function json(?string $key = null): mixed
    {
        if ($key === null) return $this->json;

        $value = $this->json;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }
        return $value;
    }

    public function body(): string
    {
        return $this->raw;
    }

    // ─── Status Assertions ─────────────────────

    public function assertStatus(int $expected): static
    {
        $this->testCase->assertEquals($expected, $this->status,
            "Expected HTTP status {$expected}, got {$this->status}.\nResponse: {$this->raw}"
        );
        return $this;
    }

    public function assertOk(): static
    {
        return $this->assertStatus(200);
    }

    public function assertCreated(): static
    {
        return $this->assertStatus(201);
    }

    public function assertUnauthorized(): static
    {
        return $this->assertStatus(401);
    }

    public function assertForbidden(): static
    {
        return $this->assertStatus(403);
    }

    public function assertNotFound(): static
    {
        return $this->assertStatus(404);
    }

    public function assertUnprocessable(): static
    {
        return $this->assertStatus(422);
    }

    // ─── JSON Assertions ─────────────────────

    /**
     * Assert the response JSON contains a key with the expected value.
     */
    public function assertJsonHas(string $key, mixed $expected): static
    {
        $actual = $this->json($key);
        $this->testCase->assertEquals($expected, $actual,
            "JSON key [{$key}] expected [{$expected}], got [{$actual}]."
        );
        return $this;
    }

    /**
     * Assert the response JSON contains specific keys (any depth).
     */
    public function assertJsonStructure(array $keys, ?array $data = null): static
    {
        $data ??= $this->json;

        foreach ($keys as $key => $value) {
            if (is_array($value)) {
                $this->testCase->assertArrayHasKey($key, $data,
                    "Missing key [{$key}] in JSON response."
                );
                $this->assertJsonStructure($value, $data[$key]);
            } else {
                $this->testCase->assertArrayHasKey($value, $data,
                    "Missing key [{$value}] in JSON response."
                );
            }
        }

        return $this;
    }

    /**
     * Assert the response JSON equals the expected array.
     */
    public function assertExactJson(array $expected): static
    {
        $this->testCase->assertEquals($expected, $this->json);
        return $this;
    }

    /**
     * Assert the successful response contains data.
     */
    public function assertSuccess(): static
    {
        return $this->assertJsonHas('success', true);
    }

    /**
     * Assert the response is a paginated list.
     */
    public function assertPaginated(): static
    {
        return $this->assertJsonStructure(['success', 'data', 'meta' => ['total', 'page', 'per_page', 'total_pages']]);
    }

    /**
     * Assert the data array has a specific count.
     */
    public function assertCount(int $expected): static
    {
        $data = $this->json('data');
        $this->testCase->assertIsArray($data, 'Response data is not an array');
        $this->testCase->assertCount($expected, $data);
        return $this;
    }

    /**
     * Dump the response for debugging.
     */
    public function dump(): static
    {
        echo "\n--- Response (HTTP {$this->status}) ---\n";
        echo json_encode($this->json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo "\n--- End ---\n";
        return $this;
    }
}
