
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
        end_time TIME,
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
        email VARCHAR(255),

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createUsersTable) === TRUE) {
        echo "Table Events created successfully";
    }
    
    $createSchedulesTable = "CREATE TABLE Schedule (
        user_id INT UNSIGNED,
        event_id INT UNSIGNED,
        deliberate BOOL,
        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, event_id)
    )";
    if ($pdo->query($createSchedulesTable) === TRUE) {
        echo "Table Duties created successfully";
    }
    
    $createOutlineScheduleTable = "CREATE TABLE OutlineSchedule (
        user_id INT UNSIGNED,
        day INT UNSIGNED,
        
        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, day)
    )";
    if ($pdo->query($createOutlineScheduleTable) === TRUE) {
        echo "Table Duties created successfully";
    }

}

function initialize()
{
    initializeDatabase();
    initializeTables(); 
    
    $users = ["admin", "Tascha", "Oli", "Sophia", "Lina", "Tom", "Andi", "Domi", "Max"]; 
    
    foreach ($users as $name) {
        $displayName = $name; 
        $password = $displayName . "PW";
        
        $first = $displayName;
        $last = strtoupper($displayName)[0] . ".";
        $email = strtolower($name) . "@email.de";
        
        addUser($name, $password, $email, $first, $last);
    }
    
    // set Tascha for Do, Sa
    updateOutlineDay(2, 3, true);
    updateOutlineDay(2, 5, true);
    
    // set Oli for Fr
    updateOutlineDay(3, 4, true);
    
    // set Sophia for Do, Sa
    updateOutlineDay(4, 3, true);
    updateOutlineDay(4, 5, true);
    
    // set Lina for Fr, Sa
    updateOutlineDay(5, 4, true);
    updateOutlineDay(5, 5, true);
    
    // set Tom for Do, Fr
    updateOutlineDay(6, 3, true);
    updateOutlineDay(6, 4, true);
    
    // set Max for Fr, Sa
    updateOutlineDay(9, 4, true);
    updateOutlineDay(9, 5, true);
    updateOutlineDay(9, 5, false);
    updateOutlineDay(9, 5, true);

    
    $id = addEvent("Veranstaltung", "Masters Of Metal", "2023-05-12");           
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2023-05-13");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2023-05-19");           
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2023-05-20");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2023-05-26");           
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2023-05-27");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    // set Andi for Fr, Sa
    updateOutlineDay(7, 4, true);
    updateOutlineDay(7, 5, true);
    
    // set Domi for Do, Fr
    updateOutlineDay(8, 3, true);
    updateOutlineDay(8, 4, true);

    
    $id = addEvent("Veranstaltung", "Masters Of Metal", "2023-06-02");           
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2023-06-03");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Großputz", "2023-06-25");
    updateEvent($id, "Veranstaltung", "Großputz", "Wir putzen ihr Spasten", "14:00", "19:00", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    
    updateEventSchedule(2, $id, true, true);
    updateEventSchedule(5, $id, true, true);
    updateEventSchedule(4, $id, true, true);
    updateEventSchedule(4, $id, true, true);
    updateEventSchedule(4, $id, true, false);
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

function addUser($username, $password, $email, $first, $last)
{
    // Create a connection
    $pdo = connect();
    
    // Generate a salt
    $salt = generateSalt();
    // Hash the password with the salt
    $hashedPassword = hash('sha256', $password . $salt);

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('INSERT INTO Users (username, display_name, password, salt, first_name, last_name, email) '
            . 'VALUES (:username, :display_name, :password, :salt, :first_name, :last_name, :email)');
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindValue(':salt', $salt);
    $stmt->bindValue(':display_name', $username);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':first_name', $first);
    $stmt->bindValue(':last_name', $last);
    $stmt->execute();

    return $pdo->lastInsertId();
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

    $eventId = $pdo->lastInsertId();
    
    // now, check the outline schedule and add all users that have the correct day set
    
    // database has days from 0-6 (monday-sunday)
    // date() creates days from 0-6 (sunday-saturday), so we have to convert
    $day = date('w', strtotime($date)) - 1; 
    if ($day == -1) $day = 6;
    
    $stmt = $pdo->prepare('SELECT user_id from OutlineSchedule 
            WHERE day = :day');
    
    $stmt->bindValue(':day', $day);
    $stmt->execute();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sql = "INSERT IGNORE INTO Schedule (user_id, event_id, deliberate)
            VALUES (:user_id, :event_id, false)";
    
        $stmt2 = $pdo->prepare($sql);
        $stmt2->bindValue(":user_id", $row["user_id"]);
        $stmt2->bindValue(":event_id", $eventId);
        $stmt2->execute();
    }

    return $eventId;
}

