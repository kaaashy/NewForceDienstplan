<?php

session_start();

include_once 'initdb.php';
include_once 'database.php';
include_once 'pages.php';
include_once 'requests.php';

ensureLoggedIn();

function handleUserCreation()
{
    $login = $_POST['login'];
    $email = $_POST['email'];

    $password = base64_encode(random_bytes(12));
    $password = str_replace(['+', '/', '='], ['-', '_', ''], $password);

    list($existingId, $existingLogin) = fetchUserCredentialsByEmail($email);
    if ($existingId) {
        if ($existingLogin != $login) {
            return array(false, "Email-Adresse '$email' wird bereits von login '$existingLogin' genutzt.");
        }
    }

    list($token, $userId) = initializeUser($login, $email, $password);

    $mail = makePHPMail();

    try {
        $link = "dienstplan.newforce.de/finish-registration.php?token=$token";
        $link = "$link localhost/nf-dienstplan/finish-registration.php?token=$token";

        // Email headers and content
        $mail->addAddress($email, '');
        $mail->Subject = 'New Force Dienstplan Account-Registrierung';
        $mail->Body = "Hi $login,\r\n\r\ndu wurdest zum Dienstplan des New Force Erlangen hinzugefügt. Um deine Registrierung abzuschließen, folge bitte diesem Link: $link\r\n\r\nViele Grüße\r\nDein NewForce Team";

        // Send email
        $mail->send();

        return array("Registrierungs-Email wurde verschickt an '$email'", false);
    } catch (Exception $e) {
        return array(false, "Registrierungs-Email konnte nicht verschickt werden. Mailer Error: {$mail->ErrorInfo}");;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reinit_everything'])) {
        initialize();
        session_destroy();
        header('Location: index.php');
        die();
    } elseif (isset($_POST['createuser'])) {
        list($userCreatedInfo, $userCreatedError) = handleUserCreation();

    } elseif (isset($_POST['deleteuser'])) {
        $login = $_POST['login'];

        deleteUser($login);
    } elseif (isset($_POST['login_as'])) {
        $userId = $_POST['user_id'];

        logInAsUser($userId);
    } elseif (isset($_POST['update_db'])) {

        updateDB($login);
    } elseif (isset($_POST['send_testmail'])) {

        $mail = makePHPMail();

        try {
            // Email headers and content
            $mail->addAddress('konstantin.kronfeldner@gmail.com', 'Recipient Name');
            $mail->Subject = 'Test Email via New Force STMP';
            $mail->Body = 'This is a test email sent through New Force SMTP using PHPMailer.';

            // Send email
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin-Bereich</title>
    <meta charset="utf-8">
    <title>Dienstplan NewForce</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php jsUserId(); ?>
    <script src="utility.js" ></script>
</head>
<body>
    <div class="narrowwrapper">
        <div class="info_page">
            <script > document.write(buildNavHtml()); </script>

            <h2>Mitarbeitende Anlegen</h2>
            <p> Für existierende Logins: Setzt Passwort zurück und Email neu und schickt eine Mail, mit der das Passwort neu gesetzt werden kann. </p>
            <form method="POST" action="">
                <div>
                    <label for="login">Login:</label>
                    <input type="text" id="login" name="login" required> </input>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" required> </input>
                </div>

                <!-- Form 2 fields -->
                <input type="submit" name="createuser" value="Anlegen"> </input>
            </form>

            <?php
            if (isset($userCreatedInfo) && $userCreatedInfo) {
                echo '<div class="info-box">';
                echo "<p>$userCreatedInfo</p>";
                echo '</div>';
            }

            if (isset($userCreatedError) && $userCreatedError) {
                echo '<div class="error-box">';
                echo "<p>$userCreatedError</p>";
                echo '</div>';
            }
            ?>

            <h2>Mitarbeitende Löschen</h2>
            <p> Kann nicht rückgängig gemacht werden. Löscht Mitarbeitende sofort, restlos und ohne Nachfrage. Mitarbeitende werden aus allen Veranstaltungen entfernt. </p>
            <form method="POST" action="">
                <div>
                    <label for="login">Login:</label>
                    <input type="text" id="login" name="login" required></input>
                </div>

                <!-- Form 2 fields -->
                <input type="submit" name="deleteuser" value="Löschen"></input>
            </form>

            <h2>Als andere Mitarbeitende einloggen</h2>
            <form method="POST" action="">
                <div>
                    <label for="user_id">User ID:</label>
                    <input type="text" id="user_id" name="user_id" required></input>
                </div>

                <input type="submit" name="login_as" value="Als User Einloggen"></input>
            </form>

            <h1>Dev Maintenance</h1>
            <h2>Testmail</h2>
            <form method="POST" action="">
                <!-- Form 1 fields -->
                <input type="submit" name="send_testmail" value="Test-Email verschicken"></input>
            </form>

            <h2>Update DB</h2>
            <form method="POST" action="">
                <!-- Form 1 fields -->
                <input type="submit" name="update_db" value="Update Database Schema"></input>
            </form>

            <h2>Auf Werkseinstellungen Zurücksetzen</h2>
            <form method="POST" action="">
                <!-- Form 1 fields -->
                <input type="submit" name="reinit_everything" value="Auf Werkseinstellungen zurücksetzen"></input>
            </form>

        </div>
    </div>

</body>
</html>


