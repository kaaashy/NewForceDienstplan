
<?php

session_start(); 

include 'pages.php';

ensureLoggedIn(); 

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Dienstplan NewForce</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php jsUserId(); ?>
    <script src="script.js" defer></script>
  </head>
  <body>
    <a href="admin.php">Admin</a>
      
    <div class="wrapper">
        <div id="calendar"></div>
        <div id="calendar_data"></div>
    </div>
      
  </body>
</html>
