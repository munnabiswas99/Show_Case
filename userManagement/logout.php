<?php
session_start();
session_destroy();
header("Location: ../index.html?message=Logged%20out%20successfully");
exit();
?>
