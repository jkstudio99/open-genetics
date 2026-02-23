<?php

declare(strict_types=1);

namespace OpenGenetics\Testing;

use OpenGenetics\Core\Env;
use OpenGenetics\Core\Database;
use OpenGenetics\Auth\JwtManager;

/**
 * 🧬 OpenGenetics — Genetic Test Case
 *
 * Base class for API integration tests.
 * Provides HTTP simulation, authentication helpers, and fluent assertions.
 *
 * Usage:
 *   class UsersTest extends GeneticTestCase {
 *       public function testListUsers(): void {
 *           $this->actingAs(['id' => 1, 'email' => 'admin@test.io', 'role_name' => 'ADMIN']);
 *           $response = $this->get('/api/users');
 *           $response->assertStatus(200)->assertJsonHas('success', true);
 *       }
 *   }
 */
abstract class GeneticTestCase extends \PHPUnit\Framework\TestCase
{
    protected ?string $token = null;
    protected ?array  $authUser = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->token    = null;
        $this->authUser = null;
    }

    // ─── HTTP Methods ─────────────────────────────

    /**
     * Simulate a GET request to an endpoint.
     */
    protected function get(string $uri, array $headers = []): TestResponse
    {
        return $this->request('GET', $uri, [], $headers);
    }

    /**
     * Simulate a POST request with JSON body.
     */
    protected function post(string $uri, array $body = [], array $headers = []): TestResponse
    {
        return $this->request('POST', $uri, $body, $headers);
    }

    /**
     * Simulate a PUT request.
     */
    protected function put(string $uri, array $body = [], array $headers = []): TestResponse
    {
        return $this->request('PUT', $uri, $body, $headers);
    }

    /**
     * Simulate a PATCH request.
     */
    protected function patch(string $uri, array $body = [], array $headers = []): TestResponse
    {
        return $this->request('PATCH', $uri, $body, $headers);
    }

    /**
     * Simulate a DELETE request.
     */
    protected function delete(string $uri, array $body = [], array $headers = []): TestResponse
    {
        return $this->request('DELETE', $uri, $body, $headers);
    }

    // ─── Authentication ─────────────────────────────

    /**
     * Act as an authenticated user for subsequent requests.
     *
     * @param array $user User array with at least: id, email, role_name
     */
    protected function actingAs(array $user): static
    {
        // Set defaults
        $user = array_merge([
            'id'        => 1,
            'email'     => 'test@opengenetics.io',
            'role_id'   => 1,
            'role_name' => 'ADMIN',
            'tenant_id' => null,
        ], $user);

        $this->authUser = $user;
        $this->token    = JwtManager::encode($user);

        return $this;
    }

    /**
     * Act as the default admin user.
     */
    protected function actingAsAdmin(): static
    {
        return $this->actingAs([
            'id'        => 1,
            'email'     => 'admin@opengenetics.io',
            'role_name' => 'ADMIN',
        ]);
    }

    /**
     * Clear authentication for subsequent requests.
     */
    protected function asGuest(): static
    {
        $this->token    = null;
        $this->authUser = null;
        return $this;
    }

    // ─── Assertions ─────────────────────────────

    /**
     * Assert that a database table has a row matching the given conditions.
     */
    protected function assertDatabaseHas(string $table, array $conditions): void
    {
        $where  = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $where[]       = "`{$key}` = :{$key}";
            $params[$key]  = $value;
        }

        $whereStr = implode(' AND ', $where);
        $rows = Database::query("SELECT COUNT(*) as cnt FROM `{$table}` WHERE {$whereStr}", $params);
        $count = (int) ($rows[0]['cnt'] ?? 0);

        $this->assertGreaterThan(0, $count,
            "Failed asserting that table [{$table}] has a row matching: " . json_encode($conditions)
        );
    }

    /**
     * Assert that a database table does NOT have a row matching conditions.
     */
    protected function assertDatabaseMissing(string $table, array $conditions): void
    {
        $where  = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $where[]       = "`{$key}` = :{$key}";
            $params[$key]  = $value;
        }

        $whereStr = implode(' AND ', $where);
        $rows = Database::query("SELECT COUNT(*) as cnt FROM `{$table}` WHERE {$whereStr}", $params);
        $count = (int) ($rows[0]['cnt'] ?? 0);

        $this->assertEquals(0, $count,
            "Failed asserting that table [{$table}] is missing a row matching: " . json_encode($conditions)
        );
    }

    // ─── Internal ─────────────────────────────

    /**
     * Build and send an HTTP request, capturing the JSON response.
     */
    private function request(string $method, string $uri, array $body = [], array $headers = []): TestResponse
    {
        // Add auth header if acting as a user
        if ($this->token !== null) {
            $headers['Authorization'] = "Bearer {$this->token}";
        }

        // Build curl request to the local dev server
        $baseUrl = Env::get('APP_URL', 'http://127.0.0.1:8080');
        $url     = rtrim($baseUrl, '/') . '/' . ltrim($uri, '/');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        // Headers
        $curlHeaders = ['Content-Type: application/json', 'Accept: application/json'];
        foreach ($headers as $key => $value) {
            $curlHeaders[] = "{$key}: {$value}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

        // Body
        if (!empty($body) && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $rawBody    = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error      = curl_error($ch);
        curl_close($ch);

        if ($rawBody === false) {
            $this->fail("HTTP request failed: {$error}");
        }

        $json = json_decode($rawBody, true);

        return new TestResponse($statusCode, $json ?? [], $rawBody, $this);
    }
}
