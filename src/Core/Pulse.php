<?php

declare(strict_types=1);

namespace OpenGenetics\Core;

/**
 * Pulse — Server-Sent Events / Real-time Push (OpenGenetics v2.0)
 *
 * Provides real-time streaming to browsers via SSE (EventSource API).
 * No WebSocket server needed — works over plain HTTP.
 *
 * Usage in an endpoint (e.g. api/events.php):
 *
 *   Pulse::stream(function (Pulse $pulse) {
 *       while (true) {
 *           $notifications = Notification::pending();
 *           foreach ($notifications as $n) {
 *               $pulse->send($n, 'notification');
 *           }
 *           $pulse->heartbeat();
 *           sleep(2);
 *       }
 *   });
 *
 * Client (JavaScript):
 *   const es = new EventSource('/api/events');
 *   es.addEventListener('notification', e => console.log(JSON.parse(e.data)));
 *   es.addEventListener('heartbeat', e => console.log('alive'));
 */
final class Pulse
{
    private int $heartbeatInterval;
    private int $lastHeartbeat;
    private ?string $lastEventId;
    private int $eventCounter = 0;

    public function __construct(int $heartbeatInterval = 15)
    {
        $this->heartbeatInterval = $heartbeatInterval;
        $this->lastHeartbeat     = time();
        $this->lastEventId       = $_SERVER['HTTP_LAST_EVENT_ID'] ?? null;
    }

    // ── Stream bootstrap ──────────────────────────────────

    /**
     * Bootstrap SSE headers and run the provided callback in a streaming loop.
     * The callback receives a Pulse instance for sending events.
     *
     * @param callable $callback function(Pulse $pulse): void
     * @param int      $heartbeatInterval seconds between heartbeats
     * @param int      $maxRuntime maximum seconds before closing (0 = unlimited)
     */
    public static function stream(
        callable $callback,
        int $heartbeatInterval = 15,
        int $maxRuntime = 0
    ): never {
        self::headers();

        $pulse     = new static($heartbeatInterval);
        $startTime = time();

        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }

        set_time_limit(0);
        ignore_user_abort(true);

        $callback($pulse);

        // If callback returns, close gracefully
        $pulse->close();
        exit;
    }

    /**
     * Send SSE response headers.
     */
    public static function headers(): void
    {
        // Kill any buffering
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream; charset=UTF-8');
        header('Cache-Control: no-cache, no-store');
        header('X-Accel-Buffering: no');         // Disable nginx buffering
        header('Connection: keep-alive');
        header('Access-Control-Allow-Origin: *');
    }

    // ── Event sending ─────────────────────────────────────

    /**
     * Send an event to the connected client.
     *
     * @param mixed  $data    Data to send (will be JSON-encoded if array/object)
     * @param string $event   Event type (default: 'message')
     * @param string|null $id Event ID for client reconnection
     */
    public function send(mixed $data, string $event = 'message', ?string $id = null): void
    {
        $this->eventCounter++;
        $id ??= (string) $this->eventCounter;

        $payload = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_UNICODE);

        // Multi-line data support
        $lines = explode("\n", $payload);

        echo "id: {$id}\n";
        echo "event: {$event}\n";
        foreach ($lines as $line) {
            echo "data: {$line}\n";
        }
        echo "\n"; // End of event

        $this->flush();
        $this->lastHeartbeat = time();
    }

    /**
     * Send a heartbeat to keep the connection alive.
     * Automatically called if heartbeat interval is exceeded.
     */
    public function heartbeat(): void
    {
        echo ": heartbeat\n\n";
        $this->flush();
        $this->lastHeartbeat = time();
    }

    /**
     * Check if heartbeat is due and send it if needed.
     * Call this inside your loop.
     */
    public function checkHeartbeat(): void
    {
        if (time() - $this->lastHeartbeat >= $this->heartbeatInterval) {
            $this->heartbeat();
        }
    }

    /**
     * Send retry instruction to client (ms).
     * Tells EventSource how long to wait before reconnecting.
     */
    public function retry(int $milliseconds): void
    {
        echo "retry: {$milliseconds}\n\n";
        $this->flush();
    }

    /**
     * Close the stream gracefully.
     */
    public function close(): void
    {
        $this->send(['type' => 'close', 'message' => 'Stream closed'], 'close');
    }

    // ── Connection helpers ────────────────────────────────

    /**
     * Check if the client is still connected.
     */
    public function isConnected(): bool
    {
        return !connection_aborted();
    }

    /**
     * Get the Last-Event-ID sent by the client (for resume support).
     */
    public function lastEventId(): ?string
    {
        return $this->lastEventId;
    }

    /**
     * Get number of events sent in this session.
     */
    public function eventCount(): int
    {
        return $this->eventCounter;
    }

    // ── Broadcast helpers ─────────────────────────────────

    /**
     * Send a broadcast message to a channel stored in a file queue.
     * Use this from non-SSE endpoints to publish events.
     *
     * Pulse::broadcast('chat', ['user' => 'John', 'msg' => 'Hello']);
     */
    public static function broadcast(string $channel, mixed $data): void
    {
        $dir  = dirname(__DIR__, 2) . '/storage/pulse';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file    = $dir . '/' . md5($channel) . '.events';
        $payload = json_encode([
            'channel'   => $channel,
            'data'      => $data,
            'timestamp' => microtime(true),
        ]) . "\n";

        file_put_contents($file, $payload, FILE_APPEND | LOCK_EX);
    }

    /**
     * Consume and clear events from a channel queue.
     * Used inside SSE stream loops.
     *
     * @return array List of event payloads
     */
    public static function consume(string $channel): array
    {
        $dir  = dirname(__DIR__, 2) . '/storage/pulse';
        $file = $dir . '/' . md5($channel) . '.events';

        if (!file_exists($file)) {
            return [];
        }

        // Atomic read + clear
        $fp = fopen($file, 'r+');
        if (!$fp) {
            return [];
        }

        flock($fp, LOCK_EX);
        $lines = [];
        while (!feof($fp)) {
            $line = trim(fgets($fp));
            if ($line !== '') {
                $decoded = json_decode($line, true);
                if ($decoded) {
                    $lines[] = $decoded;
                }
            }
        }
        ftruncate($fp, 0);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $lines;
    }

    // ── Internal ──────────────────────────────────────────

    private function flush(): void
    {
        if (function_exists('fastcgi_finish_request')) {
            // Not used for SSE — just flush
        }
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }
}
