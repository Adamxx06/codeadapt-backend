<?php
// Enable CORS
header("Access-Control-Allow-Origin: *"); // or "http://localhost:5173"
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require __DIR__ . "/db.php";

// Validate topic_id
$topicId = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

if ($topicId <= 0) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid topic ID"
    ]);
    exit;
}

try {
    // Fetch quiz for the topic
    $quizQuery = $conn->prepare("SELECT * FROM quizzes WHERE topic_id = ?");
    $quizQuery->execute([$topicId]);
    $quiz = $quizQuery->fetch(PDO::FETCH_ASSOC);

    if (!$quiz) {
        echo json_encode([
            "success" => false,
            "error" => "No quiz found for this topic"
        ]);
        exit;
    }

    // Fetch questions for the quiz
    $questionsQuery = $conn->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ?");
    $questionsQuery->execute([$quiz['id']]);
    $questions = $questionsQuery->fetchAll(PDO::FETCH_ASSOC);

    // Attach questions to quiz
    $quiz['questions'] = $questions;

    echo json_encode([
        "success" => true,
        "data" => $quiz
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
