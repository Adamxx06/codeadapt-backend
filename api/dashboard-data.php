<?php
// dashboard-data.php
require_once 'db.php'; 

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

try {
    // ------------------- A. USER DATA -------------------

    // 1. Total Users
    $stmt = $pdo->query("SELECT COUNT(id) AS totalUsers FROM users");
    $totalUsers = (int) $stmt->fetchColumn();

    // 2. Active Users (we’ll treat all users as active for now)
    $activeUsers = $totalUsers;
    $inactiveUsers = 0;

    // 3. User Growth by Month (for line chart)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%b') AS month,
            COUNT(id) AS users
        FROM users
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY YEAR(created_at), MONTH(created_at)
    ");
    $userGrowthData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Pie chart data (Active vs Inactive)
    $activeUsersData = [
        ["name" => "Active", "value" => $activeUsers],
        ["name" => "Inactive", "value" => $inactiveUsers],
    ];

    // ------------------- B. COURSE DATA -------------------

    // 5. Total Courses
    $stmt = $pdo->query("SELECT COUNT(id) AS totalCourses FROM courses");
    $totalCourses = (int) $stmt->fetchColumn();

    // 6. Courses per Level (for bar chart)
    $stmt = $pdo->query("
        SELECT 
            level AS category,
            COUNT(id) AS courses
        FROM courses
        GROUP BY level
        ORDER BY courses DESC
    ");
    $courseData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ------------------- C. COMBINE EVERYTHING -------------------

    $quickStats = [
        "totalUsers" => $totalUsers,
        "activeUsers" => $activeUsers,
        "totalCourses" => $totalCourses,
        "engagement" => ($totalUsers > 0) 
            ? round(($activeUsers / $totalUsers) * 100) . "%" 
            : "0%"
    ];

    $data = [
        "userGrowth" => $userGrowthData,
        "courseData" => $courseData,
        "activeUsersData" => $activeUsersData,
        "quickStats" => $quickStats,
    ];

    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
