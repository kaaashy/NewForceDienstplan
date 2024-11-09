<?php

session_start();

include 'initdb.php';
include 'pages.php';

$token = htmlspecialchars($_GET["token"]);

// get the initialization details of the user from the password token
list($username, $email) = fetchInitializationToken($token);
if (!isset($email) || !isset($username)) {
    $errorMessage = "Registrierung nicht mehr verfügbar. Bitte wenden Sie sich die Person, die Sie für den Dienstplan registrierte.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['init_user'])) {
        $display_name = $_POST['display_name'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];

        $new_password = $_POST['password'];
        $new_password_repeat = $_POST['password_repeat'];

        // $password = $_POST['password'];
        // $email = $_POST['email'];
        // $username = $_SESSION['username'];
        $updateData = true;

        if ($new_password != "" && $new_password != $new_password_repeat) {
            $errorMessage = "Passwörter stimmen nicht überein!";
            $updateData = false;
        }

        if ($updateData) {
            updateUserPassword($username, $new_password);
            updateUserProfileData($username, $display_name, $first_name, $last_name, $email);
            removeInitializationToken($username, $token);

            header("Location: userprofile.php");
            die();
        }

    } elseif (isset($_POST['mail_user'])) {
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
            <h1>Registrierung Abschließen</h1>

            <?php
            echo "Login: $username <br/>";
            echo "Email: $email <br/><br/>";
            ?>


            <form method="POST" action="">
                <div>
                    <label for="display_name">Anzeigename:</label>
                    <input type="text" id="display_name" name="display_name" required>
                </div>
                <div>
                    <label for="first_name">Vorname (optional):</label>
                    <input type="text" id="first_name" name="first_name">
                </div>
                <div>
                    <label for="last_name">Nachname (optional):</label>
                    <input type="text" id="last_name" name="last_name">
                </div>
                <div>
                    <label for="password">Passwort:</label>
                    <input type="password" id="password" name="password" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}" title="Must contain at least one lowercase letter, one uppercase letter, and one number. Minimum length: 8 characters." required>
                </div>
                <div>
                    <label for="password_repeat">Wiederholen:</label>
                    <input type="password" id="password_repeat" name="password_repeat" required>
                </div>
                <?php
                if (isset($errorMessage)) {
                    echo '<div class="error-box">';
                    echo "<p>$errorMessage</p>";
                    echo '</div>';
                }
                ?>

                <!-- Form 2 fields -->
                <input type="submit" name="init_user" value="Abschließen">
            </form>
        </div>
    </div>
</body>
</html>


