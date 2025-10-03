<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

$topicId = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$limit   = isset($_GET['count']) ? intval($_GET['count']) : 5;

if ($topicId <= 0) {
    echo json_encode(["error" => "Invalid topic_id"]);
    exit;
}

// Find quiz for this topic
$sqlQuiz = "SELECT id FROM quizzes WHERE topic_id = ? LIMIT 1";
$stmtQuiz = $conn->prepare($sqlQuiz);
$stmtQuiz->bind_param("i", $topicId);
$stmtQuiz->execute();
$resQuiz = $stmtQuiz->get_result();

if ($resQuiz->num_rows === 0) {
    echo json_encode(["error" => "No quiz found for this topic"]);
    exit;
}

$quiz = $resQuiz->fetch_assoc();
$quizId = $quiz['id'];

// Fetch questions
$sql = "SELECT id AS question_id, question_text, option_a, option_b, option_c, option_d, correct_option
        FROM quiz_questions WHERE quiz_id = ? ORDER BY RAND() LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $quizId, $limit);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

echo json_encode($questions);
?>
