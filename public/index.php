<?php

session_start();

include_once 'initdb.php';

// Check if the user is already logged in
if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true){
    header('Location: dienstplan.php');
    exit;
}

// Check if the form is submitted
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Validate the login credentials
    $username = $_POST['username'];
    $password = $_POST['password'];

    list($loginCorrect, $userId) = checkLogin($username, $password);

    if ($loginCorrect) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_id'] = $userId;
        
        header('Location: dienstplan.php');
        exit;
    } else {
        $loginError = 'Invalid username or password';
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if(isset($loginError)) { ?>
        <p><?php echo $loginError; ?></p>
    <?php } ?>
    <form method="POST" action="">
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <input type="submit" value="Login">
        </div>
    </form>
</body>
</html>


