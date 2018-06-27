<?php
require_once('helpers.inc.php');
session_start();
session_unset();
session_destroy();
redirect('/login.php');
?>