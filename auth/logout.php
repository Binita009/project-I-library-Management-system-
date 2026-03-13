<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the home page instead of a missing login.php
header("Location: ../index.php");
exit;
?>