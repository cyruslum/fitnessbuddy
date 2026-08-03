<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment received</title>
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
    >
</head>
<body>
    <main class="container py-5">
        <div class="alert alert-success">
            Your payment was received. Your Premium membership is being
            activated.
        </div>

        <a href="myProfile.php" class="btn btn-primary">
            Return to profile
        </a>
    </main>
</body>
</html>