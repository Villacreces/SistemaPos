<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim((string) $path, '/');

$basePath = __DIR__ . '/app';

$routes = [
    '' => $basePath . '/pages/index.php',
    'index.php' => $basePath . '/pages/index.php',

    'dashboard.php' => $basePath . '/pages/dashboard.php',
    'catalogo.php' => $basePath . '/pages/catalogo.php',
    'clientes.php' => $basePath . '/pages/clientes.php',
    'factura.php' => $basePath . '/pages/factura.php',
    'historial.php' => $basePath . '/pages/historial.php',
    'pos.php' => $basePath . '/pages/pos.php',

    'backend/process_login.php' =>
        $basePath . '/backend/process_login.php',

    'backend/logout.php' =>
        $basePath . '/backend/logout.php',

    'backend/test_network.php' =>
        $basePath . '/backend/test_network.php',

    'backend/api_productos.php' =>
        $basePath . '/backend/api_productos.php',

    'backend/api_clientes.php' =>
        $basePath . '/backend/api_clientes.php',

    'backend/crud_clientes.php' =>
        $basePath . '/backend/crud_clientes.php',

    'backend/procesar_venta.php' =>
        $basePath . '/backend/procesar_venta.php',

    'backend/api_historial.php' =>
        $basePath . '/backend/api_historial.php',

    'backend/api_detalle_venta.php' =>
        $basePath . '/backend/api_detalle_venta.php',

    'backend/anular_venta.php' =>
        $basePath . '/backend/anular_venta.php',
];

if (!array_key_exists($path, $routes)) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'error' => 'Ruta no registrada',
        'ruta_solicitada' => $path
    ]);

    exit();
}

$file = $routes[$path];

if (!is_file($file)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'error' => 'Archivo no encontrado',
        'ruta_solicitada' => $path,
        'archivo_buscado' => $file
    ]);

    exit();
}

require $file;