
<?php

session_start(); 

include_once 'initdb.php';
include_once 'pages.php';

ensureLoggedIn(); 

function handleUserProfileUpdate()
{
    $display_name = trim($_POST['display_name']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);

    $new_password = $_POST['new_password'];
    $new_password_repeat = $_POST['new_password_repeat'];

    $password = $_POST['password'];

    $login = $_SESSION['login'];
    $updateData = true;

    list($existingId, $existingLogin) = fetchUserCredentialsByEmail($email);
    if ($existingId) {
        if ($existingLogin != $login) {
            jsMessage("phpError", "Email-Adresse '$email' wird bereits von einer anderen Person genutzt!");
            $updateData = false;
        }
    }

    list($loginCorrect, $userId) = checkLogin($login, $password);

    if (!$loginCorrect) {
        jsMessage("phpError", "Falsches Passwort!");
        $updateData = false;
    }

    if ($new_password != "" && $new_password != $new_password_repeat) {
        jsMessage("phpError", "Neue Passwörter stimmen nicht überein!");
        $updateData = false;
    }

    if ($updateData) {
        if ($new_password != "") {
            updateUserPassword($login, $new_password);
        }

        $details = getUserDetails($userId);
        $currentEmail = $details["email"];

        updateUserProfileData($login, $display_name, $first_name, $last_name, $currentEmail);

        if ($currentEmail != $email) {
            removeEmailToken($userId, "");
            $token = createEmailToken($userId, $email);

            $mail = makePHPMail();

            try {
                $link = "dienstplan.newforce.de/userprofile.php?token=$token";
                $link = "$link localhost/nf-dienstplan/userprofile.php?token=$token";

                // Email headers and content
                $mail->addAddress($email, '');
                $mail->Subject = 'New Force Dienstplan Email Bestätigen';
                $mail->Body = "Hi $login,\r\n\r\njemand (wahrscheinlich du) hat veranlasst, deine Email-Adresse zu ändern. Um die Änderung zu bestätigen, folge bitte diesem Link: $link\r\n\r\nBitte beachte: Dieser Link verfällt in 7 Tagen.\r\n\r\nViele Grüße\r\nDein NewForce Team";

                // Send email
                if (!$mail->send()) {
                    return array(false, "Bestätigungsemail konnte nicht verschickt werden. Mailer Error: {$mail->ErrorInfo}");
                }

                return array("Eine Email zur Bestätigung der neuen Adresse wurde verschickt an '$email'", false);
            } catch (Exception $e) {
                return array(false, "Bestätigungsemail konnte nicht verschickt werden. Mailer Error: {$mail->ErrorInfo}");
            }
        }
    }

}

if (isset($_GET["token"])) {
    $token = htmlspecialchars($_GET["token"]);

    if ($token != "") {
        // get the initialization details of the user from the password token
        $confirmationSuccesful = confirmEmailToken($token);
        if ($confirmationSuccesful) {
            jsMessage("userUpdateInfo", "Email-Adresse bestätigt.");
        }
        else {
            jsMessage("userUpdateError", "Änderung nicht mehr verfügbar. Bitte ändere die Email-Adresse erneut.");
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_userprofile'])) {
        list($userUpdateInfo, $userUpdateError) = handleUserProfileUpdate();

        if ($userUpdateInfo) jsMessage("userUpdateInfo", $userUpdateInfo);
        if ($userUpdateError) jsMessage("userUpdateError", $userUpdateError);

    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header('Location: index.php');
        die();
    }
}


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
        <div id="user_index" class="info_page"> </div>
    </div>
</body>
</html>


