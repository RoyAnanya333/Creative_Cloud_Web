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
<style>
    /* Content spacing to avoid sidebar/header overlap */
.content {
    margin-left: 240px; /* width of sidebar */
    margin-top: 80px;   /* height of top header */
    padding: 20px;
    max-width: 800px;
}

/* Success and error messages */
.success {
    color: #16a34a;
    background: #d1fae5;
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 15px;
}

.error {
    color: #dc2626;
    background: #fee2e2;
    padding: 10px 15px;
    border-radius: 6px;
    margin-bottom: 15px;
}
/* Ensure dashboard content respects sidebar and header */
.dashboard-container {
    margin-left: 240px; /* width of sidebar */
    margin-top: 400px;   /* height of header */
    padding: 20px;
    max-width: 1200px;
}
/* Profile form styling */
.profile-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    background: #f7f7f7;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.profile-form label {
    font-weight: bold;
    color: #1e3a8a;
    margin-bottom: 5px;
}

.profile-form input {
    padding: 10px 12px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    outline: none;
    font-size: 1rem;
    width: 100%;
    box-sizing: border-box;
}

.profile-form input:read-only {
    background-color: #e5e7eb;
    cursor: not-allowed;
}

/* Button styling */
.profile-form .btn {
    padding: 10px 18px;
    background-color: #1e3a8a;
    color: #fff;
    border-radius: 6px;
    text-decoration: none;
    font-size: 1rem;
    border: none;
    cursor: pointer;
    transition: background 0.3s;
    width: fit-content;
}

.profile-form .btn:hover {
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

    .profile-form .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

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
