<?php
// Include database config
include '../config/config.php';

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize errors array
$errors = [];

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email']);
    $password       = trim($_POST['password']);

    // SQL: wrap OR condition in parentheses to avoid precedence issues
    $stmt = $conn->prepare("
        SELECT id, full_name, username, email, password_hash, user_type 
        FROM users 
        WHERE (username=? OR email=?) AND is_active=1
        LIMIT 1
    ");
    $stmt->bind_param("ss", $username_email, $username_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password_hash'])) {
            // Store session variables
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            // -------------------------
            // Tutor check added properly
            // -------------------------
            switch ($user['user_type']) {
                case 'admin':
                    header("Location: ../admin/index.php");
                    exit;
                case 'tutor':
                    // Ensure tutor has a profile
                    $stmt_tutor = $conn->prepare("SELECT id FROM tutor_profiles WHERE user_id=? LIMIT 1");
                    $stmt_tutor->bind_param("i", $user['id']);
                    $stmt_tutor->execute();
                    $tutor_result = $stmt_tutor->get_result();
                    if ($tutor_result->num_rows === 1) {
                        header("Location: ../tutor/index.php");
                        exit;
                    } else {
                        $errors[] = "Tutor profile not found. Contact admin.";
                    }
                    break;
                case 'student':
                    header("Location: ../student/index.php");
                    exit;
                default:
                    $errors[] = "Unknown user type.";
            }
        } else {
            $errors[] = "Incorrect password.";
        }
    } else {
        $errors[] = "User not found or inactive.";
    }
}
?>

<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="../assets/css/main.css">

<div class="auth-container">
    <h2>Login</h2>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" class="auth-form">
        <input type="text" name="username_email" placeholder="Username or Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
</div>

<?php include '../includes/footer.php'; ?>
