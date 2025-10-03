<?php
// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require __DIR__ . "/db.php";

// Validate topic_id
$topicId = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

if ($topicId <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid topic ID"]);
    exit;
}

try {
    // Fetch the topic
    $stmt = $conn->prepare("SELECT id, title, content, code_snippet FROM topics WHERE id = ?");
    $stmt->execute([$topicId]);
    $topic = $stmt->fetch();

    if (!$topic) {
        echo json_encode(["success" => false, "error" => "Topic not found"]);
        exit;
    }

    // Return success response
    echo json_encode([
        "success" => true,
        "data" => $topic
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
