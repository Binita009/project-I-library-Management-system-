<?php
require_once '../config/db.php';
requireAdmin();

$fine_rate = 2; // NRS per day

// Handle View Toggle
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';

// 1. PENDING Fines
$pending_sql = "SELECT 
                    u.id as user_id, 
                    u.full_name, 
                    u.username, 
                    u.phone, 
                    COUNT(ib.id) as overdue_books,
                    SUM(DATEDIFF(CURDATE(), ib.due_date) * $fine_rate) as total_due
                FROM issued_books ib
                JOIN users u ON ib.user_id = u.id
                WHERE ib.status = 'issued' 
                AND ib.due_date < CURDATE()
                GROUP BY u.id
                ORDER BY total_due DESC";
$pending_res = mysqli_query($conn, $pending_sql);

$pending_data = [];
$total_pending_amount = 0;
if ($pending_res) {
    while($row = mysqli_fetch_assoc($pending_res)) {
        $pending_data[] = $row;
        $total_pending_amount += $row['total_due'];
    }
}

// 2. COLLECTED Fines
$history_sql = "SELECT ib.*, b.title, u.full_name, u.username, bc.unique_code 
                FROM issued_books ib 
                JOIN books b ON ib.book_id = b.id 
                JOIN users u ON ib.user_id = u.id 
                LEFT JOIN book_copies bc ON ib.copy_id = bc.id
                WHERE ib.status = 'returned' AND ib.fine_amount > 0
                ORDER BY ib.return_date DESC";
$history_res = mysqli_query($conn, $history_sql);

$history_data = [];
$total_collected_amount = 0;
if ($history_res) {
    while($row = mysqli_fetch_assoc($history_res)) {
        $history_data[] = $row;
        $total_collected_amount += $row['fine_amount'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Fine Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .nav-tabs { display: flex; margin-bottom: 20px; border-bottom: 2px solid #ddd; }
        .nav-item { padding: 12px 20px; text-decoration: none; color: #555; font-weight: 600; border-bottom: 3px solid transparent; cursor: pointer; }
        .nav-item:hover { color: var(--primary); background: #f8f9fa; }
        .nav-item.active { color: var(--primary); border-bottom: 3px solid var(--primary); }
        .stat-box { padding: 20px; border-radius: 10px; color: white; display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .nrs-symbol { font-size: 0.8em; opacity: 0.9; margin-right: 2px; }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Fine Management</h1>
            </div>

            <div class="nav-tabs">
                <a href="?tab=pending" class="nav-item <?= $active_tab == 'pending' ? 'active' : '' ?>">
                    <i class="fas fa-clock"></i> Unpaid / Due Fines
                </a>
                <a href="?tab=collected" class="nav-item <?= $active_tab == 'collected' ? 'active' : '' ?>">
                    <i class="fas fa-check-circle"></i> Collected History
                </a>
            </div>

            <?php if ($active_tab == 'pending'): ?>
                <!-- PENDING VIEW -->
                <div class="stat-box" style="background: #e74c3c;">
                    <i class="fas fa-exclamation-circle" style="font-size: 30px;"></i>
                    <div>
                        <h2 style="margin:0;"><span class="nrs-symbol">NRS.</span> <?= number_format($total_pending_amount, 2) ?></h2>
                        <span style="opacity: 0.9;">Total Outstanding Fines</span>
                    </div>
                </div>

                <div class="card">
                    <h3>Students with Due Fines</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Contact</th>
                                    <th>Overdue Books</th>
                                    <th>Total Fine Due</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($pending_data) > 0): ?>
                                    <?php foreach($pending_data as $row): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                            <small><?= htmlspecialchars($row['username']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['phone']) ?></td>
                                        <td><span class="badge badge-danger"><?= $row['overdue_books'] ?> Books</span></td>
                                        <td>
                                            <span style="color: #e74c3c; font-weight: bold; font-size: 1.1em;">
                                                <small>NRS.</small> <?= number_format($row['total_due'], 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="return_book.php" class="btn btn-primary btn-sm">Collect</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No pending fines!</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php else: ?>
                <!-- COLLECTED VIEW -->
                <div class="stat-box" style="background: #27ae60;">
                    <i class="fas fa-wallet" style="font-size: 30px;"></i>
                    <div>
                        <h2 style="margin:0;"><span class="nrs-symbol">NRS.</span> <?= number_format($total_collected_amount, 2) ?></h2>
                        <span style="opacity: 0.9;">Total Fines Collected</span>
                    </div>
                </div>

                <div class="card">
                    <h3>Collection History</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Returned Date</th>
                                    <th>Student</th>
                                    <th>Book</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($history_data) > 0): ?>
                                    <?php foreach($history_data as $row): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($row['return_date'])) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($row['full_name']) ?></strong><br>
                                            <small><?= htmlspecialchars($row['username']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['title']) ?></td>
                                        <td>
                                            <span style="color: #27ae60; font-weight: bold;">
                                                <small>NRS.</small> <?= number_format($row['fine_amount'], 2) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">No history yet.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>