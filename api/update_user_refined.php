<?php
require __DIR__ . '/db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS'); // keep POST to match frontend
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST allowed.']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

$id = isset($data['id']) ? (int)$data['id'] : 0;
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$role = trim($data['role'] ?? '');

if ($id <= 0 || $name === '' || $email === '' || $role === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing or invalid fields.',
        'received_data' => $data
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Split full name into firstName / lastName
$name_parts = preg_split('/\s+/', $name, 2);
$firstName = $name_parts[0];
$lastName = $name_parts[1] ?? '';

try {
    // Optional: ensure email uniqueness (exclude current user)
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->execute([$email, $id]);
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Another user already uses that email.']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET firstName = ?, lastName = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$firstName, $lastName, $email, $role, $id]);

    echo json_encode(['success' => true, 'message' => 'User updated successfully.', 'rows_affected' => $stmt->rowCount()]);
} catch (PDOException $e) {
    error_log('update_user_refined.php DB error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database update failed.', 'error_details' => $e->getMessage()]);
}
?>
