<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include_once 'database.php';

function logInAsUser($userId)
{
    if (isset($_SESSION['overrider_id'])) {
        echo 'ERR_ALREADY_OVERRIDING';
        return;
    }

    $details = getUserDetails($userId);
    if ($details) {
        if ($_SESSION['user_id'] == $details['id']) {
            echo 'ERR_SAME_USER';
            return;
        }

        $_SESSION['overrider_id'] = $_SESSION['user_id'];
        $_SESSION['loggedin'] = true;
        $_SESSION['login'] = $details['login'];
        $_SESSION['user_id'] = $details['id'];
    }
}

function logOutFromUser()
{
    if (!isset($_SESSION['overrider_id'])) {
        echo 'ERR_NOT_OVERRIDING';
        return;
    }

    $overriderId = $_SESSION['overrider_id'];
    $details = getUserDetails($overriderId);
    if ($details) {
        unset($_SESSION['overrider_id']);

        $_SESSION['loggedin'] = true;
        $_SESSION['login'] = $details['login'];
        $_SESSION['user_id'] = $details['id'];
    }
}


?>
