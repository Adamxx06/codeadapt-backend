<?php
require_once 'db.php'; // your PDO connection

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

try {
    $stmt = $pdo->query("
        SELECT 
            id, 
            title, 
            level, 
            created_at, 
            updated_at 
        FROM courses
        ORDER BY created_at DESC
    ");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "courses" => $courses
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
