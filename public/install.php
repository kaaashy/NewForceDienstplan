<?php

session_start();

include_once 'initdb.php';
include_once 'database.php';
include_once 'pages.php';
include_once 'requests.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['reinit_everything'])) {

        initialize();
        createExampleDB();
        session_destroy();
        header('Location: index.php');
        die();
    } elseif (isset($_POST['update_db'])) {

        updateDB();

    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dienstplan-Installer</title>
    <meta charset="utf-8">
    <title>Dienstplan NewForce</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="utility.js" defer></script>
    <script src="requests.js" defer></script>
    <script src="install.js" defer></script>
</head>
<body>
    <div class="narrowwrapper">
        <div id="mainpage" class="info_page"> </div>
    </div>

</body>
</html>


