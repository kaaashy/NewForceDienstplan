<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include 'initdb.php';

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)){
    echo 'not logged in';
    exit;
}

if (isset($_POST['month']) && isset($_POST['year'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
    
    getEvents($month, $year);
}

?>
