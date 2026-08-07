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

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate JSON
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON input.'
    ]);

    exit;
}

// Extract values safely
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$membershipTier = 'free';

// Validate required fields
if ($email === '' || $password === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields are required.'
    ]);

    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid email format.'
    ]);

    exit;
}

// Create username from email
$username = explode('@', $email)[0];

// Validate password strength
if (
    !preg_match(
        '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        $password
    )
) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Password must be at least 8 characters long, contain a number, and a special character.'
    ]);

    exit;
}

// Check if email already exists
$stmt = $conn->prepare(
    'SELECT id
     FROM users
     WHERE email = :email
     LIMIT 1'
);

$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();

$existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingUser) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email is already registered.'
    ]);

    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare(
    'INSERT INTO users
    (username, email, password_hash, membership_tier)
    VALUES
    (:username, :email, :password_hash, :membership_tier)'
);

$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->bindParam(':password_hash', $hashedPassword, PDO::PARAM_STR);
$stmt->bindParam(':membership_tier', $membershipTier, PDO::PARAM_STR);

if ($stmt->execute()) {

    $_SESSION['user_id'] = $conn->lastInsertId();

    echo json_encode([
        'status' => 'success',
        'message' => 'Account created successfully!',
        'redirect' => 'profileSetup.php'
    ]);

} else {

    echo json_encode([
        'status' => 'error',
        'message' => 'Something went wrong. Try again.'
    ]);
}

$stmt = null;
$conn = null;
?>
