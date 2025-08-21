<?php
// Include database config (adjust path if needed)
include __DIR__ . "/config/config.php";

// Start session (optional, in case you want messages in session)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = trim($_POST['password']);
    $contact   = trim($_POST['contact_number']);

    // Basic validation
    if (!$full_name || !$username || !$email || !$password) {
        $errors[] = "Please fill in all required fields.";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Username or email already exists.";
    }

    // If no errors, insert new admin
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO users (full_name, username, email, password_hash, user_type, contact_number) VALUES (?, ?, ?, ?, 'admin', ?)");
        $insert->bind_param("sssss", $full_name, $username, $email, $password_hash, $contact);
        if ($insert->execute()) {
            $success = "New admin created successfully!";
        } else {
            $errors[] = "Database error: Could not create admin.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Admin</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        body{font-family: Arial, sans-serif;background:#f3f4f6;padding:20px;}
        .container{max-width:500px;margin:auto;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
        h2{text-align:center;color:#4f46e5;margin-bottom:20px;}
        input[type=text], input[type=email], input[type=password]{width:100%;padding:10px;margin:6px 0 12px 0;border:1px solid #ccc;border-radius:6px;}
        button{background:#4f46e5;color:#fff;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;width:100%;}
        button:hover{background:#4338ca;}
        .error{color:red;margin-bottom:10px;}
        .success{color:green;margin-bottom:10px;}
        label{font-weight:bold;}
    </style>
</head>
<body>

<div class="container">
    <h2>Create Admin</h2>

    <?php if(!empty($errors)): ?>
        <?php foreach($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Full Name *</label>
        <input type="text" name="full_name" required>

        <label>Username *</label>
        <input type="text" name="username" required>

        <label>Email *</label>
        <input type="email" name="email" required>

        <label>Password *</label>
        <input type="password" name="password" required>

        <label>Contact Number</label>
        <input type="text" name="contact_number">

        <button type="submit">Create Admin</button>
    </form>
</div>

</body>
</html>
