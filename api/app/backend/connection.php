<?php

declare(strict_types=1);

$host = trim((string) getenv('DB_HOST'));
$port = trim((string) (getenv('DB_PORT') ?: '4000'));
$dbname = trim((string) getenv('DB_NAME'));
$user = trim((string) getenv('DB_USER'));
$password = (string) getenv('DB_PASSWORD');

if (
    $host === '' ||
    $port === '' ||
    $dbname === '' ||
    $user === '' ||
    $password === ''
) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'error' => 'Faltan variables de conexión.',
        'host_configurado' => $host !== '',
        'port_configurado' => $port !== '',
        'database_configurada' => $dbname !== '',
        'user_configurado' => $user !== '',
        'password_configurado' => $password !== ''
    ]);

    exit();
}

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_TIMEOUT => 30
];

try {
    $connection = new PDO(
        $dsn,
        $user,
        $password,
        $options
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'error' => 'Database connection failed',
        'detalle' => $e->getMessage(),
        'host' => $host,
        'port' => $port,
        'database' => $dbname,
        'user' => $user
    ]);

    exit();
}