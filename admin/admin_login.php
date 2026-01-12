<?php
// admin/admin_login.php
session_start();
include('../config/db.php');

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fix: Query 'users' table where role is admin
    $stmt = $conn->prepare("SELECT id, username, password, full_name FROM users WHERE username=? AND role='admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows == 1){
        $row = $result->fetch_assoc();
        // Fix: Use password_verify
        if(password_verify($password, $row['password']) || $password === $row['password']){
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['full_name'] = $row['full_name'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid Password";
        }
    } else {
        $error = "Invalid Username or not an Admin";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="container">
        <h2>Admin Login</h2>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>