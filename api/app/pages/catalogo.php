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
    <title>Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/frontend/css/dashboard.css">

    <style>
        .btn-verde {
            background-color: var(--verdeO);
            color: white;
        }

        .btn-verde:hover {
            background-color: var(--verdeM);
            color: white;
        }
    </style>
</head>

<body>

    <?php
    require_once __DIR__ . '/../backend/includes/sidebar.php';
    ?>

    <main id="content" style="margin-left: 250px; width: calc(100% - 250px);">

        <nav class="navbar bg-white shadow-sm mb-4 p-3">
            <div class="container-fluid">

                <span class="navbar-brand mb-0 h4 text-secondary">
                    Catálogo
                </span>

                <span class="fw-bold" style="color: var(--verdeO);">
                    <?= htmlspecialchars($usuario['usuario']) ?>
                </span>

            </div>
        </nav>

        <section class="container-fluid px-4">

            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">

                <input type="text" id="input-busqueda" class="form-control" style="max-width: 350px;"
                    placeholder="Buscar producto...">

                <button type="button" class="btn btn-verde" onclick="abrirModal()">
                    + Nuevo producto
                </button>

            </div>

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>

                            <tbody id="cuerpo-tabla">
                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </section>

    </main>

    <div class="modal fade" id="modalProducto" tabindex="-1" aria-labelledby="modalTitulo" aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title" id="modalTitulo">
                        Gestionar producto
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>

                </div>

                <div class="modal-body">

                    <input type="hidden" id="prod-id">

                    <div class="mb-3">
                        <label for="prod-codigo" class="form-label">
                            Código de barras
                        </label>

                        <input type="text" id="prod-codigo" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="prod-nombre" class="form-label">
                            Nombre
                        </label>

                        <input type="text" id="prod-nombre" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="prod-precio" class="form-label">
                            Precio
                        </label>

                        <input type="number" id="prod-precio" class="form-control" step="0.01" min="0">
                    </div>

                    <div class="mb-3">
                        <label for="prod-stock" class="form-label">
                            Stock
                        </label>

                        <input type="number" id="prod-stock" class="form-control" min="0">
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button" class="btn btn-verde" onclick="guardarProducto()">
                        Guardar cambios
                    </button>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="/frontend/js/catalogo.js"></script>

</body>

</html>
```