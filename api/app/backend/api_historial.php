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

$fechaInicio = trim($_GET['fecha_inicio'] ?? '');
$fechaFin = trim($_GET['fecha_fin'] ?? '');
$cliente = trim($_GET['cliente'] ?? '');
$factura = trim($_GET['factura'] ?? '');

try {
    $condiciones = [];
    $parametros = [];

    if ($fechaInicio !== '') {
        $condiciones[] = 'DATE(v.fecha_emision) >= ?';
        $parametros[] = $fechaInicio;
    }

    if ($fechaFin !== '') {
        $condiciones[] = 'DATE(v.fecha_emision) <= ?';
        $parametros[] = $fechaFin;
    }

    if ($cliente !== '') {
        $condiciones[] = '(c.nombre_completo LIKE ? OR c.cedula LIKE ?)';
        $parametros[] = "%$cliente%";
        $parametros[] = "%$cliente%";
    }

    if ($factura !== '') {
        $condiciones[] = 'v.id = ?';
        $parametros[] = (int)$factura;
    }

    $where = '';

    if (!empty($condiciones)) {
        $where = 'WHERE ' . implode(' AND ', $condiciones);
    }

    $sql = "
        SELECT
            v.id,
            v.fecha_emision,
            v.total_factura,
            v.estado,
            c.nombre_completo AS cliente,
            c.cedula,
            u.usuario AS cajero
        FROM ventas v
        INNER JOIN clientes c
            ON v.cliente_id = c.id
        INNER JOIN usuarios u
            ON v.usuario_id = u.id
        $where
        ORDER BY v.fecha_emision DESC, v.id DESC
    ";

    $stmt = $connection->prepare($sql);
    $stmt->execute($parametros);
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalVendido = 0;
    $cantidadFacturas = 0;

    foreach ($facturas as $venta) {
        if ($venta['estado'] === 'Pagada') {
            $totalVendido += (float)$venta['total_factura'];
            $cantidadFacturas++;
        }
    }

    $ticketPromedio = $cantidadFacturas > 0
        ? $totalVendido / $cantidadFacturas
        : 0;

    echo json_encode([
        'estado' => 'success',
        'resumen' => [
            'total_vendido' => round($totalVendido, 2),
            'cantidad_facturas' => $cantidadFacturas,
            'ticket_promedio' => round($ticketPromedio, 2)
        ],
        'facturas' => $facturas
    ]);

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Error al consultar el historial: ' . $e->getMessage()
    ]);
}