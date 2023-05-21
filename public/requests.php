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


//echo '
//{
//    "5/7/2023" : {
//        "type" : "Veranstaltung",
//        "title" : "Großputz",
//        "time" : "16:00",
//        "venue" : "NewForce",
//        "location" : "Buckenhofer Weg 69",
//        "desc" : "Wir putzen ihr Spasten",
//        "more" : "Mehr Info gibts net"
//    },
//    "5/6/2023" : {
//        "type" : "Veranstaltung",
//        "title" : "Blasts In Brucklyn",
//        "time" : "20:00",
//        "venue" : "NewForce",
//        "location" : "Buckenhofer Weg 69",
//        "desc" : "Blasts In Brucklyn",
//        "more" : "Fette Blasts"
//    },
//    "5/5/2023" : {
//        "type" : "Veranstaltung",
//        "title" : "Masters Of Metal",
//        "time" : "20:00",
//        "venue" : "NewForce",
//        "location" : "Buckenhofer Weg 69",
//        "desc" : "Heavy, Pagan, Power",
//        "more" : "Fette Blasts"
//    }
//}';


?>
