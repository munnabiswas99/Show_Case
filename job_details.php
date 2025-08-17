<?php
session_start();

$conn = new mysqli("localhost", "root", "", "showcase");
$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
if (!$job) {
    echo "<div class='text-red-500 text-center mt-4'>❌ Job not found</div>";
    exit;
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
<html>

<head>
    <title><?= htmlspecialchars($job['job_title']) ?> – Job Details</title>
    <script src="https://cdn.tailwindcss.com"></script>


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

<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-100 via-teal-50 to-mint-50 flex justify-between items-center sticky top-0 z-50 relative shadow-md">
        <a href="home.php"><img src="Images/logo.webp" class="w-[55px] h-[85px] ml-10 hover:cursor-pointer" loading="eager"></a>

        <div class="text-center pl-40">
            <a href="home.php" class="pl-40 hover:text-sky-500">Home</a>
            <a href="aboutUs.php" class="pl-14 hover:text-sky-500">About Us</a>
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



    <div class="pt-20 bg-gradient-to-b from-sky-50 via-mint-50 to-mint-50">
        <div class="max-w-5xl mx-auto bg-white p-8 rounded-2xl shadow-xl transition-all duration-300 hover:shadow-2xl animate-rise-in">
            <!-- Title and Basic Info -->
            <div class="mb-6 border-b pb-4">
                <h1 class="text-3xl font-extrabold text-blue-800 mb-2"><?= htmlspecialchars($job['job_title']) ?></h1>
                <h2 class="text-xl font-semibold text-gray-700">
                    <?= htmlspecialchars($job['company_name']) ?> ·
                    <span class="text-sm font-normal text-gray-500"><?= htmlspecialchars($job['location']) ?></span>
                </h2>
                <p class="text-sm text-gray-500 mt-2">
                    <i class="far fa-calendar-alt"></i> Published: <?= $job['published'] ?> |
                    <i class="far fa-clock"></i> Deadline: <?= $job['deadline'] ?>
                </p>
            </div>

            <!-- Quick Facts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8 text-gray-700">
                <p><span class="font-semibold">🔧 Experience:</span> <?= $job['experience'] ?></p>
                <p><span class="font-semibold">🎂 Age Limit:</span> <?= $job['age_limit'] ?></p>
                <p><span class="font-semibold">💼 Employment Status:</span> <?= $job['employment_status'] ?></p>
                <p><span class="font-semibold">💰 Salary:</span> <?= $job['salary'] ?></p>
            </div>

            <!-- Detailed Sections -->
            <div class="space-y-6 text-gray-800">
                <div>
                    <h3 class="text-lg font-semibold text-blue-700 mb-1">📝 Job Description</h3>
                    <p class="whitespace-pre-line leading-relaxed"><?= nl2br($job['description']) ?></p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-blue-700 mb-1">🎓 Education</h3>
                    <p class="whitespace-pre-line leading-relaxed"><?= nl2br($job['education']) ?></p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-blue-700 mb-1">📌 Requirements</h3>
                    <p class="whitespace-pre-line leading-relaxed"><?= nl2br($job['requirements']) ?></p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-blue-700 mb-1">🛠️ Responsibilities</h3>
                    <p class="whitespace-pre-line leading-relaxed"><?= nl2br($job['responsibilities']) ?></p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-blue-700 mb-1">💡 Skills</h3>
                    <p class="whitespace-pre-line leading-relaxed"><?= nl2br($job['skills']) ?></p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-blue-700 mb-1">📨 Apply Procedure</h3>
                    <p class="whitespace-pre-line leading-relaxed"><?= nl2br($job['apply_procedure']) ?></p>
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