<?php
session_start();
include("../config/config.php");
include("../includes/student_header.php");

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

// Handle profile update
$success = $error = "";
if($_SERVER['REQUEST_METHOD'] == "POST"){
    $username  = mysqli_real_escape_string($conn, $_POST['username']);
    $email     = mysqli_real_escape_string($conn, $_POST['email']);
    $contact   = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $nid       = mysqli_real_escape_string($conn, $_POST['nid']);

    $update_stmt = $conn->prepare("
        UPDATE users SET username=?, email=?, contact_number=?, nid=? 
        WHERE id=? AND user_type='student'
    ");
    $update_stmt->bind_param("ssssi", $username, $email, $contact, $nid, $student_id);

    if($update_stmt->execute()){
        $success = "Profile updated successfully!";
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Error updating profile.";
    }
}
?>


<div class="content">
    <h2>Edit Profile</h2>

    <?php if($success) echo "<p class='success'>$success</p>"; ?>
    <?php if($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST" class="profile-form">
        <label>Full Name (Readonly)</label>
        <input type="text" value="<?= htmlspecialchars($user['full_name']) ?>" readonly>

        <label>Date of Birth (Readonly)</label>
        <input type="date" value="<?= htmlspecialchars($user['dob']) ?>" readonly>

        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

        <label>Contact Number</label>
        <input type="text" name="contact_number" value="<?= htmlspecialchars($user['contact_number']) ?>">

        <label>NID</label>
        <input type="text" name="nid" value="<?= htmlspecialchars($user['nid']) ?>">

        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>

<?php include("../includes/student_footer.php"); ?>
