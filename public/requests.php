<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include 'initdb.php';

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)){
    echo 'not logged in';
    exit;
}

function getEvents($month, $year) {    
    $pdo = connect();

    // Retrieve the salt from the database
    $sql = "SELECT *
            FROM Events
            WHERE MONTH(date) = :month
            AND YEAR(date) = :year;";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':month', $month);
    $stmt->bindValue(':year', $year);
    $stmt->execute();
    
    $rows = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
        $rows[] = $row;
    }
    
    echo json_encode($rows);
}

if (isset($_POST['month']) && isset($_POST['year'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
    
    getEvents($month, $year);
}

?>
