<?php
session_set_cookie_params([
  'secure' => true,
  'httponly' => true,
  'samesite' => 'Strict'
]);
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "showcase");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Initialize error variables
$signup_error = "";
$login_error = "";

$default_cover = "Images/default_cover.jpg";
$default_profile = "Images/default_profile.png";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $form_type = $_POST['form_type'];

  if ($form_type == "signup") {
    // SIGNUP logic
    $name = $_POST['name'];
    $email = $_POST['email'];
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
      $signup_error = "Email already registered.";
    } else {
      $stmt = $conn->prepare("INSERT INTO users (name, email, password, cover_photo, profile_picture) VALUES (?, ?, ?, ?, ?)");
      $stmt->bind_param("sssss", $name, $email, $hashed_password, $default_cover, $default_profile);

      if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['toast_message'] = "Welcome, $name!";
        header("Location: ../home.php");

        exit();
      } else {
        $signup_error = "Registration failed: " . $stmt->error;
      }
      $stmt->close();
    }
    $check->close();
  } elseif ($form_type == "login") {
    // LOGIN logic
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
      $stmt->bind_result($id, $name, $hashed_password);
      $stmt->fetch();

      if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;

        $message = urlencode("Welcome back, $name!");
        header("Location: ../home.php?message=$message");
        exit();
      } else {
        $login_error = "Incorrect password.";
      }
    } else {
      $login_error = "User not found.";
    }
    $stmt->close();
  }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <title>ShowCase | Landing</title>
  <!--font awesome cdn link-->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
  <style>
    /* Sliding Texts Animation */
    .TEXT {
      word-wrap: break-word;
      text-transform: uppercase;
    }

    .TEXT span:nth-child(2) {
      animation-delay: .05s;
    }

    .TEXT span:nth-child(3) {
      animation-delay: .1s;
    }

    .TEXT span:nth-child(4) {
      animation-delay: .15s;
    }

    .TEXT span:nth-child(5) {
      animation-delay: .2s;
    }

    .TEXT span:nth-child(6) {
      animation-delay: .25s;
    }

    .TEXT span:nth-child(7) {
      animation-delay: .3s;
    }

    .TEXT span:nth-child(6) {
      animation-delay: .35s;
    }

    .TEXT span:nth-child(9) {
      animation-delay: .4s;
    }

    .TEXT span:nth-child(10) {
      animation-delay: .45s;
    }

    .TEXT span:nth-child(11) {
      animation-delay: .5s;
    }

    .TEXT span:nth-child(12) {
      animation-delay: .55s;
    }

    .TEXT span:nth-child(13) {
      animation-delay: .6s;
    }

    .TEXT span:nth-child(14) {
      animation-delay: .65s;
    }

    .TEXT span:nth-child(15) {
      animation-delay: .7s;
    }

    .TEXT span:nth-child(16) {
      animation-delay: .75s;
    }

    .TEXT span {
      color: #002F5E;
      opacity: 0;
      display: inline-block;
      text-shadow: 3px 3px 4px rgba(201, 53, 53, 0.2);
      animation: animate .3s forwards;
      transform: translate(-300px, 0) scale(0);
    }

    @keyframes animate {
      60% {
        transform: translate(20px, 0) scale(1);
        color: #002F5E;
        ;
      }

      80% {
        transform: translate(20px, 0) scale(1);
        color: #002F5E;
      }

      99% {
        transform: translate(0) scale(1.2);
        color: #ff3f3f;
      }

      100% {
        transform: translate(0) scale(1);
        color: #002F5E;
        opacity: 1;
      }
    }

    /* Sliding Texts Animation */


    @keyframes slideCards {

      0%,
      40% {
        transform: translateX(0%);
      }

      50%,
      90% {
        transform: translateX(-50%);
      }

      100% {
        transform: translateX(0%);
      }
    }

    /* slider styles */
    .tranding-slider {
      padding: 2rem 0;
      position: relative;
      width: 100%;
    }

    /* Slide Style */
    .tranding-slide {
      background: #fff;
      border-radius: 2rem;
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
      transition: transform 0.5s ease, box-shadow 0.5s ease;
      width: 300px;
      height: auto;
      overflow: hidden;
    }

    .tranding-slide .tranding-slide-img img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-top-left-radius: 2rem;
      border-top-right-radius: 2rem;
    }

    /* Text Content */
    .tranding-slide-content {
      padding: 1rem;
      text-align: center;
    }

    .tranding-slide-content h3 {
      font-size: 1.25rem;
      margin-bottom: 0.25rem;
    }

    .tranding-slide-content p {
      font-size: 0.875rem;
      color: #555;
    }

    /* Button */
    .tranding-slide button {
      display: block;
      margin: 1rem auto;
    }

    .swiper-button-prev,
    .swiper-button-next {
      position: static !important;
    }



    /* Fade-in animation */
    @keyframes fadeInUp {
      0% {
        opacity: 0;
        transform: translateY(100px);
      }

      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .fade-in-up {
      animation: fadeInUp 1.1s ease-out both;
    }
  </style>

</head>

<body class="bg-white font-sans">

  <!-- Header start -->
  <header class="bg-gradient-to-r from-blue-100 via-teal-50 to-mint-50 flex justify-between items-center sticky top-0 z-50 shadow-md px-4 md:px-10">
    <!-- Logo -->
    <a href="#">
      <img src="../Images/logo.webp" class="w-[45px] h-[65px] md:w-[55px] md:h-[85px] hover:cursor-pointer" loading="eager">
    </a>

    <!-- Desktop Nav -->
    <div class="hidden md:flex space-x-8 lg:space-x-14 text-center">
      <a onclick="document.getElementById('signup_modal').showModal()" class="cursor-pointer hover:text-sky-500">Home</a>
      <a onclick="document.getElementById('signup_modal').showModal()" class="cursor-pointer hover:text-sky-500">About Us</a>
      <a onclick="document.getElementById('signup_modal').showModal()" class="cursor-pointer hover:text-sky-500">Events</a>
      <a onclick="document.getElementById('signup_modal').showModal()" class="cursor-pointer hover:text-sky-500">Jobs</a>
      <a onclick="document.getElementById('signup_modal').showModal()" class="cursor-pointer hover:text-sky-500">Contact Us</a>
    </div>

    <!-- Desktop Buttons -->
    <div class="hidden md:flex gap-3 font-semibold border-l-2 border-gray-400 pl-3">
      <button type="button" onclick="document.getElementById('login_modal').showModal()"
        class="text-gray-700 px-6 py-1 hover:text-blue-500 rounded-md">Login</button>
      <button type="button" onclick="document.getElementById('signup_modal').showModal()"
        class="bg-gradient-to-r from-blue-400 to-teal-400 rounded-md text-white px-6 py-2 hover:bg-gradient-to-l">Sign Up</button>
    </div>

    <!-- Mobile Menu Button -->
    <button class="md:hidden text-3xl" onclick="document.getElementById('mobile_menu').classList.toggle('hidden')">
      ☰
    </button>
  </header>

  <!-- Mobile Menu -->
  <div id="mobile_menu" class="hidden flex flex-col items-center bg-white shadow-md absolute top-[80px] left-0 w-full z-40 md:hidden">
    <a onclick="document.getElementById('signup_modal').showModal()" class="py-2 hover:text-sky-500">Home</a>
    <a onclick="document.getElementById('signup_modal').showModal()" class="py-2 hover:text-sky-500">About Us</a>
    <a onclick="document.getElementById('signup_modal').showModal()" class="py-2 hover:text-sky-500">Events</a>
    <a onclick="document.getElementById('signup_modal').showModal()" class="py-2 hover:text-sky-500">Jobs</a>
    <a onclick="document.getElementById('signup_modal').showModal()" class="py-2 hover:text-sky-500">Contact Us</a>

    <div class="flex flex-col gap-2 py-3 border-t w-full items-center">
      <button type="button" onclick="document.getElementById('login_modal').showModal()"
        class="text-gray-700 px-6 py-1 hover:text-blue-500">Login</button>
      <button type="button" onclick="document.getElementById('signup_modal').showModal()"
        class="bg-gradient-to-r from-blue-400 to-teal-400 rounded-md text-white px-6 py-2 hover:bg-gradient-to-l">Sign Up</button>
    </div>
  </div>
  <!-- Header end -->




  <!-- Body start -->
  <div class="bg-gradient-to-b from-sky-50 via-teal-50 to-white fade-in-up">
    <div>

      <!-- Hero Section start -->
      <div class="flex flex-col md:flex-row justify-between p-8 ml-10">
        <div>
          <section class="ml-20 mt-40">
            <p class="text-xl font-semibold text-gray-600">CREATE YOUR SUCCESS</p>
            <h1 class="text-6xl font-bold mt-3 mb-4 text-gray-700">Build Your <span class="text-transparent bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text hover:from-red-400 hover:to-yellow-400 transition-all duration-500">Future</span> Here</h1>
            <p class="text-xl font-semibold">
            <div class="TEXT"><span>S</span><span>h</span><span>a</span><span>r</span><span>e</span>&nbsp;<span>y</span><span>o</span><span>u</span><span>r</span>&nbsp;<span>s</span><span>k</span><span>i</span><span>l</span><span>l</span><span>s</span></div>
            <div class="TEXT"><span>S</span><span>h</span><span>o</span><span>w</span>&nbsp;<span>y</span><span>o</span><span>u</span><span>r</span>&nbsp;<span>p</span><span>r</span><span>o</span><span>j</span><span>e</span><span>c</span><span>t</span><span>s</span></div>
            <div class="TEXT"><span>a</span><span>n</span><span>d</span>&nbsp;<span>s</span><span>t</span><span>a</span><span>n</span><span>d</span>&nbsp;<span>o</span><span>u</span><span>t.</span></div>
            </p>
            <p class="text-xl text-gray-800 font-semibold mt-7">Join thousands of learners and creators sharing their work <br> every day.</p>
            <button type="button" onclick="document.getElementById('signup_modal').showModal()"
              class="bg-gradient-to-r from-blue-400 to-teal-400 rounded-md text-white px-8 py-3 font-semibold mt-10 hover:bg-gradient-to-l from-blue-400 to-teal-400">Get Started <i
                class="fa-solid fa-arrow-right pl-2"></i></button>
          </section>
        </div>

        <div class="relative w-[450px] h-[500px] mr-40 mt-10">
          <!-- Top Image with badge -->
          <div class="relative z-10 rounded-xl overflow-hidden shadow-lg">
            <img src="../Images/landingTopImage.webp" alt="" class="w-80 h-80 object-cover rounded-xl" loading="lazy">
          </div>
          <!-- Badge top left -->
          <div class=" absolute top-0 left-0 z-30 bg-gradient-to-r from-blue-400 to-teal-500 text-white px-5 py-1 rounded-lg shadow-md text-xl -translate-x-5 -translate-y-5 flex items-center gap-2">
            <i class="fa-solid fa-briefcase mr-2 text-2xl"></i>
            <p class="text-sm"><span class="text-xl font-semibold">45k </span> <br>Jobs</p>
          </div>
          <!-- Badge top right -->
          <div
            class="absolute top-0 right-0 z-30 bg-gradient-to-r from-sky-100 to-sky-50 text-black px-5 py-1 rounded-lg shadow-md text-xl -translate-x-5 translate-y-20 flex items-center gap-2">
            <i class="fa-solid fa-star"></i>
            <p class="text-sm"><span class="text-xl font-semibold">4.9/5 </span> <br>reviews</p>
          </div>

          <!-- Bottom Image overlapping to right bottom with badge -->
          <div class="absolute bottom-0 right-0 z-20 rounded-xl overflow-hidden shadow-lg translate-x-10 translate-y-10">
            <img src="../Images/landingButtonImage.webp" alt=" " class="w-64 h-80 object-cover rounded-xl" loading="lazy">
          </div>
          <div
            class=" absolute buttom-0 right-0 z-30 bg-gradient-to-r from-green-200 to-green-100 text-blue-500 px-5 py-1 rounded-lg shadow-md text-xl translate-x-20 translate-y-40 flex items-center gap-2">
            <i class="fa-solid fa-star"></i>
            <p class="text-sm"><span class="text-xl font-semibold">20k </span> <br>skills</p>
          </div>
          <!-- Bottom left badge (outside images) -->
          <div
            class="absolute bottom-0 left-0 bg-gradient-to-r from-sky-200 to-sky-0 text-blue-500 px-4 py-2 rounded-lg shadow-md text-sm font-semibold flex items-center">
            <i class="fa-solid fa-user mr-2 text-3xl"></i>
            <p class="text-md"><span class="text-2xl font-semibold">30k+ </span> <br>Active Users</p>
          </div>
        </div>
      </div>
    </div>




    <!-- Categories Section -->
    <div class="text-center py-8 mt-20">
      <p class="text-blue-700 font-semibold uppercase mb-2">Browse Categories</p>
      <h1 class="text-transparent bg-gradient-to-r from-gray-800 via-gray-700 to-gray-500 bg-clip-text text-4xl font-bold mb-10">Popular Topics to Learn <i class="fa-solid fa-share"></i></h1>

      <div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-20 -px-4">
        <!-- Category Card -->
        <div class="bg-gradient-to-r from-teal-100 via-white to-mint-200 shadow-md rounded-xl p-3 flex items-center gap-4 hover:shadow-lg transition">
          <div class="p-2 rounded-md">
            <i class="fa-solid fa-building w-8 h-8 text-3xl text-blue-500"></i>
          </div>
          <p class="font-semibold text-left">Web Design &<br>Development</p>
        </div>

        <div class="bg-gradient-to-r from-teal-100 via-white to-mint-200 shadow-md rounded-xl p-3 flex items-center gap-4 hover:shadow-lg transition">
          <div class="p-2 rounded-md">
            <i class="fa-solid fa-palette w-8 h-8 text-3xl text-blue-500"></i>
          </div>
          <p class="font-semibold text-left">Creative Arts <br>& Media</p>
        </div>

        <div class="bg-gradient-to-r from-teal-100 via-white to-mint-200 shadow-md rounded-xl p-3 flex items-center gap-4 hover:shadow-lg transition">
          <div class="p-2 rounded-md">
            <i class="fa-regular fa-images w-8 h-8 text-3xl text-blue-500"></i>
          </div>
          <p class="font-semibold text-left">Photo &<br>Video Editing</p>
        </div>

        <div class="bg-gradient-to-r from-teal-100 via-white to-mint-200 shadow-md rounded-xl p-2 flex items-center gap-4 hover:shadow-lg transition">
          <div class="p-2 rounded-md">
            <i class="fa-brands fa-figma w-8 h-8 text-3xl text-blue-500"></i>
          </div>
          <p class="font-semibold text-left">App Design & Development</p>
        </div>

        <div class="bg-gradient-to-r from-teal-100 via-white to-mint-200 shadow-md rounded-xl p-5 flex items-center gap-4 hover:shadow-lg transition">
          <div class="p-2 rounded-md">
            <i class="fa-solid fa-headphones-simple w-8 h-8 text-3xl text-blue-500"></i>
          </div>
          <p class="font-semibold text-left">Public<br>Speaking</p>
        </div>

        <div class="bg-gradient-to-r from-teal-100 via-white to-mint-200 shadow-md rounded-xl p-3 flex items-center gap-4 hover:shadow-lg transition">
          <div class="p-2 rounded-md">
            <i class="fa-solid fa-laptop-medical w-8 h-8 text-3xl text-blue-500"></i>
          </div>
          <p class="font-semibold text-left">Animation & VFX</p>
        </div>

        <div class="bg-gradient-to-r from-teal-100 via-white to-mint-200 shadow-md rounded-xl p-5 flex items-center gap-4 hover:shadow-lg transition">
          <div class="p-2 rounded-md">
            <i class="fa-solid fa-language w-8 h-8 text-3xl text-blue-500"></i>
          </div>
          <p class="font-semibold text-left">Language <br>Learning</p>
        </div>
      </div>

      <!-- Browse all categories button -->
      <div class="mt-10 mb-10">
        <a href="#" class="text-blue-500 font-bold text-xl hover:underline inline-flex items-center gap-2">Browse All
          Categories <i class="fa-solid fa-arrow-right"></i> </a>
      </div>
    </div>
  </div>



  <section class="py-10 px-6 bg-white">
    <h2 class="text-blue-500 font-bold uppercase mb-2">SKILL SPOTLIGHT</h2>
    <h1 class="text-transparent bg-gradient-to-r from-gray-800 via-gray-700 to-gray-500 bg-clip-text text-4xl font-bold mb-10 text-gray-600">Pick a Topic</h1>

    <div class="swiper tranding-slider">
      <div class="swiper-wrapper">
        <!-- slider 1 -->
        <div class="swiper-slide bg-white rounded-2xl shadow-xl w-80 overflow-hidden">
          <img src="../Images/music.webp" class="w-full h-64 object-cover" />
          <div class="p-4">
            <h3 class="font-semibold text-lg">Music Theory</h3>
            <p class="text-sm text-gray-500 mt-1">Creative Arts</p>
          </div>
          <button class="bg-gradient-to-r from-blue-400 to-teal-500 text-white py-2 px-4 ml-4 mb-4 rounded-md hover:from-teal-500 hover:to-blue-400">Details</button>
        </div>
        <!-- slider 2 -->
        <div class="swiper-slide bg-white rounded-2xl shadow-xl w-80 overflow-hidden">
          <img src="../Images/animation.webp" class="w-full h-64 object-cover" />
          <div class="p-4">
            <h3 class="font-semibold text-lg">Animation Basics</h3>
            <p class="text-sm text-gray-500 mt-1">VFX</p>
          </div>
          <button class="bg-gradient-to-r from-blue-400 to-teal-500 text-white py-2 px-4 ml-4 mb-4 rounded-md hover:from-teal-500 hover:to-blue-400">Details</button>
        </div>
        <!-- slider 3 -->
        <div class="swiper-slide bg-white rounded-2xl shadow-xl w-80 overflow-hidden">
          <img src="../Images/uidesign.webp" class="w-full h-64 object-cover" />
          <div class="p-4">
            <h3 class="font-semibold text-lg">UI Design</h3>
            <p class="text-sm text-gray-500 mt-1">App Design</p>
          </div>
          <button class="bg-gradient-to-r from-blue-400 to-teal-500 text-white py-2 px-4 ml-4 mb-4 rounded-md hover:from-teal-500 hover:to-blue-400">Details</button>
        </div>
        <!-- slider 4 -->
        <div class="swiper-slide bg-white rounded-2xl shadow-xl w-80 overflow-hidden">
          <img src="../Images/photography.webp" class="w-full h-64 object-cover" />
          <div class="p-4">
            <h3 class="font-semibold text-lg">Photography</h3>
            <p class="text-sm text-gray-500 mt-1">Visual Media</p>
          </div>
          <button class="bg-gradient-to-r from-blue-400 to-teal-500 text-white py-2 px-4 ml-4 mb-4 rounded-md hover:from-teal-500 hover:to-blue-400">Details</button>
        </div>
        <!-- slider 5 -->
        <div class="swiper-slide bg-white rounded-2xl shadow-xl w-80 overflow-hidden">
          <img src="../Images/contentwriting.webp" class="w-full h-64 object-cover" />
          <div class="p-4">
            <h3 class="font-semibold text-lg">Content Writing</h3>
            <p class="text-sm text-gray-500 mt-1">Writing</p>
          </div>
          <button class="bg-gradient-to-r from-blue-400 to-teal-500 text-white py-2 px-4 ml-4 mb-4 rounded-md hover:from-teal-500 hover:to-blue-400">Details</button>
        </div>
        <!-- slider 6 -->
        <div class="swiper-slide bg-white rounded-2xl shadow-xl w-80 overflow-hidden">
          <img src="../Images/language.webp" class="w-full h-64 object-cover" />
          <div class="p-4">
            <h3 class="font-semibold text-lg">Language Learning</h3>
            <p class="text-sm text-gray-500 mt-1">Language</p>
          </div>
          <button class="bg-gradient-to-r from-blue-400 to-teal-500 text-white py-2 px-4 ml-4 mb-4 rounded-md hover:from-teal-500 hover:to-blue-400">Details</button>
        </div>
        <!-- slider 7 -->
        <div class="swiper-slide bg-white rounded-2xl shadow-xl w-80 overflow-hidden">
          <img src="../Images/dancing.webp" class="w-full h-64 object-cover" />
          <div class="p-4">
            <h3 class="font-semibold text-lg">Dancing</h3>
            <p class="text-sm text-gray-500 mt-1">Rhythm</p>
          </div>
          <button class="bg-gradient-to-r from-blue-400 to-teal-500 text-white py-2 px-4 ml-4 mb-4 rounded-md hover:from-teal-500 hover:to-blue-400">Details</button>
        </div>
        <!-- slider 8 -->
        <div class="swiper-slide bg-white rounded-2xl shadow-xl w-80 overflow-hidden">
          <img src="../Images/project.webp" class="w-full h-64 object-cover" />
          <div class="p-4">
            <h3 class="font-semibold text-lg">Iot Project</h3>
            <p class="text-sm text-gray-500 mt-1">Technology</p>
          </div>
          <button class="bg-gradient-to-r from-blue-400 to-teal-500 text-white py-2 px-4 ml-4 mb-4 rounded-md hover:from-teal-500 hover:to-blue-400">Details</button>
        </div>
      </div>

      <!-- Pagination -->
      <div class="swiper-pagination !static mt-10"></div>
    </div>

  </section>








  <!-- Section 2: Membership -->
  <section class="text-center py-16 px-6">
    <h4 class="text-blue-600 font-semibold uppercase">Membership</h4>
    <h1 class="text-transparent bg-gradient-to-r from-gray-800 via-gray-700 to-gray-500 bg-clip-text text-4xl font-bold mt-2">Start your Journey Today</h1>

    <!-- Features -->
    <div class="mt-12 flex flex-col md:flex-row justify-center gap-6">
      <!-- Feature 1 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-md rounded-lg p-6 flex  space-x-4">
        <div class="p-2 rounded-full text-xl">
          <i class="fa-solid fa-magnifying-glass"></i>
        </div>
        <div class="text-left">
          <h3 class="font-semibold">Skill-Based Matching</h3>
          <p class="text-sm text-gray-500">Find peers with similar skills</p>
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-md rounded-lg p-6 flex items-center space-x-4">
        <div class="p-2 rounded-full text-xl">
          <i class="fa-solid fa-file"></i>
        </div>
        <div class="text-left">
          <h3 class="font-semibold">Portfolio Builder</h3>
          <p class="text-sm text-gray-500">Create your public portfolio</p>
        </div>
      </div>

      <!-- Feature 3 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-md rounded-lg p-6 flex items-center space-x-4">
        <div class="p-2 rounded-full text-xl">
          <i class="fa-solid fa-hourglass-start"></i>
        </div>
        <div class="text-left">
          <h3 class="font-semibold">Flexible Scheduling</h3>
          <p class="text-sm text-gray-500">Learn anytime, anywhere</p>
        </div>
      </div>

      <!-- Feature 4 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-md rounded-lg p-6 flex items-center space-x-4">
        <div class="p-2 text-xl">
          <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a6 6 0 016 6v1.586a1 1 0 01-.293.707l-5 5a1 1 0 01-1.414 0l-5-5A1 1 0 014 9.586V8a6 6 0 016-6z" />
          </svg>
        </div>
        <div class="text-left">
          <h3 class="font-semibold">Educator Support</h3>
          <p class="text-sm text-gray-500">Get your answers.</p>
        </div>
      </div>
    </div>

    <!-- Call to Action -->
    <div class="mt-12">
      <button type="button" onclick="document.getElementById('signup_modal').showModal()"
        class="bg-gradient-to-r from-blue-400 to-teal-400 text-white px-6 py-3 rounded-md font-semibold hover:bg-blue-700 transition">
        Sign Up Now →
      </button>
    </div>
  </section>


  <section class="text-center py-16 px-4">
    <h4 class="text-blue-600 font-bold uppercase mb-2">Funfact</h4>
    <h2 class="text-4xl font-extrabold text-transparent bg-gradient-to-r from-gray-800 via-gray-700 to-gray-500 bg-clip-text mb-10">
      Strength in Numbers</h2>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 max-w-5xl mx-auto">
      <!-- Card 1 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-lg rounded-xl p-6">
        <div class="text-3xl font-bold text-blue-600 counter" data-target="23000000">0</div>
        <p class="text-gray-600 mt-2">Active members</p>
      </div>
      <!-- Card 2 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-lg rounded-xl p-6">
        <div class="text-3xl font-bold text-blue-600 counter" data-target="130000">0</div>
        <p class="text-gray-600 mt-2">Share topic</p>
      </div>
      <!-- Card 3 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-lg rounded-xl p-6">
        <div class="text-3xl font-bold text-blue-600 counter" data-target="36">0</div>
        <p class="text-gray-600 mt-2">Categories</p>
      </div>
      <!-- Card 4 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-lg rounded-xl p-6">
        <div class="text-3xl font-bold text-blue-600 counter" data-target="52145684">0</div>
        <p class="text-gray-600 mt-2">Job posts</p>
      </div>
      <!-- Card 5 -->
      <div class="bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-lg rounded-lg p-6">
        <div class="text-3xl font-bold text-blue-600 counter" data-target="4.9">0</div>
        <p class="text-gray-600 mt-2">Overall review</p>
      </div>
    </div>
  </section>


  <section class="px-10 py-20 flex flex-col md:flex-row items-center justify-between max-w-6xl mx-auto">

    <!-- Left Content -->
    <div class="max-w-md mb-10 md:mb-0">
      <h5 class="text-blue-600 font-bold uppercase mb-2">Get Started</h5>
      <h2 class="text-4xl font-extrabold text-transparent bg-gradient-to-r from-gray-800 via-gray-700 to-gray-500 bg-clip-text mb-4">Become a Member</h2>
      <p class="text-gray-600 mb-6">Join millions of people from around the world learning together. Online learning is as easy and natural as chatting.</p>
      <button type="button" onclick="document.getElementById('signup_modal').showModal()"
        class="bg-gradient-to-l from-blue-400 to-teal-400 rounded-md text-white px-8 py-3 font-semibold mt-10 hover:bg-gradient-to-r from-blue-400 to-teal-400">Join now <i
          class="fa-solid fa-arrow-right pl-2"></i></button>
    </div>

    <!-- Right Content (Image + Floating Stat) -->
    <div class="relative">
      <img src="../Images/member.webp" alt="Member" class="w-[500px] h-auto rounded-xl shadow-lg" />
      <div class="absolute bottom-[-30px] left-[-30px] bg-gradient-to-r from-blue-50 via-teal-50 to-white shadow-xl rounded-xl p-4 flex items-center space-x-3">
        <div class="p-2">
          <!-- Icon (Member) -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C10.067 2 8.5 3.567 8.5 5.5S10.067 9 12 9s3.5-1.567 3.5-3.5S13.933 2 12 2zM3 20v-1.278C3 16.838 5.79 15 9.125 15h5.75C18.21 15 21 16.838 21 18.722V20H3z" />
          </svg>
        </div>
        <div>
          <div class="text-lg font-bold text-blue-600">20k</div>
          <div class="text-sm text-gray-500">Members</div>
        </div>
      </div>
    </div>
  </section>




  <section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
      <!-- Title -->
      <div class="text-center mb-10">
        <p class="text-blue-600 font-semibold">TESTIMONIALS</p>
        <h2 class="text-3xl font-bold text-transparent bg-gradient-to-r from-gray-800 via-gray-700 to-gray-500 bg-clip-text">Member Community Feedback</h2>
      </div>

      <!-- Testimonials -->
      <div class="flex flex-col md:flex-row gap-6">
        <!-- Testimonial Card 1 -->
        <div class="bg-teal-50 rounded-lg p-6 shadow-md flex-1">
          <div class="flex items-center mb-4">
            <!-- Stars -->
            <div class="flex text-yellow-400">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
          </div>
          <p class="text-gray-700 mb-6">“It’s so simple, yet rewards wonderful. I am a novice in programming, but thanks to ShowCase, no more.”</p>
          <div class="flex items-center">
            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="w-12 h-12 rounded-full mr-4">
            <div>
              <h4 class="font-bold">Maruf Sikder</h4>
              <p class="text-gray-500 text-sm">Bangladesh</p>
            </div>
          </div>
        </div>

        <!-- Testimonial Card 2 -->
        <div class="bg-teal-50 rounded-lg p-6 shadow-md flex-1">
          <div class="flex items-center mb-4">
            <div class="flex text-yellow-400">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
          </div>
          <p class="text-gray-700 mb-6">“ShowCase helped me build my portfolio and find mentors. Highly recommended for students.”</p>
          <div class="flex items-center">
            <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="User" class="w-12 h-12 rounded-full mr-4">
            <div>
              <h4 class="font-bold">Munna Biswas</h4>
              <p class="text-gray-500 text-sm">Bangladesh</p>
            </div>
          </div>
        </div>

        <!-- Testimonial Card 3 -->
        <div class="bg-teal-50 rounded-lg p-6 shadow-md flex-1">
          <div class="flex items-center mb-4">
            <div class="flex text-yellow-400">
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
              <i class="fas fa-star"></i>
            </div>
          </div>
          <p class="text-gray-700 mb-6">“Great community and excellent features for learning and networking in one place.”</p>
          <div class="flex items-center">
            <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="User" class="w-12 h-12 rounded-full mr-4">
            <div>
              <h4 class="font-bold">Tomalika Sarker</h4>
              <p class="text-gray-500 text-sm">Bangladesh</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>



  <!-- Sign Up Modal -->
  <dialog id="signup_modal" class="modal rounded-lg w-11/12 md:w-1/2 p-6">
    <div class="modal-box">
      <h3 class="font-bold text-2xl mb-6 text-center">Join ShowCase</h3>

      <form method="post" class="space-y-4">
        <input type="hidden" name="form_type" value="signup">
        <input type="text" name="name" placeholder="Full Name" required
          class="w-full border border-gray-300 rounded p-2">
        <input type="email" name="email" placeholder="Email Address" required
          class="w-full border border-gray-300 rounded p-2">
        <?php if (!empty($signup_error)) {
          echo "<p style='color:red;'>$signup_error</p>";
        } ?>
        <input type="password" name="password" placeholder="Password" required
          class="w-full border border-gray-300 rounded p-2">
        <button type="submit" class="bg-gradient-to-r from-blue-400 to-teal-400 text-white w-full p-2 rounded hover:bg-gray-800">Create Account</button>
      </form>

      <div class="text-right mt-4">
        <form method="dialog">
          <button type="submit" class="text-sm border border-gray-100 px-6 py-2 mt-6 bg-red-300 text-black items-center hover:bg-red-500 rounded-lg">Close</button>
        </form>
      </div>
    </div>
  </dialog>

  <!-- Login Modal -->
  <dialog id="login_modal" class="modal rounded-lg w-11/12 md:w-1/3 p-6 ">
    <div class="modal-box items-center">
      <h3 class="text-2xl font-bold mb-6 text-center text-transparent bg-gradient-to-r from-gray-800 via-gray-700 to-gray-500 bg-clip-text">Welcome Back</h3>
      <form method="post" class="space-y-4">
        <input type="hidden" name="form_type" value="login">
        <input type="email" name="email" placeholder="Email Address" required
          class="w-full border border-gray-300 rounded p-2">
        <input type="password" name="password" placeholder="Password" required
          class="w-full border border-gray-300 rounded p-2">
        <?php if (!empty($login_error)) {
          echo "<p style='color:red;'>$login_error</p>";
        } ?>
        <button type="submit" class="bg-gradient-to-r from-blue-400 to-teal-500 text-white w-full p-2 rounded hover:bg-gradient-to-t from-blue-400 to-teal-500">Login</button>
      </form>
      <div class="modal-action text-right mt-4r">
        <form class="" method="dialog">
          <button type="submit" class="text-sm border border-gray-100 px-6 py-2 mt-6 bg-red-300 text-black items-center hover:bg-red-500 rounded-lg">Close</button>
        </form>
      </div>
    </div>
  </dialog>



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
          <li><a href="#" class="hover:text-blue-400 transition">Home</a></li>
          <li><a href="aboutUs.php" class="hover:text-blue-400 transition">About Us</a></li>
          <li><a href="#" class="hover:text-blue-400 transition">Events</a></li>
          <li><a href="#" class="hover:text-blue-400 transition">Jobs</a></li>
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
          <a href="#"><img src="../Images/twitter.webp" alt="Twitter"
              class="w-6 h-6 hover:scale-110 transition duration-200" /></a>
          <a href="#"><img src="../Images/youtube.webp" alt="YouTube"
              class="w-6 h-6 hover:scale-110 transition duration-200" /></a>
          <a href="#"><img src="../Images/facebook.webp" alt="Facebook"
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

  <!-- Swiper JS -->
  <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      <?php if (!empty($login_error)) { ?>
        document.getElementById('login_modal').showModal();
      <?php } elseif (!empty($signup_error)) { ?>
        document.getElementById('signup_modal').showModal();
      <?php } ?>
    });

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


    const trandingSlider = new Swiper('.tranding-slider', {
      effect: 'coverflow',
      grabCursor: true,
      centeredSlides: true,
      loop: true,
      slidesPerView: 'auto',
      spaceBetween: 20,

      coverflowEffect: {
        rotate: 0,
        stretch: 0,
        depth: 100,
        modifier: 2.5,
        slideShadows: true,
      },

      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },

      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      autoplay: {
        delay: 1000,
        disableOnInteraction: false,
      },
    });
    const sliderContainer = document.querySelector('.tranding-slider');

    sliderContainer.addEventListener('mouseenter', () => {
      trandingSlider.autoplay.stop();
    });

    sliderContainer.addEventListener('mouseleave', () => {
      trandingSlider.autoplay.start();
    });
  </script>
</body>

</html>