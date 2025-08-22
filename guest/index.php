<?php
    session_start(); // Start the session at the very beginning
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creative Cloud - Online Learning Platform</title>
    <!-- IMPORTANT: Make sure you have your style.css file linked -->
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>
    
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
            <a href="index.php" class="text-blue-600 font-semibold transition">Home</a>
            <a href="about.php" class="hover:text-blue-600 transition">About</a>
            <a href="courses.php" class="hover:text-blue-600 transition">Courses</a>
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

    <Style>
        * {    margin: 0;    padding: 0;
    box-sizing: border-box;}
body {
    font-family: 'Inter', sans-serif;
    line-height: 1.6;
    color: #333;
    background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%);
    min-height: 100vh;}.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}
/* Header Styles */
.header {
    background: white;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    position: relative;
    z-index: 1000;
}
.header-content {
    display: flex;    align-items: center;
    justify-content: space-between;    padding: 16px 0;
}
.logo {
    display: flex;    align-items: center;    gap: 8px;    text-decoration: none;
    color: inherit;
}
.logo-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);    border-radius: 8px;
    display: flex;    align-items: center;    justify-content: center;    color: white;
    font-size: 20px;
}.logo-text {
    font-family: 'Pacifico', serif;
    font-size: 24px;
    color: #1f2937;
    font-weight: normal;
}
/* Navigation */
.nav {
    display: flex;
    align-items: center;
    gap: 24px;
}
.nav-link {
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
}.nav-link:hover {    color: #2563eb;
}
.nav-btn {
    background: #2563eb;
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
}.nav-btn:hover {    background: #1d4ed8;
}
/* Hero Styles */
.hero {
    position: relative;
    padding: 80px 24px;    background: url('https://readdy.ai/api/search-image?query=modern online education platform with students learning on laptops in bright classroom environment, clean minimalist design, soft lighting, professional educational atmosphere, digital learning concept&width=1200&height=600&seq=hero1&orientation=landscape') center/cover;    min-height: 600px;    display: flex;
    align-items: center;
}.hero-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
}.hero-content {
    position: relative;
    text-align: center;
    color: white;
    max-width: 1200px;
    margin: 0 auto;
}
.hero-title {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 24px;
    line-height: 1.2;
}.hero-highlight {
    color: #93c5fd;
}.hero-description {
    font-size: 20px;    margin-bottom: 48px;
    max-width: 600px;    margin-left: auto;
    margin-right: auto;
    opacity: 0.9;
}
/* Dashboard Grid */
.dashboard-grid {    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    max-width: 1000px;
    margin: 0 auto;
}
.dashboard-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    padding: 24px;
    text-decoration: none;
    color: white;
    transition: all 0.3s ease;    text-align: center;}
