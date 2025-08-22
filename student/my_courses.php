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
<style>
    /* Container spacing to avoid sidebar & header overlap */
.container {
    margin-left: 240px; /* width of sidebar */
    margin-top: 0px;   /* height of top header */
    padding: 20px;
    max-width: 1200px;
}
/* Course list grid: 4 per row */
.course-list {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 4 courses per row */
    gap: 20px;
    margin-top: 20px;
}

/* Responsive adjustments */
@media screen and (max-width: 1200px) {
    .course-list {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media screen and (max-width: 992px) {
    .course-list {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media screen and (max-width: 600px) {
    .course-list {
        grid-template-columns: 1fr;
    }
}



/* Individual course card */
.course-card {
    background: #f7f7f7;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.course-card h3 {
    font-size: 1.2rem;
    margin-bottom: 8px;
}

.course-card p {
    font-size: 0.95rem;
    margin: 4px 0 12px 0;
}

/* Button styling */
.course-card .btn {
    display: inline-block;
    padding: 8px 15px;
    background: #1e3a8a;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s;
}

.course-card .btn:hover {
    background: #374ccc;
}
</style>
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

