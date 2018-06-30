<?php
require_once("includes/header_without_auth.php");
require_once("includes/helpers.inc.php");
?>

<?php
session_start();

if (!isset($_SESSION['auth'])) {
  redirect('/login.php');
}


?>