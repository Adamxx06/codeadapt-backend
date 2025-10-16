<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

$topicId = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$limit   = isset($_GET['count']) ? intval($_GET['count']) : 5;

if ($topicId <= 0) {
    echo json_encode(["success" => false, "error" => "Invalid topic_id"]);
    exit;
}

try {
    // Find quiz for this topic
    $stmtQuiz = $pdo->prepare("SELECT id FROM quizzes WHERE topic_id = ? LIMIT 1");
    $stmtQuiz->execute([$topicId]);
    $quiz = $stmtQuiz->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode(["success" => false, "error" => "No quiz found for this topic"]);
        exit;
    }

    // Fetch random questions for this quiz
    $stmt = $pdo->prepare("
        SELECT id, question, options, correct_answer, explanation, points, difficulty 
        FROM quiz_questions 
        WHERE quiz_id = ? 
        ORDER BY RAND() 
        LIMIT ?
    ");
    $stmt->execute([$quiz['id'], $limit]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($questions as &$q) {
        $q['options'] = json_decode($q['options'], true) ?: [];
    }

    echo json_encode(["success" => true, "data" => $questions]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
