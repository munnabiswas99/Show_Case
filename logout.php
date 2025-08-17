<?php
session_start();
session_unset();
session_destroy();
header("Location: userManagement/landing.php");
exit;
