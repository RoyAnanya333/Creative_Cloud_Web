<?php
include("../includes/header_admin.php");
include("../config/config.php");

$errors = [];
$success = '';

// ✅ Corrected typo: REQUEST_METHOD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $contact   = trim($_POST['contact_number']);
    $dob       = trim($_POST['dob']);
    $nid       = trim($_POST['nid']);
    $password  = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Validation
    if (empty($full_name) || empty($username) || empty($email) || empty($_POST['password'])) {
        $errors[] = "Full Name, Username, Email, and Password are required.";
    }

    // Check for duplicate username/email
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username or Email already exists.";
        }
    }

    // Insert data
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, contact_number, dob, nid, user_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'student')");
        $stmt->bind_param("sssssss", $full_name, $username, $email, $password, $contact, $dob, $nid);
        if ($stmt->execute()) {
            $success = "✅ Student added successfully!";
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }
}
?>

<!-- Load CSS -->

<link rel="stylesheet" href="../assets/CSS/stud.css">

<link rel="stylesheet" href="../assets/CSS/admin.css">
<div class="content">
    <h1>Add Student</h1>

    <?php if(!empty($errors)): ?>
        <div class="error">
            <?php foreach($errors as $error) echo "<p>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" class="form">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" placeholder="Enter full name" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Enter username" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter email" required>

        <label for="contact_number">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number" placeholder="Enter contact number">

        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob">

        <label for="nid">NID</label>
        <input type="text" id="nid" name="nid" placeholder="Enter NID number">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter password" required>

        <button type="submit" class="btn">+ Add Student</button>
    </form>
</div>

<?php include("../includes/footer_admin.php"); ?>
