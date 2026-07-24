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

$ventaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$ventaId) {
    http_response_code(400);
    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'ID de factura no válido'
    ]);
    exit();
}

try {
    $stmt = $connection->prepare(
        "SELECT
            v.id,
            v.fecha_emision,
            v.total_factura,
            v.estado,
            c.nombre_completo AS cliente,
            c.cedula,
            u.usuario AS cajero
         FROM ventas v
         INNER JOIN clientes c ON v.cliente_id = c.id
         INNER JOIN usuarios u ON v.usuario_id = u.id
         WHERE v.id = ?"
    );

    $stmt->execute([$ventaId]);
    $venta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venta) {
        http_response_code(404);
        echo json_encode([
            'estado' => 'error',
            'mensaje' => 'La factura no existe'
        ]);
        exit();
    }

    $stmt = $connection->prepare(
        "SELECT
            p.codigo_barras,
            p.nombre_producto,
            dv.cantidad,
            dv.precio_congelado,
            dv.cantidad * dv.precio_congelado AS subtotal
         FROM detalles_venta dv
         INNER JOIN productos p ON dv.producto_id = p.id
         WHERE dv.venta_id = ?
         ORDER BY dv.id"
    );

    $stmt->execute([$ventaId]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'estado' => 'success',
        'venta' => $venta,
        'detalles' => $detalles
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error al consultar la factura: ' . $e->getMessage()
    ]);
}