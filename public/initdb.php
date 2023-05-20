
<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include 'DatabaseInfo.php';

function connect()
{
    $dbInfo = getDatabaseInfo(); 
    
    // Create connection
    $connection = new mysqli($dbInfo->serverName, $dbInfo->userName, $dbInfo->password, $dbInfo->dbName);

    // Check connection
    if ($connection->connect_error) {
      die("Connection failed: " . $connection->connect_error);
    }

    echo "Connected successfully";

    return $connection;
}

function initializeDatabase()
{
    $dbInfo = getDatabaseInfo(); 
    
    // Create connection
    $connection = new mysqli($dbInfo->serverName, $dbInfo->userName, $dbInfo->password);

    // Check connection
    if ($connection->connect_error) {
      die("initialize(): Connection failed: " . $connection->connect_error);
    }

    echo "initialize(): Connected successfully";

    $existsQuery = "SHOW DATABASES LIKE '$dbInfo->dbName'";
    $result = $connection->query($existsQuery);
    
    // first, drop database if it exists
    if ($result && $result->num_rows > 0) {
        echo "Database '$dbInfo->dbName' found, deleting";
        
        $dropDB = "DROP DATABASE $dbInfo->dbName";
        $connection->query($dropDB);
    }
    
    // recreate database
    $createDBQuery = "CREATE DATABASE $dbInfo->dbName";
    if ($connection->query($createDBQuery) === TRUE) {
      echo "Database $dbInfo->dbName created successfully";
    } else {
      echo "Error creating database: " . $connection->error;
    }
    
    $connection->close(); 
}

function initializeTables()
{
    $connection = connect(); 
    
    $createEventsTable = "CREATE TABLE Events (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(50) NOT NULL,
        title VARCHAR(50) NOT NULL,
        description TEXT,
        date DATE,
        time TIME,
        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($connection->query($createEventsTable) === TRUE) {
      echo "Table Events created successfully";
    } else {
      echo "Error creating table: " . $connection->error;
    }
    
    $createUsersTable = "CREATE TABLE Users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        salt VARCHAR(50) NOT NULL,

        displayname VARCHAR(50),
        firstname VARCHAR(50),
        lastname VARCHAR(50),

        Creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($connection->query($createUsersTable) === TRUE) {
      echo "Table Events created successfully";
    } else {
      echo "Error creating table: " . $connection->error;
    }

    $connection->close(); 
}

function initialize()
{
    initializeDatabase();
    initializeTables(); 
    
    addUser("admin", "AdminPW");
}

function generateSalt($length = 16) {
    // Generate random bytes
    $randomBytes = random_bytes($length);

    // Encode the random bytes using base64
    $salt = base64_encode($randomBytes);

    // Remove characters that are not URL-safe
    $salt = str_replace(['+', '/', '='], ['-', '_', ''], $salt);

    // Truncate the salt to the desired length
    $salt = substr($salt, 0, $length);

    return $salt;
}

function addUser($username, $password)
{
    // Create a connection
    $connection = connect();
    
    // Generate a salt
    $salt = generateSalt();
    // Hash the password with the salt
    $hashedPassword = hash('sha256', $password . $salt);

    // Prepare and execute the SQL statement
    $stmt = $connection->prepare('INSERT INTO Users (username, password, salt) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $username, $hashedPassword, $salt);
    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        echo 'User created successfully.';
    } else {
        echo 'Error creating user.';
    }

    $stmt->close();
    $connection->close();
}

function deleteUser($username) {
    
    if ($username == "admin") {
        echo 'Cannot delete admin for safety reasons.';
        return; 
    }
    
    $connection = connect();

    // Prepare and execute the SQL statement
    $stmt = $connection->prepare('DELETE FROM Users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        echo 'User deleted successfully.';
    } else {
        echo 'Error deleting user.';
    }

    $stmt->close();
    $connection->close();
}

function checkLogin($username, $password)
{
    $connection = connect();

    // Retrieve the salt from the database
    $sql = "SELECT password, salt FROM Users WHERE username = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    $result = false;
    if ($stmt->num_rows === 1) {
        $hashedPassword = "";
        $salt = "";
        
        $stmt->bind_result($hashedPassword, $salt);
        $stmt->fetch();

        // Verify the password
        $hashedInput = hash('sha256', $password . $salt);
        
        $result = ($hashedInput === $hashedPassword);
    }

    $stmt->close();
    $connection->close();
    
    return $result;
}

?>