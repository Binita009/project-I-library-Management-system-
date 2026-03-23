<?php
require_once '../config/db.php';
requireMember();

$user_id = $_SESSION['user_id'];
$notifications = [];

// 1. Fetch Book Requests Activity
$req_q = mysqli_query($conn, "SELECT br.status, br.request_date, b.title FROM book_requests br JOIN books b ON br.book_id = b.id WHERE br.user_id = $user_id");
while($r = mysqli_fetch_assoc($req_q)) {
    if($r['status'] == 'pending') {
        $notifications[] = [
            'date' => $r['request_date'],
            'icon' => 'fas fa-clock',
            'color' => '#f39c12',
            'title' => 'Request Pending',
            'message' => "Your request for <strong>" . htmlspecialchars($r['title']) . "</strong> is awaiting librarian approval."
        ];
    } elseif($r['status'] == 'approved') {
        $notifications[] = [
            'date' => $r['request_date'],
            'icon' => 'fas fa-check-circle',
            'color' => '#27ae60',
            'title' => 'Request Approved',
            'message' => "Your request for <strong>" . htmlspecialchars($r['title']) . "</strong> was approved! The book has been issued to you."
        ];
    } else {
         $notifications[] = [
            'date' => $r['request_date'],
            'icon' => 'fas fa-times-circle',
            'color' => '#e74c3c',
            'title' => 'Request Rejected',
            'message' => "Your request for <strong>" . htmlspecialchars($r['title']) . "</strong> was declined by the librarian."
        ];
    }
}

// 2. Fetch Issued and Returned Books Activity
$issue_q = mysqli_query($conn, "SELECT issue_date, due_date, return_date, status, fine_amount, b.title FROM issued_books ib JOIN books b ON ib.book_id = b.id WHERE ib.user_id = $user_id");
while($r = mysqli_fetch_assoc($issue_q)) {
    
    // Issue Event (Mocking exact time to 9 AM since DB only stores DATE)
    $notifications[] = [
        'date' => $r['issue_date'] . ' 09:00:00', 
        'icon' => 'fas fa-hand-holding',
        'color' => '#3498db',
        'title' => 'Book Issued',
        'message' => "<strong>" . htmlspecialchars($r['title']) . "</strong> was issued under your name. Please return it by " . date('M d, Y', strtotime($r['due_date'])) . "."
    ];

    // Return Event
    if($r['status'] == 'returned') {
        $fine_msg = $r['fine_amount'] > 0 ? " You paid a fine of <strong>NRS. {$r['fine_amount']}</strong>." : " Returned on time.";
        $notifications[] = [
            'date' => $r['return_date'] . ' 17:00:00', 
            'icon' => 'fas fa-undo',
            'color' => '#8e44ad',
            'title' => 'Book Returned',
            'message' => "You successfully returned <strong>" . htmlspecialchars($r['title']) . "</strong>.{$fine_msg}"
        ];
    }

    // Overdue Alert Event (Dynamic live calculation)
    if($r['status'] == 'issued') {
        $due = new DateTime($r['due_date']);
        $today = new DateTime();
        if($today > $due) {
            $days = $today->diff($due)->days;
            $fine = $days * 2; // NRS 2 per day
            $notifications[] = [
                'date' => date('Y-m-d H:i:s'), // Appears at the very top (current time)
                'icon' => 'fas fa-exclamation-triangle',
                'color' => '#e74c3c',
                'title' => '🚨 Overdue Alert',
                'message' => "<strong>" . htmlspecialchars($r['title']) . "</strong> is overdue by {$days} days! Current pending fine: <strong>NRS. {$fine}</strong>."
            ];
        }
    }
}

// 3. Sort Notifications by Date (Newest first)
usort($notifications, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Notifications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 20px auto;
        }
        .timeline::after {
            content: '';
            position: absolute;
            width: 2px;
            background: #e9ecef;
            top: 0; bottom: 0; left: 24px;
            margin-left: -1px;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 25px;
            padding-left: 65px;
        }
        .timeline-icon {
            position: absolute;
            left: 0;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            z-index: 1;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .timeline-content {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }
        .timeline-date {
            font-size: 13px;
            color: #7f8c8d;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .timeline-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
            background: white;
            border-radius: 12px;
        }
    </style>
</head>
<body>

<div class="admin-container">
    <?php include 'member_sidebar.php'; ?>

    <div class="main-content">
        <div class="content-header">
            <div>
                <h1><i class="fas fa-bell" style="color: #f39c12;"></i> Notifications</h1>
                <p style="color: #7f8c8d; margin-top: 5px;">Your library activity timeline.</p>
            </div>
        </div>

        <div class="timeline">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $note): ?>
                    <div class="timeline-item">
                        <div class="timeline-icon" style="background: <?= $note['color'] ?>;">
                            <i class="<?= $note['icon'] ?>"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-date">
                                <i class="fas fa-clock"></i> <?= date('d M Y - h:i A', strtotime($note['date'])) ?>
                            </div>
                            <div class="timeline-title"><?= $note['title'] ?></div>
                            <div style="color: #555; line-height: 1.5;"><?= $note['message'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash" style="font-size: 40px; color: #bdc3c7; margin-bottom: 15px;"></i>
                    <h3>No Notifications Yet</h3>
                    <p>When you request or borrow a book, updates will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>