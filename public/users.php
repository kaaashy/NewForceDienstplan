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
    <script src="requests.js" defer></script>
    <script src="users.js" defer></script>
    <link rel="stylesheet" href="style.css">
    <?php jsUserId(); ?>

</head>
<body>
    <div class="wrapper">
        <div id="user_index"> </div>
    </div>
</body>
</html>


