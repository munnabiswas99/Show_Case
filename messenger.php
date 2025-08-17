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
    <title>Messenger</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

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

<body class="bg-gray-100">


    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-100 via-teal-50 to-mint-50 flex justify-between items-center sticky top-0 z-50 relative shadow-md"> <a href="home.php"><img src="Images/logo.webp" class="w-[55px] h-[85px] ml-10 hover:cursor-pointer" loading="eager"></a>
        <!-- Navigation Links -->
        <nav class="hidden md:flex items-center space-x-10">
            <a href="home.php" class="hover:text-sky-500">Home</a>
            <a href="aboutUs.php" class="hover:text-sky-500">About Us</a>
            <a href="event.php" class="hover:text-sky-500">Events</a>
            <a href="job.php" class="hover:text-sky-500">Jobs</a>
            <a href="contact.php" class="hover:text-sky-500">Contact</a>
        </nav>

        <!-- Mobile Menu Button -->
        <div class="md:hidden flex items-center">
            <button id="menuBtn" class="text-gray-700 hover:text-sky-500 focus:outline-none">
                <!-- Hamburger icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Mobile Dropdown (hidden by default) -->
        <div id="mobileMenu" class="hidden md:hidden flex flex-col space-y-4 mt-2 bg-white shadow-md p-4">
            <a href="home.php" class="hover:text-sky-500">Home</a>
            <a href="aboutUs.php" class="hover:text-sky-500">About Us</a>
            <a href="event.php" class="hover:text-sky-500">Events</a>
            <a href="job.php" class="hover:text-sky-500">Jobs</a>
            <a href="contact.php" class="hover:text-sky-500">Contact</a>
        </div>

        <script>
            // Mobile menu toggle
            const menuBtn = document.getElementById("menuBtn");
            const mobileMenu = document.getElementById("mobileMenu");

            menuBtn.addEventListener("click", () => {
                mobileMenu.classList.toggle("hidden");
            });
        </script>
        <a href="messenger.php" title="Messages" class="relative inline-flex items-center justify-center w-8 h-8 text-blue-700 hover:text-blue-900 transition-all duration-300"> <!-- Chat bubble icon (clean style) --> <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h5m-2 8.5a9.5 9.5 0 1 0-6.364-16.364C2.85 4.977 2 6.69 2 8.5c0 1.216.323 2.354.886 3.333L2 21l4.333-2.167A9.5 9.5 0 0 0 10 20.5z" />
            </svg> </a>
        <div class="relative inline-block text-left"> <!-- Profile Button --> <button type="button" id="profileMenuButton" class="flex items-center border-l-2 border-gray-300 p-2 mr-4"> <img src="<?php echo $imagePath . '?v=' . time(); ?>" alt="Profile Picture" class="rounded-full h-10 w-10 border-2 border-gray-300 ml-14"> <!-- Down Arrow --> <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                </svg> </button> <!-- Dropdown Menu -->
            <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg z-50"> <a href="profileSystem/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100"> View Profile </a>
                <form method="POST" action="logout.php"> <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100"> Logout </button> </form>
            </div>
        </div>
    </header>





    <div class="flex h-screen animate-rise-in bg-gradient-to-b from-sky-50 via-mint-50 to-mint-50">
        <!-- Users List -->
        <div class="w-1/4 bg-blue-100 border-r overflow-y-auto">
            <h2 class="text-xl font-bold p-4 border-b">Chats</h2>

            <?php
            $current_user_id = $_SESSION['user_id'];

            $sql = "
        SELECT u.id, u.name, u.profile_picture,
               (
                 SELECT message 
                 FROM messages 
                 WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id)
                 ORDER BY sent_at DESC 
                 LIMIT 1
               ) AS last_message
        FROM users u
        WHERE u.id != ?
        ";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                die("SQL error: " . $conn->error);
            }

            $stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
            $stmt->execute();
            $users_query = $stmt->get_result();
            ?>

            <ul>
                <?php while ($user = $users_query->fetch_assoc()): ?>
                    <?php
                    // Handle profile picture path
                    $profilePath = $user['profile_picture'];

                    if (!empty($profilePath) && strpos($profilePath, 'uploads/') === 0) {
                        // Path like 'uploads/xxx.jpg'
                        $profilePath = '/showcase/profileSystem/' . $profilePath;
                    } elseif (!empty($profilePath) && strpos($profilePath, 'Images/') === 0) {
                        // Default stored like 'Images/default.png'
                        $profilePath = '/showcase/' . $profilePath;
                    } else {
                        // If empty or invalid, use default profile
                        $profilePath = '/showcase/Images/default_profile.jpg';
                    }
                    ?>
                    <li class="flex items-start gap-3 px-4 py-3 hover:bg-gray-100 border-b">
                        <img src="<?= htmlspecialchars($profilePath) ?>" alt="Profile" class="w-10 h-10 rounded-full object-cover">
                        <div class="flex-1">
                            <button onclick="loadChat(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" class="text-left w-full">
                                <div class="font-semibold text-gray-800"><?= htmlspecialchars($user['name']) ?></div>
                                <div class="text-sm text-gray-500 truncate">
                                    <?= htmlspecialchars($user['last_message'] ?? 'No message yet') ?>
                                </div>
                            </button>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>



        <!-- Chat Window -->
        <div class="flex-1 flex flex-col">
            <div class="bg-mint-100 p-4 border-b">
                <h2 class="text-xl font-semibold" id="chatWith">Select a user to chat</h2>
            </div>

            <div id="chatBox" class="flex-1 overflow-y-auto p-4 space-y-2"></div>

            <div class="bg-white p-4 border-t flex items-center gap-3">
                <!-- File Upload Icon -->
                <label for="fileInput" class="cursor-pointer text-gray-600 hover:text-blue-600 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 002.828 2.828l6.586-6.586a4 4 0 10-5.656-5.656l-6.586 6.586a6 6 0 108.486 8.486L21 13" />
                    </svg>
                </label>
                <input id="fileInput" type="file" onchange="uploadFile(event)" class="hidden" />

                <!-- Message Input -->
                <input id="messageInput" type="text" class="flex-1 border rounded px-4 py-2" placeholder="Type a message...">

                <!-- Send Button -->
                <button onclick="sendMessage()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors duration-200">Send</button>
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
    </div>

    <script>
        let selectedUserId = null;
        let fetchInterval;

        function loadChat(userId, userName) {
            selectedUserId = userId;
            document.getElementById("chatWith").innerText = "Chatting with " + userName;
            fetchMessages();
            if (fetchInterval) clearInterval(fetchInterval);
            fetchInterval = setInterval(fetchMessages, 2000);
        }

        function fetchMessages() {
            if (!selectedUserId) return;
            fetch("fetch_messages.php?receiver_id=" + selectedUserId)
                .then(res => res.text())
                .then(data => {
                    document.getElementById("chatBox").innerHTML = data;
                    document.getElementById("chatBox").scrollTop = document.getElementById("chatBox").scrollHeight;
                });
        }

        function sendMessage() {
            const message = document.getElementById("messageInput").value;
            if (message.trim() === "") return;

            fetch("send_message.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `receiver_id=${selectedUserId}&message=${encodeURIComponent(message)}`
            }).then(() => {
                document.getElementById("messageInput").value = "";
                fetchMessages();
            });
        }


        function uploadFile(event) {
            const file = event.target.files[0];
            if (!file || !selectedUserId) return alert("Select a chat user first!");

            const formData = new FormData();
            formData.append("file", file);
            formData.append("receiver_id", selectedUserId);

            fetch("send_message.php", { // your PHP handler (see next step)
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchMessages();
                        document.getElementById("fileInput").value = ""; // reset input
                    } else {
                        alert("Upload failed: " + (data.message || "Unknown error"));
                    }
                })
                .catch(() => alert("Upload error!"));
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