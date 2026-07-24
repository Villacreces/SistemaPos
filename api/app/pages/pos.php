<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: /index.php');
    exit();
}

$usuario = $_SESSION['usuario_activo'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Punto de venta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/frontend/css/dashboard.css">
</head>

<body>

    <?php
    require_once __DIR__ . '/../backend/includes/sidebar.php';
    ?>

    <main id="content" style="margin-left: 250px; width: calc(100% - 250px);">
        <nav class="navbar bg-white shadow-sm mb-4 p-3">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h4 text-secondary">Punto de venta</span>
                <span class="fw-bold" style="color: var(--verdeO);">
                    <?= htmlspecialchars($usuario['usuario']) ?>
                    | ROL:
                    <?= ucfirst(htmlspecialchars($usuario['rol'])) ?>
                </span>

            </div>
        </nav>

        <section class="container-fluid px-4 pb-4 pos-container">

            <div class="row g-4">

                <!-- Zona de operación -->
                <div class="col-lg-8">

                    <div class="panel-pos p-4 h-100">

                        <h5 class="mb-3">Zona de operación</h5>

                        <div class="mb-4">

                            <label for="busqueda-producto" class="form-label fw-bold">
                                Buscar producto o escanear código
                            </label>

                            <div class="input-group">

                                <input type="text" id="busqueda-producto" class="form-control buscador-pos"
                                    placeholder="Nombre o código de barras..." autocomplete="off" autofocus>

                                <button type="button" id="btn-escanear" class="btn btn-outline-primary">
                                    📷 Escanear
                                </button>

                            </div>

                            <div id="resultados-productos" class="list-group mt-2"></div>

                            <!-- Cámara -->
                            <div id="contenedor-escaner" class="mt-3" style="display:none;">

                                <div id="lector-codigo"></div>

                                <div class="mt-2 text-end">

                                    <button type="button" id="btn-cerrar-escaner" class="btn btn-danger btn-sm">
                                        Cerrar cámara
                                    </button>

                                </div>

                            </div>

                        </div>

                        <div class="table-responsive">

                            <table class="table table-hover align-middle tabla-carrito">

                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>

                                <tbody id="cuerpo-carrito">

                                    <tr id="fila-carrito-vacio">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No hay productos agregados.
                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <!-- Panel de facturación -->
                <div class="col-lg-4">

                    <div class="panel-pos p-4">

                        <h5 class="mb-4">Panel de facturación</h5>

                        <div class="mb-4">

                            <label for="busqueda-cliente" class="form-label fw-bold">
                                Cliente
                            </label>

                            <div class="input-group">

                                <input type="text" id="busqueda-cliente" class="form-control"
                                    placeholder="Buscar cliente...">

                                <button type="button" class="btn btn-outline-secondary" onclick="usarConsumidorFinal()">
                                    Consumidor final
                                </button>

                            </div>

                            <div id="resultados-clientes" class="list-group mt-2"></div>

                            <div id="cliente-seleccionado" class="cliente-seleccionado mt-2">
                                Cliente: Consumidor final
                            </div>

                        </div>

                        <hr>

                        <div class="resumen-total">

                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal">$0.00</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span>IVA (15%):</span>
                                <span id="iva">$0.00</span>
                            </div>

                            <div class="d-flex justify-content-between total-final mt-3">
                                <span>Total:</span>
                                <span id="total">$0.00</span>
                            </div>

                        </div>

                        <hr>

                        <div class="mb-3">

                            <label for="monto-pagado" class="form-label fw-bold">
                                Monto pagado
                            </label>

                            <input type="number" id="monto-pagado" class="form-control form-control-lg" min="0"
                                step="0.01" placeholder="0.00">

                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Cambio</label>
                            <input type="text" id="cambio" class="form-control form-control-lg" value="$0.00" readonly>
                        </div>

                        <button type="button" class="btn btn-procesar w-100" onclick="procesarVenta()">Procesar
                            venta</button>

                    </div>

                </div>

            </div>

        </section>

    </main>

    <script src="/frontend/js/pos.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

</body>

</html>