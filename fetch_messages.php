<?php
session_start();
$conn = new mysqli("localhost", "root", "", "showcase");

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'] ?? 0;

// Mark received messages as 'read'
$update = $conn->prepare("
    UPDATE messages SET status = 'read'
    WHERE sender_id = ? AND receiver_id = ? AND status = 'unread'
");
$update->bind_param("ii", $receiver_id, $sender_id);
$update->execute();


$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?)
    ORDER BY sent_at ASC
");
$stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $is_sender = $row['sender_id'] == $sender_id;
    $alignClass = $is_sender ? "justify-end" : "justify-start";
    $bubbleBg = $is_sender ? "bg-blue-100 text-blue-900" : "bg-gray-100 text-gray-900";
    $timestampColor = $is_sender ? "text-blue-600" : "text-gray-500";

    // Format timestamp nicely
    $time = date("g:i a, M j", strtotime($row['sent_at']));

    echo "<div class='flex $alignClass mb-2'>";
    echo "<div class='max-w-xs px-4 py-2 rounded-lg $bubbleBg shadow hover:shadow-md transition-shadow duration-200'>";

    // Message text
    if (!empty($row['message'])) {
        echo "<p class='whitespace-pre-wrap'>" . htmlspecialchars($row['message']) . "</p>";
    }

    // File content
    if (!empty($row['file_path']) && !empty($row['file_type'])) {
        $file_url = htmlspecialchars($row['file_path']);
        $type = $row['file_type'];

        if ($type === 'image') {
            echo "<img src='$file_url' class='mt-2 rounded max-w-full' style='max-width:200px;' alt='Image'>";
        } elseif ($type === 'video') {
            echo "<video controls class='mt-2 rounded max-w-full' style='max-width:200px;'>
                    <source src='$file_url' type='video/mp4'>Your browser does not support video.
                  </video>";
        } elseif ($type === 'audio') {
            echo "<audio controls class='mt-2 w-full'>
                    <source src='$file_url' type='audio/mpeg'>Your browser does not support audio.
                  </audio>";
        } else {
            $fileName = basename($row['file_path']);
            echo "<a href='$file_url' download class='mt-2 inline-block text-blue-600 underline'>📎 $fileName</a>";
        }
    }

    // Timestamp
    echo "<div class='text-xs mt-1 $timestampColor'>$time</div>";

    echo "</div></div>";
}
