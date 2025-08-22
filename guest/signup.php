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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Student Signup — Creative Cloud</title>
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
            <a href="login.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition">Login</a>
          </div>
        </div>
      </div>
    </header>

  <!-- Main content -->
  <main class="flex-grow flex items-center justify-center py-12">
    <div class="bg-white rounded-2xl shadow-lg p-10 max-w-lg w-full">
        <h2 class="text-3xl font-bold text-gray-900 mb-6 text-center">Student Signup</h2>

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
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" name="full_name" required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                <input type="text" name="contact_number"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NID</label>
                <input type="text" name="nid"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                <input type="date" name="dob"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
            </div>
            <button type="submit"
                class="w-full bg-gradient-to-r from-green-500 to-blue-600 text-white py-3 rounded-lg font-medium hover:from-green-600 hover:to-blue-700 transition">Sign Up</button>
        </form>

        <p class="mt-4 text-center text-gray-600">
            Already have an account? 
            <a href="login.php" class="text-blue-600 font-semibold hover:underline">Login here</a>
        </p>
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
