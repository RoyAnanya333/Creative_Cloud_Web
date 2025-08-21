<?php 
session_start();
include("../config/config.php");
include("../includes/student_header.php");

// Ensure user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header("Location: ../guest/login.php");
    exit;
}

$student_id = $_SESSION['user_id']; // Use user_id from session

?>
<link rel="stylesheet" href="../assets/css/main.css">

<div class="container">
    <h2>My Courses</h2>
    <div class="course-list">
        <?php
        $stmt = $conn->prepare("
            SELECT c.* FROM courses c 
            JOIN enrollments e ON c.id = e.course_id
            WHERE e.student_id = ?
        ");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<div class='course-card'>
                        <h3>".htmlspecialchars($row['title'])."</h3>
                        <p>".htmlspecialchars($row['description'])."</p>
                        <a href='classroom.php?course_id=".$row['id']."' class='btn'>Go to Classroom</a>
                      </div>";
            }
        } else {
            echo "<p>You haven’t enrolled in any courses yet.</p>";
        }
        ?>
    </div>
</div>

<?php include("../includes/student_footer.php"); ?>
