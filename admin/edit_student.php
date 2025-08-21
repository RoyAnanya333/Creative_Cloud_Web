<?php
include("../includes/header_admin.php");
include("../config/config.php");

$errors = [];
$success = '';
$id = $_GET['id'] ?? 0;

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM users WHERE id=? AND user_type='student'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo "<p>Student not found.</p>";
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $contact   = trim($_POST['contact_number']);
    $dob       = trim($_POST['dob']);
    $nid       = trim($_POST['nid']);

    // Update
    $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, email=?, contact_number=?, dob=?, nid=? WHERE id=?");
    $stmt->bind_param("ssssssi", $full_name, $username, $email, $contact, $dob, $nid, $id);
    if ($stmt->execute()) {
        $success = "Student updated successfully!";
        $student = array_merge($student, $_POST); // update local copy
    } else {
        $errors[] = "Database error: " . $conn->error;
    }
}
?>
<link rel="stylesheet" href="../assets/CSS/stud.css">
<div class="content">
    <h1>Edit Student</h1>

    <?php if(!empty($errors)): ?>
        <div class="error">
            <?php foreach($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if($success) echo "<p style='color:green;'>$success</p>"; ?>

    <form method="POST" class="form">
        <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" placeholder="Full Name" required><br>
        <input type="text" name="username" value="<?= htmlspecialchars($student['username']) ?>" placeholder="Username" required><br>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" placeholder="Email" required><br>
        <input type="text" name="contact_number" value="<?= htmlspecialchars($student['contact_number']) ?>" placeholder="Contact Number"><br>
        <input type="date" name="dob" value="<?= htmlspecialchars($student['dob']) ?>" placeholder="Date of Birth"><br>
        <input type="text" name="nid" value="<?= htmlspecialchars($student['nid']) ?>" placeholder="NID"><br>
        <button type="submit" class="btn">Update Student</button>
    </form>
</div>

<?php include("../includes/footer_admin.php"); ?>
