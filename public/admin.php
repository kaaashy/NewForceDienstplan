<?php

session_start();

include_once 'initdb.php';
include_once 'database.php';
include_once 'pages.php';
include_once 'requests.php';

ensureLoggedIn();

function handleRequest()
{
    $callingUser = $_SESSION['user_id'];
    $operationPermitted = getUserHasPermission($callingUser, 'admin_dev_maintenance');
    if (!$operationPermitted) {
        return array(false, "Keine Berechtigung.");
    }

    if (isset($_POST['send_testmail'])) {

        $details = getUserDetails(1);
        $address = $details['email'];

        $mail = makePHPMail();

        try {
            // Email headers and content
            $mail->addAddress($address, 'Recipient Name');
            $mail->Subject = 'Test Email via New Force STMP';
            $mail->Body = 'This is a test email sent through New Force SMTP using PHPMailer.';

            // Send email
            $mail->send();
            return array("Test-Message has been sent to '$address'", false);
        } catch (Exception $e) {
            return array(false, "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
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
    <title>Admin-Bereich</title>
    <meta charset="utf-8">
    <title>Dienstplan NewForce</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php jsUserId(); ?>
    <script src="utility.js" defer></script>
    <script src="requests.js" defer></script>
    <script src="admin.js" defer></script>
</head>
<body>
    <div class="narrowwrapper">
        <div id="mainpage" class="info_page"> </div>
    </div>

</body>
</html>


