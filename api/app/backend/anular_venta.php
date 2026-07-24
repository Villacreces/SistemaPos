<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'connection.php';

if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Sesión no válida'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$ventaId = (int)($input['venta_id'] ?? 0);

if ($ventaId <= 0) {
    http_response_code(400);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'ID de factura no válido'
    ]);
    exit();
}

try {
    $connection->beginTransaction();

    $stmt = $connection->prepare(
        "SELECT estado
         FROM ventas
         WHERE id = ?
         FOR UPDATE"
    );

    $stmt->execute([$ventaId]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        throw new Exception('La factura no existe.');
    }

    if ($venta['estado'] === 'Anulada') {
        throw new Exception('La factura ya fue anulada.');
    }

    $stmt = $connection->prepare(
        "SELECT producto_id, cantidad
         FROM detalles_venta
         WHERE venta_id = ?"
    );

    $stmt->execute([$ventaId]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($detalles)) {
        throw new Exception('La factura no tiene productos registrados.');
    }

    $actualizarStock = $connection->prepare(
        "UPDATE productos
         SET stock_disponible = stock_disponible + ?
         WHERE id = ?"
    );

    foreach ($detalles as $detalle) {
        $actualizarStock->execute([
            (int)$detalle['cantidad'],
            (int)$detalle['producto_id']
        ]);
    }

    $stmt = $connection->prepare(
        "UPDATE ventas
         SET estado = 'Anulada'
         WHERE id = ?"
    );

    $stmt->execute([$ventaId]);

    $connection->commit();

    echo json_encode([
        'estado' => 'success',
        'mensaje' => 'Factura anulada y stock devuelto correctamente.'
    ]);

} catch (Throwable $e) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => $e->getMessage()
    ]);
}