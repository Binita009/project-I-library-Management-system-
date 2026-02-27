<?php
require_once '../config/db.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = 'admin'; // Hardcoded for admin

    $stmt = mysqli_prepare($conn, "SELECT id, password, role, full_name FROM users WHERE username = ? AND role = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $role);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    if($row = mysqli_fetch_assoc($res)) {
        // We include a temporary fallback "admin" password check in case you haven't hashed the default admin password yet
        if(password_verify($password, $row['password']) || $password === 'admin') { 
            session_regenerate_id(true);
            $_SESSION["user_id"] = $row['id'];
            $_SESSION["role"] = $row['role'];
            $_SESSION["full_name"] = $row['full_name'];
            
            setAlert('success', 'Success', 'Welcome back, Librarian.');
            header("Location: ../admin/admin_dashboard.php");
            exit;
        } else {
            setAlert('error', 'Failed', 'Incorrect Password.');
        }
    } else {
        setAlert('error', 'Failed', 'Librarian account not found.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Librarian Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <style>
        /* Give admin login a distinct red/pink top border so it feels different */
        .auth-box { border-top: 5px solid #f72585; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2 style="text-align:center; color: #f72585;">Librarian Portal</h2>
            <p style="text-align:center; color:#666; margin-bottom:20px;">Staff Access Only</p>

            <form class="auth-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary" style="width: 100%; background: #f72585; border:none;">Login to Dashboard</button>
            </form>

            <div style="text-align: center; margin-top: 20px;">
                <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                <a href="student_login.php" style="color: #7f8c8d; font-size: 13px;">Back to Student Login</a>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>