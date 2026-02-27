<?php
require_once '../config/db.php';
requireAdmin();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// 1. Fetch current Admin details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    verify_csrf();
    
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (empty($full_name) || empty($email)) {
        $error = "Name and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Password logic
        $password_update_sql = "";
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $error = "New password must be at least 6 characters.";
            } elseif ($new_password !== $confirm_password) {
                $error = "Passwords do not match.";
            } else {
                $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update_sql = ", password = '$hashed_pw'";
            }
        }

        if (empty($error)) {
            $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ? $password_update_sql WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt_update, "sssi", $full_name, $email, $phone, $user_id);

            if (mysqli_stmt_execute($stmt_update)) {
                $_SESSION['full_name'] = $full_name; // Update session name
                setAlert('success', 'Updated', 'Profile updated successfully!');
                header("Location: profile.php");
                exit;
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Manage Profile</h1>
            </div>

            <?php if($error): ?>
                <div class="card" style="background: #fff5f5; border-left: 5px solid #f72585; color: #f72585; padding: 15px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    
                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Username (Read Only)</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" readonly style="background: #eee;">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Phone Number</label>
<input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($admin['phone'] ?? '') ?>">                        </div>
                    </div>

                    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">
                    
                    <h3>Change Password</h3>
                    <p style="color: #666; font-size: 13px; margin-bottom: 15px;">Leave password fields blank if you don't want to change it.</p>

                    <div class="form-row" style="display: flex; gap: 20px;">
                        <div class="form-group" style="flex: 1;">
                            <label>New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Min 6 characters">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>