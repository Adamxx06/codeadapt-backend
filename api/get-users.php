<?php
require 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, firstName, lastName, email, role, created_at FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();

    $formatted = array_map(function($u) {
        return [
            'id' => $u['id'],
            'name' => $u['firstName'] . ' ' . $u['lastName'],
            'email' => $u['email'],
            'role' => $u['role'],
            'joined' => date('Y-m-d', strtotime($u['created_at']))
        ];
    }, $users);

    echo json_encode(['success' => true, 'users' => $formatted]);
} catch (PDOException $e) {
    error_log('get-users error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching users.']);
}
?>
