<?php
// dashboard-data.php located in codeadapt-backend/api/
require_once 'db.php'; 

// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

try {
    // ------------------- A. USER DATA QUERIES -------------------

    // 1. QUICK STAT: Total Users
    $stmt = $conn->query("SELECT COUNT(id) AS totalUsers FROM users");
    $totalUsers = $stmt->fetchColumn() ?? 0;
    
    // 2. LINE CHART: User Growth by Month (requires created_at column)
    $stmt = $conn->query("
        SELECT 
            DATE_FORMAT(created_at, '%b') AS month,
            COUNT(id) AS users
        FROM 
            users
        GROUP BY 
            YEAR(created_at), MONTH(created_at), MONTHNAME(created_at)
        ORDER BY 
            YEAR(created_at) ASC, MONTH(created_at) ASC
    ");
    $userGrowthData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. PIE CHART: Active/Inactive Users
    // NOTE: This is based on Total Users since you don't have a 'last_login' column yet.
    $activeUsers = (int)$totalUsers; 
    $inactiveUsers = 0; 
    $activeUsersData = [
        ['name' => 'Active', 'value' => $activeUsers], 
        ['name' => 'Inactive', 'value' => $inactiveUsers],
    ];

    // ------------------- B. COURSE DATA QUERIES -------------------

    // 4. QUICK STAT: Total Courses
    $stmt = $conn->query('SELECT COUNT(id) FROM courses');
    $totalCourses = $stmt->fetchColumn() ?? 0;

    // 5. BAR CHART: Courses by Level (assuming 'level' column for grouping)
    $stmt = $conn->query("
        SELECT 
            level AS category, 
            COUNT(id) AS courses 
        FROM 
            courses 
        GROUP BY 
            level
        ORDER BY 
            courses DESC
    ");
    $courseData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ------------------- C. COMBINE AND OUTPUT -------------------

    $quickStats = [
        'totalUsers' => (int)$totalUsers,
        'activeUsers' => (int)$activeUsers,
        'totalCourses' => (int)$totalCourses,
        // Calculate engagement score dynamically
        'engagement' => ($totalUsers > 0) ? round(($activeUsers / $totalUsers) * 100) . '%' : '0%',
    ];

    $data = [
        'userGrowth' => $userGrowthData,
        'courseData' => $courseData,
        'activeUsersData' => $activeUsersData,
        'quickStats' => $quickStats,
    ];

    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500); // Server Error
    echo json_encode(["error" => "Data retrieval failed: " . $e->getMessage()]);
}
?>