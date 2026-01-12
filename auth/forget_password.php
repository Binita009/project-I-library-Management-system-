<?php
require_once '../config/db.php';

$error = '';
$success = '';
$debug_link = ''; // To show link on localhost since email won't work without SMTP

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Check if email exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // 1. Generate secure token
        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutes from now

        // 2. Store hash in DB
        $update = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
        $stmt = mysqli_prepare($conn, $update);
        mysqli_stmt_bind_param($stmt, "sss", $token_hash, $expiry, $email);
        mysqli_stmt_execute($stmt);

        // 3. Create Link
        // Adjust 'localhost/library...' to match your actual folder structure
        $actual_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

        // 4. Simulate Email Sending
        $to = $email;
        $subject = "Password Reset";
        $message = "Click here to reset: " . $actual_link;
        $headers = "From: no-reply@library.com";

        // Try sending email (might fail on localhost)
        if(@mail($to, $subject, $message, $headers)) {
            $success = "Reset link sent to your email.";
        } else {
            $success = "Reset link generated (Email failed on localhost).";
            // FOR DEVELOPMENT ONLY: Show link directly
            $debug_link = $actual_link;
        }
    } else {
        // Security: Don't reveal if email doesn't exist
        $error = "If that email exists, we have sent a reset link.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-box">
        <div class="auth-header">
            <h2>Forgot Password</h2>
            <p>Enter your email to receive a reset link</p>
        </div>

        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <!-- DEV ONLY: Show link for testing -->
        <?php if ($debug_link): ?>
            <div class="alert" style="background:#e8f4fd; color:#0c5460; font-size:13px; word-break:break-all;">
                <strong>Localhost Dev Link:</strong><br>
                <a href="<?= $debug_link ?>">Click here to Reset Password</a>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
            
            <div class="auth-footer">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>