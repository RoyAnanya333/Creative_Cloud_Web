<?php
session_start();
include "../config/config.php";
include "../includes/header_tutor.php";

// Ensure tutor is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: ../guest/login.php");
    exit();
}

$tutor_user_id = $_SESSION['user_id'];

// Fetch or auto-create tutor profile
$stmt = $conn->prepare("SELECT * FROM tutor_profiles WHERE user_id = ?");
$stmt->bind_param("i", $tutor_user_id);
$stmt->execute();
$tutor_profile = $stmt->get_result()->fetch_assoc();

if (!$tutor_profile) {
    // Create profile automatically
    $stmt_insert = $conn->prepare("INSERT INTO tutor_profiles (user_id, bio) VALUES (?, '')");
    $stmt_insert->bind_param("i", $tutor_user_id);
    $stmt_insert->execute();
    
    $profile_id = $stmt_insert->insert_id;
    $stmt = $conn->prepare("SELECT * FROM tutor_profiles WHERE id = ?");
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $tutor_profile = $stmt->get_result()->fetch_assoc();
}

// Fetch courses assigned to this tutor
$stmt_courses = $conn->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id=c.id) AS total_students,
           (SELECT IFNULL(SUM(p.amount),0) FROM payments p WHERE p.course_id=c.id AND p.status='paid') AS total_earning
    FROM courses c
    WHERE c.tutor_profile_id = ?
    ORDER BY c.created_at DESC
");
$stmt_courses->bind_param("i", $tutor_profile['id']);
$stmt_courses->execute();
$courses = $stmt_courses->get_result();
?>

<div class="content">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
    <p>Your assigned courses and status overview:</p>

    <table class="table">
        <thead>
            <tr>
                <th>SL</th>
                <th>Course Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Total Enrolled</th>
                <th>Total Earning (BDT)</th>
            </tr>
        </thead>
        <tbody>
            <?php if($courses->num_rows > 0): ?>
                <?php $sl=1; while($course = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><?= $sl++ ?></td>
                        <td><?= htmlspecialchars($course['title']) ?></td>
                        <td><?= htmlspecialchars($course['description']) ?></td>
                        <td>
                            <?php
                            if (!$course['tutor_profile_id']) {
                                echo "No Course Added Yet";
                            } elseif ($course['status'] == 'draft') {
                                echo "Not Approved";
                            } else {
                                echo ucfirst($course['status']);
                            }
                            ?>
                        </td>
                        <td><?= $course['total_students'] ?></td>
                        <td><?= number_format($course['total_earning'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No courses assigned yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.content {
    margin-left: 220px;
    padding: 20px;
}
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
.table th, .table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}
.table th {
    background-color: #1e3a8a;
    color: white;
}
.table tr:nth-child(even) {
    background-color: #f2f2f2;
}
</style>
