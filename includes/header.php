<?php
// Add the contents of the header
require_once("includes/header_without_auth.php");
// Import for the redirect helper
require_once("includes/helpers.inc.php");
?>

<?php
// Start the session to determine if the auth key has been set on the session
session_start();

// If not set the user isn't logged in so redirect to login page.
if (!isset($_SESSION['auth'])) {
  redirect('/login.php');
  exit(); // Stop processing to prevent the rest of the page rendering
}

?>