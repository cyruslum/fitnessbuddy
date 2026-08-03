<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);

    exit;
}

require_csrf_token(true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the raw POST data and decode it as JSON
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if the data is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
        exit();
    }

    // Extract values from the JSON data
    $email = trim($data['email']);
    $password = $data['password'];
    $membershipTier = 'free';

    // We no longer have username in the form, so create one from email
    $username = explode('@', $email)[0]; // Use part before @ as username

    // Validate fields
    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit();
    }

    // Validate password strength
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long, contain a number, and a special character."]);
        exit();
    }

    // Check if email already exists using PDO
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "Email is already registered."]);
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database using PDO with the new membership tier field
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, membership_tier) VALUES (:username, :email, :password_hash, :membership_tier)");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password_hash', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':membership_tier', $membershipTier, PDO::PARAM_STR);

    if ($stmt->execute()) {
        // Jag~ profileSetup redirect
        session_start(); // Start a session and store the user ID
        $_SESSION['user_id'] = $conn->lastInsertId();

        // Redirection based on accept header
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            // API request - return JSON
            header('Content-Type: application/json');
            echo json_encode(["status" => "success", "message" => "Account created successfully!", "redirect" => "profileSetup.php"]);
        } else {
            // Direct browser request - redirect
            header("Location: profileSetup.php");
            exit();
        }
    } else {
        header('Content-Type: application/json');  
        // Jag~ End
        echo json_encode(["status" => "error", "message" => "Something went wrong. Try again."]);
    }

    $stmt = null;
    $conn = null;
} else {
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>