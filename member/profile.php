<?php
require_once '../config/db.php';
requireMember();

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = Validation::sanitize($_POST['full_name']);
    $email = Validation::sanitize($_POST['email']);
    $phone = Validation::sanitize($_POST['phone']);
    $current_password = Validation::sanitize($_POST['current_password'] ?? '');
    $new_password = Validation::sanitize($_POST['new_password'] ?? '');
    $confirm_password = Validation::sanitize($_POST['confirm_password'] ?? '');
    
    // Validate name
    $name_validation = Validation::validate('Full Name', $full_name, 'name');
    if(isset($name_validation['error'])) {
        $error = $name_validation['error'];
    }
    
    // Validate email
    $email_validation = Validation::validate('Email', $email, 'email');
    if(isset($email_validation['error'])) {
        $error = $email_validation['error'];
    }
    
    // Validate phone if provided
    if(!empty($phone)) {
        $phone_validation = Validation::validate('Phone', $phone, 'phone');
        if(isset($phone_validation['error'])) {
            $error = $phone_validation['error'];
        }
    }
    
    // Check password change
    if(!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // Check if current password is correct
        if($current_password !== $user['password']) {
            $error = "Current password is incorrect";
        } elseif($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        } elseif(strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters";
        } else {
            // Update with new password
            $update_password = $new_password;
        }
    }
    
    if(empty($error)) {
        $password_to_use = isset($update_password) ? $update_password : $user['password'];
        
        $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, password = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ssssi", $full_name, $email, $phone, $password_to_use, $user_id);
        
        if(mysqli_stmt_execute($update_stmt)) {
            $success = "Profile updated successfully!";
            // Update session
            $_SESSION['full_name'] = $full_name;
            // Refresh user data
            $user['full_name'] = $full_name;
            $user['email'] = $email;
            $user['phone'] = $phone;
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
        mysqli_stmt_close($update_stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php include 'member_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>My Profile</h1>
            </div>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <form method="POST" onsubmit="return validateProfileForm()">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <small class="help-text">Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="role">Account Type</label>
                            <input type="text" id="role" class="form-control" 
                                   value="<?php echo ucfirst($user['role']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="join_date">Member Since</label>
                            <input type="text" id="join_date" class="form-control" 
                                   value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly>
                        </div>
                    </div>
                    
                    <hr style="margin: 30px 0;">
                    
                    <h3>Change Password (Optional)</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control">
                            <small class="help-text">Leave empty if not changing</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    function validateProfileForm() {
        let fullName = document.getElementById('full_name');
        let email = document.getElementById('email');
        let phone = document.getElementById('phone');
        let newPassword = document.getElementById('new_password');
        let confirmPassword = document.getElementById('confirm_password');
        
        // Clear previous errors
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.classList.remove('show');
        });
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        let isValid = true;
        
        // Validate full name
        if(!fullName.value.trim()) {
            showError(fullName, "Full name is required");
            isValid = false;
        } else if(!/^[a-zA-Z\s]{2,50}$/.test(fullName.value)) {
            showError(fullName, "Full name must be 2-50 letters and spaces only");
            isValid = false;
        }
        
        // Validate email
        if(!email.value.trim()) {
            showError(email, "Email is required");
            isValid = false;
        } else if(!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email.value)) {
            showError(email, "Enter a valid email address");
            isValid = false;
        }
        
        // Validate phone if provided
        if(phone.value.trim() && !/^[6-9]\d{9}$/.test(phone.value)) {
            showError(phone, "Enter a valid 10-digit mobile number");
            isValid = false;
        }
        
        // Validate password if changing
        if(newPassword.value || confirmPassword.value) {
            if(newPassword.value !== confirmPassword.value) {
                showError(confirmPassword, "Passwords do not match");
                isValid = false;
            }
            if(newPassword.value && newPassword.value.length < 6) {
                showError(newPassword, "Password must be at least 6 characters");
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function showError(element, message) {
        element.classList.add('is-invalid');
        let feedback = element.nextElementSibling;
        if(feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
            feedback.classList.add('show');
        }
    }
    </script>
</body>
</html>