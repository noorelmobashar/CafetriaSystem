<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    db()->query('SELECT 1');

    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'message' => 'Database connection established successfully.',
        'driver' => (string) env('DB_CONNECTION', 'mysql'),
        'database' => (string) env('DB_DATABASE', ''),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.',
        'details' => (bool) env('APP_DEBUG', false) ? $exception->getMessage() : null,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}