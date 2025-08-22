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

            switch ($user['user_type']) {
                case 'admin':
                    header("Location: ../admin/index.php");
                    exit;
                case 'tutor':
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Login — Creative Cloud</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="bg-white shadow-sm border-b sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <a href="index.php" class="flex items-center">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mr-3">
              <i class="ri-graduation-cap-line text-white text-xl"></i>
            </div>
            <span class="text-xl font-bold text-gray-800">Creative Cloud</span>
          </a>
          <div class="hidden md:flex items-center space-x-4">
            <a href="signup.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition">Sign Up</a>
          </div>
        </div>
      </div>
    </header>

  <!-- Main content -->
  <main class="flex-grow flex items-center justify-center py-12">
    <div class="bg-white rounded-2xl shadow-lg p-10 max-w-md w-full">
        <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Login To Your Profile</h2>

        <?php if (!empty($errors)): ?>
            <div class="space-y-2 mb-4">
            <?php foreach ($errors as $error): ?>
                <div class="rounded-md bg-red-100 p-3 text-sm text-red-700 flex items-center gap-2">
                    <i class="ri-error-warning-line"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                <input type="text" name="username_email" required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-green-500 to-blue-600 text-white py-3 rounded-lg font-medium hover:from-green-600 hover:to-blue-700 transition">Login</button>
        </form>


    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white border-t py-8 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-gray-600">
          <div class="flex items-center justify-center mb-4">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full flex items-center justify-center mr-3">
               <i class="ri-graduation-cap-line text-white text-xl"></i>
            </div>
            <span class="text-xl font-bold text-gray-800">Creative Cloud</span>
          </div>
          © 2024 Creative Cloud. All rights reserved.
      </div>
  </footer>

</body>
</html>
