<?php
require_once 'db.php';

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

try {
    // Fetch the 10 most recent users
    $stmt = $pdo->query("
        SELECT 
            id,
            firstName,
            lastName,
            email,
            role,
            created_at
        FROM users
        ORDER BY created_at DESC
        LIMIT 10
    ");

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "users" => $users]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
