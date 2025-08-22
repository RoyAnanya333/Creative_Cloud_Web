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

// Fetch tutor courses
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

// Pick one active course (you can extend with ?course_id= param)
$active_course = $courses->fetch_assoc();
if (!$active_course) {
    echo "<p>You have no courses assigned yet.</p>";
    exit;
}
$course_id = $active_course['id'];

// Fetch stream posts
$stmt_posts = $conn->prepare("SELECT * FROM posts WHERE course_id=? ORDER BY created_at DESC");
$stmt_posts->bind_param("i", $course_id);
$stmt_posts->execute();
$posts = $stmt_posts->get_result();

// Fetch assignments
$stmt_assignments = $conn->prepare("SELECT * FROM assignments WHERE course_id=? ORDER BY due_date ASC");
$stmt_assignments->bind_param("i", $course_id);
$stmt_assignments->execute();
$assignments = $stmt_assignments->get_result();

// Fetch people (students)
$stmt_people = $conn->prepare("
    SELECT u.full_name, u.email 
    FROM enrollments e 
    JOIN users u ON e.student_id = u.id 
    WHERE e.course_id = ?
");
$stmt_people->bind_param("i", $course_id);
$stmt_people->execute();
$people = $stmt_people->get_result();

?>

<?php include("../includes/header_tutor.php"); ?>
<link rel="stylesheet" href="../assets/css/main.css">

<div class="classroom-container">
    <div class="classroom-header">
        <h1><?= htmlspecialchars($active_course['title']) ?></h1>
        <p><?= htmlspecialchars($active_course['description']) ?></p>
        <p><strong>Class Code:</strong> <?= htmlspecialchars($active_course['class_code']) ?></p>
    </div>

    <div class="tabs">
        <button class="tab-button active" onclick="showTab('stream')">Stream</button>
        <button class="tab-button" onclick="showTab('people')">People</button>
    </div>

    <!-- Stream -->
    <div id="stream" class="tab-content active">
        <h2>Stream</h2>
        <form method="POST" action="post_stream.php">
            <textarea name="content" placeholder="Share something with your class..." required></textarea>
            <input type="hidden" name="course_id" value="<?= $course_id ?>">
            <button type="submit" class="btn">Post</button>
        </form>
        <div class="post-list">
            <?php if ($posts->num_rows > 0): ?>
                <?php while ($post = $posts->fetch_assoc()): ?>
                    <div class="post-card">
                        <p><?= htmlspecialchars($post['content']) ?></p>
                        <small>Posted at <?= $post['created_at'] ?></small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No posts yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Classwork -->
    <div id="classwork" class="tab-content">
        <h2>Classwork</h2>
        <a href="create_assignment.php?course_id=<?= $course_id ?>" class="btn">+ Create Assignment</a>
        <div class="assignment-list">
            <?php if ($assignments->num_rows > 0): ?>
                <?php while ($a = $assignments->fetch_assoc()): ?>
                    <div class="assignment-card">
                        <h3><?= htmlspecialchars($a['title']) ?></h3>
                        <p><?= htmlspecialchars($a['description']) ?></p>
                        <p><strong>Due:</strong> <?= $a['due_date'] ?></p>
                        <a href="view_submissions.php?assignment_id=<?= $a['id'] ?>" class="btn">View Submissions</a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No assignments yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- People -->
    <div id="people" class="tab-content">
        <h2>People</h2>
        <h3>Students (<?= $active_course['enrolled_students'] ?>)</h3>
        <ul class="people-list">
            <?php while ($p = $people->fetch_assoc()): ?>
                <li><?= htmlspecialchars($p['full_name']) ?> (<?= htmlspecialchars($p['email']) ?>)</li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<style>
.classroom-container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}
.classroom-header h1 {
    margin: 0;
    color: #1e3a8a;
}
.tabs {
    margin: 20px 0;
}
.tab-button {
    padding: 10px 20px;
    margin-right: 5px;
    border: none;
    background: #f1f1f1;
    cursor: pointer;
    border-radius: 5px 5px 0 0;
}
.tab-button.active {
    background: #1e3a8a;
    color: white;
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}
.post-card, .assignment-card {
    padding: 15px;
    margin: 10px 0;
    background: #f9f9f9;
    border-radius: 8px;
}
.people-list {
    list-style: none;
    padding: 0;
}
.people-list li {
    margin: 5px 0;
}
textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.btn {
    background: #1e3a8a;
    color: #fff;
    padding: 8px 15px;
    border-radius: 6px;
    text-decoration: none;
}
.btn:hover {
    background: #374ccc;
}
</style>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
    event.target.classList.add('active');
}
</script>

<?php include("../includes/footer.php"); ?>
