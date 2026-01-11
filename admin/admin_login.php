<?php
session_start();
include('../config/db.php');

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM admin WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if($result->num_rows == 1){
        $admin = $result->fetch_assoc();
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error = "Invalid Admin username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - Library System</title>
</head>
<body>
<h2>Admin Login</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" action="">
    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="login">Login</button>
</form>
</body>
</html>
