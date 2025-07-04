<?php
session_set_cookie_params([
    'secure' => true,         // Only send cookie over HTTPS
    'httponly' => true,       // JavaScript can't access the cookie
    'samesite' => 'Strict'    // Prevent CSRF (cross-site requests)
]);
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "showcase");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get login info
$email = $_POST['email'];
$password = $_POST['password'];

// Fetch user(stmt = statement) from database
$stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 1) {
    $stmt->bind_result($id, $name, $hashed_password);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;

        // Encode the welcome message with user's name
        $message = urlencode("Welcome back, $name!");

        // Redirect with the message
        header("Location: ../home.html?message=$message");
        exit();
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found.";
}

$stmt->close();
$conn->close();
?>
