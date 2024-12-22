<?php

session_start();

include_once 'initdb.php';
include_once 'pages.php';

$token = htmlspecialchars($_GET["token"]);

// get the initialization details of the user from the password token
list($login, $email) = fetchInitializationToken($token);
if (!isset($email) || !isset($login)) {
    $errorMessage = "Registrierung nicht mehr verfügbar. Bitte wenden Sie sich die Person, die Sie für den Dienstplan registrierte.";
}

list ($userId, $login) = fetchUserCredentialsByEmail($email);
$userDetails = getUserDetails($userId);

if (!$userDetails) {
    $userDetails = array();
    $userDetails['display_name'] = "";
    $userDetails['first_name'] = "";
    $userDetails['last_name'] = "";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['init_user'])) {
        $display_name = trim($_POST['display_name']);
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);

        $new_password = $_POST['password'];
        $new_password_repeat = $_POST['password_repeat'];

        $updateData = true;

        if ($new_password != "" && $new_password != $new_password_repeat) {
            $errorMessage = "Passwörter stimmen nicht überein!";
            $updateData = false;
        }

        // not strictly necessary since email can't be changed here, but better safe than sorry
        list($existingId, $existingLogin) = fetchUserCredentialsByEmail($email);
        if ($existingId) {
            if ($existingLogin != $login) {
                $errorMessage = "Email-Adresse '$email' wird bereits von einer anderen Person genutzt.";
                $updateData = false;
            }
        }

        if ($updateData) {
            updateUserPassword($login, $new_password);
            updateUserProfileData($login, $display_name, $first_name, $last_name, $email);
            removeInitializationToken($login, $token);

            header("Location: userprofile.php");
            die();
        }
    }
}

?>

<!DOCTYPE html>
<html>
<head>
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

            <?php
            echo '<h1>Registrierung Abschließen / Passwort zurücksetzen</h1>';
            echo "Login: $login <br/>";
            echo "Email: $email <br/><br/>";
            ?>


            <form method="POST" action="">
                <div>
                    <label for="display_name">Anzeigename:</label>
                    <input type="text" id="display_name" name="display_name" value="<?php echo $userDetails['display_name']; ?>" required>
                </div>
                <div>
                    <label for="first_name">Vorname (optional):</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo $userDetails['first_name']; ?>">
                </div>
                <div>
                    <label for="last_name">Nachname (optional):</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo $userDetails['last_name']; ?>">
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


