<?php
session_start();
$conn = new mysqli("localhost", "root", "", "showcase");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if (!isset($_SESSION['user_id'])) {
    die("User not logged in. Please login first.");
}

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Show profile of the user who posted the skill
} else {
    $user_id = $_SESSION['user_id']; // Fallback to logged-in user's profile
}



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
    header("Location: profile.php");
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



$conn = new mysqli("localhost", "root", "", "showcase");
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']); // Show profile of the user who posted the skill
} else {
    $user_id = $_SESSION['user_id']; // Fallback to logged-in user's profile
}


// Fetch experience
$exp_query = $conn->query("SELECT * FROM user_details WHERE user_id=$user_id AND section='experience'");
$skill_query = $conn->query("SELECT * FROM user_details WHERE user_id=$user_id AND section='skill'");
$edu_query = $conn->query("SELECT * FROM user_details WHERE user_id=$user_id AND section='education'");

?>

<?php if (isset($_GET['message'])): ?>
    <div id="flashMessage" class="fixed top-40 left-1/2 transform -translate-x-1/2 bg-green-100 border border-green-400 text-green-800 px-6 py-4 rounded-lg shadow-lg w-[90%] max-w-xl  flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
            <span class="text-center font-semibold"><?= htmlspecialchars($_GET['message']) ?></span>
        </div>
        <div id="timerBar" class="absolute bottom-0 left-0 h-1 bg-green-500 animate-timerBar w-full"></div>
    </div>
<?php endif; ?>




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


        @keyframes shrinkBar {
            from {
                width: 100%;
            }

            to {
                width: 0%;
            }
        }

        .animate-timerBar {
            animation: shrinkBar 4s linear forwards;
        }
    </style>
</head>

