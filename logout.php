<?php
require_once('includes/helpers.inc.php');
session_start();
session_unset();
session_destroy();
redirect('/login.php');
?>