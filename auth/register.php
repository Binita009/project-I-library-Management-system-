<?php
// auth/register.php
require_once '../config/db.php';
require_once '../config/validation.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize
    $username    = Validation::sanitize($_POST['username']);
    $first_name  = Validation::sanitize($_POST['first_name']);
    $last_name   = Validation::sanitize($_POST['last_name']);
    $email       = Validation::sanitize($_POST['email']);
    $phone       = Validation::sanitize($_POST['phone'] ?? '');
    $password    = Validation::sanitize($_POST['password']);
    $confirm_pw  = Validation::sanitize($_POST['confirm_password']);

    // Combine for DB
    $full_name = trim("$first_name $last_name");

    // Basic Validation
    if(empty($username) || empty($first_name) || empty($email) || empty($password)) {
        $error = "Please fill all required fields.";
    } elseif ($password !== $confirm_pw) {
        $error = "Passwords do not match.";
    } else {
        // Check duplicates
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? OR username = ?");
        mysqli_stmt_bind_param($stmt, "ss", $email, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Username or Email already exists.";
        } else {
            // Create User
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_prepare($conn, 
                "INSERT INTO users (username, full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'member')"
            );
            mysqli_stmt_bind_param($insert, "sssss", $username, $full_name, $email, $phone, $hashed);
            
            if (mysqli_stmt_execute($insert)) {
                $success = "Registration successful! <a href='login.php'>Login here</a>";
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-box">
        <h2>Create Account</h2>
        <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <form method="POST" class="auth-form">
            <!-- Added Username Field -->
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div class="form-row" style="display:flex; gap:10px;">
                <div class="form-group" style="flex:1">
                    <label>First Name *</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1">
                    <label>Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>

            <div class="form-row" style="display:flex; gap:10px;">
                <div class="form-group" style="flex:1">
                    <label>Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1">
                    <label>Confirm *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
            <div class="auth-footer">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>