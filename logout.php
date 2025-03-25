<?php
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect the user to the login page or the home page
header("Location: examineers_login.php");
exit();
?>
