<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require __DIR__ . "/db.php";

// Get topic ID
$topicId = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
if ($topicId <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid topic ID"]);
    exit;
}

// Difficulty parameter: "easy", "medium", "hard" mapped to integers
$difficultyMap = ['easy' => 1, 'medium' => 2, 'hard' => 3];
$minDifficultyKey = $_GET['difficulty'] ?? 'easy';
$minDifficulty = $difficultyMap[$minDifficultyKey] ?? 1;

$excludeIds = isset($_GET['exclude_ids']) ? array_map('intval', explode(',', $_GET['exclude_ids'])) : [];

try {
    // Fetch quiz for the topic
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE topic_id = ?");
    $stmt->execute([$topicId]);
    $quiz = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode(["success" => false, "error" => "No quiz found for this topic"]);
        exit;
    }

    // Build dynamic query for adaptive questions
    $query = "SELECT id, question, options, correct_answer, explanation, points, difficulty 
              FROM quiz_questions WHERE quiz_id = ? AND difficulty >= ?";
    $params = [$quiz['id'], $minDifficulty];

    if (!empty($excludeIds)) {
        $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
        $query .= " AND id NOT IN ($placeholders)";
        $params = array_merge($params, $excludeIds);
    }

    $stmtQ = $pdo->prepare($query);
    $stmtQ->execute($params);
    $questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

    foreach ($questions as &$q) {
        $q['options'] = json_decode($q['options'], true) ?: [];
    }

    $quiz['questions'] = $questions;

    echo json_encode(["success" => true, "data" => $quiz]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
