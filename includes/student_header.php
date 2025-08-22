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
<style>
/* Reset */
*, *:before, *:after { box-sizing: border-box; margin:0; padding:0; }

/* Variables */
:root {
    --primary: #4b0082;
    --bg: #f5f0ff;
    --surface: #fff;
    --header: #dcd0f5;
    --footer: #fff7ed;
}

/* Body */
body, html {
    height: 100%;
    font-family: Arial, sans-serif;
    background: var(--bg);
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 220px;
    height: 100vh;
    background: #e0d6f5;
    color: var(--primary);
    padding: 20px;
    box-shadow: 2px 0 8px rgba(0,0,0,0.05);
    overflow-y: auto;
}
.sidebar h2 {
    font-size: 1.2rem;
    margin-bottom: 20px;
}
.sidebar a {
    display: block;
    padding: 10px 8px;
    margin-bottom: 6px;
    color: var(--primary);
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
}
.sidebar a:hover {
    background: #d1c1f0;
    color: #fff;
}

/* Top header */
.top-header {
    position: fixed;
    top: 0;
    left: 220px; /* offset by sidebar */
    width: calc(100% - 220px);
    height: 60px;
    background: var(--header);
    padding: 16px 24px;
    font-size: 1.1rem;
    font-weight: bold;
    color: var(--primary);
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    z-index: 100;
}

/* Content */
.content {
    margin-left: 220px; /* sidebar width */
    padding: 24px;
    padding-top: 80px; /* header height + some spacing */
    min-height: calc(100vh - 60px);
}

/* Cards (example) */
.cards {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
.card {
    background: var(--surface);
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    min-width: 180px;
}

/* Footer */
footer {
    position: fixed;
    bottom: 0;
    left: 220px;
    width: calc(100% - 220px);
    background: var(--footer);
    color: #78350f;
    padding: 12px 24px;
    text-align: center;
    box-shadow: 0 -2px 6px rgba(0,0,0,0.05);
}

/* Responsive */
@media(max-width:900px){
    .sidebar { width:100%; height:auto; position:relative; }
    .top-header { left:0; width:100%; }
    .content { margin-left:0; padding-top:120px; }
    footer { left:0; width:100%; }
}
</style>
</head>
<body>

<div class="sidebar">
    <h2><?= htmlspecialchars($full_name) ?></h2>
    <a href="index.php">Dashboard</a>
    <a href="my_courses.php">My Courses</a>
    <a href="profile.php">Profile</a>
    <a href="../logout.php">Logout</a>
</div>

<div class="top-header">
    Student Dashboard
</div>



</body>
</html>
