<?php
session_start();  // Start session to access session variables

// DB connection
$conn = new mysqli("localhost", "root", "", "showcase");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<div class='text-red-500 text-center mt-4'>❌ You must be logged in to add events.</div>";
    exit;
}

$user_id = $_SESSION['user_id'];  // Get logged-in user ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify user exists just in case
    $user_check = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $user_check->bind_param("i", $user_id);
    $user_check->execute();
    $result = $user_check->get_result();

    if ($result->num_rows === 0) {
        echo "<div class='text-red-500 text-center mt-4'>❌ Error: Logged-in user not found in database.</div>";
        exit;
    }

    // Collect form data safely
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $date_time = $_POST['date_time'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $platform = $_POST['platform'] ?? '';
    $registration_link = $_POST['registration_link'] ?? '';
    $host_name = $_POST['host_name'] ?? '';
    $is_paid = isset($_POST['is_paid']) ? 1 : 0;
    $certificate_available = isset($_POST['certificate_available']) ? 1 : 0;

    // Handle image upload
    $image_name = "";
    if (!empty($_FILES['image']['name'])) {
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $upload_path = "eventJob/" . $image_name;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            echo "<div class='text-red-500 text-center mt-4'>❌ Error uploading image.</div>";
            exit;
        }
    }

    // Insert event
    $stmt = $conn->prepare("INSERT INTO events (user_id, title, description, date_time, duration, image, platform, registration_link, host_name, is_paid, certificate_available) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssssii", $user_id, $title, $description, $date_time, $duration, $image_name, $platform, $registration_link, $host_name, $is_paid, $certificate_available);

    if ($stmt->execute()) {
        // Store success message in session
        $_SESSION['success_message'] = "✅ Event added successfully!";
        // Redirect to event.php
        header("Location: event.php");
        exit();
    } else {
        echo "<div class='text-red-500 text-center mt-4'>❌ Error: " . $stmt->error . "</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4 text-center">Add New Event</h1>
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="text" name="title" placeholder="Event Title" required class="w-full border rounded px-4 py-2" />

            <textarea name="description" placeholder="Event Description" required class="w-full border rounded px-4 py-2"></textarea>

            <input type="datetime-local" name="date_time" required class="w-full border rounded px-4 py-2" />

            <input type="text" name="duration" placeholder="Duration (e.g. 2 hours)" class="w-full border rounded px-4 py-2" />

            <input type="file" name="image" accept="image/*" class="w-full border rounded px-4 py-2" />

            <input type="text" name="platform" placeholder="Platform (e.g. Zoom)" class="w-full border rounded px-4 py-2" />

            <input type="url" name="registration_link" placeholder="Registration Link" class="w-full border rounded px-4 py-2" />

            <input type="text" name="host_name" placeholder="Host Name" class="w-full border rounded px-4 py-2" />

            <div class="flex items-center gap-4">
                <label><input type="checkbox" name="is_paid" /> Paid Event</label>
                <label><input type="checkbox" name="certificate_available" /> Certificate Available</label>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Submit</button>
        </form>
    </div>
</body>

</html>