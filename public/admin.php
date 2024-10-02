<?php

session_start(); 

include 'initdb.php';
include 'pages.php';

ensureLoggedIn(); 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reinit_everything'])) {
        initialize();
        session_destroy();
        header('Location: index.php');
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header('Location: index.php');
    } elseif (isset($_POST['createuser'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        addUser($username, $password);
    } elseif (isset($_POST['deleteuser'])) {
        $username = $_POST['username'];
        
        deleteUser($username);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="utf-8">
    <title>Dienstplan NewForce</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="narrowwrapper">
        <div class="info_page">
        
            <h1>Dev Maintenance</h1>

            <h2>Mitarbeitende Anlegen</h2>
            <form method="POST" action="">
                <div>
                    <label for="username">Name:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Passwort:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <!-- Form 2 fields -->
                <input type="submit" name="createuser" value="Anlegen">
            </form>

            <h2>Mitarbeitende Löschen</h2>
            <form method="POST" action="">
                <div>
                    <label for="username">Name:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <!-- Form 2 fields -->
                <input type="submit" name="deleteuser" value="Löschen">
            </form>


            <h2>Logout</h2>
            <form method="POST" action="">
                <!-- Form 2 fields -->
                <input type="submit" name="logout" value="Ausloggen">
            </form>

            <h2>Auf Werkseinstellungen Zurücksetzen</h2>
            <form method="POST" action="">
                <!-- Form 1 fields -->
                <input type="submit" name="reinit_everything" value="Auf Werkseinstellungen zurücksetzen">
            </form>
        </div>
    </div>
</body>
</html>


