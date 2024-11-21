<?php 

include_once 'database.php';

function ensureLoggedIn()
{
    if (isset($_SESSION['user_id'])) {
        if (!getUserActive($_SESSION['user_id'])){
            session_destroy();
        }
    }

    if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)){
        backToLogin();
        exit;
    }
}

function backToLogin($msg = "not logged in")
{
    echo $msg;
    echo '<a href="index.php">Back To Login</a>';
}

function jsUserId()
{
    echo "<script>";
    echo "let loggedInUserLogin = " . json_encode($_SESSION['login']) . ";";
    echo "let loggedInUserId = " . json_encode($_SESSION['user_id']) . ";";
    if (isset($_SESSION['overrider_id'])) {
        echo "let overridingUserId = " . json_encode($_SESSION['overrider_id']) . ";";
    } else {
        echo "let overridingUserId";
    }

    echo "</script>";
}

function jsMessage($name, $message)
{
    echo "<script>";
    echo "let $name = " . json_encode($message) . ";";
    echo "</script>";
}

?>
