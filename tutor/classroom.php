<?php
session_start();
include "../config/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'tutor') {
    header("Location: ../guest/login.php");
    exit();
}

$course_id = $_GET['course_id'];
$course = $conn->query("SELECT * FROM courses WHERE id='$course_id'")->fetch_assoc();
$posts = $conn->query("SELECT * FROM posts WHERE course_id='$course_id' ORDER BY created_at DESC");
$classworks = $conn->query("SELECT * FROM classwork WHERE course_id='$course_id'");
$students = $conn->query("SELECT s.name FROM enrollments e JOIN students s ON e.student_id=s.id WHERE e.course_id='$course_id'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Classroom - <?= $course['title']; ?></title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include "../includes/sidebar_tutor.php"; ?>
    <div class="content">
        <h2><?= $course['title']; ?> - Classroom</h2>
        
        <h3>📢 Announcements</h3>
        <?php while ($p = $posts->fetch_assoc()) { ?>
            <div class="post-box"><?= $p['content']; ?></div>
        <?php } ?>

        <h3>📚 Classwork</h3>
        <?php while ($cw = $classworks->fetch_assoc()) { ?>
            <div class="post-box"><?= $cw['title']; ?> (<?= $cw['type']; ?>)</div>
        <?php } ?>

        <h3>👥 Enrolled Students</h3>
        <ul>
            <?php while ($s = $students->fetch_assoc()) { ?>
                <li><?= $s['name']; ?></li>
            <?php } ?>
        </ul>
    </div>
</body>
</html>
