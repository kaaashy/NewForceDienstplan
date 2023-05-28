
<?php

session_start(); 

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include 'pages.php';
include 'initdb.php';

ensureLoggedIn(); 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['newevent'])) {
        
        $type = "Veranstaltung";
        $details = "";
        
        $date = filter_input(INPUT_POST, 'date');
        $title = filter_input(INPUT_POST, 'title');
        $description = filter_input(INPUT_POST, 'description');
        $time = filter_input(INPUT_POST, 'time');
        $end_time = filter_input(INPUT_POST, 'end_time');
        $venue = filter_input(INPUT_POST, 'venue');
        $address = filter_input(INPUT_POST, 'address');
        $id = filter_input(INPUT_POST, 'id');
               
        if (!$id && $date)
            $id = addEvent($type, $title, $date);
        
        updateEvent($id, $type, $title, $description, $time, $end_time, $venue, $address);
        
        header('Location: dienstplan.php');
        
    } else if (isset($_POST['deleteevent'])) {
        $id = filter_input(INPUT_POST, 'id');
        deleteEvent($id);
        
        header('Location: dienstplan.php');
    }
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Dienstplan NewForce</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php jsUserId(); ?>
    <script src="requests.js" defer></script>
    <script src="script.js" defer></script>
  </head>
  <body>
    <a href="admin.php">Admin</a>
    <a href="users.php">Mitarbeitenden-Index</a>
      
    <div class="wrapper">
        <div id="calendar"></div>
        <div id="calendar_data"></div>
    </div>
      
  </body>
</html>
