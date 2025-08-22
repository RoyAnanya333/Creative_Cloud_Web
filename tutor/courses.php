<?php
session_start();
include("../config/config.php");

// -------------------------
// Session check for tutor
// -------------------------
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: ../guest/login.php");
    exit;
}

$tutor_user_id = $_SESSION['user_id'];

// Fetch tutor profile
$stmt = $conn->prepare("SELECT * FROM tutor_profiles WHERE user_id=? LIMIT 1");
$stmt->bind_param("i", $tutor_user_id);
$stmt->execute();
$tutor_profile = $stmt->get_result()->fetch_assoc();

if (!$tutor_profile) {
    echo "<p>No tutor profile found. Contact admin.</p>";
    exit;
}

// Fetch courses assigned to this tutor
$stmt_courses = $conn->prepare("
    SELECT c.*, 
           (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS enrolled_students
    FROM courses c
    WHERE c.tutor_profile_id = ?
    ORDER BY c.created_at DESC
");
$stmt_courses->bind_param("i", $tutor_profile['id']);
$stmt_courses->execute();
$courses = $stmt_courses->get_result();
?>

<?php include("../includes/header_tutor.php"); ?>

<div class="courses-container">
    <h1>Your Courses</h1>
    <?php if ($courses->num_rows > 0): ?>
        <div class="course-cards">
            <?php while ($course = $courses->fetch_assoc()): ?>
                <div class="course-card">
                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                    <p><?= htmlspecialchars($course['description']) ?></p>
                    <p><strong>Level:</strong> <?= htmlspecialchars($course['level']) ?></p>
                    <p><strong>Enrolled Students:</strong> <?= $course['enrolled_students'] ?></p>
                    <a href="manage_course.php?course_id=<?= $course['id'] ?>" class="btn">Classroom</a>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>You have no assigned courses yet.</p>
    <?php endif; ?>
</div>

<style>
.courses-container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.courses-container h1 {
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 25px;
}

.course-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.course-card {
    padding: 15px;
    border-radius: 8px;
    background: #f7f7f7;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.course-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.1);
}

.course-card h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: #1e3a8a;
}

.course-card p {
    font-size: 0.95rem;
    margin: 5px 0;
    color: #333;
}

.course-card .btn {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 15px;
    background: #1e3a8a;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
}

.course-card .btn:hover {
    background: #374ccc;
}
</style>

