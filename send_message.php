<?php
session_start();
$conn = new mysqli("localhost", "root", "", "showcase");

if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'] ?? null;
$message = trim($_POST['message'] ?? '');

if (!$receiver_id) {
    exit(json_encode(['status' => 'error', 'message' => 'Receiver ID missing']));
}

$file_path = null;
$file_type = null;

// Handle file upload if exists
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $original_name = basename($_FILES['file']['name']);
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
    $target_file = $upload_dir . $new_file_name;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $file_path = $target_file;

        // Determine file type
        if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $file_type = 'image';
        } elseif (in_array($file_ext, ['mp4', 'webm', 'mov'])) {
            $file_type = 'video';
        } elseif (in_array($file_ext, ['mp3', 'wav', 'm4a'])) {
            $file_type = 'audio';
        } else {
            $file_type = 'document';
        }
    } else {
        exit(json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']));
    }
}

// Insert message or file or both
if ($message !== '' || $file_path !== null) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, file_path, file_type, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisss", $sender_id, $receiver_id, $message, $file_path, $file_type);
    $stmt->execute();

    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Empty message and no file']);
}
