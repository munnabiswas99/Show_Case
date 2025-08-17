<?php
session_start();
$conn = new mysqli("localhost", "root", "", "showcase");

$user_id = $_SESSION['user_id'];
$section = $_POST['section'];
$titles = $_POST['title'];
$orgs = $_POST['org'];
$durations = $_POST['duration'];
$ids = $_POST['id'] ?? [];

foreach ($titles as $index => $title) {
    $organization = $orgs[$index] ?? '';
    $duration = $durations[$index] ?? '';
    $id = $ids[$index] ?? null;

    if ($id) {
        $stmt = $conn->prepare("UPDATE user_details SET title=?, organization_or_institute=?, duration=? WHERE id=? AND user_id=?");
        $stmt->bind_param("sssii", $title, $organization, $duration, $id, $user_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO user_details (user_id, section, title, organization_or_institute, duration) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $section, $title, $organization, $duration);
    }

    $stmt->execute();
}

header("Location: ../profileSystem/profile.php?message=Details updated successfully");
