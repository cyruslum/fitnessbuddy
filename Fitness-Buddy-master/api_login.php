<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON input."
        ]);
        exit();
    }

    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode([
            "success" => false,
            "message" => "Both email and password are required."
        ]);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email format."
        ]);
        exit();
    }

    $stmt = $conn->prepare(
        "SELECT id, password_hash 
         FROM users 
         WHERE email = :email 
         LIMIT 1"
    );

    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);
    exit();
}

session_regenerate_id(true);
$_SESSION["user_id"] = $user['id'];

    echo json_encode([
        "success" => true,
        "message" => "Login successful. Redirecting..."
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
}
?>
