<?php
// Get the redirect function into scope
require_once('includes/helpers.inc.php');

// Get access to the session variable
session_start();

// Clear the session then destroy it
session_unset();
session_destroy();

// Send the user back to the login page
redirect('/login.php');
?>