<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();

require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usernameInput = trim($_POST['username'] ?? '');
    $passwordInput = trim($_POST['password'] ?? '');

    try {
        $stmt = $connection->prepare(
            'SELECT id, usuario, password_hash, rol 
             FROM usuarios 
             WHERE usuario = :usuario 
             AND estado = 1
             LIMIT 1'
        );

        $stmt->execute([
            ':usuario' => $usernameInput
        ]);

        $userDB = $stmt->fetch();

        if ($userDB && $passwordInput === $userDB['password_hash']) {

            $_SESSION['usuario_activo'] = [
                'id' => $userDB['id'],
                'usuario' => $userDB['usuario'],
                'rol' => $userDB['rol']
            ];

            header('Location: ../dashboard.php');
            exit();

        } else {
            header('Location: ../index.php?error=1');
            exit();
        }

    } catch (PDOException $e) {
        die('Database error: ' . $e->getMessage());
    }

} else {
    header('Location: ../index.php');
    exit();
}