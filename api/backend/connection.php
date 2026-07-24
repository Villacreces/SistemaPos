<?php

// Creation of database credentials
$host = 'localhost';
$port = 3307;
$dbname = 'posventas2';
$user = 'root';
$password = '1234';
$character_set = 'utf8mb4';

// Connection configuration
$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$character_set";

// Strict option configurations for PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Exceptions management
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Ways to fetch data from the database
    PDO::ATTR_EMULATE_PREPARES => false, // Disabling emulation of prepared statements
];

// Database connection creation
try {
    $connection = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}
