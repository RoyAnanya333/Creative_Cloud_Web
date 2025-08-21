<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../guest/login.php");
    exit;
}

$full_name = $_SESSION['full_name'] ?? 'Admin';

// Detect current page for sidebar highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        body {
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            margin: 100;
        }

        /* Sidebar */
        .sidebar {
            width: 220px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: #dbeafe; /* soft indigo */
            color: #1e3a8a;
            padding: 20px;
            box-shadow: 2px 0 8px rgba(0,0,0,0.05);
        }
        .sidebar a {
            color: #1e3a8a;
            display: block;
            padding: 10px 8px;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .sidebar a:hover {
            background: #bfdbfe;
            color: #1e40af;
        }
        .sidebar a.active {
            background: #1e3a8a;
            color: #fff;
        }
        .sidebar h2 {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }

        /* Top header */
        .top-header {
            margin-left: 220px; /* keep space for sidebar */
            background: #e0f2fe;
            padding: 16px 24px;
            font-size: 1.1rem;
            font-weight: bold;
            color: #1e3a8a;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            display: flex;
            justify-content: flex-end; /* push content to right */
            align-items: center;
        }

        /* Content */
        .content {
            margin-left: 220px; /* prevent overlap */
            padding: 24px;
            min-height: calc(100vh - 120px); /* header + footer height */
        }

        /* Cards for dashboard */
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
    <a href="students.php" class="<?= $current_page == 'students.php' ? 'active' : '' ?>">Students</a>
    <a href="tutors.php" class="<?= $current_page == 'tutors.php' ? 'active' : '' ?>">Tutors</a>
    <a href="courses.php" class="<?= $current_page == 'courses.php' ? 'active' : '' ?>">Courses</a>
    <a href="messages.php" class="<?= $current_page == 'messages.php' ? 'active' : '' ?>">Messages</a>
    <a href="profile.php" class="<?= $current_page == 'profile.php' ? 'active' : '' ?>">Profile</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="top-header">
    Admin Dashboard
</div>

<div class="content">
