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

$jobs = $conn->query("SELECT * FROM jobs ORDER BY created_at DESC");

// Handle search query
$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';
$experience = $_GET['experience'] ?? '';

// Build query with filters
$sql = "SELECT * FROM jobs WHERE 1=1";
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (job_title LIKE '%$search%' OR company_name LIKE '%$search%')";
}
if (!empty($location)) {
    $location = $conn->real_escape_string($location);
    $sql .= " AND location LIKE '%$location%'";
}
if (!empty($experience)) {
    $experience = $conn->real_escape_string($experience);
    $sql .= " AND experience LIKE '%$experience%'";
}

$sql .= " ORDER BY created_at DESC";
$jobs = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>showcase | Events</title>
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
            <a href="aboutUs.php" class="pl-14 hover:text-sky-500">About Us</a>
            <a href="event.php" class="pl-14 hover:text-sky-500">Events</a>
            <a href="job.php" class="text-blue-500 text-xl pl-14 hover:text-sky-500">Jobs</a>
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

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 text-center">
            ✅ Job posted successfully!
        </div>
    <?php endif; ?>




    <div class="pt-20 bg-gradient-to-b from-sky-50 via-mint-50 to-mint-50">
        <div class="max-w-6xl mx-auto animate-rise-in">
            <div class="flex-1 p-6 min-h-[260px]  text-center  px-20  mt-10">
                <h2 class="text-2xl font-bold text-gray-800 mb-2 flex justify-center items-center gap-2">
                    🚀 Share Exciting Opportunities with Top Talent!
                </h2>
                <p class="text-gray-800 mb-4">Post job openings with role details, requirements, and perks. Connect with passionate professionals who are ready to contribute and grow with your organization.</p>
                <a href="job_form.php" class="inline-block bg-gradient-to-r from-blue-400 to-teal-400 hover:bg-gradient-to-l hover:from-blue-400 hover:to-teal-400 text-white font-semibold px-6 py-2 rounded-lg transition duration-300">
                    <i class="fas fa-upload mr-2"></i> Job Post
                </a>
            </div>

            <h1 class="text-3xl font-bold mb-6 text-center">Find Your Job</h1>

            <!-- Search + Filter Form -->
            <form method="GET" class="flex flex-col md:flex-row gap-4 mb-10 items-center justify-center">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by job title or company" class="border px-4 py-2 rounded w-full md:w-1/3" />
                <input type="text" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="Location" class="border px-4 py-2 rounded w-full md:w-1/4" />
                <input type="text" name="experience" value="<?= htmlspecialchars($experience) ?>" placeholder="Experience (e.g. 2 years)" class="border px-4 py-2 rounded w-full md:w-1/4" />
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Search</button>
            </form>

            <!-- Job Cards -->
            <h1 class="text-3xl font-bold mb-6 text-center">Latest Job Posts</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if ($jobs->num_rows > 0): ?>
                    <?php while ($job = $jobs->fetch_assoc()): ?>
                        <div class="bg-blue-50 rounded-lg shadow p-6 hover:bg-white hover:shadow-xl transform hover:scale-[1.02] transition duration-300 ease-in-out flex flex-col">
                            <!-- Logo Placeholder & Job Title -->
                            <div class="flex items-center space-x-4 mb-4">
                                <!-- Company Logo Placeholder -->
                                <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 font-bold uppercase text-lg select-none">
                                    <?= strtoupper(substr(htmlspecialchars($job['company_name']), 0, 2)) ?>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900 hover:text-blue-600 transition-colors cursor-pointer">
                                    <?= htmlspecialchars($job['job_title']) ?>
                                </h2>
                            </div>

                            <!-- Company & Location -->
                            <p class="text-gray-700 font-semibold mb-2">
                                <span class="text-blue-600"><?= htmlspecialchars($job['company_name']) ?></span> —
                                <span class="italic"><?= htmlspecialchars($job['location']) ?></span>
                            </p>

                            <!-- Experience & Salary -->
                            <div class="flex items-center text-gray-600 text-sm space-x-6 mb-3">
                                <div class="flex items-center space-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.34 6.873L12 14z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14L5.84 10.578a12.083 12.083 0 00-.34 6.873L12 14z" />
                                    </svg>
                                    <span>Experience: <?= htmlspecialchars($job['experience']) ?></span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 1.343-3 3 0 2 3 5 3 5s3-3 3-5c0-1.657-1.343-3-3-3z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8V5m0 10v-3" />
                                    </svg>
                                    <span>Salary: <?= htmlspecialchars($job['salary']) ?></span>
                                </div>
                            </div>

                            <!-- Deadline -->
                            <p class="text-sm text-red-600 font-semibold mb-4 flex items-center space-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-7H3v7a2 2 0 002 2z" />
                                </svg>
                                <span>Deadline: <?= htmlspecialchars($job['deadline']) ?></span>
                            </p>

                            <!-- Details Button -->
                            <a href="job_details.php?id=<?= $job['id'] ?>"
                                class="mt-auto inline-block px-5 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg font-semibold shadow-lg text-center hover:from-blue-600 hover:to-indigo-700 transition">
                                View Details
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center col-span-full text-red-500 font-semibold text-lg mt-12">❌ No jobs found.</p>
                <?php endif; ?>
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