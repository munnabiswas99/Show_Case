<?php
session_start();


$successMessage = $_SESSION['success_message'] ?? '';
if ($successMessage) {
    unset($_SESSION['success_message']);
}

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


// Handle filter and search
$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT e.*, u.name AS user_name, u.profile_picture 
        FROM events e
        JOIN users u ON e.user_id = u.id
        WHERE 1";

if ($filter === 'free') {
    $sql .= " AND e.is_paid = 0";
} elseif ($filter === 'paid') {
    $sql .= " AND e.is_paid = 1";
}

if (!empty($search)) {
    $searchSafe = $conn->real_escape_string($search);
    $sql .= " AND (e.title LIKE '%$searchSafe%' OR e.host_name LIKE '%$searchSafe%')";
}

$sql .= " ORDER BY e.date_time DESC";
$result = $conn->query($sql);



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
            <a href="event.php" class="text-blue-500 text-xl pl-14 hover:text-sky-500">Events</a>
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



    <?php if ($successMessage): ?>
        <div id="success-msg" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded max-w-2xl mx-auto mb-6 text-center" role="alert">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>




    <div class="max-w-7xl mx-auto animate-rise-in">
        <div class="flex-1 p-6 min-h-[260px]  text-center  px-20  mt-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 flex justify-center items-center gap-2">
                <i class="fas fa-rocket text-green-500"></i> Spread the Word!
            </h2>
            <p class="text-gray-800 mb-4">Share amazing events happening around you—add details, media, and let others discover opportunities to connect and grow.</p>
            <a href="event_post.php" class="inline-block bg-gradient-to-r from-blue-400 to-teal-400 hover:bg-gradient-to-l hover:from-blue-400 hover:to-teal-400 text-white font-semibold px-6 py-2 rounded-lg transition duration-300">
                <i class="fas fa-upload mr-2"></i> Add Event
            </a>
        </div>
        <!-- Search + Filter Form -->
        <form method="GET" class="flex flex-col md:flex-row gap-4 mb-10 items-center justify-center">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search by title or host..."
                class="w-full md:w-1/2 px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">

            <select name="filter" class="px-4 py-2 rounded border border-gray-300">
                <option value="">All</option>
                <option value="free" <?php if ($filter === 'free') echo 'selected'; ?>>Free</option>
                <option value="paid" <?php if ($filter === 'paid') echo 'selected'; ?>>Paid</option>
            </select>

            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Apply
            </button>
        </form>


        <!-- Event Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($event = $result->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg overflow-hidden shadow hover:shadow-md hover:scale-[1.01] transition-all duration-300">
                        <?php if (!empty($event['image'])): ?>
                            <img src="eventJob/<?php echo $event['image']; ?>" alt="Event Image" class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <img src="<?= !empty($event['profile_picture']) ? 'profileSystem/' . $event['profile_picture'] : 'Images/default_profile.jpg' ?>"
                                    alt="Profile"
                                    class="w-10 h-10 rounded-full object-cover">
                                <p class="font-semibold"><?php echo $event['user_name']; ?></p>
                            </div>

                            <h2 class="text-xl font-bold mb-1"><?php echo $event['title']; ?></h2>
                            <p class="text-sm text-gray-600 mb-2">
                                <?php echo date("M d, Y h:i A", strtotime($event['date_time'])); ?> |
                                <?php echo $event['duration']; ?>
                            </p>
                            <p class="text-gray-700 text-sm mb-3 line-clamp-3"><?php echo $event['description']; ?></p>

                            <div class="text-sm text-gray-600 mb-2">
                                <span class="font-medium">Platform:</span> <?php echo $event['platform']; ?><br>
                                <span class="font-medium">Host:</span> <?php echo $event['host_name']; ?><br>
                                <span class="font-medium">Type:</span> <?php echo $event['is_paid'] ? "Paid" : "Free"; ?> |
                                Certificate: <?php echo $event['certificate_available'] ? "Yes" : "No"; ?>
                            </div>

                            <a href="<?php echo $event['registration_link']; ?>" target="_blank"
                                class="inline-block mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                                Register Now
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="col-span-3 text-center text-gray-500 text-lg">No events found.</p>
            <?php endif; ?>
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
    </div>

    <script>
        // Hide success message after 3 seconds
        setTimeout(() => {
            const msg = document.getElementById('success-msg');
            if (msg) {
                msg.style.transition = "opacity 0.5s ease";
                msg.style.opacity = '0';
                setTimeout(() => msg.remove(), 500);
            }
        }, 3000);


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