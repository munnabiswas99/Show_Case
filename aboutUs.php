<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "showcase");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
  die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch profile picture for the logged-in user
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_picture);
$stmt->fetch();
$stmt->close();

if (!empty($profile_picture) && file_exists(__DIR__ . "/profileSystem/" . $profile_picture)) {
  $imagePath = "profileSystem/" . $profile_picture;
} else {
  $imagePath = "Images/default_profile.jpg";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>showcase | about us</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!--font awesome cdn link-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


  <style>
    @keyframes rise-in {
      0% {
        transform: translateY(50px);
        opacity: 0;
      }

      100% {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .animate-rise-in {
      animation: rise-in 0.8s ease-out forwards;
    }
  </style>


</head>

<body class="bg-white font-sans">

  <!-- Header -->
  <header class="bg-gradient-to-r from-blue-100 via-teal-50 to-mint-50 flex justify-between items-center sticky top-0 z-50 relative shadow-md">
    <a href="home.php"><img src="Images/logo.webp" class="w-[55px] h-[85px] ml-10 hover:cursor-pointer" loading="eager"></a>

    <div class="text-center pl-40">
      <a href="home.php" class="pl-40 hover:text-sky-500">Home</a>
      <a href="aboutUs.php" class="text-blue-500 text-xl pl-14 hover:text-sky-500">About Us</a>
      <a href="event.php" class="pl-14 hover:text-sky-500">Events</a>
      <a href="job.php" class="pl-14 hover:text-sky-500">Jobs</a>
      <a href="contact.php" class="pl-14 hover:text-sky-500">Contact</a>
    </div>


    <a href="messenger.php" title="Messages"
      class="relative inline-flex items-center justify-center w-8 h-8 text-blue-700 hover:text-blue-900 transition-all duration-300">
      <!-- Chat bubble icon (clean style) -->
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M7 8h10M7 12h5m-2 8.5a9.5 9.5 0 1 0-6.364-16.364C2.85 4.977 2 6.69 2 8.5c0 1.216.323 2.354.886 3.333L2 21l4.333-2.167A9.5 9.5 0 0 0 10 20.5z" />
      </svg>
    </a>

    <div class="relative inline-block text-left">
      <!-- Profile Button -->
      <button type="button" id="profileMenuButton" class="flex items-center border-l-2 border-gray-300 p-2 mr-4">
        <img src="<?php echo $imagePath . '?v=' . time(); ?>"
          alt="Profile Picture"
          class="rounded-full h-10 w-10 border-2 border-gray-300 ml-14">
        <!-- Down Arrow -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
        </svg>
      </button>

      <!-- Dropdown Menu -->
      <div id="profileDropdown"
        class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
        <a href="profileSystem/profile.php"
          class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
          View Profile
        </a>
        <form method="POST" action="logout.php">
          <button type="submit"
            class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
            Logout
          </button>
        </form>
      </div>
    </div>
  </header>


  <div class="bg-gradient-to-b from-sky-50 via-mint-50 to-mint-50">
    <section class="flex flex-col md:flex-row items-center justify-between gap-10 px-20 py-20 ease-out animate-rise-in">
      <!-- Text Content -->
      <div class="md:w-1/2 transform transition duration-700 translate-y-[50px] opacity-0 animate-rise-in">
        <p class="text-transparent text-transparent bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text font-semibold mb-2">OUR JOURNEY</p>
        <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">Our Mission is to<br>Empower Every Learner<br>to Shine.</h1>
        <p class="text-gray-600 text-lg">A platform built for students, creators, and professionals to share their skills, showcase projects, and connect with opportunities. Start building your future with ShowCase today.</p>
      </div>

      <!-- Image with Play Button -->
      <div class="relative md:w-1/2">
        <img src="https://images.pexels.com/photos/3184292/pexels-photo-3184292.jpeg" alt="Team" class="rounded-lg w-full h-auto">

        <!-- Play Button -->
        <div class="absolute inset-0 flex justify-center items-center">
          <button class="bg-white rounded-full p-4 shadow-lg hover:bg-red-500 transition">
            <svg class="w-8 h-8 text-red-500 hover:text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 5v14l11-7z" />
            </svg>
          </button>
        </div>
      </div>
    </section>




    <section class="text-center py-16 px-4">
      <h4 class="text-blue-600 font-bold uppercase mb-2">Funfact</h4>
      <h2 class="text-4xl font-extrabold text-gray-800 mb-10">
        Strength in Numbers</h2>

      <div class="grid grid-cols-1 md:grid-cols-5 gap-6 max-w-5xl mx-auto">
        <!-- Card 1 -->
        <div class="bg-gradient-to-b fromm-blue-50 to-white shadow-lg rounded-xl p-6">
          <div class="text-3xl font-bold text-blue-600 counter" data-target="23000000">0</div>
          <p class="text-gray-600 mt-2">Active members</p>
        </div>
        <!-- Card 2 -->
        <div class="bg-gradient-to-b fromm-blue-50 to-white shadow-lg rounded-xl p-6">
          <div class="text-3xl font-bold text-blue-600 counter" data-target="130000">0</div>
          <p class="text-gray-600 mt-2">Share topic</p>
        </div>
        <!-- Card 3 -->
        <div class="bg-gradient-to-b fromm-blue-50 to-white shadow-lg rounded-xl p-6">
          <div class="text-3xl font-bold text-blue-600 counter" data-target="36">0</div>
          <p class="text-gray-600 mt-2">Categories</p>
        </div>
        <!-- Card 4 -->
        <div class="bg-gradient-to-b fromm-blue-50 to-white shadow-lg rounded-xl p-6">
          <div class="text-3xl font-bold text-blue-600 counter" data-target="52145684">0</div>
          <p class="text-gray-600 mt-2">Job posts</p>
        </div>
        <!-- Card 5 -->
        <div class="bg-gradient-to-b fromm-blue-50 to-white shadow-lg rounded-xl p-6">
          <div class="text-3xl font-bold text-blue-600 counter" data-target="4.9">0</div>
          <p class="text-gray-600 mt-2">Overall review</p>
        </div>
      </div>
    </section>






    <section class="relative flex flex-col md:flex-row mb-40 mt-10">
      <!-- Left Side: Gradient background with text -->
      <div class="md:w-1/2 bg-gradient-to-r from-purple-500 to-blue-400 p-12 text-white z-10">
        <p class="font-semibold mb-3 ml-20">OUR DIFFERENCE</p>
        <h2 class="text-4xl md:text-5xl font-bold leading-tight mb-6 ml-20">A Wide Range of Opportunities to Match Your Goals.</h2>
        <p class="text-lg ml-20">ShowCase is designed for learners and creators from every field. Share your skills, showcase your projects, and connect with people in coding, design, arts, and beyond.</p>
      </div>

      <!-- Right Side: Features -->
      <div class="md:w-1/2 grid grid-cols-1 md:grid-cols-2 gap-6 p-12 z-20 absolute top-0 right-0 -translate-x-16 translate-y-6">
        <!-- Feature 1 -->
        <div class="bg-gradient-to-b from-blue-100 to-mint-200 shadow-md rounded-lg p-8 flex items-center space-x-4">
          <div class="p-2 text-2xl">
            <i class="fa-solid fa-magnifying-glass"></i>
          </div>
          <div>
            <h3 class="font-semibold">Skill-Based Matching</h3>
            <p class="text-sm text-gray-500">Find peers with similar skills</p>
          </div>
        </div>

        <!-- Feature 2 -->
        <div class="bg-gradient-to-b from-blue-100 to-mint-200 shadow-md rounded-lg p-8 flex items-center space-x-4">
          <div class="p-2 text-2xl">
            <i class="fa-solid fa-file"></i>
          </div>
          <div>
            <h3 class="font-semibold">Portfolio Builder</h3>
            <p class="text-sm text-gray-500">Create your public portfolio</p>
          </div>
        </div>

        <!-- Feature 3 -->
        <div class="bg-gradient-to-b from-blue-100 to-mint-200 shadow-md rounded-lg p-8 flex items-center space-x-4">
          <div class="p-2 text-2xl">
            <i class="fa-solid fa-hourglass-start"></i>
          </div>
          <div>
            <h3 class="font-semibold">Flexible Scheduling</h3>
            <p class="text-sm text-gray-500">Learn anytime, anywhere</p>
          </div>
        </div>

        <!-- Feature 4 -->
        <div class="bg-gradient-to-b from-blue-100 to-mint-200 shadow-md rounded-lg p-8 flex items-center space-x-4">
          <div class="p-2 text-3xl">
            <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2a6 6 0 016 6v1.586a1 1 0 01-.293.707l-5 5a1 1 0 01-1.414 0l-5-5A1 1 0 014 9.586V8a6 6 0 016-6z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold">Educator Support</h3>
            <p class="text-sm text-gray-500">Get your answers.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Second Section with top margin to separate -->
    <section class="py-10 px-4 md:px-20 flex flex-col md:flex-row items-center justify-center gap-20">

      <!-- Become a Mentor Card -->
      <div class="bg-red-500 text-white p-8 rounded-lg w-full md:w-[40rem] shadow-lg">
        <h3 class="text-yellow-300 text-lg font-semibold mb-2">Become a Mentor</h3>
        <h1 class="text-2xl md:text-3xl font-bold mb-4">Your Knowledge Can Inspire Thousands. Share It.</h1>
        <p class="mb-6">ShowCase is built for creators and learners everywhere. Teach your skills, guide projects, and help others grow in coding, design, arts, and beyond.</p>
        <button class="font-semibold px-6 py-2 border border-2 rounded hover:bg-gradient-to-r from-blue-400 to-teal-400 transition">Know more →</button>
      </div>

      <!-- Careers at ShowCase Card -->
      <div class="bg-blue-500 text-white p-8 rounded-lg w-full md:w-[40rem] shadow-lg">
        <h3 class="text-yellow-300 text-lg font-semibold mb-2">Careers at ShowCase</h3>
        <h1 class="text-2xl md:text-3xl font-bold mb-4">Join Us in Empowering Skills and Connections.</h1>
        <p class="mb-6">Be a part of ShowCase’s mission to build a community where learners and creators grow together. Work with us to create better opportunities in coding, design, and more.</p>
        <button class="font-semibold px-6 py-2 border border-2 rounded hover:bg-gradient-to-r from-blue-400 to-teal-400 transition">Know more →</button>
      </div>

    </section>
  </div>









  <!-- Footer -->
  <footer class="bg-gradient-to-b from-white to-blue-200 mt-20 pt-10 pb-6 shadow-md">
    <div class="max-w-7xl mx-auto px-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
      <!-- About Us -->
      <div>
        <h3 class="text-xl font-semibold mb-4">About ShowCase</h3>
        <p class="text-sm leading-relaxed">
          ShowCase is a platform to highlight your tech skills and creative work. Whether you're a developer,
          designer,
          or content creator — build your portfolio, connect with others, and grow.
        </p>
      </div>

      <!-- Quick Links -->
      <div class="ml-8">
        <h3 class="text-xl font-semibold mb-4">Quick Links</h3>
        <ul class="space-y-2 text-sm">
          <li><a href="home.php" class="hover:text-blue-400 transition">Home</a></li>
          <li><a href="aboutUs.php" class="hover:text-blue-400 transition">About Us</a></li>
          <li><a href="event.php" class="hover:text-blue-400 transition">Events</a></li>
          <li><a href="job.php" class="hover:text-blue-400 transition">Jobs</a></li>
          <li><a href="contact.php" class="hover:text-blue-400 transition">Contact</a></li>
        </ul>
      </div>

      <!-- Contact Info -->
      <div>
        <h3 class="text-xl font-semibold mb-4">Contact Us</h3>
        <ul class="text-sm space-y-2">
          <li><i class="fas fa-envelope mr-2"></i> info@showcase.com</li>
          <li><i class="fas fa-phone mr-2"></i> +880 1234-567890</li>
          <li><i class="fas fa-map-marker-alt mr-2"></i> Dhaka, Bangladesh</li>
        </ul>
      </div>

      <!-- Social Media -->
      <div>
        <h3 class="text-xl font-semibold mb-4">Follow Us</h3>
        <div class="flex space-x-4">
          <a href="#"><img src="Images/twitter.webp" alt="Twitter"
              class="w-6 h-6 hover:scale-110 transition duration-200" /></a>
          <a href="#"><img src="Images/youtube.webp" alt="YouTube"
              class="w-6 h-6 hover:scale-110 transition duration-200" /></a>
          <a href="#"><img src="Images/facebook.webp" alt="Facebook"
              class="w-6 h-6 hover:scale-110 transition duration-200" /></a>
        </div>
        <p class="text-sm mt-4">
          Subscribe to our newsletter for updates and announcements.
        </p>
        <form class="mt-3 flex">
          <input type="email" placeholder="Your email"
            class="w-full px-3 py-1 rounded-l bg-gray-800 text-white border border-gray-700 focus:outline-none">
          <button
            class="bg-blue-500 text-white px-4 py-1 rounded-r hover:bg-blue-600 transition">Subscribe</button>
        </form>
      </div>
    </div>

    <!-- Copyright -->
    <div class="mt-10 border-t border-gray-700 pt-4 text-center text-sm text-gray-500">
      <p>© 2025 ShowCase. All rights reserved. | Designed by X-team</p>
    </div>
  </footer>





  <script>
    // Counter Animation
    const counters = document.querySelectorAll('.counter');

    const animateCounter = (counter) => {
      const target = +counter.getAttribute('data-target');
      let current = 0;

      const speed = target < 100 ? 50 : target < 1000 ? 10 : 1;
      const increment = target / (target < 100 ? 40 : 100);

      const updateCount = () => {
        if (current < target) {
          current += increment;
          counter.innerText = Math.ceil(current);
          setTimeout(updateCount, speed);
        } else {
          if (target >= 1000000) {
            counter.innerText = (target / 1000000).toFixed(1) + 'M+';
          } else if (target >= 1000) {
            counter.innerText = (target / 1000).toFixed(1) + 'K+';
          } else {
            counter.innerText = target + (target === 4.9 ? '' : '+');
          }
        }
      };

      updateCount();
    };

    // Observer setup
    const observer = new IntersectionObserver((entries, observerInstance) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observerInstance.unobserve(entry.target); // Only run once
        }
      });
    }, {
      threshold: 0.5 // Trigger when at least 50% of the counter is visible
    });

    counters.forEach(counter => observer.observe(counter));



    // Profile Dropdown Toggle
    const profileMenuButton = document.getElementById('profileMenuButton');
    const profileDropdown = document.getElementById('profileDropdown');

    profileMenuButton.addEventListener('click', () => {
      profileDropdown.classList.toggle('hidden');
    });

    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!profileMenuButton.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.add('hidden');
      }
    });
  </script>
</body>

</html>