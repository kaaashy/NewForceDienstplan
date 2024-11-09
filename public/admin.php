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
        die();
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header('Location: index.php');
        die();
    } elseif (isset($_POST['createuser'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];

        $password = base64_encode(random_bytes(12));
        $password = str_replace(['+', '/', '='], ['-', '_', ''], $password);

        list($token, $userId) = initializeUser($username, $email, $password);

        $mail = makePHPMail();

        try {

            $link = "dienstplan.newforce.de/finish-registration.php?token=$token";
            $link = "$link localhost/nf-dienstplan/finish-registration.php?token=$token";

            // Email headers and content
            $mail->addAddress($email, '');
            $mail->Subject = 'New Force Dienstplan Account-Registrierung';
            $mail->Body = "Hi $username,\r\n\r\ndu wurdest zum Dienstplan des New Force Erlangen hinzugefügt. Um deine Registrierung abzuschließen, folge bitte diesem Link: $link\r\n\r\nViele Grüße\r\nDein NewForce Team";

            // Send email
            $mail->send();
            $userCreatedInfo = "Registration Email has been sent to '$email'";
        } catch (Exception $e) {
            $userCreatedError = "Registration email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } elseif (isset($_POST['deleteuser'])) {
        $username = $_POST['username'];

        deleteUser($username);
    } elseif (isset($_POST['send_testmail'])) {

        $mail = makePHPMail();

        try {
            // Email headers and content
            $mail->addAddress('konstantin.kronfeldner@gmail.com', 'Recipient Name');
            $mail->Subject = 'Test Email via Google SMTP';
            $mail->Body = 'This is a test email sent through Google SMTP using PHPMailer.';

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
    <title>Login</title>
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
            <h1>Dev Maintenance</h1>

            <h2>Mitarbeitende Anlegen</h2>
            <p> Für existierende Mitarbeitende: Setzt Passwort zurück und schickt eine Mail, mit der das Passwort neu gesetzt werden kann. </p>
            <form method="POST" action="">
                <div>
                    <label for="username">Name:</label>
                    <input type="text" id="username" name="username" required> </input>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" required> </input>
                </div>

                <!-- Form 2 fields -->
                <input type="submit" name="createuser" value="Anlegen"> </input>
            </form>

            <?php
            if (isset($userCreatedInfo)) {
                echo '<div class="info-box">';
                echo "<p>$userCreatedInfo</p>";
                echo '</div>';
            }

            if (isset($userCreatedError)) {
                echo '<div class="error-box">';
                echo "<p>$userCreatedError</p>";
                echo '</div>';
            }
            ?>

            <h2>Mitarbeitende Löschen</h2>
            <form method="POST" action="">
                <div>
                    <label for="username">Name:</label>
                    <input type="text" id="username" name="username" required></input>
                </div>

                <!-- Form 2 fields -->
                <input type="submit" name="deleteuser" value="Löschen"></input>
            </form>


            <h2>Logout</h2>
            <form method="POST" action="">
                <!-- Form 2 fields -->
                <input type="submit" name="logout" value="Ausloggen"></input>
            </form>

            <h2>Testmail</h2>
            <form method="POST" action="">
                <!-- Form 1 fields -->
                <input type="submit" name="send_testmail" value="Test-Email verschicken"></input>
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


