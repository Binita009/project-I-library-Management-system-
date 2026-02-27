<?php
// Include your database connection
require_once 'config/db.php';

// --- SET LIBRARIAN CREDENTIALS HERE ---
$username  = 'librarian';
$password  = 'lib12345';      // Change this to whatever you want
$full_name = 'Head Librarian';
$email     = 'librarian@mylibrary.com';
$role      = 'admin';         // Must be 'admin' for the system to recognize them

// Securely hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if the username already exists
$check_sql = "SELECT id FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    echo "<h2 style='color:red;'>Account already exists!</h2>";
    echo "A user with the username '<b>$username</b>' is already in the database.<br>";
    echo "<a href='auth/librarian_login.php'>Go to Login</a>";
} else {
    // Insert the new librarian
    $insert_sql = "INSERT INTO users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $hashed_password, $full_name, $email, $role);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo "<h2 style='color:green;'>✅ Librarian Account Created Successfully!</h2>";
        echo "<b>Username:</b> $username <br>";
        echo "<b>Password:</b> $password <br><br>";
        echo "<a href='auth/librarian_login.php' style='padding:10px 20px; background:#f72585; color:white; text-decoration:none; border-radius:5px;'>Click here to Login</a>";
    } else {
        echo "<h2 style='color:red;'>Error creating account:</h2> " . mysqli_error($conn);
    }
}
?>