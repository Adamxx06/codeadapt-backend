<?php
header("Access-Control-Allow-Origin: *"); // allow all origins (or specify 'http://localhost:5173')
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require __DIR__ . "/db.php";

// Get course_id from query parameter
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($courseId <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid course ID"]);
    exit;
}

try {
    // Fetch topics for the given course
    $stmt = $conn->prepare("SELECT id, title FROM topics WHERE course_id = ? ORDER BY created_at ASC");
    $stmt->execute([$courseId]);

    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $topics
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
