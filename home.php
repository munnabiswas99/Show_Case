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

// Filter options
$selectedCategory = $_GET['category'] ?? 'All';
$searchKeyword = $_GET['search'] ?? null;

// Fetch posts
$posts = [];

if ($searchKeyword) {
    $searchTerm = "%" . $searchKeyword . "%";
    $stmt = $conn->prepare("
        SELECT sp.*, u.name, u.profile_picture,
            (SELECT COUNT(*) FROM skill_post_likes WHERE post_id = sp.id) as love_count
        FROM skill_posts sp
        JOIN users u ON sp.user_id = u.id
        WHERE sp.title LIKE ? OR sp.description LIKE ? OR sp.category LIKE ?
        ORDER BY sp.created_at DESC
    ");
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
} elseif ($selectedCategory !== 'All') {
    $stmt = $conn->prepare("
        SELECT sp.*, u.name, u.profile_picture,
            (SELECT COUNT(*) FROM skill_post_likes WHERE post_id = sp.id) as love_count
        FROM skill_posts sp
        JOIN users u ON sp.user_id = u.id
        WHERE sp.category = ?
        ORDER BY sp.created_at DESC
    ");
    $stmt->bind_param("s", $selectedCategory);
} else {
    $stmt = $conn->prepare("
        SELECT sp.*, u.name, u.profile_picture,
            (SELECT COUNT(*) FROM skill_post_likes WHERE post_id = sp.id) as love_count
        FROM skill_posts sp
        JOIN users u ON sp.user_id = u.id
        ORDER BY sp.created_at DESC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
$stmt->close();
$conn->close();
?>


<?php if (isset($_GET['message'])): ?>
    <div id="toast-message" class="fixed top-40 left-1/2 transform -translate-x-1/2 bg-green-500 text-white border border-green-400 rounded-lg shadow-lg w-[80%] max-w-md z-50 transition-opacity duration-500">
        <div class="flex items-center justify-between px-4 py-3">
            <span class="text-sm font-medium text-center">
                <?= htmlspecialchars($_GET['message']) ?>
            </span>
        </div>
        <div class="h-1 bg-green-400" id="progress-bar"></div>
    </div>
<?php endif; ?>










<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Showcase | Contact</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="Style/home.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

    <style>
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

        /* Card hover effect */
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-white font-sans">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-100 via-teal-50 to-mint-50 flex justify-between items-center sticky top-0 z-50 relative shadow-md">
        <a href="#"><img src="Images/logo.webp" class="w-[55px] h-[85px] ml-10 hover:cursor-pointer" loading="eager"></a>

        <div class="text-center pl-40">
            <a href="home.php" class="text-blue-500 text-xl pl-40 hover:text-sky-500">Home</a>
            <a href="aboutUs.php" class="pl-14 hover:text-sky-500">About Us</a>
            <a href="event.php" class="pl-14 hover:text-sky-500">Events</a>
            <a href="job.php" class="pl-14 hover:text-sky-500">Jobs</a>
            <a href="contact.php" class="pl-14 hover:text-sky-500">Contact Us</a>
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



    <!-- Main Content -->
    <main class="px-20 py-10 bg-gradient-to-b from-sky-50 via-teal-50 to-white fade-in-up">

        <!-- Hero Section -->
        <section class="text-center py-20">
            <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                Share & Discover Skills in the Digital Era
            </h1>
            <p class="text-gray-600 mb-6">
                Start sharing your talents or exploring real skills posted by people like you — from web development to video editing!
            </p>



            <!-- Skill Search -->
            <div class="container px-4 mt-10 flex flex-col items-center justify-center">

                <!-- Title -->
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-search text-blue-500"></i> Discover New Skills
                </h2>

                <!-- Search Form -->
                <form action="home.php" method="GET" class="flex items-center bg-white border border-gray-300 rounded-full px-4 py-2 w-full max-w-2xl shadow-sm">
                    <input
                        type="text"
                        name="search"
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                        placeholder="Search by keyword or category"
                        class="flex-grow bg-transparent outline-none text-gray-700 placeholder-gray-500" />
                    <button
                        type="submit"
                        class="bg-gradient-to-r from-green-400 to-blue-500 text-white rounded-full p-2 hover:from-green-500 hover:to-blue-600 transition">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>




            <!-- Post Skill Card -->

            </div>
            </div>





        </section>




        <!-- Course Categories -->
        <div class="mt-10 text-center fade-in-up">
            <h1 class="text-2xl font-semibold mb-4">Explore Here</h1>
            <div class="flex flex-wrap justify-center gap-2">
                <?php
                $categories = ['All', 'Web Development', 'Graphic Design', 'Marketing', 'Creative Arts', 'Video Editing'];
                $selectedCategory = $_GET['category'] ?? 'All';

                foreach ($categories as $category) {
                    $isActive = $selectedCategory === $category ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700';
                    $categoryQuery = $category === 'All' ? '' : '?category=' . urlencode($category);
                    echo "<a href='home.php$categoryQuery' class='button1 px-4 py-2 rounded $isActive'>$category</a>";
                }
                ?>
            </div>
        </div>


        <!-- Cards -->
        <section class="mt-10 px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
                <?php foreach ($posts as $post): ?>
                    <div class="bg-white rounded-lg overflow-hidden shadow transition transform hover:bg-gray-100 hover:shadow-md hover:scale-[1.01] duration-300 flex flex-col">

                        <!-- User Info -->
                        <div class="flex items-center gap-3 p-4 border-b border-gray-200">
                            <img src="<?= !empty($post['profile_picture']) ? 'profileSystem/' . $post['profile_picture'] : 'Images/default_profile.jpg' ?>"
                                alt="Profile"
                                class="w-10 h-10 rounded-full object-cover">
                            <div>
                                <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($post['name']) ?></h3>
                                <p class="text-sm text-gray-500"><?= date("F j, Y, g:i a", strtotime($post['created_at'])) ?></p>
                                <?php if (!empty($post['category'])): ?>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">
                                        <?= htmlspecialchars($post['category']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Media -->
                        <div class="w-full h-60 bg-gray-100 overflow-hidden">
                            <?php
                            $mediaPath = '';
                            $isImage = false;

                            if (!empty($post['image_path']) && file_exists($post['image_path'])) {
                                $mediaPath = htmlspecialchars($post['image_path']);
                                $isImage = true;
                            } elseif (!empty($post['video_path']) && file_exists($post['video_path'])) {
                                $mediaPath = htmlspecialchars($post['video_path']);
                            }
                            ?>

                            <?php if ($mediaPath): ?>
                                <?php if ($isImage): ?>
                                    <img src="<?= $mediaPath ?>" alt="Skill Image"
                                        class="w-full h-full object-cover transition duration-300 hover:scale-105" />
                                <?php else: ?>
                                    <video controls class="w-full h-full object-cover rounded">
                                        <source src="<?= $mediaPath ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="flex items-center justify-center h-full text-gray-400 italic">
                                    No media available.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Content & Footer (Flexible space) -->
                        <div class="flex flex-col justify-between flex-grow p-4 space-y-3">
                            <!-- Content -->
                            <div>
                                <h2 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($post['title']) ?></h2>
                                <p class="text-gray-600 text-sm"><?= nl2br(htmlspecialchars($post['description'])) ?></p>
                            </div>

                            <!-- Bottom Actions -->
                            <div class="flex justify-between items-center pt-2">
                                <!-- Love Counter -->
                                <form method="POST" action="toggle_like.php">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="flex items-center gap-1 text-red-500 hover:text-red-600">
                                        <i class="fas fa-heart"></i>
                                        <span><?= htmlspecialchars($post['love_count'] ?? 0) ?></span>
                                    </button>
                                </form>

                                <!-- Details Button -->
                                <a href="postDetails.php?id=<?= $post['id'] ?>"
                                    class="bg-gradient-to-r from-blue-500 to-teal-500 text-white text-sm px-4 py-1.5 rounded-full hover:opacity-90">
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>



    </main>

    <!-- Footer -->

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
        // Category Button Toggle
        const buttons = document.querySelectorAll('.button1');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                buttons.forEach(btn => {
                    btn.classList.remove('bg-blue-500', 'text-white');
                    btn.classList.add('bg-gray-200', 'text-gray-700');
                });
                button.classList.remove('bg-gray-200', 'text-gray-700');
                button.classList.add('bg-blue-500', 'text-white');
            });
        });


        function toggleLove(button) {
            const countSpan = button.querySelector('.love-count');
            let count = parseInt(countSpan.textContent);
            const icon = button.querySelector('i');

            if (button.classList.contains('loved')) {
                count--;
                icon.classList.remove('text-red-500');
                button.classList.remove('loved');
            } else {
                count++;
                icon.classList.add('text-red-500');
                button.classList.add('loved');
            }

            countSpan.textContent = count;
        }



        const toast = document.getElementById('toast-message');
        const progressBar = document.getElementById('progress-bar');

        if (toast && progressBar) {
            let duration = 4000; // 4 seconds
            let start = Date.now();

            let interval = setInterval(() => {
                let elapsed = Date.now() - start;
                let width = 100 - (elapsed / duration) * 100;
                progressBar.style.width = width + "%";

                if (elapsed >= duration) {
                    toast.style.transition = "opacity 2.5s";
                    toast.style.opacity = 0;
                    clearInterval(interval);

                    setTimeout(() => toast.remove(), 6000);
                }
            }, 20);
        }




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