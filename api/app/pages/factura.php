<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: /index.php');
    exit();
}

require_once __DIR__ . '/../backend/connection.php';

$ventaId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$ventaId) {
    die('ID de venta no válido.');
}

$stmt = $connection->prepare(
    "SELECT v.id, v.total_factura, v.fecha_emision,
            c.cedula, c.nombre_completo, c.correo
     FROM ventas v
     INNER JOIN clientes c ON v.cliente_id = c.id
     WHERE v.id = ?"
);
$stmt->execute([$ventaId]);
$venta = $stmt->fetch();

if (!$venta) {
    die('La venta no existe.');
}

$stmt = $connection->prepare(
    "SELECT p.codigo_barras, p.nombre_producto,
            dv.cantidad, dv.precio_congelado,
            dv.cantidad * dv.precio_congelado AS subtotal
     FROM detalles_venta dv
     INNER JOIN productos p ON dv.producto_id = p.id
     WHERE dv.venta_id = ?"
);
$stmt->execute([$ventaId]);
$detalles = $stmt->fetchAll();

$subtotal = 0;

foreach ($detalles as $detalle) {
    $subtotal += (float) $detalle['subtotal'];
}

$iva = $subtotal * 0.15;
$total = (float) $venta['total_factura'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Factura N.º <?= $venta['id'] ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --verde-oscuro: #1b4332;
            --verde-medio: #2a5f4a;
            --verde-claro: #d8f3dc;
            --gris-fondo: #f4f6f9;
        }

        body {
            margin: 0;
            padding: 25px;
            background: var(--gris-fondo);
            font-family: "Segoe UI", Arial, sans-serif;
            color: #263238;
        }

        .factura {
            max-width: 900px;
            margin: auto;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 22px rgba(0, 0, 0, .12);
        }

        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 35px;
            background: var(--verde-oscuro);
            color: #fff;
        }

        .marca h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .marca p,
        .numero-factura p {
            margin: 4px 0 0;
            opacity: .85;
        }

        .numero-factura {
            text-align: right;
        }

        .numero-factura h2 {
            margin: 0;
            font-size: 1.45rem;
        }

        .contenido {
            padding: 32px 35px;
        }

        .datos-superiores {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }

        .tarjeta-datos {
            padding: 20px;
            background: #f8faf9;
            border-left: 5px solid var(--verde-medio);
            border-radius: 8px;
        }

        .tarjeta-datos h5 {
            margin-bottom: 14px;
            color: var(--verde-oscuro);
            font-weight: 700;
        }

        .tarjeta-datos p {
            margin: 5px 0;
        }

        .estado {
            display: inline-block;
            padding: 5px 14px;
            background: var(--verde-claro);
            color: var(--verde-oscuro);
            border-radius: 20px;
            font-weight: 700;
        }

        table {
            margin-bottom: 0 !important;
        }

        thead th {
            padding: 13px !important;
            background: var(--verde-medio) !important;
            color: #fff !important;
            border-color: var(--verde-medio) !important;
        }

        tbody td {
            padding: 13px !important;
            vertical-align: middle;
        }

        .seccion-totales {
            display: flex;
            justify-content: flex-end;
            margin-top: 25px;
        }

        .totales {
            width: 360px;
            padding: 20px;
            background: #f8faf9;
            border-radius: 9px;
        }

        .fila-total {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-final {
            margin-top: 12px;
            padding-top: 13px;
            border-top: 2px solid var(--verde-medio);
            color: var(--verde-oscuro);
            font-size: 1.35rem;
            font-weight: 700;
        }

        .pie-factura {
            padding: 22px 35px;
            background: #f1f5f3;
            text-align: center;
            color: #60706a;
        }

        .acciones {
            max-width: 900px;
            margin: 20px auto 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        @media (max-width: 700px) {
            body {
                padding: 0;
            }

            .factura {
                border-radius: 0;
            }

            .encabezado,
            .datos-superiores {
                display: block;
            }

            .numero-factura {
                margin-top: 20px;
                text-align: left;
            }

            .tarjeta-datos {
                margin-bottom: 15px;
            }

            .totales {
                width: 100%;
            }
        }

        @media print {
            @page {
                margin: 10mm;
            }

            body {
                padding: 0;
                background: #fff;
            }

            .factura {
                max-width: 100%;
                box-shadow: none;
                border-radius: 0;
            }

            .acciones {
                display: none;
            }

            thead {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .encabezado,
            thead th {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <article class="factura">
        <header class="encabezado">
            <div class="marca">
                <h1>Sistema POS</h1>
                <p>Comprobante de venta</p>
            </div>

            <div class="numero-factura">
                <h2>Factura N.º <?= str_pad((string) $venta['id'], 6, '0', STR_PAD_LEFT) ?></h2>
                <p><?= date('d/m/Y H:i', strtotime($venta['fecha_emision'])) ?></p>
            </div>
        </header>

        <main class="contenido">
            <section class="datos-superiores">
                <div class="tarjeta-datos">
                    <h5>Información del cliente</h5>

                    <p>
                        <strong>Nombre:</strong>
                            <?= htmlspecialchars($venta['nombre_completo']) ?>
                    </p>

                    <p>
                        <strong>Cédula:</strong>
                            <?= htmlspecialchars($venta['cedula']) ?>
                    </p>

                    <p>
                        <strong>Correo:</strong>
                            <?= htmlspecialchars($venta['correo'] ?: 'No registrado') ?>
                    </p>
                </div>

                <div class="tarjeta-datos">
                    <h5>Información de la venta</h5>

                    <p>
                        <strong>Fecha:</strong>
                            <?= date('d/m/Y', strtotime($venta['fecha_emision'])) ?>
                    </p>

                    <p>
                        <strong>Hora:</strong>
                            <?= date('H:i:s', strtotime($venta['fecha_emision'])) ?>
                    </p>

                    <p>
                        <strong>Estado:</strong>
                        <span class="estado">Pagada</span>
                    </p>
                </div>
            </section>

            <section class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio unitario</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>
                            <?php foreach ($detalles as $detalle): ?>
                            <tr>
                                <td>
                                        <?= htmlspecialchars($detalle['nombre_producto']) ?>
                                </td>

                                <td>
                                        <?= htmlspecialchars($detalle['codigo_barras']) ?>
                                </td>

                                <td class="text-center">
                                        <?= (int) $detalle['cantidad'] ?>
                                </td>

                                <td class="text-end">
                                    $<?= number_format((float) $detalle['precio_congelado'], 2) ?>
                                </td>

                                <td class="text-end fw-semibold">
                                    $<?= number_format((float) $detalle['subtotal'], 2) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="seccion-totales">
                <div class="totales">
                    <div class="fila-total">
                        <span>Subtotal</span>
                        <strong>$<?= number_format($subtotal, 2) ?></strong>
                    </div>

                    <div class="fila-total">
                        <span>IVA 15%</span>
                        <strong>$<?= number_format($iva, 2) ?></strong>
                    </div>

                    <div class="fila-total total-final">
                        <span>Total pagado</span>
                        <span>$<?= number_format($total, 2) ?></span>
                    </div>
                </div>
            </section>
        </main>

        <footer class="pie-factura">
            <strong>Gracias por su compra.</strong>
            <div>Este documento corresponde a la venta N.º <?= $venta['id'] ?>.</div>
        </footer>
    </article>

    <div class="acciones">
        <button type="button" class="btn btn-success px-4" onclick="window.print()">
            Imprimir o guardar PDF
        </button>

        <a href="/pos.php" class="btn btn-secondary px-4">
            Volver al POS
        </a>
    </div>

</body>

</html>