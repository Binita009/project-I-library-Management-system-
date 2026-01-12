<footer style="background: #2c3e50; color: white; padding: 30px 0; margin-top: 50px;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="margin-bottom: 10px;">Library Management System</h3>
                <p style="color: #bdc3c7;">© <?php echo date('Y'); ?> All rights reserved</p>
            </div>
            <div>
                <p style="color: #bdc3c7;">4th Semester Project</p>
                <p style="color: #bdc3c7;">Simple CRUD Operations</p>
            </div>
        </div>
    </div>
</footer>

<script src="assets/js/validation.js"></script>
<?php
// Example: Only load admin.js if on an admin page
if (basename($_SERVER['PHP_SELF']) == 'manage_book.php' || basename($_SERVER['PHP_SELF']) == 'add_book.php') {
    echo '<script src="assets/js/admin.js"></script>';
}
?>
