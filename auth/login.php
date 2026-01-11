<?php
session_start();
include('../config/db.php');

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM members WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if($result->num_rows == 1){
        $member = $result->fetch_assoc();
        $_SESSION['user_id'] = $member['member_id'];
        $_SESSION['username'] = $member['username'];

        header("Location: ../member/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Member Login</title>
</head>
<body>
<h2>Member Login</h2>
<?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
<form method="post" action="">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="login">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>
</body>
</html>
