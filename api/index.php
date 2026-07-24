<?php
/*
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}*/
?>

<!DOCTYPE html>
<html lang="en">
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title></title>
        <meta name="description" content="">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="frontend/css/style.css">       
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center vh-100">
        <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
            <div class="text-center mb-4">
                <h3 class="text-primary">Sistema POS</h3>
                <p class="text-muted">Ingrese sus credenciales</p>
            </div>
            <!--Error message display if not connected to database-->
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    Usuario o contraseña incorrectos. Por favor, inténtelo de nuevo.
                </div>
            <?php endif; ?>
                <form action="backend/process_login.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required autocomplete="off" >
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Iniciar sesión</button>
        </div>
    </body>
</html>

