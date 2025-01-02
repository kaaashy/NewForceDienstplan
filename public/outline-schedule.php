<?php

session_start(); 

include_once 'initdb.php';
include_once 'pages.php';

ensureLoggedIn(); 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mitarbeitenden-Index</title>
    <?php jsUserId(); ?>
    <script src="utility.js" defer></script>
    <script src="requests.js" defer></script>
    <script src="outline-schedule.js" defer></script>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="narrowwrapper">
        <div id="outline_schedule" class="info_page"> </div>
    </div>
</body>
</html>


