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

function handleUserDeletion()
{
    $login = trim($_POST['login']);

    $callingUser = $_SESSION['user_id'];
    $operationPermitted = getUserHasPermission($callingUser, 'delete_users');
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

</head>
<body>
    <div class="narrowwrapper">
        <div id="user_index" class="info_page"> </div>
    </div>
</body>
</html>


