
<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

session_start();

include_once 'pages.php';
include_once 'requests.php';

ensureLoggedIn();

if ($_SESSION['overrider_id']) {
    logOutFromUser();
} else {
    removeContextualLoginToken();
    session_destroy();
    setcookie("loginToken", "", time() - 3600, "/", "", true, true);
}

header('Location: index.php');
die();

?>
