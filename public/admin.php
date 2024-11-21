<?php

session_start();

include_once 'initdb.php';
include_once 'database.php';
include_once 'pages.php';
include_once 'requests.php';

ensureLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reinit_everything'])) {
        initialize();
        session_destroy();
        header('Location: index.php');
        die();
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


