<?php 

include_once 'database.php';

function isLoggedIn()
{
    if (isset($_COOKIE['loginToken'])) {
        if (isset($_SESSION['user_id'])) {

            $userId = $_SESSION['user_id'];
            $loginToken = $_COOKIE['loginToken'];

            if (isValidLoginToken($userId, $loginToken))
                return true;
        }
    }

    return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true);
}

function ensureLoggedIn()
{
    if (isset($_SESSION['user_id'])) {
        if (!getUserActive($_SESSION['user_id'])) {
            removeContextualLoginToken();
            session_destroy();
            setcookie("loginToken", "", time() - 3600, "/", "", true, true);
        }
    }

    if (!isLoggedIn()) {
        backToLogin();
        exit;
    }
}

function backToLogin($msg = "Not logged in.")
{
    echo '<html><head>';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '</head>';

    echo '<body>';
    echo $msg;
    echo '<br/>';
    echo '<a href="index.php">Back To Login</a>';
    echo '</body></html>';
}

function jsUserId()
{
    echo "<script>";
    echo "let loggedInUserLogin = " . json_encode($_SESSION['login']) . ";";
    echo "let loggedInUserId = " . json_encode(intval($_SESSION['user_id'])) . ";";
    if (isset($_SESSION['overrider_id'])) {
        echo "let overridingUserId = " . json_encode(intval($_SESSION['overrider_id'])) . ";";
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
