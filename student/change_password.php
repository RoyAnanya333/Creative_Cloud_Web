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
