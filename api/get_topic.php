<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require __DIR__ . "/db.php";

// Validate topic_id
$topicId = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
if ($topicId <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid topic ID"]);
    exit;
}

// --- PDO Version ---
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT id, title, content, code_snippet FROM topics WHERE id = ?");
        $stmt->execute([$topicId]);
        $topic = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$topic) {
            echo json_encode(["success" => false, "message" => "Topic not found."]);
            exit;
        }

        echo json_encode(["success" => true, "data" => $topic]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
    exit;
}

// --- MySQLi Version ---
if (isset($conn)) {
    $stmt = $conn->prepare("SELECT id, title, content, code_snippet FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topicId);
    $stmt->execute();
    $result = $stmt->get_result();
    $topic = $result->fetch_assoc();

    if (!$topic) {
        echo json_encode(["success" => false, "message" => "Topic not found."]);
        exit;
    }

    echo json_encode(["success" => true, "data" => $topic]);
    exit;
}

echo json_encode(["success" => false, "message" => "No database connection variable found (pdo/conn missing)."]);
