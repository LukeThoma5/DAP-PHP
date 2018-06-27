<?php
require_once("header_without_auth.php");
require_once("helpers.inc.php");
?>

<?php
session_start();

if (!isset($_SESSION['auth'])) {
  redirect('/login.php');
}


?>