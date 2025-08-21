<?php
// Include database config
include '../config/config.php';

// Initialize errors array
$errors = [];

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name']);
    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $contact_number = trim($_POST['contact_number']);
    $nid            = trim($_POST['nid']);
    $dob            = trim($_POST['dob']); // Expect format YYYY-MM-DD

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR username=?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Email or Username already registered.";
    } else {
        // Insert new student
        $stmt = $conn->prepare("
            INSERT INTO users 
            (full_name, username, email, password_hash, user_type, contact_number, nid, dob) 
            VALUES (?, ?, ?, ?, 'student', ?, ?, ?)
        ");
        $stmt->bind_param("sssssss", $full_name, $username, $email, $password, $contact_number, $nid, $dob);

        if ($stmt->execute()) {
            header("Location: login.php?signup=success");
            exit();
        } else {
            $errors[] = "Signup failed. Please try again.";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/main.css">

<div class="auth-container">
    <h2>Student Signup</h2>

    <?php foreach ($errors as $error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>

    <form method="POST" class="auth-form">
        <input type="text" name="full_name" placeholder="Full Name" required><br>
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="text" name="contact_number" placeholder="Contact Number"><br>
        <input type="text" name="nid" placeholder="NID"><br>
        <input type="date" name="dob" placeholder="Date of Birth"><br>
        <button type="submit">Sign Up</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
