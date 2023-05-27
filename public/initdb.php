
<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include 'DatabaseInfo.php';

function connect()
{
    $dbInfo = getDatabaseInfo(); 
    
    // Create connection
    $dsn = "mysql:host={$dbInfo->serverName};dbname={$dbInfo->dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbInfo->userName, $dbInfo->password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}

function initializeDatabase()
{
    $dbInfo = getDatabaseInfo(); 
    
    // Create connection
    $dsn = "mysql:host={$dbInfo->serverName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbInfo->userName, $dbInfo->password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "initialize(): Connected successfully";

    $existsQuery = "SHOW DATABASES LIKE '$dbInfo->dbName'";
    $result = $pdo->query($existsQuery);
    
    // first, drop database if it exists
    if ($result && $result->rowCount() > 0) {
        echo "Database '$dbInfo->dbName' found, deleting";
        
        $dropDB = "DROP DATABASE $dbInfo->dbName";
        $pdo->query($dropDB);
    }
    
    // recreate database
    $createDBQuery = "CREATE DATABASE $dbInfo->dbName";
    if ($pdo->query($createDBQuery) === TRUE) {
      echo "Database $dbInfo->dbName created successfully";
    }
}

function initializeTables()
{
    $pdo = connect(); 
    
    $createEventsTable = "CREATE TABLE Events (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        
        type VARCHAR(255),
        title VARCHAR(255),
        description TEXT,
        additional_details TEXT,
        date DATE,
        time TIME,
        venue VARCHAR(255),
        address VARCHAR(255),
        
        auto_created BOOL,
        created_by INT UNSIGNED,
        updated_by INT UNSIGNED,
        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createEventsTable) === TRUE) {
      echo "Table Events created successfully";
    } 
    
    $createUsersTable = "CREATE TABLE Users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        salt VARCHAR(50) NOT NULL,

        display_name VARCHAR(50),
        first_name VARCHAR(50),
        last_name VARCHAR(50),

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createUsersTable) === TRUE) {
      echo "Table Events created successfully";
    } 
}

function initialize()
{
    initializeDatabase();
    initializeTables(); 
    
    addUser("admin", "AdminPW");
    addUser("Tascha", "Tascha");
    addUser("Oli", "Oli");
    addUser("Sophia", "Sophia");
    addUser("Lina", "Lina");
    addUser("Tom", "Tom");
    addUser("Andi", "Andi");
    addUser("Domi", "Domi");
    addUser("Max", "Max");
        
    $id = addEvent("Veranstaltung", "Großputz", "2023-05-07");
    updateEvent($id, "Veranstaltung", "Großputz", "Wir putzen ihr Spasten", "Mehr Info gibt's net", "2023-05-07", "16:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2023-05-05");           
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "Blasts für die Melodischen unter uns", "2023-05-05", "20:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2023-05-06");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "Fette Blasts", "2023-05-06", "20:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2023-05-12");           
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "Blasts für die Melodischen unter uns", "2023-05-12", "20:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2023-05-13");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "Fette Blasts", "2023-05-13", "20:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2023-05-19");           
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "Blasts für die Melodischen unter uns", "2023-05-19", "20:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2023-05-20");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "Fette Blasts", "2023-05-20", "20:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2023-05-26");           
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "Blasts für die Melodischen unter uns", "2023-05-26", "20:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2023-05-27");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "Fette Blasts", "2023-05-27", "20:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
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
    $pdo = connect();
    
    // Generate a salt
    $salt = generateSalt();
    // Hash the password with the salt
    $hashedPassword = hash('sha256', $password . $salt);

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('INSERT INTO Users (username, display_name, password, salt) '
            . 'VALUES (:username, :display_name, :password, :salt)');
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindValue(':salt', $salt);
    $stmt->bindValue(':display_name', $username);
    $stmt->execute();

}

function deleteUser($username) {
    
    if ($username == "admin") {
        echo 'Cannot delete admin for safety reasons.';
        return; 
    }
    
    $pdo = connect();

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('DELETE FROM Users WHERE username = :username');
    $stmt->bindValue(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        echo 'User deleted successfully.';
    } else {
        echo 'Error deleting user.';
    }

}

function addEvent($type, $title, $date)
{
    // Create a connection
    $pdo = connect();

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('INSERT INTO Events (type, title, date) VALUES (:type, :title, :date)');
    $stmt->bindValue(':type', $type);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':date', $date);

    $stmt->execute();

    return $pdo->lastInsertId();
}

function updateEvent($event_id, $type, $title, $description, $details, $date, $time, $venue, $address)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    $sql = "UPDATE Events
            SET description = :description,
                type = :type,
                title = :title,
                additional_details = :additional_details,
                date = :date,
                time = :time,
                venue = :venue,
                address = :address
            WHERE id = :id";

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $event_id);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':additional_details', $details);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':time', $time);
    $stmt->bindParam(':venue', $venue);
    $stmt->bindParam(':address', $address);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "Event updated successfully.";
    } else {
        echo "Error updating event.";
    }
}

function deleteEvent($id)
{
    // Create a connection
    $pdo = connect();

    $stmt = $pdo->prepare('DELETE FROM Events WHERE id = :id');
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        echo 'Event deleted successfully.';
    } else {
        echo 'Error deleting event.';
    }
}

function checkLogin($username, $password)
{
    $pdo = connect();

    // Retrieve the salt from the database
    $sql = "SELECT password, salt FROM Users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    
    $result = false;
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row)
    {
        $hashedPassword = $row["password"];
        $salt = $row["salt"];
        
        // Verify the password
        $hashedInput = hash('sha256', $password . $salt);
        
        $result = ($hashedInput === $hashedPassword);
    }
    
    return $result;
}

function getEvents($month, $year) {    
    $pdo = connect();

    // Retrieve the salt from the database
    $sql = "SELECT *
            FROM Events
            WHERE MONTH(date) = :month
            AND YEAR(date) = :year;";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':month', $month);
    $stmt->bindValue(':year', $year);
    $stmt->execute();
    
    $rows = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }
    
    echo json_encode($rows);
}

function getUsers() {    
    $pdo = connect();

    // Retrieve the salt from the database
    $sql = "SELECT id, display_name, first_name, last_name
            FROM Users;";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
       
    $rows = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }
    
    echo json_encode($rows);
}

?>
