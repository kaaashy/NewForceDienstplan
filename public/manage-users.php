<?php

session_start();

include_once 'initdb.php';
include_once 'pages.php';
include_once 'database.php';
include_once 'requests.php';

ensureLoggedIn();

function handleUserCreation()
{
    $login = trim($_POST['login']);
    $email = trim($_POST['email']);

    $loginExistsAlready = false;

    list($existingId, $existingLogin) = fetchUserCredentialsByEmail($email);
    if ($existingId) {
        if ($existingLogin != $login) {
            return array(false, "Email-Adresse '$email' wird bereits von login '$existingLogin' genutzt.");
        }
    } else {
        $existingId = getUserId($login);

        if ($existingId)
        {
            $details = getUserDetails($existingId);

            $loginExistsAlready = true;
            $existingLogin = $details['login'];
        }
    }

    // permissions check
    $callingUser = $_SESSION['user_id'];

    if ($loginExistsAlready) {
        // if the login exists, it counts as user management
        // however, inviters should be able to change their invited users' email
        $operationPermitted = getUserHasPermission($callingUser, 'manage_users');
        if (!$operationPermitted) {
            return array(false, "Keine Berechtigung.");
        }
    } else {
        $operationPermitted = getUserHasPermission($callingUser, 'manage_users');
        if (!$operationPermitted) {
            return array(false, "Keine Berechtigung.");
        }
    }

    $password = base64_encode(random_bytes(12));
    $password = str_replace(['+', '/', '='], ['-', '_', ''], $password);

    list($token, $userId) = initializeUser($login, $email, $password);

    $mail = makePHPMail();

    try {
        $installInfo = getInstallInfo();
        $link = $installInfo->emailUrl . "/finish-registration.php?token=$token";

        // Email headers and content
        $mail->addAddress($email, '');
        $mail->Subject = 'New Force Dienstplan Account-Registrierung';
        $mail->Body = "Hi $login,\r\n\r\ndu wurdest zum Dienstplan des New Force Erlangen hinzugefügt. Um deine Registrierung abzuschließen, folge bitte diesem Link: $link\r\n\r\nBitte beachte: Dieser Link verfällt in 7 Tagen.\r\n\r\nViele Grüße\r\nDein NewForce Team";

        // Send email
        if (!$mail->send()) {
            return array(false, "Registrierungs-Email konnte nicht verschickt werden. Mailer Error: {$mail->ErrorInfo}");
        }

        $msg = "";

        if ($loginExistsAlready) {
            $msg = "Email von '$existingLogin' geändert. ";
        }

        $msg .= "Registrierungs-Email wurde verschickt an '$email'";

        return array($msg, false);
    } catch (Exception $e) {
        return array(false, "Registrierungs-Email konnte nicht verschickt werden. Mailer Error: {$mail->ErrorInfo}");;
    }
}

function handleUserDeletion()
{
    $login = trim($_POST['login']);

    $callingUser = $_SESSION['user_id'];
    $operationPermitted = getUserHasPermission($callingUser, 'manage_users');
    if (!$operationPermitted) {
        return array(false, "Keine Berechtigung.");
    }

    if ($_SESSION['login'] == $login) {
        return array(false, "Selbst-Löschung nicht möglich.");
    }

    $error = deleteUser($login);

    if ($error)
        return array(false, $error);

    return array("Benutzer '$login' gelöscht.", false);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['createuser'])) {
        list($userCreatedInfo, $userCreatedError) = handleUserCreation();

        if ($userCreatedInfo) jsMessage("userCreatedInfo", $userCreatedInfo);
        if ($userCreatedError) jsMessage("userCreatedError", $userCreatedError);
    } elseif (isset($_POST['deleteuser'])) {
        list($userDeletedInfo, $userDeletedError) = handleUserDeletion();

        if ($userDeletedInfo) jsMessage("userDeletedInfo", $userDeletedInfo);
        if ($userDeletedError) jsMessage("userDeletedError", $userDeletedError);
    } elseif (isset($_POST['login_as'])) {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

        logInAsUser($userId);
    }

}



?>

<!DOCTYPE html>
<html>
<head>
    <title>Mitarbeitenden-Index</title>
    <?php jsUserId(); ?>
    <script src="utility.js" defer></script>
    <script src="requests.js" defer></script>
    <script src="manage-users.js" defer></script>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
    <div class="narrowwrapper">
        <div id="user_index" class="info_page"> </div>
    </div>
</body>
</html>


