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

$metodo = $_SERVER['REQUEST_METHOD'];

try {
    switch ($metodo) {
        case 'GET':
            listarClientes($connection);
            break;

        case 'POST':
            registrarCliente($connection);
            break;

        case 'PUT':
            modificarCliente($connection);
            break;

        case 'DELETE':
            eliminarCliente($connection);
            break;

        default:
            http_response_code(405);

            echo json_encode([
                'estado' => 'error',
                'mensaje' => 'Método no permitido.'
            ]);
    }
} catch (PDOException $error) {
    http_response_code(500);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => 'Ocurrió un error en la base de datos.',
        'detalle' => $error->getMessage()
    ]);
}

function listarClientes(PDO $connection): void
{
    $busqueda = trim($_GET['q'] ?? '');

    if ($busqueda === '') {
        $consulta = $connection->prepare(
            'SELECT
                id,
                cedula,
                nombre_completo,
                correo,
                fecha_registro
             FROM clientes
             ORDER BY id DESC'
        );

        $consulta->execute();
    } else {
        $valor = '%' . $busqueda . '%';

        $consulta = $connection->prepare(
            'SELECT
                id,
                cedula,
                nombre_completo,
                correo,
                fecha_registro
             FROM clientes
             WHERE cedula LIKE :cedula
                OR nombre_completo LIKE :nombre
                OR correo LIKE :correo
             ORDER BY nombre_completo ASC'
        );

        $consulta->execute([
            ':cedula' => $valor,
            ':nombre' => $valor,
            ':correo' => $valor
        ]);
    }

    echo json_encode(
        $consulta->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );
}

function registrarCliente(PDO $connection): void
{
    $datos = obtenerDatosJSON();

    $cedula = trim($datos['cedula'] ?? '');
    $nombre = trim($datos['nombre_completo'] ?? '');
    $correo = trim($datos['correo'] ?? '');

    validarCliente($cedula, $nombre, $correo);

    comprobarCedulaExistente($connection, $cedula);

    $consulta = $connection->prepare(
        'INSERT INTO clientes (
            cedula,
            nombre_completo,
            correo,
            fecha_registro
        ) VALUES (
            :cedula,
            :nombre,
            :correo,
            NOW()
        )'
    );

    $consulta->execute([
        ':cedula' => $cedula,
        ':nombre' => $nombre,
        ':correo' => $correo !== '' ? $correo : null
    ]);

    http_response_code(201);

    echo json_encode([
        'estado' => 'success',
        'mensaje' => 'Cliente registrado correctamente.',
        'cliente_id' => (int) $connection->lastInsertId()
    ]);
}

function modificarCliente(PDO $connection): void
{
    $datos = obtenerDatosJSON();

    $id = (int) ($datos['id'] ?? 0);
    $cedula = trim($datos['cedula'] ?? '');
    $nombre = trim($datos['nombre_completo'] ?? '');
    $correo = trim($datos['correo'] ?? '');

    if ($id <= 0) {
        responderError('El identificador del cliente no es válido.');
    }

    validarCliente($cedula, $nombre, $correo);

    comprobarCedulaExistente($connection, $cedula, $id);

    $consulta = $connection->prepare(
        'UPDATE clientes
         SET cedula = :cedula,
             nombre_completo = :nombre,
             correo = :correo
         WHERE id = :id'
    );

    $consulta->execute([
        ':cedula' => $cedula,
        ':nombre' => $nombre,
        ':correo' => $correo !== '' ? $correo : null,
        ':id' => $id
    ]);

    if ($consulta->rowCount() === 0) {
        $comprobar = $connection->prepare(
            'SELECT id
             FROM clientes
             WHERE id = :id'
        );

        $comprobar->execute([
            ':id' => $id
        ]);

        if (!$comprobar->fetch()) {
            responderError('El cliente no existe.', 404);
        }
    }

    echo json_encode([
        'estado' => 'success',
        'mensaje' => 'Cliente modificado correctamente.'
    ]);
}

function eliminarCliente(PDO $connection): void
{
    $datos = obtenerDatosJSON();

    $id = (int) ($datos['id'] ?? 0);

    if ($id <= 0) {
        responderError('El identificador del cliente no es válido.');
    }

    $consulta = $connection->prepare(
        'DELETE FROM clientes
         WHERE id = :id'
    );

    $consulta->execute([
        ':id' => $id
    ]);

    if ($consulta->rowCount() === 0) {
        responderError('El cliente no existe.', 404);
    }

    echo json_encode([
        'estado' => 'success',
        'mensaje' => 'Cliente eliminado correctamente.'
    ]);
}

function validarCliente(
    string $cedula,
    string $nombre,
    string $correo
): void {
    if (!preg_match('/^[0-9]{10}$/', $cedula)) {
        responderError(
            'La cédula debe contener exactamente 10 números.'
        );
    }

    if ($nombre === '') {
        responderError('El nombre completo es obligatorio.');
    }

    if (mb_strlen($nombre) < 3) {
        responderError(
            'El nombre debe contener al menos 3 caracteres.'
        );
    }

    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responderError('El correo electrónico no es válido.');
    }
}

function comprobarCedulaExistente(
    PDO $connection,
    string $cedula,
    ?int $idExcluir = null
): void {
    if ($idExcluir === null) {
        $consulta = $connection->prepare(
            'SELECT id
             FROM clientes
             WHERE cedula = :cedula'
        );

        $consulta->execute([
            ':cedula' => $cedula
        ]);
    } else {
        $consulta = $connection->prepare(
            'SELECT id
             FROM clientes
             WHERE cedula = :cedula
               AND id <> :id'
        );

        $consulta->execute([
            ':cedula' => $cedula,
            ':id' => $idExcluir
        ]);
    }

    if ($consulta->fetch()) {
        responderError(
            'Ya existe un cliente registrado con esa cédula.'
        );
    }
}

function obtenerDatosJSON(): array
{
    $contenido = file_get_contents('php://input');
    $datos = json_decode($contenido, true);

    if (!is_array($datos)) {
        responderError('Los datos enviados no son válidos.');
    }

    return $datos;
}

function responderError(
    string $mensaje,
    int $codigo = 400
): void {
    http_response_code($codigo);

    echo json_encode([
        'estado' => 'error',
        'mensaje' => $mensaje
    ]);

    exit();
}