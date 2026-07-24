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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Gestión de clientes</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container py-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

            <div>
                <h2 class="mb-1">Gestión de clientes</h2>

                <p class="text-muted mb-0">
                    Registrar, consultar, modificar y eliminar clientes.
                </p>
            </div>

            <div class="d-flex gap-2">

                <a href="pos.php" class="btn btn-outline-secondary">
                    Volver al POS
                </a>

                <button
                    type="button"
                    id="btn-nuevo-cliente"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modal-cliente">
                    Nuevo cliente
                </button>

            </div>

        </div>

        <div
            id="mensaje-clientes"
            class="alert d-none"
            role="alert">
        </div>

        <div class="card border-0 shadow-sm">

            <div class="card-body">

                <div class="row mb-3">

                    <div class="col-md-6">

                        <label
                            for="buscar-cliente"
                            class="form-label fw-bold">
                            Buscar cliente
                        </label>

                        <input
                            type="text"
                            id="buscar-cliente"
                            class="form-control"
                            placeholder="Buscar por cédula, nombre o correo..."
                            autocomplete="off">

                    </div>

                </div>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Cédula</th>
                                <th>Nombre completo</th>
                                <th>Correo</th>
                                <th>Fecha de registro</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody id="tabla-clientes">

                            <tr>
                                <td
                                    colspan="6"
                                    class="text-center text-muted py-4">
                                    Cargando clientes...
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <!-- Modal para registrar y modificar -->
    <div
        class="modal fade"
        id="modal-cliente"
        tabindex="-1"
        aria-labelledby="titulo-modal-cliente"
        aria-hidden="true">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5
                        class="modal-title"
                        id="titulo-modal-cliente">
                        Registrar cliente
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                    </button>

                </div>

                <div class="modal-body">

                    <form id="formulario-cliente">

                        <input
                            type="hidden"
                            id="cliente-id">

                        <div class="mb-3">

                            <label
                                for="cliente-cedula"
                                class="form-label fw-bold">
                                Cédula
                            </label>

                            <input
                                type="text"
                                id="cliente-cedula"
                                class="form-control"
                                maxlength="10"
                                inputmode="numeric"
                                required>

                            <div class="form-text">
                                Debe contener 10 números.
                            </div>

                        </div>

                        <div class="mb-3">

                            <label
                                for="cliente-nombre"
                                class="form-label fw-bold">
                                Nombre completo
                            </label>

                            <input
                                type="text"
                                id="cliente-nombre"
                                class="form-control"
                                maxlength="150"
                                required>

                        </div>

                        <div class="mb-3">

                            <label
                                for="cliente-correo"
                                class="form-label fw-bold">
                                Correo
                            </label>

                            <input
                                type="email"
                                id="cliente-correo"
                                class="form-control"
                                maxlength="150"
                                placeholder="cliente@correo.com">

                        </div>

                    </form>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        id="btn-guardar-cliente"
                        class="btn btn-primary">
                        Guardar cliente
                    </button>

                </div>

            </div>

        </div>

    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>

    <script src="/frontend/js/clientes.js"></script>

</body>

</html>