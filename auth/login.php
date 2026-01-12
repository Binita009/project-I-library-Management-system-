<?php
// auth/login.php
require_once '../config/db.php';
require_once '../config/validation.php';

$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = Validation::sanitize($_POST['username']);
    $password = $_POST['password']; // Don't sanitize password before verification
    $role = $_POST['role'];
    
    // Check user in DB
    $sql = "SELECT id, username, password, role, full_name FROM users WHERE username = ? AND role = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($result)) {
        // Verify Password (supports both hashed and legacy plain text from SQL dump)
        if(password_verify($password, $row['password']) || $password === $row['password']) {
            $_SESSION["user_id"] = $row['id'];
            $_SESSION["username"] = $row['username'];
            $_SESSION["role"] = $row['role'];
            $_SESSION["full_name"] = $row['full_name'];
            
            if($role == 'admin') {
                header("Location: ../admin/admin_dashboard.php");
            } else {
                header("Location: ../member/dashboard.php");
            }
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found or incorrect role.";
    }
}
?>
<!-- HTML section remains largely the same, just ensure form action is correct -->
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
            <div class="auth-header"><h2>Login</h2></div>
            <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
            
            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Login As</label>
                    <select name="role" class="form-control">
                        <option value="member">Student</option>
                        <option value="admin">Librarian (Admin)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
                <div class="auth-footer">
                    <a href="register.php">Create Account</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>