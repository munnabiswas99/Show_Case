<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// DB connection
$conn = new mysqli("localhost", "root", "", "showcase");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['id'] ?? null;
$editing = false;

// Initialize fields
$title = $description = $category = $tools_used = $mode = $duration = '';
$image_path = '';
$video_path = '';
$course_outline = $weekly_class = $course_type = '';

// Fetch data if editing
if ($post_id) {
    $editing = true;
    $stmt = $conn->prepare("SELECT * FROM skill_posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $post = $result->fetch_assoc();
        $title = $post['title'];
        $description = $post['description'];
        $category = $post['category'];
        $tools_used = $post['tools_used'];
        $mode = $post['MODE'];
        $duration = $post['duration'];
        $image_path = $post['image_path'];
        $video_path = $post['video_path'];
        $course_outline = $post['course_outline'];
        $weekly_class = $post['weekly_class'];
        $course_type = $post['course_type'];
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $tools_used = $_POST['tools_used'] ?? '';
    $mode = $_POST['mode'] ?? 'Online';
    $duration = $_POST['duration'] ?? '';
    $course_outline = $_POST['course_outline'] ?? '';
    $weekly_class = $_POST['weekly_class'] ?? '';
    $course_type = $_POST['course_type'] ?? 'free';

    $new_image_path = $image_path;
    $new_video_path = $video_path;

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $new_image_path = "profileSystem/uploads/" . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path);
    }

    // Handle video upload
    if (!empty($_FILES['video']['name'])) {
        $video_name = time() . '_' . basename($_FILES['video']['name']);
        $new_video_path = "profileSystem/uploads/" . $video_name;
        move_uploaded_file($_FILES['video']['tmp_name'], $new_video_path);
    }

    if ($editing && $post_id) {
        // Update
        $stmt = $conn->prepare("UPDATE skill_posts 
            SET title=?, description=?, category=?, tools_used=?, mode=?, duration=?, image_path=?, video_path=?, 
                course_outline=?, weekly_class=?, course_type=? 
            WHERE id=? AND user_id=?");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssssssssssii", $title, $description, $category, $tools_used, $mode, $duration, $new_image_path, $new_video_path, $course_outline, $weekly_class, $course_type, $post_id, $user_id);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO skill_posts 
            (user_id, title, description, category, tools_used, mode, duration, image_path, video_path, course_outline, weekly_class, course_type, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("isssssssssss", $user_id, $title, $description, $category, $tools_used, $mode, $duration, $new_image_path, $new_video_path, $course_outline, $weekly_class, $course_type);
    }

    $stmt->execute();
    $stmt->close();
    $conn->close();

    header("Location: profileSystem/post.php");
    exit;
}
?>

<!-- HTML Form Section -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Post Your Skill</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>

<body class="bg-gray-100">
    <div class="max-w-xl mx-auto mt-10 bg-white p-8 rounded shadow-lg">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center"><?= $editing ? "Edit Your Skill" : "Share Your Skill" ?></h2>
        <form method="POST" enctype="multipart/form-data" action="skillPost.php<?= $editing ? '?id=' . $post_id : '' ?>">

            <input type="text" name="title" value="<?= htmlspecialchars($title) ?>" placeholder="Skill Title" class="w-full p-2 border rounded mb-4" required>

            <textarea name="description" placeholder="Describe your skill..." class="w-full p-2 border rounded mb-4" required><?= htmlspecialchars($description) ?></textarea>

            <!-- Category -->
            <select name="category" class="w-full p-2 border rounded mb-4" required>
                <option value="">Select Category</option>
                <option value="Web Development" <?= $category === 'Web Development' ? 'selected' : '' ?>>Web Development</option>
                <option value="Graphic Design" <?= $category === 'Graphic Design' ? 'selected' : '' ?>>Graphic Design</option>
                <option value="Content Writing" <?= $category === 'Content Writing' ? 'selected' : '' ?>>Content Writing</option>
                <option value="Digital Marketing" <?= $category === 'Digital Marketing' ? 'selected' : '' ?>>Digital Marketing</option>
                <option value="Data Science" <?= $category === 'Data Science' ? 'selected' : '' ?>>Data Science</option>
                <option value="Video Editing" <?= $category === 'Video Editing' ? 'selected' : '' ?>>Video Editing</option>
            </select>

            <!-- Tools Used -->
            <input type="text" name="tools_used" value="<?= htmlspecialchars($tools_used) ?>" placeholder="Tools used (e.g., VS Code, Photoshop)" class="w-full p-2 border rounded mb-4" required>

            <!-- Mode -->
            <select name="mode" class="w-full p-2 border rounded mb-4" required>
                <option value="Online" <?= $mode === 'Online' ? 'selected' : '' ?>>Online</option>
                <option value="Offline" <?= $mode === 'Offline' ? 'selected' : '' ?>>Offline</option>
            </select>

            <!-- Duration -->
            <input type="text" name="duration" value="<?= htmlspecialchars($duration) ?>" placeholder="Number of classes or total hours" class="w-full p-2 border rounded mb-4" required>

            <!-- NEW: Course Outline -->
            <textarea name="course_outline" placeholder="Course Outline (Week-by-week breakdown)" class="w-full p-2 border rounded mb-4"><?= htmlspecialchars($course_outline) ?></textarea>

            <!-- NEW: Weekly Class -->
            <input type="text" name="weekly_class" value="<?= htmlspecialchars($weekly_class) ?>" placeholder="Weekly Class Schedule (e.g., Sun & Tue, 7PM)" class="w-full p-2 border rounded mb-4">

            <!-- NEW: Course Type -->
            <select name="course_type" class="w-full p-2 border rounded mb-4" required>
                <option value="free" <?= $course_type === 'free' ? 'selected' : '' ?>>Free</option>
                <option value="paid" <?= $course_type === 'paid' ? 'selected' : '' ?>>Paid</option>
            </select>

            <!-- Others -->
            <textarea name="others" placeholder="Any additional information..." class="w-full p-2 border rounded mb-4"></textarea>

            <!-- Image Upload -->
            <label class="block text-gray-600 mb-1">Upload Image:</label>
            <input type="file" name="image" class="w-full p-2 border rounded mb-4">
            <?php if ($editing && $image_path): ?>
                <img src="<?= $image_path ?>" class="h-32 rounded mt-2" alt="Existing Image">
            <?php endif; ?>

            <!-- Video Upload -->
            <label class="block text-gray-600 mb-1">Upload Video:</label>
            <input type="file" name="video" class="w-full p-2 border rounded mb-4">
            <?php if (!empty($video_path)): ?>
                <video controls class="w-full max-w-md mb-4 rounded shadow">
                    <source src="<?= $video_path ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>

            <!-- Buttons -->
            <div class="flex justify-between items-center mt-4">
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition duration-300">
                    <?= $editing ? 'Update Post' : 'Post Skill' ?>
                </button>
                <a href="profileSystem/post.php" class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition duration-300">Close</a>
            </div>

        </form>
    </div>
</body>

</html>