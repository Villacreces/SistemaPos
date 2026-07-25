<?php

declare(strict_types=1);

$host = getenv('DB_HOST');
$port = getenv('DB_PORT') ?: '4000';
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

if (
    !$host ||
    !$dbname ||
    !$user ||
    $password === false ||
    $password === ''
) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'error' => 'Faltan variables de entorno para conectar con la base de datos.'
    ]);

    exit();
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $host,
    $port,
    $dbname
);

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::ATTR_TIMEOUT => 15
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
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);

    exit();
}