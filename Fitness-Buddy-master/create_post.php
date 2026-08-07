<?php

session_start();
require_once __DIR__ . '/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Invalid request method.');
}

// Make sure the user is logged in
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    exit('Please log in to post.');
}

// Get and validate post content
$content = trim($_POST['content'] ?? '');

if ($content === '') {
    exit('Post content cannot be empty.');
}

// Convert user ID to integer
$user_id = (int) $user_id;

// Insert post using a prepared statement
$stmt = $conn->prepare(
    "INSERT INTO posts (user_id, content)
     VALUES (:user_id, :content)"
);

$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->bindValue(':content', $content, PDO::PARAM_STR);

$stmt->execute();

// Redirect back to forum
header('Location: forum.php');
exit;
?>
