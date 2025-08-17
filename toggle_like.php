<?php
session_start();
$conn = new mysqli("localhost", "root", "", "showcase");
if ($conn->connect_error) die("DB error");


$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];

$check = $conn->prepare("SELECT id FROM skill_post_likes WHERE user_id = ? AND post_id = ?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $delete = $conn->prepare("DELETE FROM skill_post_likes WHERE user_id = ? AND post_id = ?");
    $delete->bind_param("ii", $user_id, $post_id);
    $delete->execute();
} else {
    $insert = $conn->prepare("INSERT INTO skill_post_likes (user_id, post_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $post_id);
    $insert->execute();
}

header("Location: home.php");
exit;
