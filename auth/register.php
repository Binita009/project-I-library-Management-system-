<?php
require_once '../config/db.php';
$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $fullname = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$username' OR email='$email'");
    if(mysqli_num_rows($check) > 0) {
        $error = "Username or Email already taken.";
    } else {
        // We hardcode role to 'member'
        $sql = "INSERT INTO users (username, full_name, email, password, role) 
                VALUES ('$username', '$fullname', '$email', '$password', 'member')";
        if(mysqli_query($conn, $sql)) {
            header("Location: student_login.php?msg=success");
            exit;
        } else {
            $error = "Registration failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2 style="text-align:center;">Create Student Account</h2>
            <?php if($error): ?><div style="color:red;"><?= $error ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>
            </form>
            <p style="text-align:center; margin-top:15px;">Have an account? <a href="student_login.php">Login</a></p>
        </div>
    </div>
</body>
</html>