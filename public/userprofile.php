
<?php

session_start(); 

include_once 'initdb.php';
include_once 'pages.php';

ensureLoggedIn(); 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_userprofile'])) {
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
            
            updateUserProfileData($login, $display_name, $first_name, $last_name, $email);
        }
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


