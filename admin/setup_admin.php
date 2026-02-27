<?php
require_once '../config/db.php';

// CONFIGURATION: Change these to your desired admin details
$admin_user = 'head_librarian';
$admin_pass = 'librarian123';
$admin_name = 'Chief Librarian';
$admin_email = 'admin@library.com';

$hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);

// Check if user exists
$check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$admin_user'");

if (mysqli_num_rows($check) > 0) {
    echo "Admin user already exists.";
} else {
    $sql = "INSERT INTO users (username, password, full_name, email, role) 
            VALUES ('$admin_user', '$hashed_pass', '$admin_name', '$admin_email', 'admin')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<h1>Admin Created Successfully!</h1>";
        echo "Username: $admin_user <br> Password: $admin_pass <br>";
        echo "<a href='../auth/admin_login.php'>Go to Librarian Login</a>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>