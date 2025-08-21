<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>About Us | Creative Cloud</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head> 


<body class="bg-gray-50">  
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex flex-col">
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
            <a href="login.php" class="text-gray-700 hover:text-blue-600 font-medium transition">Login</a>
            <a href="register.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2 rounded-lg hover:from-blue-700 hover:to-indigo-700 transition">Sign Up</a>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content Section -->
    <main class="flex-grow">
        <div class="py-16 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl font-extrabold text-gray-900 mb-4">About Creative Cloud</h1>
                <p class="text-lg text-gray-600">
                    Empowering learners worldwide with cutting-edge education and professional development opportunities.
                </p>
            </div>

            <div class="max-w-5xl mx-auto mt-16 bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="md:flex">
                    <div class="md:w-1/2">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=800&q=80" alt="Creative Cloud Team" class="h-64 w-full object-cover md:h-full">
                    </div>
                    <div class="md:w-1/2 p-8 md:p-12">
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">Our Mission</h2>
                        <p class="text-gray-700 leading-relaxed">
                            At Creative Cloud, we believe that quality education should be accessible to everyone, everywhere.
                            Founded in 2020, we have been dedicated to transforming the way people learn and grow professionally.
                            Our platform connects passionate learners with industry experts, providing comprehensive courses that bridge the gap between academic knowledge and real-world application.
                            We are committed to fostering a community where innovation thrives and every individual has the opportunity to reach their full potential.
                        </p>
                    </div>
                </div>
            </div>
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
  </div>
</body>
</html>
