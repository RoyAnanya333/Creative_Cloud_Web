<?php
session_start();
include("../includes/student_header.php");
include("../config/config.php");

$student_id = $_SESSION['user_id'];

// Get course ID from query string
$course_id = $_GET['course_id'] ?? null;
if (!$course_id) {
    echo "<p>Invalid course.</p>";
    include("../includes/student_footer.php");
    exit;
}

// Fetch course details
$stmt = $conn->prepare("
    SELECT c.*, u.full_name AS tutor_name, u.email AS tutor_email
    FROM courses c
    LEFT JOIN tutor_profiles tp ON c.tutor_profile_id = tp.id
    LEFT JOIN users u ON tp.user_id = u.id
    WHERE c.id=? AND c.status='published'
    LIMIT 1
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    echo "<p>Course not found or unpublished.</p>";
    include("../includes/student_footer.php");
    exit;
}

$already_enrolled = false;
$success_msg = '';
$error_msg = '';
$show_payment_form = false;

// Check if student is already enrolled
$stmt_check = $conn->prepare("SELECT * FROM enrollments WHERE student_id=? AND course_id=?");
$stmt_check->bind_param("ii", $student_id, $course_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
if ($res_check->num_rows > 0) {
    $already_enrolled = true;
}

// Handle dummy payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay'])) {
    // Check again if already enrolled
    $stmt_check = $conn->prepare("SELECT * FROM enrollments WHERE student_id=? AND course_id=?");
    $stmt_check->bind_param("ii", $student_id, $course_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    if ($res_check->num_rows > 0) {
        $already_enrolled = true;
        $success_msg = "You are already enrolled in this course.";
    } else {
        // Insert dummy payment
        $method = 'Dummy';
        $status = 'paid';
        $stmt_payment = $conn->prepare("INSERT INTO payments (student_id, course_id, amount, currency, method, status, paid_at) VALUES (?,?,?,?,?,?,NOW())");
        $stmt_payment->bind_param("iidsis", $student_id, $course_id, $course['price'], $course['currency'], $method, $status);
        if ($stmt_payment->execute()) {
            $payment_id = $conn->insert_id;
            // Insert enrollment
            $stmt_enroll = $conn->prepare("INSERT INTO enrollments (student_id, course_id, status, payment_id, enrollment_date) VALUES (?,?, 'active', ?, NOW())");
            $stmt_enroll->bind_param("iii", $student_id, $course_id, $payment_id);
            if ($stmt_enroll->execute()) {
                $success_msg = "Payment successful! You are now enrolled in <strong>" . htmlspecialchars($course['title']) . "</strong>.";
                $already_enrolled = true;
            } else {
                $error_msg = "Enrollment failed: " . $stmt_enroll->error;
            }
        } else {
            $error_msg = "Payment failed: " . $stmt_payment->error;
        }
    }
}

// Show payment form if user clicks Enroll Now
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $show_payment_form = true;
}

// Banner image
$image_path = "../assets/images/default_course.jpg";
if (!empty($course['image_url']) && file_exists("../" . $course['image_url'])) {
    $image_path = "../" . $course['image_url'];
}
?>

<link rel="stylesheet" href="../assets/css/main.css">

<div class="course-detail">
    <h2><?= htmlspecialchars($course['title']) ?></h2>
    <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($course['title']) ?>" class="course-banner">
    <p><strong>Description:</strong> <?= htmlspecialchars($course['description']) ?></p>
    <p><strong>Level:</strong> <?= htmlspecialchars($course['level']) ?></p>
    <p><strong>Duration:</strong> <?= htmlspecialchars($course['duration_weeks']) ?> weeks</p>
    <p><strong>Fee:</strong> <?= number_format($course['price'],2) ?> <?= htmlspecialchars($course['currency']) ?></p>
    <p><strong>Tutor:</strong> <?= htmlspecialchars($course['tutor_name'] ?? 'Unassigned') ?> (<?= htmlspecialchars($course['tutor_email'] ?? '') ?>)</p>

    <?php if ($success_msg): ?>
        <div class="success-box"><?= $success_msg ?></div>
    <?php elseif ($error_msg): ?>
        <div class="error-box"><?= $error_msg ?></div>
    <?php endif; ?>

    <?php if (!$already_enrolled && !$show_payment_form): ?>
        <form method="POST">
            <button type="submit" name="enroll" class="btn-enroll">Enroll Now</button>
        </form>
    <?php endif; ?>

    <?php if ($show_payment_form && !$already_enrolled): ?>
        <div class="payment-box">
            <h3>Dummy Payment Portal</h3>
            <p>Amount: <?= number_format($course['price'],2) ?> <?= htmlspecialchars($course['currency']) ?></p>
            <form method="POST">
                <label>Enter OTP (dummy)</label>
                <input type="text" name="otp" placeholder="Enter OTP" required>
                <button type="submit" name="pay" class="btn-enroll">Pay Now</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<style>
.course-detail {
    max-width: 700px;
    margin: 20px auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    text-align: center;
}

.course-detail img.course-banner {
    width: 100%;
    height: 250px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 15px;
}

.course-detail p { margin:8px 0; }

.btn-enroll {
    display: inline-block;
    padding: 10px 20px;
    background: #1e3a8a;
    color: #fff;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    margin-top: 15px;
    font-size: 1rem;
}
.btn-enroll:hover { background: #374ccc; }

.success-box { background: #d1fae5; color: #065f46; padding: 12px; border-radius:6px; margin-bottom:15px; }
.error-box { background: #fee2e2; color: #b91c1c; padding: 12px; border-radius:6px; margin-bottom:15px; }

.payment-box {
    background: #f0f0f0;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
}
.payment-box input[type="text"] {
    padding: 8px;
    width: 60%;
    margin-bottom: 10px;
}
</style>

