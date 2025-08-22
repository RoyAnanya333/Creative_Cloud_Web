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


<style>
    /* Content container to avoid overlapping sidebar/header */
.content {
    margin-left: 240px; /* width of sidebar */
    margin-top: 80px;   /* height of sticky header */
    padding: 20px;
    max-width: 1000px;
}

/* Profile section */
.profile-view {
    background: #f7f7f7;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

.profile-view p {
    font-size: 1rem;
    margin: 8px 0;
    color: #333;
}

.profile-view strong {
    color: #1e3a8a;
}

/* Buttons */
.btn {
    display: inline-block;
    margin-right: 10px;
    margin-top: 10px;
    padding: 10px 18px;
    background-color: #1e3a8a;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.3s;
}

.btn:hover {
    background-color: #374ccc;
}

/* Responsive adjustments */
@media screen and (max-width: 992px) {
    .content {
        margin-left: 20px;
        margin-top: 80px;
        padding: 15px;
    }
}
@media screen and (max-width: 600px) {
    .content {
        margin-left: 10px;
        padding: 10px;
    }

    .btn {
        width: 100%;
        text-align: center;
        margin-bottom: 10px;
    }
}
</style>
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
