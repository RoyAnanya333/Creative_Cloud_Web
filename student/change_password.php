<?php
session_start();
include("../config/config.php");
include("../includes/student_header.php");

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student'){
    header("Location: ../guest/login.php");
    exit;
}

$student_id = $_SESSION['user_id'];
$success = $error = "";

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if(!password_verify($current, $user['password_hash'])){
        $error = "Current password is incorrect.";
    } elseif($new !== $confirm){
        $error = "New passwords do not match.";
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $update->bind_param("si", $new_hash, $student_id);
        if($update->execute()){
            $success = "Password changed successfully!";
        } else {
            $error = "Error updating password.";
        }
    }
}
?>
<style>
    /* Page content wrapper */
.content {
    margin-left: 240px;  /* space for sidebar */
    margin-top: 80px;    /* space for top header */
    padding: 20px 30px;
    max-width: 900px;
    background: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    font-family: Arial, sans-serif;
}

/* Page title */
.content h2 {
    font-size: 28px;
    margin-bottom: 20px;
    color: #1a1a1a;
}

/* Success & error messages */
.success, .error {
    padding: 10px 14px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-weight: 600;
}

.success {
    background: #e6f4ea;
    color: #188038;
    border: 1px solid #cde9d6;
}

.error {
    background: #fce8e6;
    color: #d93025;
    border: 1px solid #fad2cf;
}

/* Form styling */
.profile-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* Labels */
.profile-form label {
    font-weight: 600;
    margin-bottom: 4px;
    color: #202124;
}

/* Inputs */
.profile-form input[type="text"],
.profile-form input[type="email"],
.profile-form input[type="password"],
.profile-form input[type="date"] {
    padding: 10px 12px;
    border: 1px solid #e0e3e7;
    border-radius: 8px;
    font-size: 14px;
    width: 100%;
    transition: border 0.3s, box-shadow 0.3s;
}

.profile-form input:focus {
    border-color: #1a73e8;
    box-shadow: 0 0 4px rgba(26, 115, 232, 0.3);
    outline: none;
}

/* Button */
.profile-form .btn {
    padding: 10px 16px;
    background: #1a73e8;
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}

.profile-form .btn:hover {
    background: #374ccc;
}

/* Responsive adjustments */
@media screen and (max-width: 768px) {
    .content {
        margin-left: 20px;
        margin-top: 60px;
        padding: 16px;
    }
}
</style>
<div class="content">
    <h2>Change Password</h2>

    <?php if($success) echo "<p class='success'>$success</p>"; ?>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST" class="profile-form">
        <label>Current Password</label>
        <input type="password" name="current_password" required>

        <label>New Password</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" class="btn">Change Password</button>
    </form>
</div>

<?php include("../includes/student_footer.php"); ?>
