<?php
session_start();
include "../config/config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'tutor') {
    header("Location: ../guest/login.php");
    exit();
}

$tutor_id = $_SESSION['user_id'];

// Add classwork
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $course_id = $_POST['course_id'];
    $type = $_POST['type']; // assignment, quiz, material
    $conn->query("INSERT INTO classwork (course_id, tutor_id, title, type) VALUES ('$course_id', '$tutor_id', '$title', '$type')");
}

$my_courses = $conn->query("SELECT * FROM courses WHERE tutor_id='$tutor_id'");
$classworks = $conn->query("SELECT * FROM classwork WHERE tutor_id='$tutor_id'");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Classwork</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <?php include "../includes/sidebar_tutor.php"; ?>
    <div class="content">
        <h2>Create Classwork</h2>
        <form method="POST">
            <input type="text" name="title" placeholder="Classwork Title" required>
            <select name="course_id" required>
                <option value="">Select Course</option>
                <?php while ($c = $my_courses->fetch_assoc()) { ?>
                    <option value="<?= $c['id']; ?>"><?= $c['title']; ?></option>
                <?php } ?>
            </select>
            <select name="type">
                <option value="assignment">Assignment</option>
                <option value="quiz">Quiz</option>
                <option value="material">Material</option>
            </select>
            <button type="submit">Create</button>
        </form>

        <h3>My Classworks</h3>
        <?php while ($cw = $classworks->fetch_assoc()) { ?>
            <div class="post-box">
                <strong><?= $cw['title']; ?></strong> (<?= $cw['type']; ?>)
            </div>
        <?php } ?>
    </div>
</body>
</html>
