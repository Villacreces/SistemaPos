<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/connection.php';

if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Sesión no válida.'
    ]);

    exit();
}

$busqueda = trim($_GET['q'] ?? '');

if ($busqueda === '') {
    echo json_encode([]);
    exit();
}

try {
    $stmt = $connection->prepare(
        'SELECT
            id,
            cedula,
            nombre_completo,
            correo
        FROM clientes
        WHERE nombre_completo LIKE :nombre
           OR cedula LIKE :cedula
           OR correo LIKE :correo
        ORDER BY nombre_completo
        LIMIT 10'
    );

    $valor = '%' . $busqueda . '%';

    $stmt->execute([
        ':nombre' => $valor,
        ':cedula' => $valor,
        ':correo' => $valor
    ]);

    echo json_encode(
        $stmt->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );

} catch (PDOException $error) {
    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'No se pudieron cargar los clientes.',
        'detalle' => $error->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}