<body class="bg-white font-sans">

    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-100 via-teal-50 to-mint-50 shadow-md sticky top-0 z-50">
        <div class="flex justify-between items-center px-4 md:px-10 py-2">

            <!-- Logo -->
            <a href="../home.php">
                <img src="../Images/logo.webp" class="w-[50px] h-[70px] hover:cursor-pointer" loading="eager">
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex space-x-10">
                <a href="../home.php" class="hover:text-sky-500">Home</a>
                <a href="../aboutUs.php" class="hover:text-sky-500">About Us</a>
                <a href="../event.php" class="hover:text-sky-500">Events</a>
                <a href="../job.php" class="hover:text-sky-500">Jobs</a>
                <a href="../contact.php" class="hover:text-sky-500">Contact</a>
            </nav>

            <!-- Right Icons -->
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
                    <button type="button" id="profileMenuButton" class="flex items-center border-l-2 border-gray-300 pl-3">
                        <img src="<?php echo $profile_picture . '?v=' . time(); ?>"
                            alt="Profile Picture"
                            class="rounded-full h-10 w-10 border-2 border-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600 ml-1" viewBox="0 0 20 20"
                            fill="currentColor">
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
                <button id="menuToggle" class="md:hidden focus:outline-none">
                    <svg class="w-7 h-7 text-gray-700" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden flex-row space-y-2 px-6 pb-4">
            <a href="../home.php" class="hover:text-sky-500">Home</a>
            <a href="../aboutUs.php" class="hover:text-sky-500">About Us</a>
            <a href="../event.php" class="hover:text-sky-500">Events</a>
            <a href="../job.php" class="hover:text-sky-500">Jobs</a>
            <a href="../contact.php" class="hover:text-sky-500">Contact</a>
        </div>
    </header>

    <script>
        // Mobile Menu Toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('mobileMenu').classList.toggle('hidden');
        });

        // Profile Dropdown Toggle
        document.getElementById('profileMenuButton').addEventListener('click', function() {
            document.getElementById('profileDropdown').classList.toggle('hidden');
        });
    </script>




    <!-- Body Container -->
    <div class="px-20 py-10 bg-gradient-to-b from-sky-50 via-teal-50 to-white fade-in-up">

        <!-- Left Side -->
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
                        <button onclick="document.getElementById('editModal').showModal()" class="border-blue-400 border-2 rounded-lg p-2 hover:shadow-md hover:text-xl mt-4 md:mt-0">
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
                        <button class="px-6 py-2 bg-gradient-to-r from-blue-400 to-teal-400 text-white text-sm rounded-md hover:bg-gradient-to-l from-blue-400 to-teal-400">About</button>
                        <button onclick="window.location.href='post.php'"
                            class="px-4 py-2 border border-gray-400 text-sm rounded-md hover:bg-gray-400">Posts</button>
                    </div>
                </div>
            </div>



            <!-- EXPERIENCE SECTION -->
            <form action="save_details.php" method="post">
                <input type="hidden" name="section" value="experience">
                <div id="experience-section" class="border border-gray-300 rounded-lg p-4 shadow bg-white mb-6">
                    <div class="mb-4 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-blue-600 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6M12 2a10 10 0 100 20 10 10 0 000-20z" />
                            </svg>
                            Experience
                        </h2>
                        <button type="button" onclick="toggleEdit('experience-section')" class="text-sm text-blue-600 hover:bg-teal-200 px-4 py-2 rounded-md"><i class="fa-solid fa-pen"></i> Edit</button>
                    </div>

                    <?php while ($row = $exp_query->fetch_assoc()): ?>
                        <div class="mb-3">
                            <p class="preview-text mb-1"><strong><?= $row['title'] ?></strong> at <?= $row['organization_or_institute'] ?> (<?= $row['duration'] ?>)</p>
                            <input type="hidden" name="id[]" value="<?= $row['id'] ?>">
                            <input type="text" name="title[]" required value="<?= $row['title'] ?>" placeholder="Job Title" class="editable hidden border p-2 rounded w-full mb-2">
                            <input type="text" name="org[]" required value="<?= $row['organization_or_institute'] ?>" placeholder="Organization" class="editable hidden border p-2 rounded w-full mb-2">
                            <input type="text" name="duration[]" required value="<?= $row['duration'] ?>" placeholder="Duration" class="editable hidden border p-2 rounded w-full">
                        </div>
                    <?php endwhile; ?>

                    <!-- New Entry -->
                    <div class="mb-3 editable hidden">
                        <input type="hidden" name="id[]" value="">
                        <input type="text" name="title[]" required placeholder="Job Title (New)" class="border p-2 rounded w-full mb-2">
                        <input type="text" name="org[]" required placeholder="Organization (New)" class="border p-2 rounded w-full mb-2">
                        <input type="text" name="duration[]" required placeholder="Duration (New)" class="border p-2 rounded w-full">
                    </div>

                    <button type="submit" class="save-btn hidden bg-gradient-to-b from-blue-400 to-teal-400 text-white px-4 py-2 rounded mt-3">💾 Save Experience</button>
                </div>
            </form>

            <!-- SKILL SECTION -->
            <form action="save_details.php" method="post">
                <input type="hidden" name="section" value="skill">
                <div id="skill-section" class="border border-gray-300 rounded-lg p-4 shadow bg-white mb-6">
                    <div class="mb-4 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-green-600 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0M12 14v7m-4-4h8" />
                            </svg>
                            Skills
                        </h2>
                        <button type="button" onclick="toggleEdit('skill-section')" class="text-sm text-green-600 hover:bg-teal-200 px-4 py-2 rounded-md"><i class="fa-solid fa-pen"></i> Edit</button>
                    </div>

                    <?php while ($row = $skill_query->fetch_assoc()): ?>
                        <div class="mb-3">
                            <p class="preview-text mb-1"><strong><?= $row['title'] ?></strong> (<?= $row['duration'] ?>)</p>
                            <input type="hidden" name="id[]" value="<?= $row['id'] ?>">
                            <input type="text" name="title[]" required value="<?= $row['title'] ?>" placeholder="Skill Name" class="editable hidden border p-2 rounded w-full mb-2">
                            <input type="text" name="org[]" value="<?= $row['organization_or_institute'] ?>" class="hidden">
                            <input type="text" name="duration[]" required value="<?= $row['duration'] ?>" placeholder="Proficiency/Level" class="editable hidden border p-2 rounded w-full">
                        </div>
                    <?php endwhile; ?>

                    <!-- New Entry -->
                    <div class="mb-3 editable hidden">
                        <input type="hidden" name="id[]" value="">
                        <input type="text" name="title[]" required placeholder="Skill Name (New)" class="border p-2 rounded w-full mb-2">
                        <input type="text" name="org[]" value="" class="hidden">
                        <input type="text" name="duration[]" required placeholder="Proficiency/Level (New)" class="border p-2 rounded w-full">
                    </div>

                    <button type="submit" class="save-btn hidden bg-gradient-to-b from-green-400 to-teal-400 text-white px-4 py-2 rounded mt-3">💾 Save Skills</button>
                </div>
            </form>

            <!-- EDUCATION SECTION -->
            <form action="save_details.php" method="post">
                <input type="hidden" name="section" value="education">
                <div id="education-section" class="border border-gray-300 rounded-lg p-4 shadow bg-white">
                    <div class="mb-4 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-purple-600 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 7V9" />
                            </svg>
                            Education
                        </h2>
                        <button type="button" onclick="toggleEdit('education-section')" class="text-sm text-purple-600 hover:bg-teal-200 px-4 py-2 rounded-md"><i class="fa-solid fa-pen"></i> Edit</button>
                    </div>

                    <?php while ($row = $edu_query->fetch_assoc()): ?>
                        <div class="mb-3">
                            <p class="preview-text mb-1"><strong><?= $row['title'] ?></strong> at <?= $row['organization_or_institute'] ?> (<?= $row['duration'] ?>)</p>
                            <input type="hidden" name="id[]" value="<?= $row['id'] ?>">
                            <input type="text" name="title[]" required value="<?= $row['title'] ?>" placeholder="Degree" class="editable hidden border p-2 rounded w-full mb-2">
                            <input type="text" name="org[]" required value="<?= $row['organization_or_institute'] ?>" placeholder="Institute" class="editable hidden border p-2 rounded w-full mb-2">
                            <input type="text" name="duration[]" required value="<?= $row['duration'] ?>" placeholder="Year" class="editable hidden border p-2 rounded w-full">
                        </div>
                    <?php endwhile; ?>

                    <!-- New Entry -->
                    <div class="mb-3 editable hidden">
                        <input type="hidden" name="id[]" value="">
                        <input type="text" name="title[]" required placeholder="Degree (New)" class="border p-2 rounded w-full mb-2">
                        <input type="text" name="org[]" required placeholder="Institute (New)" class="border p-2 rounded w-full mb-2">
                        <input type="text" name="duration[]" required placeholder="Year (New)" class="border p-2 rounded w-full">
                    </div>

                    <button type="submit" class="save-btn hidden bg-gradient-to-b from-purple-500 to-teal-500 text-white px-4 py-2 rounded mt-3">💾 Save Education</button>
                </div>
            </form>





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

        // Auto hide after 4 seconds
        setTimeout(() => {
            const msg = document.getElementById('flashMessage');
            if (msg) msg.remove();
        }, 8000);


        function toggleEdit(sectionId) {
            const fields = document.querySelectorAll(`#${sectionId} .editable`);
            const saveBtn = document.querySelector(`#${sectionId} .save-btn`);
            fields.forEach(field => field.classList.toggle('hidden'));
            saveBtn.classList.toggle('hidden');
        }



        function toggleEdit(sectionId) {
            const section = document.getElementById(sectionId);
            const editables = section.querySelectorAll('.editable');
            const saveBtn = section.querySelector('.save-btn');
            const previews = section.querySelectorAll('.preview-text');

            editables.forEach(el => el.classList.toggle('hidden'));
            previews.forEach(el => el.classList.toggle('hidden'));
            saveBtn.classList.toggle('hidden');
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