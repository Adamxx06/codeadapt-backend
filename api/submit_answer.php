<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['topic_id'], $data['answers'], $data['user_id'])) {
    echo json_encode(["success" => false, "error" => "Invalid payload"]);
    exit;
}

$topic_id = intval($data['topic_id']);
$answers  = $data['answers']; // { question_id: selected_option, ... }
$user_id  = intval($data['user_id']);

if (empty($answers)) {
    echo json_encode(["success" => false, "error" => "No answers submitted"]);
    exit;
}

$score = 0;

try {
    foreach ($answers as $question_id => $selected) {
        $question_id = intval($question_id);

        // Fetch question details including correct answer & difficulty
        $stmt = $conn->prepare("SELECT correct_answer, quiz_id, difficulty FROM quiz_questions WHERE id = ? LIMIT 1");
        $stmt->execute([$question_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) continue;

        $is_correct = ($row['correct_answer'] === $selected) ? 1 : 0;
        $score += $is_correct;
        $quiz_id = $row['quiz_id'];
        $difficulty = $row['difficulty'] ?? 'easy';

        // Insert/update user progress with difficulty
        $stmtInsert = $conn->prepare("
            INSERT INTO user_progress 
            (user_id, quiz_id, question_id, selected_option, is_correct, difficulty, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmtInsert->execute([$user_id, $quiz_id, $question_id, $selected, $is_correct, $difficulty]);
    }

    echo json_encode([
        "success" => true,
        "score" => $score,
        "total" => count($answers)
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
