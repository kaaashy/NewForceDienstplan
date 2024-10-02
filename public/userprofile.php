
<?php

session_start(); 

include 'initdb.php';
include 'pages.php';

ensureLoggedIn(); 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Mein Profil</title>
    <?php jsUserId(); ?>
    <script src="utility.js" defer></script>
    <script src="requests.js" defer></script>
    <script src="userprofile.js" defer></script>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <div class="narrowwrapper">
        <div id="user_index"> </div>
    </div>
</body>
</html>


