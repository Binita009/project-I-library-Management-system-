<?php
require_once '../config/db.php';

$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = Validation::sanitize($_POST['username']);
    $password = Validation::sanitize($_POST['password']);
    $role = Validation::sanitize($_POST['role']);
    
    // Validate login
    $errors = Validation::validateLogin($username, $password);
    
    if(empty($errors)) {
        $sql = "SELECT id, username, password, role, full_name FROM users 
                WHERE username = ? AND role = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $role);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role, $full_name);
            mysqli_stmt_fetch($stmt);
            
            // Simple password check (for demo)
            $demo_password = $username . "123";
            
            if($password === $hashed_password || $password === $demo_password) {
                session_start();
                $_SESSION["user_id"] = $id;
                $_SESSION["username"] = $username;
                $_SESSION["role"] = $role;
                $_SESSION["full_name"] = $full_name;
                
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
            $error = "No account found with that username.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h2>Library Login</h2>
                <p>Access your library account</p>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form class="auth-form" method="POST" onsubmit="return validateLoginForm()">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" class="form-control" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="invalid-feedback"></div>
                </div>
                
                <div class="form-group">
                    <label for="role">Login as *</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="member" <?php echo (isset($_POST['role']) && $_POST['role'] == 'member') ? 'selected' : ''; ?>>Student</option>
                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Librarian</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
                
                <div class="auth-footer">
                    <div style="margin-bottom: 10px;">
                        <small>Demo Accounts:</small><br>
                        <small>• Admin: admin / admin123</small><br>
                        <small>• Student: student / student123</small>
                    </div>
                    Don't have an account? <a href="register.php">Register here</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/auth.js"></script>
</body>
</html>