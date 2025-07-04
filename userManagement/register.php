<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "showcase");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password

// Check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "Email already registered.";
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $message = urlencode("Welcome, $name!");
        header("Location: ../home.html?message=$message");
        exit();
    }else {
        echo "Registration failed: " . $stmt->error;
    }
    $stmt->close(); // Closes the insert statement after use.
}

//Closes both the SELECT check statement and the database connection properly to free up resources.
$check->close();
$conn->close();
?>
