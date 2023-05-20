<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include 'initdb.php';

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)){
    echo 'not logged in';
    exit;
}

$connection = connect(); 
$connection->close();

echo '
{
    "5/7/2023" : {
        type : "Veranstaltung",
        title : "Großputz",
        time : "16:00",
        venue : "NewForce",
        location : "Buckenhofer Weg 69",
        desc : "Wir putzen ihr Spasten",
        more : "Mehr Info gibts net"
    },
    "5/6/2023" : {
        type : "Veranstaltung",
        title : "Blasts In Brucklyn",
        time : "20:00",
        venue : "NewForce",
        location : "Buckenhofer Weg 69",
        desc : "Blasts In Brucklyn",
        more : "Fette Blasts"
    },
    "5/5/2023" : {
        type : "Veranstaltung",
        title : "Masters Of Metal",
        time : "20:00",
        venue : "NewForce",
        location : "Buckenhofer Weg 69",
        desc : "Heavy, Pagan, Power",
        more : "Fette Blasts"
    }
}';


?>
