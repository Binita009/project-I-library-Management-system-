<?php
session_start();
include('../config/db.php');

if(isset($_POST['register'])){
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);

    if($password != $confirm_password){
        $error = "Passwords do not match!";
    } else {
        // Check if username exists
        $check = "SELECT * FROM members WHERE username='$username'";
        $result = $conn->query($check);

        if($result->num_rows > 0){
            $error = "Username already taken!";
        } else {
            $sql = "INSERT INTO members (full_name, email, phone, address, username, password) 
                    VALUES ('$full_name', '$email', '$phone', '$address', '$username', '$password')";
            if($conn->query($sql) === TRUE){
                $success = "Registration successful! <a href='login.php'>Login now</a>.";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Member Registration</title>
</head>
<body>
<h2>Member Registration</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
<form method="post" action="">
    <input type="text" name="full_name" placeholder="Full Name" required><br>
    <input type="email" name="email" placeholder="Email"><br>
    <input type="text" name="phone" placeholder="Phone"><br>
    <textarea name="address" placeholder="Address"></textarea><br>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
    <button type="submit" name="register">Register</button>
</form>
<p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>
