<?php

session_start();

include_once 'initdb.php';
include_once 'database.php';
include_once 'pages.php';
include_once 'requests.php';

function handleRequest()
{
    if (!isset($_POST['password'])) {
        return array(false, "No password");
    }

    $installInfo = getInstallInfo();

    if ($_POST['password'] != $installInfo->installPassword) {
        return array(false, "Wrong password");
    }

    if (isset($_POST['reinit_clean'])) {
        initialize();
        return array("Datenbanken wurden auf Werkseinstellungen zurückgesetzt.", false);
    }

    if (isset($_POST['reinit_with_examples'])) {
        initialize();
        createExampleDB();
        return array("Datenbanken wurden auf Werkseinstellungen zurückgesetzt.", false);
    }

    if (isset($_POST['update_db'])) {
        updateDB();
        return array("Datenbanken wurden auf die neueste Version updated.", false);
    }

    return array(false, false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    list($infoMessage, $errorMessage) = handleRequest();

    if ($infoMessage) jsMessage("infoMessage", $infoMessage);
    if ($errorMessage) jsMessage("errorMessage", $errorMessage);
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


