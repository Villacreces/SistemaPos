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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Dashboard | Sistema POS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="frontend/css/dashboard.css"
    >
</head>

<body>

    <?php include 'backend/includes/sidebar.php'; ?>

    <main
        id="content"
        style="margin-left: 250px; width: calc(100% - 250px);"
    >

        <nav class="navbar bg-white shadow-sm mb-4 p-3">
            <div class="container-fluid">

                <span class="navbar-brand mb-0 h4 text-secondary">
                    Dashboard
                </span>

                <div class="d-flex align-items-center gap-3">

                    <span
                        class="fw-bold"
                        style="color: var(--verdeO);"
                    >
                        <?= htmlspecialchars($usuario['usuario']) ?>
                        | ROL:
                        <?= ucfirst(htmlspecialchars($usuario['rol'])) ?>
                    </span>

                    <a
                        href="backend/logout.php"
                        class="btn btn-danger"
                    >
                        Cerrar sesión
                    </a>

                </div>

            </div>
        </nav>

        <section class="container-fluid px-4">

            <div class="row">

                <div class="col-12">

                    <div
                        class="card shadow-sm border-0 border-top border-4"
                        style="border-color: var(--verdeM) !important;"
                    >

                        <div class="card-body py-5 text-center">

                            <h2 style="color: var(--verdeO);">
                                Bienvenido,
                                <?= htmlspecialchars($usuario['usuario']) ?>!
                            </h2>

                            <p class="text-muted fs-5 mt-3">
                                Seleccione una opción del menú para comenzar.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </main>

</body>
</html>
```
