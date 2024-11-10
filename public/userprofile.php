
<?php

session_start(); 

include_once 'initdb.php';
include_once 'pages.php';

ensureLoggedIn(); 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_userprofile'])) {
        $display_name = $_POST['display_name'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        
        $new_password = $_POST['new_password'];
        $new_password_repeat = $_POST['new_password_repeat'];
        
        $password = $_POST['password'];
        
        $username = $_SESSION['username']; 
        $updateData = true;
        
        list($loginCorrect, $userId) = checkLogin($username, $password);

        if (!$loginCorrect) {
            jsErrorMessage("Falsches Passwort!");
            $updateData = false;
        } 
        
        if ($new_password != "" && $new_password != $new_password_repeat) {
            jsErrorMessage("Neue Passwörter stimmen nicht überein!");
            $updateData = false;
        }
        
        if ($updateData) {
            if ($new_password != "") {
                updateUserPassword($username, $new_password);
            }
            
            updateUserProfileData($username, $display_name, $first_name, $last_name, $email);
        }
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


