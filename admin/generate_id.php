<?php
require_once '../config/db.php';
requireAdmin();

if(!isset($_GET['id'])) {
    header("Location: manage_members.php");
    exit;
}

$id = (int)$_GET['id'];
$sql = "SELECT * FROM users WHERE id = ? AND role = 'member'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if(!$user) die("Student not found.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Student ID Card - <?= htmlspecialchars($user['full_name']) ?></title>
    <style>
        body { background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; font-family: Arial, sans-serif;}
        .id-card { 
            width: 320px; background: #fff; border-radius: 12px; overflow: hidden; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.15); border: 1px solid #e0e0e0;
        }
        .id-header { background: #4361ee; color: #fff; padding: 15px; text-align: center; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .id-body { padding: 25px 20px; text-align: center; }
        .profile-pic { 
            width: 90px; height: 90px; background: #f0f2f5; border-radius: 50%; 
            margin: 0 auto 15px; display: flex; justify-content: center; align-items: center; font-size: 40px; color: #adb5bd; border: 3px solid #4361ee;
        }
        .details { margin-top: 20px; text-align: left; font-size: 14px; color: #333; line-height: 1.8; }
        .id-footer { background: #f8f9fa; text-align: center; padding: 12px; font-size: 11px; color: #888; border-top: 1px solid #eee; }
        .print-btn { padding: 10px 20px; background: #4361ee; color: #fff; border: none; border-radius: 5px; cursor: pointer; display: block; margin: 0 auto 20px; width: 320px; font-size: 16px; }
        @media print {
            body { background: white; align-items: flex-start; margin-top: 20px;}
            .no-print { display: none; }
            .id-card { box-shadow: none; border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div>
        <button onclick="window.print()" class="print-btn no-print">🖨️ Print ID Card</button>
        <div class="id-card">
            <div class="id-header">Library Member ID</div>
            <div class="id-body">
                <div class="profile-pic">👤</div>
                <h3 style="margin: 0 0 5px 0; color: #1e1e2d;"><?= htmlspecialchars($user['full_name']) ?></h3>
                <span style="background: #e1f5fe; color: #0288d1; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">Student</span>
                
                <div class="details">
                    <p><strong>Member ID:</strong> #<?= str_pad($user['id'], 5, "0", STR_PAD_LEFT) ?></p>
                    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Joined:</strong> <?= date('F d, Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
            <div class="id-footer">Valid for Internal Library Access Only</div>
        </div>
    </div>
</body>
</html>