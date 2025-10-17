<?php
require_once 'db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true);
$userId = intval($input['id'] ?? 0);
$firstName = trim($input['firstName'] ?? '');
$lastName = trim($input['lastName'] ?? '');
$email = trim($input['email'] ?? '');
$role = trim($input['role'] ?? '');

if ($userId <= 0 || !$firstName || !$lastName || !$email || !$role) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$firstName, $lastName, $email, $role, $userId]);

    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating user: ' . $e->getMessage()]);
}
?>
