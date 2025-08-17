<?php
session_start();
$conn = new mysqli("localhost", "root", "", "showcase");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if (!isset($_SESSION['user_id'])) {
    die("User not logged in. Please login first.");
}

$user_id = $_SESSION['user_id'];




// Handle cover photo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['cover'])) {
    $target_dir = "uploads/";
    $filename = time() . "_" . basename($_FILES["cover"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["cover"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("UPDATE users SET cover_photo=? WHERE id=?");
        $stmt->bind_param("si", $target_file, $user_id);
        $stmt->execute();
    }
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $profile_path = null;

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] == 0) {
        $target_dir = "uploads/";
        $filename = time() . "_" . basename($_FILES["profile"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["profile"]["tmp_name"], $target_file)) {
            $profile_path = $target_file;
        }
    }

    if ($profile_path) {
        $stmt = $conn->prepare("UPDATE users SET name=?, profile_picture=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $profile_path, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $user_id);
    }
    $stmt->execute();
    header("Location: post.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT name, email, cover_photo, profile_picture FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $cover_photo, $profile_picture);
$stmt->fetch();
$stmt->close();

$cover_file_path = __DIR__ . '/' . $cover_photo;
$profile_file_path = __DIR__ . '/' . $profile_picture;

if (!$cover_photo || !file_exists($cover_file_path)) {
    $cover_photo = "../Images/default_cover.jpg";
}
if (!$profile_picture || !file_exists($profile_file_path)) {
    $profile_picture = "../Images/default_profile.jpg";
}


if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Delete post if delete_id passed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete the post
    $stmt = $conn->prepare("DELETE FROM skill_posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Optionally delete likes (if using foreign keys with cascade, this is not needed)
    $stmt = $conn->prepare("DELETE FROM skill_post_likes WHERE post_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch user's posts
$stmt = $conn->prepare("
    SELECT sp.*, u.name, u.profile_picture,
        (SELECT COUNT(*) FROM skill_post_likes WHERE post_id = sp.id) as love_count
    FROM skill_posts sp
    JOIN users u ON sp.user_id = u.id
    WHERE sp.user_id = ?
    ORDER BY sp.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}
$stmt->close();

// Profile picture fallback
function getProfileImagePath($profile_picture)
{
    $file = __DIR__ . '/' . $profile_picture;
    return (!empty($profile_picture) && file_exists($file)) ? $profile_picture : "../Images/default_profile.jpg";
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>showcase | profile</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

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
    </style>
</head>

<body class="bg-white font-sans">

    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-100 via-teal-50 to-mint-50 sticky top-0 z-50 shadow-md">
        <div class="flex justify-between items-center px-4 md:px-10 py-2">
            <!-- Logo -->
            <a href="../home.php">
                <img src="../Images/logo.webp" class="w-[55px] h-[85px] hover:cursor-pointer" loading="eager">
            </a>

            <!-- Desktop Nav -->
            <div class="hidden md:flex text-center pl-20">
                <a href="../home.php" class="pl-10 hover:text-sky-500">Home</a>
                <a href="../aboutUs.php" class="pl-10 hover:text-sky-500">About Us</a>
                <a href="../event.php" class="pl-10 hover:text-sky-500">Events</a>
                <a href="../job.php" class="pl-10 hover:text-sky-500">Jobs</a>
                <a href="../contact.php" class="pl-10 hover:text-sky-500">Contact</a>
            </div>

            <!-- Right Section -->
            <div class="flex items-center space-x-4">
                <!-- Messenger -->
                <a href="messenger.php" title="Messages"
                    class="relative inline-flex items-center justify-center w-8 h-8 text-blue-700 hover:text-blue-900 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 8h10M7 12h5m-2 8.5a9.5 9.5 0 1 0-6.364-16.364C2.85 4.977 2 6.69 2 8.5c0 1.216.323 2.354.886 3.333L2 21l4.333-2.167A9.5 9.5 0 0 0 10 20.5z" />
                    </svg>
                </a>

                <!-- Profile -->
                <div class="relative inline-block text-left">
                    <button type="button" id="profileMenuButton"
                        class="flex items-center border-l-2 border-gray-300 p-2">
                        <img src="<?php echo $profile_picture . '?v=' . time(); ?>"
                            alt="Profile Picture"
                            class="rounded-full h-10 w-10 border-2 border-gray-300 ml-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600 ml-1" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <!-- Dropdown -->
                    <div id="profileDropdown"
                        class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                        <form method="POST" action="../logout.php">
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-800" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Nav -->
        <div id="mobileMenu" class="hidden md:hidden flex flex-col bg-white shadow-md px-6 py-4 space-y-2">
            <a href="../home.php" class="hover:text-sky-500">Home</a>
            <a href="../aboutUs.php" class="hover:text-sky-500">About Us</a>
            <a href="../event.php" class="hover:text-sky-500">Events</a>
            <a href="../job.php" class="hover:text-sky-500">Jobs</a>
            <a href="../contact.php" class="hover:text-sky-500">Contact</a>
        </div>
    </header>

    <script>
        // Toggle mobile menu
        document.getElementById("mobileMenuBtn").addEventListener("click", function() {
            document.getElementById("mobileMenu").classList.toggle("hidden");
        });
    </script>




    <!-- Body Container -->
    <div class="px-20 py-10 bg-gradient-to-b from-sky-50 via-teal-50 to-white fade-in-up">
        <div class="text-center  px-20">
            <!-- Profile Section -->
            <div class="p-4 shadow rounded-lg">
                <!-- Cover -->
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="relative w-full h-[500px] rounded-lg">
                        <img id="coverPreview" src="<?= htmlspecialchars($cover_photo) ?>" alt="Cover Photo" class="w-full h-full rounded-lg object-cover">

                        <!-- Hidden file input -->
                        <input type="file" name="cover" id="coverInput" accept="image/*" class="hidden" onchange="previewCoverAndSubmit()">

                        <!-- Button to trigger file input -->
                        <div onclick="document.getElementById('coverInput').click()" class="absolute top-5 right-2 bg-blue-400 text-white p-2 rounded-md shadow-md flex items-center gap-2 cursor-pointer hover:bg-white hover:text-black hover:shadow-lg hover:transition hover:duration-200">
                            <i class="fa-solid fa-camera"></i>
                            <p class="text-sm font-semibold">Edit cover photo</p>
                        </div>
                    </div>
                </form>



                <!-- Profile Info Card -->
                <div class="bg-gradient-to-b  from-blue-50 to-teal-50 p-6 -mt-20 rounded-lg shadow-md relative z-10">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                        <!-- Profile Info -->
                        <div class="flex flex-col md:flex-row gap-6" id="profileSection">
                            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-blue-400 shadow-md -mt-16">
                                <img src="<?= htmlspecialchars($profile_picture) ?>" class="w-full h-full object-cover" id="profilePhoto">
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-transparent bg-gradient-to-r from-gray-800 to-blue-600 bg-clip-text" id="profileName"><?php echo $name; ?></h1>
                                <div class="flex items-center gap-2 mt-2">
                                    <i class="fas fa-phone-alt text-blue-400"></i>
                                    <p><a href="#" class="text-sm text-blue-600" onclick="copyEmail('<?php echo $email; ?>')">Contact info</a></p>
                                </div>
                            </div>
                        </div>

                        <!-- Edit Button -->
                        <button onclick="document.getElementById('editModal').showModal()"
                            class="border-blue-400 border-2 rounded-lg p-2 hover:shadow-md hover:text-xl mt-4 md:mt-0">
                            <i class="fa-solid fa-pen-to-square text-gray-600 w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Edit Modal -->
                    <dialog id="editModal" class="rounded-lg p-4 w-96">
                        <form method="POST" action="profile.php" enctype="multipart/form-data">
                            <input type="hidden" name="update_profile" value="1">
                            <label class="block mb-2">Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" class="w-full p-2 border rounded mb-4">

                            <label class="block mb-2">Profile Photo</label>
                            <input type="file" name="profile" accept="image/*" class="mb-4">

                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save</button>
                            <button type="button" onclick="document.getElementById('editModal').close()" class="ml-2">Cancel</button>
                        </form>
                    </dialog>

                    <!-- Profile Action Buttons -->
                    <div class="mt-4 flex flex-wrap gap-3">
                        <button onclick="window.location.href='profile.php'"
                            class="px-4 py-2 border border-gray-400 text-sm rounded-md hover:bg-gray-400">About</button>
                        <button class="px-6 py-2 bg-gradient-to-r from-blue-400 to-teal-400 text-white text-sm rounded-md hover:bg-gradient-to-l from-blue-400 to-teal-400">Posts</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="flex-1 p-6 min-h-[260px]  text-center  px-20  mt-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-2 flex justify-center items-center gap-2">
            <i class="fas fa-rocket text-green-500"></i> Share Your Talent with the World!
        </h2>
        <p class="text-gray-800 mb-4">Post your skills through image, text, or video and let others discover your expertise.</p>
        <a href="../skillPost.php" class="inline-block bg-gradient-to-r from-blue-400 to-teal-400 hover:bg-gradient-to-l hover:from-blue-400 hover:to-teal-400 text-white font-semibold px-6 py-2 rounded-lg transition duration-300">
            <i class="fas fa-upload mr-2"></i> Post Your Skill
        </a>
    </div>


    <div class="max-w-7xl mx-auto p-6 mt-10">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">Your Skill Posts</h1>

        <?php if (empty($posts)) : ?>
            <p class="text-center text-gray-600">You haven't posted any skills yet.</p>
        <?php else : ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($posts as $post) : ?>
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow hover:shadow-md transition transform hover:-translate-y-1 duration-300">
                        <!-- Header: User Info -->
                        <div class="flex items-center px-4 py-3 border-b border-gray-200 bg-blue-50">
                            <img src="<?= getProfileImagePath($post['profile_picture']) ?>?v=<?= time(); ?>" class="h-10 w-10 rounded-full object-cover border-2 border-gray-300" alt="Profile">
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($post['name']) ?></p>
                                <p class="text-xs text-gray-500"><?= date("M d, Y h:i A", strtotime($post['created_at'])) ?></p>
                            </div>
                        </div>

                        <!-- Body: Title, Description -->
                        <div class="p-4 bg-white">
                            <h2 class="text-lg font-bold text-gray-800 mb-2"><?= htmlspecialchars($post['title']) ?></h2>
                            <p class="text-gray-600 text-sm mb-3"><?= nl2br(htmlspecialchars($post['description'])) ?></p>

                            <?php if (!empty($post['image_path']) && file_exists(__DIR__ . '/../' . $post['image_path'])) : ?>
                                <img src="../<?= $post['image_path'] ?>" class="w-full h-48 object-cover rounded mb-3" alt="Skill Image">
                            <?php endif; ?>

                            <?php if (!empty($post['video_path']) && file_exists(__DIR__ . '/../' . $post['video_path'])) : ?>
                                <video class="w-full rounded mb-3" controls>
                                    <source src="../<?= $post['video_path'] ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>

                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-700 text-sm"><i class="fas fa-heart text-red-500"></i> <?= $post['love_count'] ?> </span>
                                <a href="../postDetails.php?id=<?= $post['id'] ?>"
                                    class="bg-gradient-to-r from-blue-500 to-teal-500 text-white text-sm px-4 py-1.5 rounded-full hover:opacity-90">
                                    Details
                                </a>
                            </div>


                            <div class="flex justify-center items-center gap-10 mt-4">
                                <!-- Edit Button -->
                                <a href="edit_post.php?id=<?= $post['id'] ?>" class="bg-yellow-400 hover:bg-yellow-500 text-white text-sm px-3 py-1 rounded">
                                    <i class="fas fa-edit"></i> Edit
                                </a>

                                <!-- Delete Button -->
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    <input type="hidden" name="delete_id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white text-sm px-3 py-1 rounded">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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


    <script>
        function previewCoverAndSubmit() {
            const input = document.getElementById('coverInput');
            const preview = document.getElementById('coverPreview');

            const file = input.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);

                // Submit the form after preview
                input.form.submit();
            }
        }

        function copyEmail(email) {
            navigator.clipboard.writeText(email).then(() => {
                alert("Email copied: " + email);
            });
        }

        document.querySelectorAll('[onclick^="document.getElementById(\'editModal\')"]').forEach(btn => {
            btn.addEventListener('click', () => document.getElementById('editModal').showModal());
        });



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