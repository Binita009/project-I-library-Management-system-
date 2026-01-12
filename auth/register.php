<?php
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = Validation::sanitize($_POST['username']);
    $email = Validation::sanitize($_POST['email']);
    $password = Validation::sanitize($_POST['password']);
    $confirm_password = Validation::sanitize($_POST['confirm_password']);
    $full_name = Validation::sanitize($_POST['full_name']);
    $phone = Validation::sanitize($_POST['phone'] ?? '');

    // Validate fields
    $errors = Validation::validateUser(compact('username','email','password','full_name'));

    if ($password !== $confirm_password) {
        $errors['password'] = "Passwords do not match";
    }

    if (empty($errors)) {
        // Check if username or email exists
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username=? OR email=?");
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error = "Username or email already exists";
        } else {
            $hashed_password = $password; // Use password_hash($password, PASSWORD_DEFAULT) in real apps

            $stmt = mysqli_prepare($conn,
                "INSERT INTO users (username,email,password,full_name,phone,role)
                 VALUES (?, ?, ?, ?, ?, 'member')"
            );
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $hashed_password, $full_name, $phone);

            if (mysqli_stmt_execute($stmt)) {
                $success = "Registration successful! You can now login.";
                $_POST = [];
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-box">
        <h2>Create Account</h2>
        <p>Join our library community</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <?php 
            $fields = [
                'full_name'=>'Full Name',
                'username'=>'Username',
                'email'=>'Email',
                'phone'=>'Phone Number',
                'password'=>'Password',
                'confirm_password'=>'Confirm Password'
            ];
            foreach ($fields as $name=>$label):
            ?>
                <div class="form-group">
                    <label for="<?= $name ?>"><?= $label ?> <?= in_array($name,['full_name','username','email','password','confirm_password'])?'*':'' ?></label>
                    <input type="<?= in_array($name,['password','confirm_password'])?'password':'text' ?>" 
                           id="<?= $name ?>" name="<?= $name ?>" 
                           class="form-control"
                           value="<?= htmlspecialchars($_POST[$name] ?? '') ?>"
                           <?= in_array($name,['full_name','username','email','password','confirm_password'])?'required':'' ?>>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary">Register</button>
            <p class="auth-footer">Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
</div>

<script src="../assets/js/auth.js"></script>
</body>
</html>
