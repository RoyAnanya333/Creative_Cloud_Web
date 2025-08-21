<?php
session_start();
include "../config/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'tutor') {
    header("Location: ../guest/login.php");
    exit();
}

$tutor_id = $_SESSION['user_id'];

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $course_id = $_POST['course_id'];
    $conn->query("INSERT INTO posts (course_id, tutor_id, content) VALUES ('$course_id', '$tutor_id', '$content')");
}
$my_courses = $conn->query("SELECT * FROM courses WHERE tutor_id='$tutor_id'");
$my_posts = $conn->query("SELECT * FROM posts WHERE tutor_id='$tutor_id' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Posts</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include "../includes/sidebar_tutor.php"; ?>
    <div class="content">
        <h2>Post Announcement</h2>
        <form method="POST">
            <select name="course_id" required>
                <option value="">Select Course</option>
                <?php while ($c = $my_courses->fetch_assoc()) { ?>
                    <option value="<?= $c['id']; ?>"><?= $c['title']; ?></option>
                <?php } ?>
            </select>
            <textarea name="content" placeholder="Write your announcement..." required></textarea>
            <button type="submit">Post</button>
        </form>

        <h3>My Announcements</h3>
        <?php while ($p = $my_posts->fetch_assoc()) { ?>
            <div class="post-box">
                <p><?= $p['content']; ?></p>
                <small><?= $p['created_at']; ?></small>
            </div>
        <?php } ?>
    </div>
</body>
</html>
