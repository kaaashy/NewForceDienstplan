<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include 'initdb.php';

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)){
    echo 'not logged in';
    exit;
}

if (isset($_POST['startDate']) && isset($_POST['endDate'])) {
    $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    getEvents($startDate, $endDate);
}

if (isset($_POST['users'])) {
    getUsers();
}

if (isset($_POST['outline_schedule'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $day = filter_input(INPUT_POST, 'day', FILTER_SANITIZE_NUMBER_INT);
    $active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT) > 0;
    
    updateOutlineDay($userId, $day, $active);
}

?>
