<?php
require_once '../config/db.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf();

    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
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
            
            if($role == 'admin') header("Location: ../admin/admin_dashboard.php");
            else header("Location: ../member/dashboard.php");
            exit;
        }
    }
    setAlert('error', 'Failed', 'Invalid credentials');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Login</h2>
            <form class="auth-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" 
                           pattern="[a-zA-Z0-9_]{3,20}" 
                           title="Your username"
                           required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="member">Student</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="register.php">Create Account</a>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>