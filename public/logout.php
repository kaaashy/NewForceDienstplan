
<?php

session_start();

include_once 'pages.php';

ensureLoggedIn();

session_destroy();
header('Location: index.php');
die();

?>
