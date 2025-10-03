<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data['question_id'], $data['selected'], $data['quiz_id'], $data['user_id'])) {
    echo json_encode(["error" => "Invalid payload"]);
    exit;
}

$question_id = intval($data['question_id']);
$selected    = $data['selected'];
$quiz_id     = intval($data['quiz_id']);
$user_id     = intval($data['user_id'] ?? 0);

// Check correctness
$sql = "SELECT correct_option FROM quiz_questions WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$correct = ($row['correct_option'] === $selected) ? 1 : 0;

// Record in user_progress
$sqlInsert = "INSERT INTO user_progress (user_id, quiz_id, question_id, selected_option, is_correct, created_at)
              VALUES (?, ?, ?, ?, ?, NOW())";
$stmtInsert = $conn->prepare($sqlInsert);
$stmtInsert->bind_param("iiisi", $user_id, $quiz_id, $question_id, $selected, $correct);
$stmtInsert->execute();

echo json_encode(["correct" => $correct]);
?>
