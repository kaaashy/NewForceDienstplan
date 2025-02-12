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
    <script src="statistics.js" defer></script>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
    <div class="narrowwrapper">
        <div id="user_index" class="info_page"> </div>
    </div>
</body>
</html>


