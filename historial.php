<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: index.php');
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

    <title>Historial de facturas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="frontend/css/dashboard.css">

    <style>
        .tarjeta-resumen {
            border: 0;
            border-left: 5px solid var(--verdeM);
            border-radius: 10px;
        }

        .tarjeta-resumen h3 {
            color: var(--verdeO);
            font-weight: 700;
        }

        .panel-filtros,
        .panel-tabla {
            background: #fff;
            border-radius: 10px;
        }

        .btn-verde {
            background: var(--verdeO);
            color: #fff;
        }

        .btn-verde:hover {
            background: var(--verdeM);
            color: #fff;
        }

        .badge-pagada {
            background: #198754;
        }

        .badge-anulada {
            background: #dc3545;
        }

        .acciones-tabla {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
    </style>
</head>

<body>
    <?php include 'backend/includes/sidebar.php'; ?>

    <main id="content" style="margin-left:250px; width:calc(100% - 250px);">

        <nav class="navbar bg-white shadow-sm mb-4 p-3">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h4 text-secondary">
                    Historial de facturas
                </span>

                <span class="fw-bold" style="color:var(--verdeO);">
                    <?= htmlspecialchars($usuario['usuario']) ?> |
                    ROL: <?= ucfirst(htmlspecialchars($usuario['rol'])) ?>
                </span>
            </div>
        </nav>

        <section class="container-fluid px-4 pb-4">

            <!-- Totalizadores -->
            <div class="row g-3 mb-4">

                <div class="col-md-4">
                    <div class="card tarjeta-resumen shadow-sm h-100">
                        <div class="card-body">
                            <span class="text-muted">Total vendido</span>
                            <h3 id="total-vendido" class="mt-2 mb-0">$0.00</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card tarjeta-resumen shadow-sm h-100">
                        <div class="card-body">
                            <span class="text-muted">Cantidad de facturas</span>
                            <h3 id="cantidad-facturas" class="mt-2 mb-0">0</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card tarjeta-resumen shadow-sm h-100">
                        <div class="card-body">
                            <span class="text-muted">Ticket promedio</span>
                            <h3 id="ticket-promedio" class="mt-2 mb-0">$0.00</h3>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Filtros -->
            <div class="panel-filtros shadow-sm p-4 mb-4">

                <h5 class="mb-3">Filtros de búsqueda</h5>

                <div class="row g-3">

                    <div class="col-md-3">
                        <label for="fecha-inicio" class="form-label">
                            Fecha inicio
                        </label>

                        <input type="date"
                               id="fecha-inicio"
                               class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label for="fecha-fin" class="form-label">
                            Fecha fin
                        </label>

                        <input type="date"
                               id="fecha-fin"
                               class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label for="filtro-cliente" class="form-label">
                            Cliente
                        </label>

                        <input type="text"
                               id="filtro-cliente"
                               class="form-control"
                               placeholder="Nombre o cédula">
                    </div>

                    <div class="col-md-3">
                        <label for="filtro-factura" class="form-label">
                            Nº de factura
                        </label>

                        <input type="number"
                               id="filtro-factura"
                               class="form-control"
                               min="1"
                               placeholder="Ej. 8">
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">

                    <button type="button"
                            class="btn btn-outline-secondary"
                            id="btn-limpiar">
                        Limpiar
                    </button>

                    <button type="button"
                            class="btn btn-verde"
                            id="btn-buscar">
                        Buscar
                    </button>

                </div>

            </div>

            <!-- Tabla -->
            <div class="panel-tabla shadow-sm p-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Facturas registradas</h5>

                    <span id="texto-resultados" class="text-muted">
                        0 resultados
                    </span>
                </div>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>Nº Factura</th>
                                <th>Fecha y hora</th>
                                <th>Cliente</th>
                                <th>Cajero</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody id="cuerpo-historial">
                            <tr>
                                <td colspan="7"
                                    class="text-center text-muted py-4">
                                    Cargando facturas...
                                </td>
                            </tr>
                        </tbody>

                    </table>

                </div>

            </div>

        </section>
    </main>

    <!-- Modal de detalles -->
    <div class="modal fade"
         id="modalDetalleVenta"
         tabindex="-1"
         aria-labelledby="tituloDetalleVenta"
         aria-hidden="true">

        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloDetalleVenta">
                        Detalles de la factura
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar">
                    </button>
                </div>

                <div class="modal-body">

                    <div id="datos-factura" class="mb-3"></div>

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody id="cuerpo-detalle-venta">
                                <tr>
                                    <td colspan="5"
                                        class="text-center text-muted">
                                        Seleccione una factura.
                                    </td>
                                </tr>
                            </tbody>

                        </table>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cerrar
                    </button>

                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="frontend/js/historial.js"></script>
</body>
</html>