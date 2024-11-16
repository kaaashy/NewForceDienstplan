<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

session_start();

include_once 'initdb.php';

// Check if the user is already logged in
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
    header('Location: dienstplan.php');
    exit;
}

function validateLogin()
{
    // Check if the form is submitted
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        // Validate the login credentials
        $login = $_POST['login'];
        $password = $_POST['password'];

        list($loginCorrect, $userId) = checkLogin($login, $password);

        echo ($loginCorrect);
        echo ($userId);

        if (!$loginCorrect) {
            return 'Falscher Login oder Passwort.';
        }

        if (!getUserActive($userId)) {
            return "Account '$login' wurde deaktiviert.";
        }

        $_SESSION['loggedin'] = true;
        $_SESSION['login'] = $login;
        $_SESSION['user_id'] = $userId;

        header('Location: dienstplan.php');
        exit;
    }
}

$loginError = validateLogin();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if(isset($loginError) && $loginError != "") { ?>
        <p><?php echo $loginError; ?></p>
    <?php } ?>
    <form method="POST" action="">
        <div>
            <label for="login">Login:</label>
            <input type="text" id="login" name="login" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <input type="submit" value="Login">
        </div>
    </form>

    <div>
        <a href="restore-password.php">Passwort vergessen?</a>
    </div>
</body>
</html>


