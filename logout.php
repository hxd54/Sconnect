<?php
// logout.php - Handle user logout
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Redirect to homepage with logout message
header('Location: index.php?logout=1');
exit;
?>
