<?php
require 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request. Token missing.']);
    exit;
}

try {
    // Check if token exists
    $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Token exists
        if ((int)$user['is_verified'] === 1) {
            echo json_encode(['success' => true, 'message' => 'Your email is already verified.']);
            exit;
        }

        // Verify the user and clear the token
        $update = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
        $update->execute([$user['id']]);

        echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
        exit;
    } 

    // Token not found → maybe already used
    echo json_encode(['success' => true, 'message' => 'Your email is already verified.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
