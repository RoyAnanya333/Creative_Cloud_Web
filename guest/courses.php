<?php
include '../config/config.php';
include '../includes/header.php';
?>

<h1>Available Courses</h1>
<div class="courses-grid">
<?php
$sql = "SELECT c.id, c.title, c.description, c.price, c.image_url, u.full_name AS tutor_name
        FROM courses c
        LEFT JOIN tutor_profiles tp ON c.tutor_profile_id = tp.id
        LEFT JOIN users u ON tp.user_id = u.id
        WHERE c.status='published'";
$result = $conn->query($sql);

if ($result->num_rows > 0):
    while ($course = $result->fetch_assoc()):
?>
<div class="course-card">
    <img src="<?php echo $course['image_url'] ?? '../assets/images/default-course.png'; ?>" alt="Course">
    <h3><?php echo $course['title']; ?></h3>
    <p>By <?php echo $course['tutor_name'] ?? 'Unknown'; ?></p>
    <p>Price: <?php echo $course['price']; ?> BDT</p>
    <a href="signup.php">Enroll Now</a>
</div>
<?php
    endwhile;
else:
    echo "<p>No courses available.</p>";
endif;
?>
</div>

<?php include '../includes/footer.php'; ?>
