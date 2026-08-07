<?php

session_start();
require_once __DIR__ . '/db.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Validate post ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: forum.php');
    exit();
}

$postId = (int) $_GET['id'];
$userId = (int) $_SESSION['user_id'];

// Check that the post exists and belongs to this user
$stmt = $conn->prepare(
    "SELECT id
     FROM posts
     WHERE id = :post_id
     AND user_id = :user_id"
);

$stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: forum.php');
    exit();
}

// Delete only the logged-in user's post
$deleteStmt = $conn->prepare(
    "DELETE FROM posts
     WHERE id = :post_id
     AND user_id = :user_id"
);

$deleteStmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
$deleteStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

if ($deleteStmt->execute()) {
    header('Location: forum.php?deleted=1');
} else {
    header('Location: forum.php?error=1');
}

exit();
?>
