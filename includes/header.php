<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link rel="stylesheet" href="../assets/css/main.css">
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creative Cloud</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="courses.php">Courses</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <li><a href="signup.php">Signup</a></li>
            <li><a href="login.php">Login</a></li>
        <?php else: ?>
            <li><a href="../logout.php">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>
<hr>
