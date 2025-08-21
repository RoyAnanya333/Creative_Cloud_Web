<?php
include("../includes/header_admin.php");
include("../config/config.php"); // DB connection

// Fetch total students
$stmt = $conn->prepare("SELECT COUNT(*) as total_students FROM users WHERE user_type='student' AND is_active=1");
$stmt->execute();
$result = $stmt->get_result();
$total_students = $result->fetch_assoc()['total_students'] ?? 0;

// Fetch total tutors
$stmt = $conn->prepare("SELECT COUNT(*) as total_tutors FROM users WHERE user_type='tutor' AND is_active=1");
$stmt->execute();
$result = $stmt->get_result();
$total_tutors = $result->fetch_assoc()['total_tutors'] ?? 0;

// Fetch total courses
$stmt = $conn->prepare("SELECT COUNT(*) as total_courses FROM courses WHERE status='published'");
$stmt->execute();
$result = $stmt->get_result();
$total_courses = $result->fetch_assoc()['total_courses'] ?? 0;

// Fetch total income
$stmt = $conn->prepare("SELECT IFNULL(SUM(amount),0) as total_income FROM payments WHERE status='paid'");
$stmt->execute();
$result = $stmt->get_result();
$total_income = $result->fetch_assoc()['total_income'] ?? 0;
?>
<link rel="stylesheet" href="../assets/CSS/admin.css">
<!-- Main Content Wrapper -->
<div class="admin-content">
    <h1>Admin Dashboard</h1>
    <div class="cards">
        <div class="card">👨‍🎓 Total Students: <?= $total_students ?></div>
        <div class="card">👨‍🏫 Total Tutors: <?= $total_tutors ?></div>
        <div class="card">📚 Total Courses: <?= $total_courses ?></div>
        <div class="card">💰 Total Income: BDT <?= number_format($total_income, 2) ?></div>
    </div>
</div>

<?php include("../includes/footer_admin.php"); ?>
