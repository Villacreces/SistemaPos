<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once 'connection.php';

if (!isset($_SESSION['usuario_activo'])) {
    http_response_code(401);
    echo json_encode(['estado' => 'error', 'mensaje' => 'Sesión no válida']);
    exit();
}
$usuarioId = (int)$_SESSION['usuario_activo']['id'];
$input = json_decode(file_get_contents('php://input'), true);
$productos = $input['productos'] ?? [];
$clienteId = $input['cliente_id'] ?? null;

if (empty($productos)) {
    http_response_code(400);
    echo json_encode(['estado' => 'error', 'mensaje' => 'El carrito está vacío']);
    exit();
}

try {
    $connection->beginTransaction();

    // Usar Consumidor Final si no se escogió cliente
    if (empty($clienteId)) {
        $stmt = $connection->prepare(
            "SELECT id FROM clientes WHERE cedula = '9999999999' LIMIT 1"
        );
        $stmt->execute();
        $clienteId = $stmt->fetchColumn();

        if (!$clienteId) {
            throw new Exception('No existe el cliente Consumidor Final');
        }
    }

    $subtotal = 0;
    $detalles = [];

    foreach ($productos as $producto) {
        $productoId = (int)($producto['id'] ?? 0);
        $cantidad = (int)($producto['cantidad'] ?? 0);

        if ($productoId <= 0 || $cantidad <= 0) {
            throw new Exception('Datos de producto incorrectos');
        }

        $stmt = $connection->prepare(
            "SELECT nombre_producto, precio_actual, stock_disponible
             FROM productos
             WHERE id = ?
             FOR UPDATE"
        );
        $stmt->execute([$productoId]);
        $productoDB = $stmt->fetch();

        if (!$productoDB) {
            throw new Exception("El producto $productoId no existe");
        }

        if ($productoDB['stock_disponible'] < $cantidad) {
            throw new Exception(
                "Stock insuficiente para {$productoDB['nombre_producto']}"
            );
        }

        $precio = (float)$productoDB['precio_actual'];
        $subtotal += $precio * $cantidad;

        $detalles[] = [
            'producto_id' => $productoId,
            'cantidad' => $cantidad,
            'precio' => $precio
        ];
    }

    $iva = $subtotal * 0.15;
    $total = $subtotal + $iva;

   $stmt = $connection->prepare(
    "INSERT INTO ventas
     (cliente_id, usuario_id, total_factura, estado)
     VALUES (?, ?, ?, 'Pagada')"
);

$stmt->execute([
    $clienteId,
    $usuarioId,
    $total
]);

    $ventaId = (int)$connection->lastInsertId();

    $insertDetalle = $connection->prepare(
        "INSERT INTO detalles_venta
         (venta_id, producto_id, cantidad, precio_congelado)
         VALUES (?, ?, ?, ?)"
    );

    $actualizarStock = $connection->prepare(
        "UPDATE productos
         SET stock_disponible = stock_disponible - ?
         WHERE id = ?"
    );

    foreach ($detalles as $detalle) {
        $insertDetalle->execute([
            $ventaId,
            $detalle['producto_id'],
            $detalle['cantidad'],
            $detalle['precio']
        ]);

        $actualizarStock->execute([
            $detalle['cantidad'],
            $detalle['producto_id']
        ]);
    }

    $connection->commit();

    echo json_encode([
        'estado' => 'success',
        'mensaje' => 'Venta procesada correctamente',
        'venta_id' => $ventaId,
        'subtotal' => round($subtotal, 2),
        'iva' => round($iva, 2),
        'total' => round($total, 2)
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