<?php
require_once 'db.php'; // adjust path if needed

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['id'], $input['title'], $input['level'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$id = (int)$input['id'];
$title = trim($input['title']);
$level = trim($input['level']);

try {
    $stmt = $pdo->prepare("UPDATE courses SET title = :title, level = :level, updated_at = NOW() WHERE id = :id");
    $stmt->execute([
        ':title' => $title,
        ':level' => $level,
        ':id' => $id
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Course updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Course not found or no changes made']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
