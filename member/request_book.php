<?php
require_once '../config/db.php';
requireMember();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    verify_csrf();
    $book_id = (int)$_POST['book_id'];
    $user_id = $_SESSION['user_id'];

    // Check if already requested and pending
    $check = mysqli_query($conn, "SELECT id FROM book_requests WHERE user_id = $user_id AND book_id = $book_id AND status = 'pending'");
    
    if (mysqli_num_rows($check) > 0) {
        setAlert('error', 'Already Requested', 'You already have a pending request for this book.');
    } else {
        mysqli_query($conn, "INSERT INTO book_requests (user_id, book_id) VALUES ($user_id, $book_id)");
        setAlert('success', 'Success', 'Book requested successfully! A librarian will review your request.');
    }
    header("Location: view_book.php?id=" . $book_id);
    exit;
}

header("Location: books.php");
exit;
?>