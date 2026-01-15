<?php
require_once '../config/db.php';
requireAdmin();

// Default Dates: First day of this month to Today
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');
$report_type = $_GET['report_type'] ?? 'issue_history';

$results = null;

// Logic based on Report Type
if ($report_type == 'issue_history') {
    // Shows who took what book within the date range
    $sql = "SELECT ib.*, b.title, u.full_name, u.username, bc.unique_code 
            FROM issued_books ib 
            JOIN books b ON ib.book_id = b.id 
            JOIN users u ON ib.user_id = u.id 
            LEFT JOIN book_copies bc ON ib.copy_id = bc.id
            WHERE ib.issue_date BETWEEN '$start_date' AND '$end_date'
            ORDER BY ib.issue_date DESC";
    $results = mysqli_query($conn, $sql);

} elseif ($report_type == 'overdue') {
    // Shows currently overdue books
    $sql = "SELECT ib.*, b.title, u.full_name, u.phone, bc.unique_code 
            FROM issued_books ib 
            JOIN books b ON ib.book_id = b.id 
            JOIN users u ON ib.user_id = u.id 
            LEFT JOIN book_copies bc ON ib.copy_id = bc.id
            WHERE ib.status = 'issued' AND ib.due_date < CURDATE()
            ORDER BY ib.due_date ASC";
    $results = mysqli_query($conn, $sql);

} elseif ($report_type == 'top_books') {
    // Shows which books are most popular
    $sql = "SELECT b.title, b.author, COUNT(ib.id) as issue_count 
            FROM issued_books ib 
            JOIN books b ON ib.book_id = b.id 
            GROUP BY ib.book_id 
            ORDER BY issue_count DESC 
            LIMIT 20";
    $results = mysqli_query($conn, $sql);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Library Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div class="content-header">
                <h1>Library Reports</h1>
                <button onclick="window.print()" class="btn btn-primary no-print">
                    <i class="fas fa-print"></i> Print / Save PDF
                </button>
            </div>

            <!-- Filter Form (Hidden when printing) -->
            <div class="card no-print">
                <form method="GET" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Report Type</label>
                        <select name="report_type" class="form-control" onchange="this.form.submit()">
                            <option value="issue_history" <?= $report_type=='issue_history'?'selected':'' ?>>Issue History</option>
                            <option value="overdue" <?= $report_type=='overdue'?'selected':'' ?>>Overdue Books</option>
                            <option value="top_books" <?= $report_type=='top_books'?'selected':'' ?>>Most Popular Books</option>
                        </select>
                    </div>

                    <?php if($report_type != 'overdue' && $report_type != 'top_books'): ?>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>From Date</label>
                        <input type="date" name="start_date" value="<?= $start_date ?>" class="form-control">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>To Date</label>
                        <input type="date" name="end_date" value="<?= $end_date ?>" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Report Results -->
            <div class="card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2>
                        <?php 
                        if($report_type == 'issue_history') echo "Issue History Report";
                        elseif($report_type == 'overdue') echo "Overdue Books Report";
                        else echo "Top Issued Books";
                        ?>
                    </h2>
                    <p style="color: #666;">
                        Generated on: <?= date('d M Y') ?> 
                        <?php if($report_type == 'issue_history'): ?>
                            <br>Period: <?= date('d M Y', strtotime($start_date)) ?> to <?= date('d M Y', strtotime($end_date)) ?>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        
                        <!-- 1. ISSUE HISTORY TABLE -->
                        <?php if($report_type == 'issue_history'): ?>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Book Title</th>
                                <th>Unique Code</th>
                                <th>Student</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($results)): ?>
                            <tr>
                                <td><?= date('d-m-Y', strtotime($row['issue_date'])) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td style="font-family: monospace;"><?= htmlspecialchars($row['unique_code'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?> (<?= $row['username'] ?>)</td>
                                <td>
                                    <?php if($row['status'] == 'returned'): ?>
                                        <span style="color: green;">Returned (<?= date('d-m-Y', strtotime($row['return_date'])) ?>)</span>
                                    <?php else: ?>
                                        <span style="color: orange;">Issued</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>

                        <!-- 2. OVERDUE TABLE -->
                        <?php elseif($report_type == 'overdue'): ?>
                        <thead>
                            <tr>
                                <th>Due Date</th>
                                <th>Book Title</th>
                                <th>Unique Code</th>
                                <th>Student</th>
                                <th>Phone</th>
                                <th>Days Late</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if(mysqli_num_rows($results) > 0):
                            while($row = mysqli_fetch_assoc($results)): 
                                $days_late = floor((time() - strtotime($row['due_date'])) / (60 * 60 * 24));
                            ?>
                            <tr>
                                <td style="color: red; font-weight: bold;"><?= date('d-m-Y', strtotime($row['due_date'])) ?></td>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td style="font-family: monospace;"><?= htmlspecialchars($row['unique_code'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                                <td style="color: red;"><?= $days_late ?> Days</td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="6" class="text-center">No overdue books found!</td></tr>
                            <?php endif; ?>
                        </tbody>

                        <!-- 3. TOP BOOKS TABLE -->
                        <?php elseif($report_type == 'top_books'): ?>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Times Issued</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            while($row = mysqli_fetch_assoc($results)): 
                            ?>
                            <tr>
                                <td>#<?= $rank++ ?></td>
                                <td style="font-weight: bold;"><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['author']) ?></td>
                                <td><?= $row['issue_count'] ?> times</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <?php endif; ?>

                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>