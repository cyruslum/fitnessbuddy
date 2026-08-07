<?php

require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

$stmt = $conn->query(
    "SELECT 
        posts.id,
        posts.content,
        posts.created_at,
        users.username
     FROM posts
     JOIN users ON posts.user_id = users.id
     ORDER BY posts.created_at DESC"
);

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($posts);
?>
