
<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

session_start();

include_once 'pages.php';

ensureLoggedIn();

session_destroy();
header('Location: index.php');
die();

?>
