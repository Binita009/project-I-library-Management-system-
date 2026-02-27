<?php
require_once '../config/db.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = 'member'; // Hardcoded for student

    $stmt = mysqli_prepare($conn, "SELECT id, password, role, full_name FROM users WHERE username = ? AND role = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $role);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($res)) {
        if(password_verify($password, $row['password'])) {
            session_regenerate_id(true);
            $_SESSION["user_id"] = $row['id'];
            $_SESSION["role"] = $row['role'];
            $_SESSION["full_name"] = $row['full_name'];
            
            setAlert('success', 'Success', 'Login successful');
            header("Location: ../member/dashboard.php");
            exit;
        } else {
            setAlert('error', 'Failed', 'Incorrect Password.');
        }
    } else {
        setAlert('error', 'Failed', 'Student account not found.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2 style="text-align:center; color: #4361ee;">Student Login</h2>
            <p style="text-align:center; color:#666; margin-bottom:20px;">Welcome back to the library</p>

            <form class="auth-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <div style="text-align: center; margin-top: 15px;">
                <p>New student? <a href="register.php">Create Account</a></p>
                <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                <a href="librarian_login.php" style="color: #7f8c8d; font-size: 13px;">Switch to Librarian Login</a>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>