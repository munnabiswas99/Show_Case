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


// Handle form submission
$submitted = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $conn = new mysqli("localhost", "root", "", "showcase");

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $question = trim($_POST['question'] ?? '');
  $agreed = isset($_POST['terms']);

  if ($name && $email && $question && $agreed) {
    $stmt = $conn->prepare("INSERT INTO `Q&A` (name, email, question) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $question);

    if ($stmt->execute()) {
      $submitted = true;
    }

    $stmt->close();
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>showcase | Contact</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!--font awesome cdn link-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


  <style>
    @keyframes slide-in {
      0% {
        transform: translateX(-50px);
        opacity: 0;
      }

      100% {
        transform: translateX(0);
        opacity: 1;
      }
    }

    .animate-slide-in {
      animation: slide-in 0.7s ease-out forwards;
    }

    .delay-200 {
      animation-delay: 0.2s;
    }

    .delay-400 {
      animation-delay: 0.4s;
    }


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

  <!-- Success Message -->
  <?php if ($submitted): ?>
    <div id="successMessage" class="fixed top-40 left-1/2 transform -translate-x-1/2 bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded shadow-md flex items-center gap-2 z-50">
      <i class="fas fa-check-circle"></i>
      <span>Your question has been submitted successfully!</span>
      <div id="progressBar" class="h-1 bg-green-500 absolute bottom-0 left-0"></div>
    </div>

    <script>
      const bar = document.getElementById('progressBar');
      let width = 100;
      const interval = setInterval(() => {
        width -= 1;
        bar.style.width = width + "%";
        if (width <= 0) {
          clearInterval(interval);
          document.getElementById("successMessage").style.display = "none";
        }
      }, 30); // auto-hide in ~3 seconds
    </script>
  <?php endif; ?>

  <!-- Header -->
  <header class="bg-gradient-to-r from-blue-100 via-teal-50 to-mint-50 flex justify-between items-center sticky top-0 z-50 relative shadow-md">
    <a href="home.php"><img src="Images/logo.webp" class="w-[55px] h-[85px] ml-10 hover:cursor-pointer" loading="eager"></a>

    <div class="text-center pl-40">
      <a href="home.php" class="pl-40 hover:text-sky-500">Home</a>
      <a href="aboutUs.php" class="pl-14 hover:text-sky-500">About Us</a>
      <a href="event.php" class="pl-14 hover:text-sky-500">Events</a>
      <a href="job.php" class="pl-14 hover:text-sky-500">Jobs</a>
      <a href="contact.php" class="text-blue-500 text-xl pl-14 hover:text-sky-500">Contact</a>
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





  <div class="bg-gradient-to-b from-sky-50 via-mint-50 to-mint-50 animate-rise-in pt-10">
    <div class="max-w-7xl ml-20 mr-20 px-4 py-12 grid md:grid-cols-2 gap-8 items-center pt-20">

      <!-- Contact Info Section -->
      <div class="ml-10">
        <h4 class="text-red-500 font-semibold mb-2 text-xl">Contact</h4>
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-8">Get in touch with us</h1>

        <!-- Phone -->
        <div class="flex items-start mb-6 transform transition duration-700 ease-out translate-x-[-50px] opacity-0 animate-slide-in">
          <div class="bg-blue-600 text-white rounded-full p-4 mr-4">
            <i class="fas fa-phone-alt text-xl"></i>
          </div>
          <div>
            <h3 class="font-bold text-lg">Call us on: +8801610348126</h3>
            <p class="text-gray-500 text-sm">Our office hours are Sunday – Tuesday, 9 am–6 pm</p>
          </div>
        </div>

        <!-- Email -->
        <div class="flex items-start mb-6 transform transition duration-700 ease-out translate-x-[-50px] opacity-0 animate-slide-in delay-200">
          <div class="bg-yellow-400 text-white rounded-full p-4 mr-4">
            <i class="fas fa-envelope text-xl"></i>
          </div>
          <div>
            <h3 class="font-bold text-lg">Email us directly</h3>
            <p class="text-gray-500 text-sm">showcase@gmail.com</p>
          </div>
        </div>

        <!-- Location -->
        <div class="flex items-start mb-6 transform transition duration-700 ease-out translate-x-[-50px] opacity-0 animate-slide-in delay-400">
          <div class="bg-green-500 text-white rounded-full p-4 mr-4">
            <i class="fas fa-map-marker-alt text-xl"></i>
          </div>
          <div>
            <h3 class="font-bold text-lg">Our Location</h3>
            <p class="text-gray-500 text-sm">LM-Tower, Uttra Sector-11, Dhaka, Bangladesh</p>
          </div>
        </div>

        <!-- Get Direction and Social Links -->
        <div class="mt-6 flex items-center space-x-6">
          <a href="#" class="text-red-600 font-semibold border-b-2 border-red-600 pb-1">Get Direction</a>
          <div class="flex space-x-4 text-gray-600">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-linkedin-in"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
          </div>
        </div>
      </div>

      <!-- Map Section -->
      <div class="shadow-lg rounded-lg overflow-hidden">
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d29182.018192188203!2d90.30683359919722!3d23.898401565069424!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sbd!4v1752263957275!5m2!1sen!2sbd"
          width="100%"
          height="450"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>







    <div class="max-w-7xl mx-auto px-4 py-12">
      <div class="text-center mb-10">
        <h4 class="text-red-500 font-semibold uppercase">Sent us a message</h4>
        <h2 class="text-3xl md:text-4xl font-extrabold mt-2 text-transparent bg-gradient-to-r from-blue-400 via-teal-600 to-mint-600 bg-clip-text">We will Answer all your Questions</h2>
      </div>

      <div class="grid md:grid-cols-2 gap-8 items-center">

        <!-- Left Image -->
        <div>
          <img src="https://images.pexels.com/photos/8867431/pexels-photo-8867431.jpeg" alt="Support Person" class="rounded-xl shadow-md w-full object-cover" />
        </div>

        <!-- Right Form -->
        <!-- Contact Form -->
        <div class="max-w-xl mx-auto mt-20 p-6 bg-white rounded shadow-md">
          <h2 class="text-2xl font-bold mb-6 text-center text-gray-700">Ask a Question</h2>

          <form method="POST" class="space-y-4">
            <input
              type="text"
              name="name"
              placeholder="Name"
              required
              class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-400" />

            <input
              type="email"
              name="email"
              placeholder="Email"
              required
              class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-400" />

            <textarea
              name="question"
              rows="5"
              placeholder="Your Question"
              required
              class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>

            <div class="flex items-center space-x-2">
              <input type="checkbox" id="terms" name="terms" required class="accent-red-500" />
              <label for="terms" class="text-sm text-gray-700">
                I agree to the <span class="font-semibold text-gray-900">Terms</span>
              </label>
            </div>

            <button
              type="submit"
              class="bg-gradient-to-r from-blue-400 to-teal-400 text-white px-6 py-3 rounded-md shadow-md hover:bg-gradient-to-b flex items-center gap-2">
              <i class="fas fa-paper-plane"></i> Send
            </button>
          </form>
        </div>
      </div>
    </div>



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