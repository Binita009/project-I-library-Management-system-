<?php
require_once '../config/db.php';

// The username you want to fix
$username = 'super_admin'; 
// The new password you want
$new_pass = '1234';

// 1. Encrypt password using YOUR system's algorithm
$hash = password_hash($new_pass, PASSWORD_DEFAULT);

// 2. Update database
$sql = "UPDATE users SET password = '$hash', role = 'admin' WHERE username = '$username'";

if(mysqli_query($conn, $sql)) {
    echo "<h1>Success!</h1>";
    echo "Username: <b>$username</b><br>";
    echo "Password: <b>$new_pass</b><br>";
    echo "Role: <b>admin</b><br><br>";
    echo "<a href='../auth/login.php'>Go to Login</a>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>