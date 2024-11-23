
<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include_once 'database.php';

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
        date DATE,
        time TIME,
        end_time TIME,
        minimum_users INT UNSIGNED,
        locked BOOL NOT NULL DEFAULT 0,
        
        organizer VARCHAR(255),
        description TEXT,
        
        venue VARCHAR(255),
        address VARCHAR(255),
        additional_details TEXT,
        
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
        login VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        salt VARCHAR(50) NOT NULL,

        display_name VARCHAR(50),
        first_name VARCHAR(50),
        last_name VARCHAR(50),
        email VARCHAR(255),

        visible BOOL,
        active BOOL,

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createUsersTable) === TRUE) {
        echo "Table Users created successfully";
    }
    
    $createPermissionsTable = "CREATE TABLE Permissions (
        user_id INT UNSIGNED PRIMARY KEY,

        lock_event_schedule BOOL,
        manage_other_schedules BOOL,

        manage_events BOOL,

        change_other_outline_schedule BOOL,
        view_statistics BOOL,

        invite_users BOOL,
        manage_users BOOL,
        delete_users BOOL,

        login_as_others BOOL,
        manage_permissions BOOL,
        admin_dev_maintenance BOOL,

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createPermissionsTable) === TRUE) {
        echo "Table Permissions created successfully";
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

    $createPasswordTokensTable = "CREATE TABLE PasswordTokens (
        user_id INT UNSIGNED,
        token VARCHAR(255),

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createPasswordTokensTable) === TRUE) {
        echo "Table Roles created successfully";
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

    // set admin user to invisible & allow everything
    updateUserStatus(1, false, true);
    updateUserPermissions(1, true, true, true, true, true, true, true, true, true, true, true);

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

    // set Andi for Fr, Sa
    updateOutlineDay(7, 4, true);
    updateOutlineDay(7, 5, true);

    // set Domi for Do, Fr
    updateOutlineDay(8, 3, true);
    updateOutlineDay(8, 4, true);

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2024-11-15");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2024-11-16");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2024-11-22");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2024-11-23");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2024-11-29");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2024-11-30");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2024-12-06");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2024-12-07");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Spielmannstreiben", "2024-12-13");
    updateEvent($id, "Veranstaltung", "Spielmannstreiben", "Rudelgedudel", "20:00", "02:00", 4, "DomiTheWall", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Großputz", "2024-12-15");
    updateEvent($id, "Veranstaltung", "Großputz", "Wir putzen ihr Spasten", "14:00", "19:00", 8, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    updateEventSchedule(2, $id, true, true);
    updateEventSchedule(5, $id, true, true);
    updateEventSchedule(4, $id, true, true);
    updateEventSchedule(4, $id, true, true);
    updateEventSchedule(4, $id, true, false);
}

// mostly for debugging / database altering
function updateDB()
{
    $pdo = connect();

    // Retrieve the outline schedule
    $sql = "ALTER TABLE Events
            ADD locked BOOL;";

    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute();
    } catch(Exception $e) {
        $msg = $e->getMessage();
        echo "$msg";
    }

    try {
        if ($pdo->query("DROP TABLE Permissions") === TRUE) {
            echo "Table Permissions deleted successfully";
        }

        $createPermissionsTable = "CREATE TABLE Permissions (
            user_id INT UNSIGNED PRIMARY KEY,

            lock_event_schedule BOOL,
            manage_other_schedules BOOL,

            manage_events BOOL,

            change_other_outline_schedule BOOL,
            view_statistics BOOL,

            invite_users BOOL,
            manage_users BOOL,
            delete_users BOOL,

            login_as_others BOOL,
            manage_permissions BOOL,
            admin_dev_maintenance BOOL,

            creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        if ($pdo->query($createPermissionsTable) === TRUE) {
            echo "Table Permissions created successfully";
        }

        $stmt = $pdo->prepare("INSERT INTO Permissions (user_id,
            lock_event_schedule,
            manage_other_schedules,
            manage_events,
            change_other_outline_schedule,
            view_statistics,
            invite_users,
            manage_users,
            delete_users,
            login_as_others,
            manage_permissions,
            admin_dev_maintenance
            ) VALUES (0, true, true, true, true, true, true, true, true, true, true, true)");
        $stmt->execute();

        for ($i = 1; $i < 10; ++$i) {
            $stmt = $pdo->prepare("INSERT INTO Permissions (user_id) VALUES ($i)");
            $stmt->execute();
        }

    } catch(Exception $e) {
        $msg = $e->getMessage();
        echo "$msg";
    }

}


?>
