<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$host = trim((string) getenv('DB_HOST'));
$port = (int) (getenv('DB_PORT') ?: 4000);

$ip = gethostbyname($host);

$errno = 0;
$error = '';

$start = microtime(true);

$socket = @fsockopen(
    $host,
    $port,
    $errno,
    $error,
    15
);

$duration = round(microtime(true) - $start, 2);

if (is_resource($socket)) {
    fclose($socket);

    echo json_encode([
        'estado' => 'ok',
        'host' => $host,
        'ip_resuelta' => $ip,
        'port' => $port,
        'tiempo' => $duration
    ]);

    exit();
}

http_response_code(500);

echo json_encode([
    'estado' => 'error',
    'host' => $host,
    'ip_resuelta' => $ip,
    'port' => $port,
    'codigo' => $errno,
    'detalle' => $error,
    'tiempo' => $duration
]);