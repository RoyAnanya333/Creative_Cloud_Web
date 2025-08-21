<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in or not a tutor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: ../guest/login.php");
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Tutor';

// Detect current page for sidebar highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tutor Dashboard</title>
<link rel="stylesheet" href="../assets/css/dashboard.css">
<style>
    body {
        background: #f9fafb;
        font-family: Arial, sans-serif;
        margin: 0;
    }
    /* Sidebar */
    .sidebar {
        width: 220px;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        background: #fef3c7; /* soft amber */
        color: #78350f;
        padding: 20px;
        box-shadow: 2px 0 8px rgba(0,0,0,0.05);
    }
    .sidebar a {
        color: #78350f;
        display: block;
        padding: 10px 8px;
        text-decoration: none;
        border-radius: 6px;
        margin-bottom: 4px;
        font-weight: 500;
    }
    .sidebar a:hover {
        background: #fde68a;
        color: #92400e;
    }
    .sidebar a.active {
        background: #78350f;
        color: #fff;
    }
    .sidebar h2 {
        font-size: 1.2rem;
        margin-bottom: 20px;
    }

    /* Top header */
    .top-header {
        margin-left: 220px;
        background: #fff7ed;
        padding: 16px 24px;
        font-size: 1.1rem;
        font-weight: bold;
        color: #78350f;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    /* Content */
    .content {
        margin-left: 220px;
        padding: 24px;
        min-height: calc(100vh - 120px);
    }

    /* Cards */
    .cards {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .card {
        background: #fff;
        padding: 16px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        min-width: 180px;
        text-align: center;
    }
</style>
</head>
<body>

<div class="sidebar">
    <h2><?= htmlspecialchars($full_name) ?></h2>
    <a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Dashboard</a>
    <a href="posts.php" class="<?= $current_page == 'posts.php' ? 'active' : '' ?>">Posts</a>
    <a href="classwork.php" class="<?= $current_page == 'classwork.php' ? 'active' : '' ?>">Classwork</a>
    <a href="classroom.php" class="<?= $current_page == 'classroom.php' ? 'active' : '' ?>">Classroom</a>
    <a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">Profile</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="top-header">
    Tutor Dashboard
</div>

<div class="content">
