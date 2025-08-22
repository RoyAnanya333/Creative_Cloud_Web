<?php
include '../config/config.php';
?>

<link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
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
          <nav class="hidden md:flex items-center space-x-8 text-gray-700 font-medium">
            <a href="index.php" class="hover:text-blue-600 transition">Home</a>
            <a href="about.php" class="hover:text-blue-600 transition">About</a>
            <a href="courses.php" class="text-blue-600 font-semibold transition">Courses</a>
            <a href="contact.php" class="hover:text-blue-600 transition">Contact</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
            <?php if(isset($_SESSION['username'])): ?>
                <?php
                    $dashboard_link = 'student.php'; // Default
                    if (isset($_SESSION['userType'])) {
                        if ($_SESSION['userType'] === 'tutor') $dashboard_link = 'tutor.php';
                        if ($_SESSION['userType'] === 'admin') $dashboard_link = 'admin.php';
                    }
                ?>
                <a href="<?php echo $dashboard_link; ?>" class="font-medium text-gray-700 hover:text-blue-600 transition">Dashboard</a>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm font-medium">
                    Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)
                </a>
            <?php else: ?>
                <a href="login.php" class="text-gray-700 hover:text-blue-600 font-medium transition">Login</a>
                <a href="signup.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition">Sign Up</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </header>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Available Courses</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php
    $stmt_courses = $conn->prepare("
        SELECT c.*, u.full_name AS tutor_name 
        FROM courses c
        LEFT JOIN tutor_profiles tp ON c.tutor_profile_id = tp.id
        LEFT JOIN users u ON tp.user_id = u.id
        WHERE c.status='published'
        ORDER BY c.created_at DESC
    ");
    $stmt_courses->execute();
    $courses = $stmt_courses->get_result();

    if ($courses->num_rows > 0) {
        while ($course = $courses->fetch_assoc()) {
            // Correct image logic
            $image_path = "../assets/images/default-course.png"; // fallback
            if (!empty($course['image_url']) && file_exists("../".$course['image_url'])) {
                $image_path = "../".$course['image_url'];
            }
    ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden flex flex-col hover:shadow-2xl transition duration-300">
                <img class="w-full h-48 object-cover" src="<?= $image_path ?>" alt="<?= htmlspecialchars($course['title']); ?>">
                <div class="p-6 flex flex-col flex-grow">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2"><?= htmlspecialchars($course['title']); ?></h3>
                    <p class="text-gray-700 mb-4 flex-grow"><?= strlen($course['description']) > 120 ? substr($course['description'],0,120)."..." : htmlspecialchars($course['description']); ?></p>
                    <p class="text-sm text-gray-500 mb-2">By <?= htmlspecialchars($course['tutor_name'] ?? 'Unassigned'); ?></p>
                    <p class="text-lg font-semibold text-gray-800 mb-4">Price: <?= number_format($course['price'],2); ?> BDT</p>
                    <a href="signup.php" 
                       class="mt-auto inline-block text-center bg-gradient-to-r from-green-500 to-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:from-green-600 hover:to-blue-700 transition">
                       Enroll Now
                    </a>
                </div>
            </div>
    <?php
        }
    } else {
        echo "<p class='text-center text-gray-500 col-span-full'>No courses available.</p>";
    }
    ?>
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
        © 2025 Creative Cloud. All rights reserved.
      </div>
    </footer>


