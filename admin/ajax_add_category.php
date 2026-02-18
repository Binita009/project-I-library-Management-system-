<?php
require_once '../config/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    
    if (!empty($name)) {
        // Check if exists
        $check = mysqli_query($conn, "SELECT id FROM categories WHERE name = '$name'");
        if (mysqli_num_rows($check) > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Category already exists']);
        } else {
            if (mysqli_query($conn, "INSERT INTO categories (name) VALUES ('$name')")) {
                echo json_encode(['status' => 'success', 'name' => $name]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database error']);
            }
        }
    }
    exit;
}