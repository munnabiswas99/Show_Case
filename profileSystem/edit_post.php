<?php
// Redirect to skillPost.php with post ID
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];
    header("Location: ../skillPost.php?id=$post_id");
    exit;
}
