<?php
require_once '../config/db.php';
requireAdmin();

$error = '';
$success = '';
$errors = []; // Field-specific errors

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize inputs
    $username   = Validation::sanitize(trim($_POST['username']));
    $password   = Validation::sanitize(trim($_POST['password']));
    $full_name  = Validation::sanitize(trim($_POST['full_name']));
    $email      = Validation::sanitize(trim($_POST['email']));
    $phone      = Validation::sanitize(trim($_POST['phone'] ?? ''));

    // ------------------------------
    // Server-side validation
    // ------------------------------
    $errors = Validation::validateUser([
        'username' => $username,
        'email'    => $email,
        'password' => $password,
        'full_name'=> $full_name
    ]);

    // Optional phone validation
    if (!empty($phone)) {
        $phone_validation = Validation::validate('Phone', $phone, 'phone');
        if (isset($phone_validation['error'])) {
            $errors['phone'] = $phone_validation['error'];
        }
    }

    // If no errors, proceed
    if (empty($errors)) {

        // Check if username/email already exists
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ss", $username, $email);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error = "Username or email already exists";
        } else {

            // Hash the password securely
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new member
            $insert_sql = "INSERT INTO users (username, password, full_name, email, phone, role) 
                           VALUES (?, ?, ?, ?, ?, 'member')";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "sssss", $username, $hashed_password, $full_name, $email, $phone);

            if (mysqli_stmt_execute($insert_stmt)) {
                $success = "Student added successfully!";
                $_POST = []; // Clear form
            } else {
                $error = "Error adding student: " . mysqli_error($conn);
            }

            mysqli_stmt_close($insert_stmt);
        }
        mysqli_stmt_close($check_stmt);

    } else {
        // Combine all field errors for top alert
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-container">
    <?php include '../includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <h1>Add New Student</h1>
            <a href="manage_members.php" class="btn">← Back to Students</a>
        </div>

        <div class="card">
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateMemberForm()">
                <!-- Username & Password -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?php echo $_POST['username'] ?? ''; ?>" required>
                        <?php if(isset($errors['username'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                        <?php if(isset($errors['password'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Full Name -->
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?php echo $_POST['full_name'] ?? ''; ?>" required>
                    <?php if(isset($errors['full_name'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['full_name']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email & Phone -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo $_POST['email'] ?? ''; ?>" required>
                        <?php if(isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               value="<?php echo $_POST['phone'] ?? ''; ?>" placeholder="Optional">
                        <?php if(isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Add Student</button>
                <a href="manage_members.php" class="btn">Cancel</a>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/admin.js"></script>
<script>
function validateMemberForm() {
    // Simple client-side check
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();
    const full_name = document.getElementById('full_name').value.trim();
    const email = document.getElementById('email').value.trim();

    if(!username || !password || !full_name || !email) {
        alert("Please fill all required fields.");
        return false;
    }
    return true;
}
</script>
</body>
</html>
