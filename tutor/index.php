<?php
include("../config/config.php");

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------
// Session check for tutor
// -------------------------
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header("Location: ../login/login.php");
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
<link rel="stylesheet" href="../assets/css/main.css">

<div class="dashboard-container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
    <p>Your Tutor Code: <strong><?= htmlspecialchars($tutor_profile['tutor_code']) ?></strong></p>
    <p>Bio: <?= htmlspecialchars($tutor_profile['bio'] ?? 'No bio yet') ?></p>

    <h2>Your Courses</h2>
    <div class="course-cards">
        <?php if ($courses->num_rows > 0): ?>
            <?php while ($course = $courses->fetch_assoc()): ?>
                <div class="course-card">
                    <h3><?= htmlspecialchars($course['title']) ?></h3>
                    <p><?= htmlspecialchars($course['description']) ?></p>
                    <p><strong>Level:</strong> <?= htmlspecialchars($course['level']) ?></p>
                    <p><strong>Enrolled Students:</strong> <?= $course['enrolled_students'] ?></p>
                    <a href="manage_course.php?course_id=<?= $course['id'] ?>" class="btn">Manage</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You have no assigned courses yet.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.dashboard-container h1 {
    color: #1e3a8a;
}

.course-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.course-card {
    padding: 15px;
    border-radius: 8px;
    background: #f7f7f7;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    text-align: center;
}

.course-card h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
}

.course-card p {
    font-size: 0.95rem;
    margin: 5px 0;
}

.course-card .btn {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 15px;
    background: #1e3a8a;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
}

.course-card .btn:hover {
    background: #374ccc;
}
</style>

<?php include("../includes/footer.php"); ?>
