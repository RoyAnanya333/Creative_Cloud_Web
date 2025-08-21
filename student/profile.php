<?php
session_start();
include("../config/config.php");
include("../includes/student_header.php");

// Ensure student is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student'){
    header("Location: ../guest/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND user_type='student' LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>



<div class="content">
    <h2>My Profile</h2>

    <div class="profile-view">
        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
        <p><strong>Date of Birth:</strong> <?= htmlspecialchars($user['dob']) ?></p>
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Contact Number:</strong> <?= htmlspecialchars($user['contact_number']) ?></p>
        <p><strong>NID:</strong> <?= htmlspecialchars($user['nid']) ?></p>
    </div>

    <a href="edit_profile.php" class="btn" id="editProfileBtn">Edit Profile</a>
    <a href="change_password.php" class="btn" id="editProfileBtn">Change Password</a>
</div>

<?php include("../includes/student_footer.php"); ?>
