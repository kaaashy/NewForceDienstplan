<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

session_start();

include_once 'initdb.php';

function validateReset()
{
    // Validate the login credentials
    $email = trim($_POST['email']);

    list($userId, $login) = fetchUserCredentialsByEmail($email);
    if ($userId) {
        if (getUserActive($userId)) {

            $token = addInitializationToken($userId);

            $mail = makePHPMail();

            try {

                $link = "dienstplan.newforce.de/finish-registration.php?token=$token";
                $link = "$link localhost/nf-dienstplan/finish-registration.php?token=$token";

                // Email headers and content
                $mail->addAddress($email, '');
                $mail->Subject = 'New Force Dienstplan Account-Management';
                $mail->Body = "Hi $login,\r\n\r\njemand hat veranlasst dein Passwort zurückzusetzen. Um das zu tun, folge bitte diesem Link: $link\r\n\r\nWenn das nicht du warst, kontaktiere bitte deinen Administrator.\r\n\r\nViele Grüße\r\nDein NewForce Team";

                // Send email
                $mail->send();
            } catch (Exception $e) {
            }
        }
    }

}

// Check if the form is submitted
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    validateReset();

    $response = "Wenn es einen Account mit dieser Email-Adresse gibt, wurde eine Mail dorthin versandt, mit der du dein Passwort zurücksetzen kannst. Checke bitte auch dein Spam-Folder, falls du die Mail nicht erhalten hast.";
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
    <h2>Passwort Vergessen</h2>

    <p> Gib die Email an, die mit deinem Account registriert ist, um dein Passwort zurückzusetzen.</p>
    <form method="POST" action="">
        <div>
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>
        </div>
        <div>
            <input type="submit" value="Passwort zurücksetzen">
        </div>
    </form>

    <?php
    if (isset($response)) {
        echo '<div class="info-box">';
        echo "<p>$response</p>";
        echo '</div>';
    }
    ?>

    <div>
        <a href="index.php">Zurück zum Login</a>
    </div>

</body>
</html>


