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
    <title>showcase | Profile View</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>

<body class="bg-white font-sans">

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



    <?php
    // Database connection
    $conn = new mysqli("localhost", "root", "", "showcase");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get user ID from URL
    $profile_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$profile_user_id) {
        die("Invalid user ID.");
    }

    // Fetch user info
    $stmt = $conn->prepare("SELECT name, email, cover_photo, profile_picture FROM users WHERE id=?");
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $stmt->bind_result($name, $email, $cover_photo, $profile_picture);
    $stmt->fetch();
    $stmt->close();


    // Set file system and URL paths
    $upload_dir = __DIR__ . "/profileSystem/";
    $url_base = "http://localhost/showcase/profileSystem/";

    // Profile Picture Path
    $profile_picture_path = (!empty($profile_picture) && file_exists($upload_dir . $profile_picture))
        ? $url_base . $profile_picture
        : "Images/default_profile.jpg";

    // Cover Photo Path
    $cover_photo_path = (!empty($cover_photo) && file_exists($upload_dir . $cover_photo))
        ? $url_base . $cover_photo
        : "Images/default_cover.jpg";
    ?>

    <!-- Body Container -->
    <div class="px-20 py-10 bg-gradient-to-b from-sky-50 via-teal-50 to-white fade-in-up">
        <div class="text-center px-20">
            <!-- Profile Section -->
            <div class="p-4 shadow rounded-lg">
                <!-- Cover Photo -->
                <div class="relative w-full h-[500px] rounded-lg overflow-hidden">
                    <img src="<?= htmlspecialchars($cover_photo_path) ?>"
                        alt="Cover Photo of <?= htmlspecialchars($name) ?>"
                        class="w-full h-full rounded-lg object-cover"
                        onerror="this.src='Images/default_cover.jpg'">
                </div>

                <!-- Profile Info Card -->
                <div class="bg-gradient-to-b from-blue-50 to-teal-50 p-6 -mt-20 rounded-lg shadow-md relative z-10">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="flex flex-col md:flex-row gap-6" id="profileSection">
                            <!-- Profile Picture -->
                            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-blue-400 shadow-md -mt-16">
                                <img src="<?= htmlspecialchars($profile_picture_path) ?>"
                                    alt="Profile Photo of <?= htmlspecialchars($name) ?>"
                                    class="w-full h-full object-cover"
                                    onerror="this.src='Images/default_profile.jpg'">
                            </div>
                            <!-- Name and Contact -->
                            <div>
                                <h1 class="text-2xl font-bold text-transparent bg-gradient-to-r from-gray-800 to-blue-600 bg-clip-text" id="profileName">
                                    <?= htmlspecialchars($name) ?>
                                </h1>
                                <div class="flex items-center gap-2 mt-2 text-blue-600 cursor-pointer select-none">
                                    <!-- Envelope Icon (Heroicons Mail) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m0 8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h14a2 2 0 012 2v8z" />
                                    </svg>

                                    <h1 class="text-sm font-semibold hover:text-blue-800 transition-colors duration-300">
                                        <a href="mailto:<?= htmlspecialchars($email) ?>" class="no-underline">
                                            <?= htmlspecialchars($email) ?>
                                        </a>
                                    </h1>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>






    <?php
    // Database connection
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "showcase";

    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get user ID from URL
    $profile_user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if (!$profile_user_id) {
        die("Invalid user ID.");
    }

    // Fetch user
    $user_sql = "SELECT name, email, profile_picture, cover_photo FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();
    if (!$user) {
        die("User not found.");
    }

    // Fetch user details (experience, skills, education)
    $details_query = "SELECT * FROM user_details WHERE user_id = $profile_user_id";
    $details_result = $conn->query($details_query);
    if (!$details_result) die("user_details query failed: " . $conn->error);

    // Group data by section
    $experience = $skills = $education = [];

    while ($row = $details_result->fetch_assoc()) {
        switch (strtolower($row['section'])) {
            case 'experience':
                $experience[] = $row;
                break;
            case 'skill':
                $skills[] = $row;
                break;
            case 'education':
                $education[] = $row;
                break;
        }
    }



    // Fetch skill posts for this user
    $posts_sql = "SELECT * FROM skill_posts WHERE user_id = ? ORDER BY created_at DESC";
    $stmt_posts = $conn->prepare($posts_sql);
    $stmt_posts->bind_param("i", $profile_user_id);
    $stmt_posts->execute();
    $posts_result = $stmt_posts->get_result();

    $skill_posts = [];
    while ($post = $posts_result->fetch_assoc()) {
        $skill_posts[] = $post;
    }


    ?>



    <div class="flex justify-center gap-8 max-w-7xl mx-auto px-4">
        <!-- Experience -->
        <div class="bg-white rounded-lg shadow-md p-6 flex-1 min-w-[250px]">
            <h2 class="text-2xl font-semibold text-blue-600 mb-5 flex items-center">
                <i class="fas fa-briefcase mr-3"></i> Experience
            </h2>
            <?php if (empty($experience)): ?>
                <p class="text-gray-400 italic pl-1">No experience added.</p>
            <?php else: ?>
                <ul class="space-y-4">
                    <?php foreach ($experience as $exp): ?>
                        <li class="border-l-4 border-blue-500 pl-4">
                            <p class="text-lg font-semibold text-gray-800 leading-snug">
                                <?= htmlspecialchars($exp['title']) ?>
                            </p>
                            <p class="text-gray-600 ml-1">
                                at <?= htmlspecialchars($exp['organization_or_institute']) ?>
                                <span class="text-sm text-gray-400"> (<?= htmlspecialchars($exp['duration']) ?>)</span>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Skills -->
        <div class="bg-white rounded-lg shadow-md p-6 flex-1 min-w-[250px]">
            <h2 class="text-2xl font-semibold text-green-600 mb-5 flex items-center">
                <i class="fas fa-tools mr-3"></i> Skills
            </h2>
            <?php if (empty($skills)): ?>
                <p class="text-gray-400 italic pl-1">No skills added.</p>
            <?php else: ?>
                <ul class="space-y-4">
                    <?php foreach ($skills as $skill): ?>
                        <li class="border-l-4 border-green-500 pl-4">
                            <p class="text-lg font-semibold text-gray-800 leading-snug">
                                <?= htmlspecialchars($skill['title']) ?>
                                <span class="text-sm text-gray-400 ml-2">(<?= htmlspecialchars($skill['duration']) ?>)</span>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Education -->
        <div class="bg-white rounded-lg shadow-md p-6 flex-1 min-w-[250px]">
            <h2 class="text-2xl font-semibold text-purple-600 mb-5 flex items-center">
                <i class="fas fa-graduation-cap mr-3"></i> Education
            </h2>
            <?php if (empty($education)): ?>
                <p class="text-gray-400 italic pl-1">No education info added.</p>
            <?php else: ?>
                <ul class="space-y-4">
                    <?php foreach ($education as $edu): ?>
                        <li class="border-l-4 border-purple-500 pl-4">
                            <p class="text-lg font-semibold text-gray-800 leading-snug">
                                <?= htmlspecialchars($edu['title']) ?>
                            </p>
                            <p class="text-gray-600 ml-1">
                                at <?= htmlspecialchars($edu['organization_or_institute']) ?>
                                <span class="text-sm text-gray-400"> (<?= htmlspecialchars($edu['duration']) ?>)</span>
                            </p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>





    <section class="mt-10 px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
            <?php foreach ($skill_posts as $post): ?>
                <div class="bg-white rounded-lg overflow-hidden shadow transition transform hover:bg-gray-100 hover:shadow-md hover:scale-[1.01] duration-300 flex flex-col">

                    <!-- User Info -->
                    <div class="flex items-center gap-3 p-4 border-b border-gray-200">
                        <!-- Assume you have $user array with 'profile_picture' and 'name' from user fetch -->
                        <img src="<?= !empty($user['profile_picture']) ? 'profileSystem/' . htmlspecialchars($user['profile_picture']) : 'Images/default_profile.jpg' ?>"
                            alt="Profile"
                            class="w-10 h-10 rounded-full object-cover">
                        <div>
                            <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($user['name']) ?></h3>
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