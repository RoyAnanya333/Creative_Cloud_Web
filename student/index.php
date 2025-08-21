<?php
include("../includes/student_header.php");
include("../config/config.php");
?>
<link rel="stylesheet" href="../assets/css/main.css">

<h2>Your Notifications</h2>
<div class="notifications">
    <?php
    $student_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='post-box'>
                    <strong>" . htmlspecialchars($row['title']) . "</strong>
                    <p>" . htmlspecialchars($row['body']) . "</p>
                    <span>" . $row['created_at'] . "</span>
                  </div>";
        }
    } else {
        echo "<p>No notifications yet.</p>";
    }
    ?>
</div>

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
            // Banner image path
            $image_path = "../assets/images/default_course.jpg"; // default
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

<style>
.course-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.course-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 15px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.course-card img.course-img {
    width: 100%;
    height: 180px;
    object-fit: cover; /* keeps aspect ratio and fills */
    border-radius: 8px;
    margin-bottom: 12px;
}

.course-card h3 {
    font-size: 1.2rem;
    margin: 8px 0;
}

.course-card p {
    font-size: 0.95rem;
    margin: 4px 0;
}

.btn-enroll {
    display: inline-block;
    padding: 8px 15px;
    background: #1e3a8a;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    margin-top: 10px;
}

.btn-enroll:hover {
    background: #374ccc;
}
</style>

<?php include("../includes/student_footer.php"); ?>
