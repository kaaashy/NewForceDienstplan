<?php

session_start();

include_once 'initdb.php';
include_once 'database.php';
include_once 'pages.php';
include_once 'requests.php';

ensureLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $callingUser = $_SESSION['user_id'];
    $operationPermitted = getUserHasPermission($callingUser, 'admin_dev_maintenance');
    if (!$operationPermitted) {
        echo "Keine Berechtigung.";
    }

    if (isset($_POST['reinit_everything'])) {

        initialize();
        session_destroy();
        header('Location: index.php');
        die();
    } elseif (isset($_POST['update_db'])) {

        updateDB();

    } elseif (isset($_POST['send_testmail'])) {

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
            echo "Test-Message has been sent to '$address'";
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


