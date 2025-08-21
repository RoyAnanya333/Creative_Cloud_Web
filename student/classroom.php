<?php 
session_start();
include("../config/config.php");
include("../includes/student_header.php");

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../guest/login.php");
    exit;
}

$student_id = $_SESSION['user_id']; // Correct session variable
$course_id = $_GET['course_id'] ?? null;

$course = null;
if($course_id){
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
}
?>
<link rel="stylesheet" href="../assets/css/main.css">

<div class="container">
    <?php if($course){ ?>
        <h2><?php echo htmlspecialchars($course['title']); ?> – Classroom</h2>

        <div class="tabs">
            <a href="?course_id=<?php echo $course_id; ?>&tab=stream" class="btn">Stream</a>
            <a href="?course_id=<?php echo $course_id; ?>&tab=classwork" class="btn">Classwork</a>
            <a href="?course_id=<?php echo $course_id; ?>&tab=people" class="btn">People</a>
        </div>

        <div class="classroom-content">
            <?php 
            $tab = $_GET['tab'] ?? 'stream';
            if($tab == "stream"){
                echo "<h3>Stream</h3><p>Announcements and updates will show here.</p>";
            } elseif($tab == "classwork"){
                echo "<h3>Classwork</h3><p>Assignments and materials will show here.</p>";
            } elseif($tab == "people"){
                echo "<h3>People</h3>";
                $stmt = $conn->prepare("
                    SELECT u.username 
                    FROM users u
                    JOIN enrollments e ON u.id = e.student_id
                    WHERE e.course_id = ? AND u.user_type = 'student'
                ");
                $stmt->bind_param("i", $course_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while($row = $result->fetch_assoc()){
                    echo "<p>".htmlspecialchars($row['username'])."</p>";
                }
            }
            ?>
        </div>
    <?php } else { ?>
        <p>Course not found.</p>
    <?php } ?>
</div>

<?php include("../includes/student_footer.php"); ?>
