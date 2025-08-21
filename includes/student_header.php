<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../guest/login.php");
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Student';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Light Lavender Theme */
        body {
            background: #f5f0ff;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .sidebar {
            width: 220px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: #e0d6f5; /* soft lavender */
            color: #4b0082;
            padding: 20px;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
        }
        .sidebar a {
            color: #4b0082;
            display: block;
            padding: 10px 8px;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 4px;
        }
        .sidebar a:hover {
            background: #d1c1f0;
            color: #fff;
        }

        .sidebar h2 {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        .top-header {
            margin-left: 220px;
            background: #dcd0f5;
            padding: 16px 24px;
            font-size: 1.1rem;
            font-weight: bold;
            color: #4b0082;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }

        .content {
            margin-left: 220px;
            padding: 24px;
            min-height: calc(100vh - 60px); /* leave space for footer */
        }

        .cards {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .card {
            background: #fff;
            padding: 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            min-width: 180px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><?= htmlspecialchars($full_name) ?></h2>
    <a href="index.php">Dashboard</a>
    <a href="my_courses.php">My Courses</a>
    <a href="classroom.php">Classroom</a>
    <a href="profile.php">Profile</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="top-header">
    Student Dashboard
</div>

<div class="content">
