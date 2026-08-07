<?php
session_start();
require_once __DIR__ . '/db.php';

// Check post ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$postId = (int) $_GET['id'];

$userId = isset($_SESSION['user_id'])
    ? (int) $_SESSION['user_id']
    : null;

// Fetch post with author information
$stmt = $conn->prepare(
    "SELECT posts.*, users.username
     FROM posts
     JOIN users ON posts.user_id = users.id
     WHERE posts.id = :post_id"
);

$stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
$stmt->execute();

$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Details - Fitness Buddy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/navbar.css">
    <style>
        .post-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .post-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .post-content {
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .post-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-back {
            background-color: #f8f9fa;
            color: #666;
            border: 1px solid #ddd;
        }
        
        .btn-primary {
            background-color: salmon;
            border-color: salmon;
        }
        
        .btn-primary:hover {
            background-color: #ff7c66;
            border-color: #ff7c66;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: salmon;">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">Fitness Buddy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="myProfile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Matches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="forum.php">Forum</a>
                    </li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
                <?php if ($userId): ?>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-outline-light ms-2">Logout</a>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="post-container">
        <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Post updated successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="post-header">
            <h2><?= htmlspecialchars(substr($post['content'], 0, 50)) . (strlen($post['content']) > 50 ? '...' : '') ?></h2>
            <p class="post-meta">
                Posted by <strong><?= htmlspecialchars($post['username']) ?></strong> on 
                <?= date('F j, Y, g:i a', strtotime($post['created_at'])) ?>
            </p>
        </div>

        <div class="post-content">
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
        </div>

        <div class="action-buttons">
            <a href="forum.php" class="btn btn-back">Back to Forum</a>

            <?php if ($userId == $post['user_id']): ?>
            <!-- Only shows edit & delete options to the post author -->
            <a href="editPost.php?id=<?= $post['id'] ?>" class="btn btn-primary">Edit Post</a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                Delete Post
            </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($userId == $post['user_id']): ?>
    <!-- Delete -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this post? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="deletePost.php?id=<?= $post['id'] ?>" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
