<?php
// Enable full error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set JSON header
header("Content-Type: application/json");

// Database credentials
$host = "localhost";
$db   = "codeadapt-backend";
$user = "Adams";
$pass = "AdamsPass123";

try {
    $conn = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
?>
