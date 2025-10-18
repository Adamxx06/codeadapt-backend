<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require __DIR__ . "/db.php"; // Connects to database with $pdo

// Handle preflight (CORS)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

// Parse JSON input
$input = json_decode(file_get_contents("php://input"), true);
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode([
        "success" => false,
        "message" => "Email and password are required."
    ]);
    exit;
}

try {
    // Fetch user details
    $stmt = $pdo->prepare("
        SELECT id, firstName, lastName, role, password, is_verified 
        FROM users 
        WHERE email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        // ✅ Allow Admins to log in even if not verified
        if ((int)$user['is_verified'] === 0 && strtolower($user['role']) !== 'admin') {
            echo json_encode([
                "success" => false,
                "message" => "Please verify your email before logging in."
            ]);
            exit;
        }

        // ✅ Success — verified or admin user
        echo json_encode([
            "success" => true,
            "userId" => $user['id'],
            "firstName" => $user['firstName'],
            "lastName" => $user['lastName'],
            "role" => $user['role'],
            "message" => "Login successful."
        ]);

    } else {
        // ❌ Wrong email or password
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password."
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage()
    ]);
}
