<?php
declare(strict_types=1);

// cabecera de intercambio de JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require('connection.php');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            $search = $_GET['q'] ?? '';

            $sql = "SELECT * FROM productos
                    WHERE nombre_producto LIKE ?
                    OR codigo_barras LIKE ?";

            $stmt = $connection->prepare($sql);
            $stmt->execute(["%$search%", "%$search%"]);

            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            $sql = "INSERT INTO productos
                    (codigo_barras, nombre_producto, precio_actual, stock_disponible)
                    VALUES (?, ?, ?, ?)";

            $stmt = $connection->prepare($sql);
            $stmt->execute([
                $input['codigo'],
                $input['nombre'],
                $input['precio'],
                $input['stock']
            ]);

            echo json_encode([
                'estado' => 'success',
                'mensaje' => 'Producto agregado correctamente'
            ]);
            break;

        case 'PUT':
            $sql = "UPDATE productos
                    SET nombre_producto = ?,
                        precio_actual = ?,
                        stock_disponible = ?
                    WHERE id = ?";

            $stmt = $connection->prepare($sql);
            $stmt->execute([
                $input['nombre'],
                $input['precio'],
                $input['stock'],
                $input['id']
            ]);

            echo json_encode([
                'estado' => 'success',
                'mensaje' => 'Producto actualizado correctamente'
            ]);
            break;

        case 'DELETE':
            $id = $_GET['id'] ?? 0;

            $stmt = $connection->prepare(
                "DELETE FROM productos WHERE id = ?"
            );

            $stmt->execute([$id]);

            echo json_encode([
                'estado' => 'success',
                'mensaje' => 'Producto eliminado correctamente'
            ]);
            break;

        default:
            http_response_code(405);

            echo json_encode([
                'estado' => 'error',
                'mensaje' => 'METHOD NOT ALLOWED'
            ]);
            break;
    }

} catch (PDOException $e) {
    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'ERROR IN DATABASE CONNECTION: ' . $e->getMessage()
    ]);
}
?>
