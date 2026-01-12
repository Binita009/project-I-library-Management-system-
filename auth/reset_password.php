<?php
require_once '../config/db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid token");
}

$token_hash = hash("sha256", $token);

// 1. Check if token is valid and not expired
$sql = "SELECT id FROM users WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $token_hash);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("Link is invalid or has expired.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pass = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($pass !== $confirm) {
        $error = "Passwords do not match";
    } elseif (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // 2. Update Password
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        
        $update = "UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Password reset successfully! <a href='login.php'>Login now</a>";
        } else {
            $error = "Error updating password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-box">
        <h2>Reset Password</h2>
        
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <?php if (empty($success)): ?>
        <form class="auth-form" method="POST">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>