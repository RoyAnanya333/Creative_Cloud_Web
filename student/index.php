<?php
include("../includes/student_header.php");
include("../config/config.php");
?>
<link rel="stylesheet" href="../assets/css/main.css">

<div class="dashboard-container">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>


    <h2>Available Courses</h2>
    <div class="course-cards">
        <?php
        $stmt_courses = $conn->prepare("
            SELECT c.*, u.full_name AS tutor_name 
            FROM courses c
            LEFT JOIN tutor_profiles tp ON c.tutor_profile_id = tp.id
            LEFT JOIN users u ON tp.user_id = u.id
            WHERE c.status='published'
            ORDER BY c.created_at DESC
        ");
        $stmt_courses->execute();
        $courses = $stmt_courses->get_result();

        if ($courses->num_rows > 0) {
            while ($course = $courses->fetch_assoc()) {
                $image_path = "../assets/images/default_course.jpg";
                if (!empty($course['image_url']) && file_exists("../" . $course['image_url'])) {
                    $image_path = "../" . $course['image_url'];
                }

                echo "<div class='course-card'>
                        <img src='{$image_path}' alt='" . htmlspecialchars($course['title']) . "' class='course-img'>
                        <h3>" . htmlspecialchars($course['title']) . "</h3>
                        <p>" . htmlspecialchars($course['description']) . "</p>
                        <p><strong>Tutor:</strong> " . htmlspecialchars($course['tutor_name'] ?? 'Unassigned') . "</p>
                        <p><strong>Fee:</strong> " . number_format($course['price'], 2) . " " . htmlspecialchars($course['currency']) . "</p>
                        <a href='enroll.php?course_id=" . $course['id'] . "' class='btn-enroll'>Enroll Now</a>
                      </div>";
            }
        } else {
            echo "<p>No courses available yet.</p>";
        }
        ?>
    </div>
</div>

<style>
/* Ensure dashboard content respects sidebar and header */
.dashboard-container {
    margin-left: 240px; /* width of sidebar */
    margin-top: 400px;   /* height of header */
    padding: 20px;
    max-width: 1200px;
}

/* Notifications styling */
.notifications .post-box {
    background: #f7f7f7;
    padding: 12px 15px;
    margin-bottom: 12px;
    border-radius: 8px;
    box-shadow: 0 1px 5px rgba(0,0,0,0.05);
}

/* Grid for courses: 3 per row */
.course-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* 3 cards per row */
    gap: 20px;
    margin-top: 10px;
}

/* Responsive: 2 per row on medium, 1 per row on small screens */
@media screen and (max-width: 992px) {
    .course-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media screen and (max-width: 600px) {
    .course-cards {
        grid-template-columns: 1fr;
    }
}

/* Individual course card */
.course-card {
    padding: 15px;
    border-radius: 8px;
    background: #f7f7f7;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.course-card img.course-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 12px;
}

.course-card h3 {
    font-size: 1.2rem;
    margin-bottom: 8px;
}

.course-card p {
    font-size: 0.95rem;
    margin: 4px 0;
}

.btn-enroll {
    display: inline-block;
    margin-top: 10px;
    padding: 8px 15px;
    background: #1e3a8a;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
}

.btn-enroll:hover {
    background: #374ccc;
}
</style>

