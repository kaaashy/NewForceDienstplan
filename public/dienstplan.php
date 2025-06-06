
<?php

session_start(); 

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include_once 'pages.php';
include_once 'initdb.php';

ensureLoggedIn(); 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['newevent'])) {
        
        $callingUser = $_SESSION['user_id'];
        $managingPermitted = getUserHasPermission($callingUser, 'manage_events');
        if (!$managingPermitted) {
            echo 'ERROR_NO_PERMISSION_FOR_EVENT_MANAGING';
            return;
        }

        $type = filter_input(INPUT_POST, 'type');
        $date = filter_input(INPUT_POST, 'date');
        $title = filter_input(INPUT_POST, 'title');
        $description = filter_input(INPUT_POST, 'description');
        $time = filter_input(INPUT_POST, 'time');
        $end_time = filter_input(INPUT_POST, 'end_time');
        $minUsers = filter_input(INPUT_POST, 'minimum_users');
        $organizer = filter_input(INPUT_POST, 'organizer');
        $venue = filter_input(INPUT_POST, 'venue');
        $address = filter_input(INPUT_POST, 'address');
        $id = filter_input(INPUT_POST, 'id');
        
        $minUsers = filter_var($minUsers, FILTER_SANITIZE_NUMBER_INT);
        if (!filter_var($minUsers, FILTER_VALIDATE_INT))
            $minUsers = 0;
        
        if (!$id && $date)
            $id = addEvent($type, $title, $date);
        
        updateEvent($id, $type, $title, $description, $time, $end_time, $minUsers, $organizer, $venue, $address);
        
        header('Location: dienstplan.php');
        die();
    } else if (isset($_POST['deleteevent'])) {

        $callingUser = $_SESSION['user_id'];
        $managingPermitted = getUserHasPermission($callingUser, 'manage_events');
        if (!$managingPermitted) {
            echo 'ERROR_NO_PERMISSION_FOR_EVENT_MANAGING';
            return;
        }

        $id = filter_input(INPUT_POST, 'id');
        deleteEvent($id);
        
        header('Location: dienstplan.php');
        die();
    } elseif (isset($_POST['logout'])) {
        removeContextualLoginToken();
        session_destroy();
        setcookie("loginToken", "", time() - 3600, "/", "", true, true);
        header('Location: index.php');
        die();
    }
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Dienstplan NewForce</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php jsUserId(); ?>
    <script src="utility.js" defer></script>
    <script src="requests.js" defer></script>
    <script src="mainschedule.js" defer></script>
  </head>
  <body>
      
    <div class="widewrapper">
        <div id="calendar"></div>
        <div id="calendar_data"></div>
        <div id="schedule_summary"></div>
        <div id="legend"></div>
    </div>
      
  </body>
</html>