function updateEvent($event_id, $type, $title, $description, $time, $end_time, $venue, $address)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    $sql = "UPDATE Events
            SET description = :description,
                type = :type,
                title = :title,
                time = :time,
                end_time = :end_time,
                venue = :venue,
                address = :address
            WHERE id = :id";

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $event_id);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':time', $time);
    $stmt->bindParam(':end_time', $end_time);
    $stmt->bindParam(':venue', $venue);
    $stmt->bindParam(':address', $address);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "Event updated successfully.";
    } else {
        echo "Error updating event.";
    }
}

function updateOutlineDay($userId, $day, $active)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    $sql = "";
    if ($active) {
        $sql = "INSERT IGNORE INTO OutlineSchedule (user_id, day)
            VALUES (:id, :day);";
    } else {
        $sql = "DELETE IGNORE FROM OutlineSchedule
            WHERE user_id = :id AND day = :day;";
    }
    
    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $userId);
    $stmt->bindParam(':day', $day);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "Outline schedule updated successfully.";
    } else {
        echo "Error updating outline schedule.";
    }
    
    
    // Now, update all events of today or in the future with the new outline day
    $sql = "SELECT id, date FROM Events
        WHERE date >= CURDATE() AND DAYOFWEEK(date) = :day;";
        
    // $day is 0 -> 6 for mon -> sun
    // we must convert it to 1 -> 7 for sun -> sat
    $sqlDay = $day + 2;
    if ($sqlDay == 8) $sqlDay = 1;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":day", $sqlDay);
    $stmt->execute();

    if ($active) {
        $sql = "INSERT IGNORE INTO Schedule (user_id, event_id, deliberate)
            VALUES (:user_id, :event_id, false)";
        
    } else {
        $sql = "DELETE IGNORE FROM Schedule
            WHERE user_id = :user_id AND event_id = :event_id AND deliberate = false;";
    }
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt2 = $pdo->prepare($sql);
        $stmt2->bindValue(":user_id", $userId);
        $stmt2->bindValue(":event_id", $row["id"]);
        $stmt2->execute();
    }
}

function updateEventSchedule($userId, $eventId, $deliberate, $active)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    if ($active) {
        $sql = "INSERT INTO Schedule (user_id, event_id, deliberate)
            VALUES (:user_id, :event_id, :deliberate)
            ON DUPLICATE KEY UPDATE deliberate = :deliberate;";
    } else {
        $sql = "DELETE IGNORE FROM Schedule
            WHERE user_id = :user_id AND event_id = :event_id;";
    }
    
    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':event_id', $eventId);
    
    if ($active) {
        $stmt->bindParam(':deliberate', $deliberate);
    }
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "Schedule updated successfully.";
    } else {
        echo "Error updating schedule.";
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
        // Retrieve the outline schedule
        $sql = "SELECT user_id, deliberate
                FROM Schedule
                WHERE event_id = :event_id;";

        $stmt2 = $pdo->prepare($sql);
        $stmt2->bindValue("event_id", $row["id"]);
        $stmt2->execute();
        
        $row["users"] = array();
        while ($usersRow = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $row["users"][] = $usersRow;
        }

        $rows[] = $row;
    }
    
    echo json_encode($rows);
}

function getUsers() {    
    $pdo = connect();

    // Retrieve the data from the database
    $sql = "SELECT id, username, display_name, first_name, last_name, email
            FROM Users;";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
       
    $rows = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Retrieve the outline schedule
        $sql = "SELECT day
                FROM OutlineSchedule
                WHERE user_id = :user_id;";

        $stmt2 = $pdo->prepare($sql);
        $stmt2->bindValue("user_id", $row["id"]);
        $stmt2->execute();
        
        for ($i = 0; $i < 7; $i++) {
            $row["day_".$i] = false;
        }
        
        while ($dayRow = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $row["day_".$dayRow["day"]] = true;
        }
        
        $rows[] = $row;
    }
    
    echo json_encode($rows);
}

?>
