<?php 

function ensureLoggedIn()
{
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
    echo "var userId = " . json_encode($_SESSION['username']) . ";";
    echo "</script>";
}

?>
