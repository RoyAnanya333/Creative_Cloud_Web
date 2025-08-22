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
/* Content wrapper */
.content {
    margin-left: 30px; /* adjust for sidebar */
    margin-top: 0px;   /* adjust for top header */
    padding: 20px 30px;
    max-width: 1200px;
    font-family: Arial, sans-serif;
    background: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

/* Page title */
.content h1 {
    font-size: 28px;
    margin-bottom: 10px;
    color: #1a1a1a;
}

/* Description paragraph */
.content p {
    font-size: 15px;
    color: #555;
    margin-bottom: 20px;
}

/* Table styling */
.table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

/* Table headers */
.table th {
    background-color: #1e3a8a;
    color: #fff;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

/* Table cells */
.table td {
    padding: 12px 15px;
    font-size: 14px;
    color: #202124;
    vertical-align: middle;
}

/* Alternate row colors */
.table tr:nth-child(even) {
    background-color: #f7f7f7;
}

/* Row hover effect */
.table tr:hover {
    background-color: #eaf0ff;
}

/* Responsive adjustments */
@media screen and (max-width: 1024px) {
    .content {
        margin-left: 20px;
        padding: 16px;
    }
    .table th, .table td {
        padding: 10px 12px;
        font-size: 13px;
    }
}

@media screen and (max-width: 768px) {
    .table thead {
        display: none;
    }
    .table tr {
        display: block;
        margin-bottom: 15px;
        border-bottom: 1px solid #ddd;
    }
    .table td {
        display: flex;
        justify-content: space-between;
        padding: 8px 10px;
        border: none;
        border-bottom: 1px solid #eee;
    }
    .table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #555;
    }
}

</style>