.dashboard-card:hover {
    background: rgba(255, 255, 255, 0.2);    transform: translateY(-2px);
}.dashboard-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 20px;
    color: white;
}.dashboard-card h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 8px;}
.dashboard-card p {
    font-size: 14px;
    opacity: 0.8;
}
/* Color Classes */
.bg-blue { background: #3b82f6; }
.bg-green { background: #10b981; }
.bg-purple { background: #8b5cf6; }
.bg-orange { background: #f59e0b; }
.bg-pink { background: #ec4899; }.bg-blue-light { background: #dbeafe; color: #2563eb; }
.bg-green-light { background: #d1fae5; color: #059669; }
.bg-purple-light { background: #e9d5ff; color: #7c3aed; }/* Features Section */
.features {
    padding: 64px 24px;
    background: white;
}
.section-title {
    font-size: 32px;
    font-weight: 700;
    text-align: center;
    color: #1f2937;    margin-bottom: 48px;
}


/* Custom CSS for Courses Page */

/* Ensure proper box-sizing */
*, *::before, *::after {
  box-sizing: border-box;
}

/* Custom gradient backgrounds */
.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}
.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

/* Custom transitions */
.transition-all {
  transition: all 0.3s ease;
}
.transition-colors {
  transition: color 0.2s ease;
}

/* Hover effects for course cards */
.course-card {
  transition: all 0.3s ease;
  transform: translateY(0);
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
}
.course-card:hover {
  transform: translateY(-8px);
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Custom button hover effects */
.enroll-btn {
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}
.enroll-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.enroll-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}
.enroll-btn:hover::before {
  left: 100%;
}

/* Image hover effects */
.course-image {
  border-radius: 0.375rem;
  object-fit: cover;
  transition: transform 0.3s ease;
  height: 12rem; /* fixed height */
  width: 100%;
}
.course-card:hover .course-image {
  transform: scale(1.05);
}

/* Icon animations */
.course-icon {
  transition: transform 0.3s ease;
  color: #10B981; /* Tailwind green-500 */
}
.course-card:hover .course-icon {
  transform: rotate(5deg) scale(1.1);
}

/* Price highlight animation */
.price-highlight {
  position: relative;
  color: #10B981;
  font-weight: 700;
}
.price-highlight::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(to right, #10b981, #3b82f6);
  transition: width 0.3s ease;
}
.course-card:hover .price-highlight::after {
  width: 100%;
}

/* Loading animation */
.loading {
  opacity: 0;
  transform: translateY(20px);
  animation: fadeInUp 0.6s ease forwards;
}
@keyframes fadeInUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Stagger animation for course cards */
.course-card:nth-child(1) {
  animation-delay: 0.1s;
}
.course-card:nth-child(2) {
  animation-delay: 0.2s;
}
.course-card:nth-child(3) {
  animation-delay: 0.3s;
}
.course-card:nth-child(4) {
  animation-delay: 0.4s;
}

/* Responsive design */
@media (max-width: 640px) {
  .text-4xl {
    font-size: 2.25rem;
  }
  .text-5xl {
    font-size: 3rem;
  }
}

/* contact us code css */


/* contact.css - custom tweaks for Creative Cloud contact page */

/* Use a clean default font stack */
:root {
  --cc-accent: #10b981;
}

/* Small visual polish for the toast */
#formToast {
  transition: all 220ms ease;
}

/* Field error visible helper (we toggle .hidden via JS) */
.field-error.hidden {
  display: none;
}

/* Minimal header shadow on scroll */
header.scrolled {
  box-shadow: 0 8px 30px rgba(9, 30, 66, 0.08);
}

/* Make icons a bit larger on small screens */
@media (max-width: 640px) {
  header .w-10 { width: 40px; height: 40px; }
}



/* about code */

/* Hero Section - Colorful Version */
/* Previous CSS remains the same, just add these new styles */
.learn-more-btn {
    background: #ff9e2c;
    color: rgb(139, 156, 43);
    border: none;
    padding: 12px 25px;
    font-size: 1rem;
    border-radius: 50px;
    cursor: pointer;
    margin-top: 20px;
    transition: all 0.3s ease;
}

.learn-more-btn:hover {
    background: #ff7b00;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(255, 158, 44, 0.4);
}

/* Animation classes */
.fade-in {
    animation: fadeIn 1s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/*admin*/

</Style>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h2 class="hero-title">
                    Transform Your Future with 
                    <span class="hero-highlight">Creative Cloud</span>
                </h2>
                <p class="hero-description">
                    Join thousands of students and discover your potential with our expert-led courses in web development, design, and digital marketing.
                </p>

                <!-- RESTORED: The dashboard grid with the boxes -->
                <div class="dashboard-grid">
                    <a href="about.php" class="dashboard-card">
                        <div class="dashboard-icon bg-blue">
                            <i class="ri-information-line"></i>
                        </div>
                        <h3>About Us</h3>
                        <p>Learn about our mission and values</p>
                    </a>
                    <a href="courses.php" class="dashboard-card">
                        <div class="dashboard-icon bg-green">
                            <i class="ri-book-open-line"></i>
                        </div>
                        <h3>Available Courses</h3>
                        <p>Explore our comprehensive course catalog</p>
                    </a>
                    <a href="login.php" class="dashboard-card">
                        <div class="dashboard-icon bg-purple">
                            <i class="ri-login-box-line"></i>
                        </div>
                        <h3>Login</h3>
                        <p>Access your learning dashboard</p>
                    </a>
                    <a href="contact.php" class="dashboard-card">
                        <div class="dashboard-icon bg-orange">
                            <i class="ri-contacts-line"></i>
                        </div>
                        <h3>Contact Us</h3>
                        <p>Get in touch with our support team</p>
                    </a>
                </div>
            </div>
        </div>
    </section>
    
</body>
</html>
