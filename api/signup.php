<?php
require 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

// CORS & headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

$firstName = trim($data['firstName']);
$lastName  = trim($data['lastName']);
$email     = trim($data['email']);
$password  = $data['password'];
$role      = 'Student';
$token     = bin2hex(random_bytes(32)); // generate verification token

try {
    // Check if email already exists
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered.']);
        exit;
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users 
        (firstName, lastName, email, password, role, is_verified, verification_token, created_at) 
        VALUES (?, ?, ?, ?, ?, 0, ?, NOW())
    ");
    $stmt->execute([$firstName, $lastName, $email, $hashed, $role, $token]);

    // Send verification email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'adamsblessing637@gmail.com';
        $mail->Password   = 'nidm skyt cysa adat';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('adamsblessing637@gmail.com', 'CodeAdapt');
        $mail->addAddress($email, "$firstName $lastName");

        // Link points to React route

                $verifyLink = "http://localhost:5173/verify-email?token=$token";


        $mail->isHTML(true);
        $mail->Subject = "Verify Your Email - CodeAdapt";
        $mail->Body = "
            <h2>Welcome to CodeAdapt, $firstName!</h2>
            <p>Please verify your email by clicking the link below:</p>
            <p><a href='$verifyLink' target='_blank'>Verify Email</a></p>
            <p>If you didn’t create this account, you can ignore this message.</p>
        ";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Registration successful! Check your email for the verification link.']);
    } catch (Exception $e) {
        echo json_encode(['success' => true, 'message' => 'Registered, but email could not be sent. Error: ' . $mail->ErrorInfo]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
