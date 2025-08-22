<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'tutor') {
    header("Location: ../guest/login.php"); exit;
}
$full_name = $_SESSION['full_name'] ?? 'Tutor';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tutor Dashboard</title>
<link rel="stylesheet" href="../assets/css/main.css">
<style>
<style>
/* Reset */
*, *:before, *:after { box-sizing: border-box; margin:0; padding:0; }

:root {
  --primary:#1a73e8;
  --bg:#f6f8fc;
  --surface:#fff;
  --text:#202124;
  --muted:#5f6368;
  --border:#e0e3e7;
  --chip:#e8f0fe;
}

/* Body */
body { font-family: Arial,sans-serif; background: var(--bg); color: var(--text); }

/* Sidebar */
.sidebar {
  position: fixed;
  top:0; left:0;
  width:220px;
  height:100vh;
  background: var(--chip);
  padding:20px;
  overflow-y:auto;
  box-shadow: 2px 0 8px rgba(0,0,0,0.05);
}
.sidebar h2 { font-size:1.2rem; margin-bottom:20px; }
.sidebar a {
  display:block; padding:10px 12px;
  margin-bottom:6px; border-radius:8px;
  color: var(--primary); text-decoration:none; font-weight:600;
}
.sidebar a.active { background: var(--primary); color:#fff; }
.sidebar a:hover:not(.active){ background:#bfdbfe; color:#1e40af; }

/* Top header */
.top-header {
  position: fixed;
  left:220px;
  top:0;
  height:60px;
  width:calc(100% - 220px);
  background: var(--chip);
  display:flex; justify-content:flex-end; align-items:center;
  padding:0 24px; font-weight:bold; color: var(--primary);
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  z-index:100;
}

/* Content */
.content {
  position: absolute; /* make it start exactly after header/sidebar */
  top: 60px;          /* height of the top header */
  left: 200px;        /* width of the sidebar */
  right: 0;           /* stretch to the right */
  bottom: 0;          /* stretch to the bottom */
  padding: 24px;
  overflow-y: auto;   /* scroll if content is longer */
  background: var(--bg);
}

/* Make responsive */
@media(max-width:900px){
  .sidebar { width:100%; height:auto; position:relative; }
  .top-header { left:0; width:100%; }
  .content {
    position: relative;
    top: 0;
    left: 0;
    margin-top: 120px; /* leave space for stacked header + sidebar */
    margin-left: 0;
    min-height: auto;
  }
}


/* Cards */
.cards { display:flex; flex-wrap:wrap; gap:12px; }
.card {
  background: var(--surface);
  border-radius:12px;
  padding:16px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.05);
  min-width:220px;
  flex:1;
}

/* Make responsive */
@media(max-width:900px){
  .sidebar { width:100%; height:auto; position:relative; }
  .top-header { left:0; width:100%; }
  .content { margin-left:0; margin-top:120px; }
}
</style>

</style>
</head>
<body>

<div class="sidebar">
  <h2><?= htmlspecialchars($full_name) ?></h2>
  <a href="index.php" class="<?= $current_page=='index.php'?'active':'' ?>">Dashboard</a>
  <a href="courses.php" class="<?= $current_page=='courses.php'?'active':'' ?>">My Courses</a>
  <a href="messages.php" class="<?= $current_page=='messages.php'?'active':'' ?>">Messages</a>
  <a href="../logout.php">Logout</a>
</div>

<div class="top-header">
  Tutor Dashboard
</div>

<div class="content">
<!-- Page content starts here -->
