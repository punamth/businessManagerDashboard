<?php
session_start();
// Clear session data
session_unset(); 
session_destroy();
// Start a fresh session to prevent access to previous data
session_start();
session_regenerate_id(true); // Generate a new session ID for security
// Redirect to login page
header('Location: login.php');
exit();
?>