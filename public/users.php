<?php

session_start(); 

include 'initdb.php';
include 'pages.php';

ensureLoggedIn(); 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mitarbeitenden-Index</title>
    <?php jsUserId(); ?>
    <script src="requests.js" defer></script>
    <script src="users.js" defer></script>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="wrapper">
        <div id="user_index"> </div>
    </div>
</body>
</html>


