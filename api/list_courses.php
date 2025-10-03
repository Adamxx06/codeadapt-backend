<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . "/db.php";

$sql = "SELECT id AS course_id, title, description, level, created_at, updated_at FROM courses";
$result = $conn->query($sql);

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

echo json_encode($courses);
?>
