<?php
session_start();

require_once __DIR__ . '/db.php';

// Check if the user is logged in
$user_id = $_SESSION['user_id'] ?? null;

// Fetch all forum posts
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness Buddy - Forum</title> <!-- Changed title to reflect Forum page -->
    <!-- MDB JS -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script defer src="home.js"></script>
    <link rel="stylesheet" href="./css/navbar.css">
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
                        <a class="nav-link active" href="forum.php">Forum</a> <!-- Updated to forum.php and made active -->
                    </li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2 class="mb-4">Recent Posts</h2>
        <!-- Check if the user is logged in -->
        <?php if ($user_id): ?>
            <div class="mb-3">
                <form action="create_post.php" method="POST">
                    <div class="mb-3">
                        <label for="content" class="form-label">Create a Post</label>
                        <textarea name="content" id="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Post</button>
                </form>
            </div>
        <?php endif; ?>
        <!-- Success and Failure of Deletion ~ Jag -->
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Post deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['error']) && $_GET['error'] == 1): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                An error occurred while deleting the post.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <!-- ~ Jag Done -->
        <!-- Display posts or message if no posts -->
        <div id="posts-container">
            <?php if (empty($posts)): ?>
                <p>No posts yet :(</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($post['content']) ?></h5>
                            <p class="card-text">
                                <small class="text-muted">Posted by <?= htmlspecialchars($post['username']) ?> •
                                    <?= date('F j, Y, g:i a', strtotime($post['created_at'])) ?></small>
                            </p>
                            <a href="post.php?id=<?= $post['id'] ?>" class="btn btn-primary">View Post</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
