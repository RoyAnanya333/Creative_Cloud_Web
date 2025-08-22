<?php
    include '../config/config.php'; // config.php file include kora holo
    
    $form_message = '';

    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $form_message = '<div class="mb-4 rounded-md bg-red-100 p-3 text-sm text-red-700">Please fill out all fields.</div>';
        } else {
            // Insert message into the database
            $stmt = $con->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            
            if ($stmt->execute()) {
                $form_message = '<div class="mb-4 rounded-md bg-green-100 p-3 text-sm text-green-700">Thank you for your message! We will get back to you shortly.</div>';
            } else {
                $form_message = '<div class="mb-4 rounded-md bg-red-100 p-3 text-sm text-red-700">Failed to send message. Please try again.</div>';
            }
            $stmt->close();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Contact Us — Creative Cloud</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
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
          <nav class="hidden md:flex items-center space-x-8 text-gray-700 font-medium">
            <a href="index.php" class="hover:text-blue-600 transition">Home</a>
            <a href="about.php" class="hover:text-blue-600 transition">About</a>
            <a href="courses.php" class="hover:text-blue-600 transition">Courses</a>
            <a href="contact.php" class="text-blue-600 font-semibold transition">Contact</a>
          </nav>
          <div class="hidden md:flex items-center space-x-4">
      <a href="login.php" class="text-gray-700 hover:text-blue-600 font-medium transition">Login</a>
                <a href="signup.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition">Sign Up</a>
         
        </div>
      </div>
    </header>

  <!-- Main content -->
  <main class="flex-grow">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">
        <!-- Left: Contact info -->
        <div>
          <h1 class="text-4xl font-extrabold text-gray-900 mb-4">Get in touch</h1>
          <p class="text-gray-600 mb-6">Have a question about courses, enrollment, or partnerships? Send us a message — we reply within 48 hours on business days.</p>
          <div class="space-y-6">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl"><i class="ri-mail-line"></i></div>
              <div>
                <h3 class="font-semibold text-gray-900">Email</h3>
                <p class="text-gray-600">royananya142333@gmail.com</p>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl"><i class="ri-phone-line"></i></div>
              <div>
                <h3 class="font-semibold text-gray-900">Phone</h3>
                <p class="text-gray-600">+880 1533 306735</p>
              </div>
            </div>
          </div>
        </div>
        <!-- Right: Contact form -->
        <div>
          <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Send us a message</h2>
            <?php echo $form_message; ?>
            <form action="contact.php" method="POST" class="space-y-4">
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                <input id="name" name="name" type="text" required class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
              </div>
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input id="email" name="email" type="email" required class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
              </div>
              <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input id="subject" name="subject" type="text" required class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-300" />
              </div>
              <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea id="message" name="message" rows="5" required class="w-full rounded-lg border border-gray-300 px-4 py-3 resize-none focus:outline-none focus:ring-2 focus:ring-blue-300"></textarea>
              </div>
              <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-blue-600 text-white py-3 rounded-lg font-medium hover:from-green-600 hover:to-blue-700">Send Message</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
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
