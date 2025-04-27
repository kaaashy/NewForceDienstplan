
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

        manage_schedule BOOL,

        manage_events BOOL,

        change_other_outline_schedule BOOL,
        view_statistics BOOL,

        manage_users BOOL,

        manage_permissions BOOL,
        admin_dev_maintenance BOOL,

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createPermissionsTable) === TRUE) {
        echo "Table Permissions created successfully";
    }
    
    $createAvailabilitiesTable = "CREATE TABLE Availabilities (
        user_id INT UNSIGNED,
        event_id INT UNSIGNED,
        deliberate BOOL,
        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, event_id)
    )";
    if ($pdo->query($createAvailabilitiesTable) === TRUE) {
        echo "Table Availabilities created successfully";
    }

    $createSchedulesTable = "CREATE TABLE Schedule (
        user_id INT UNSIGNED,
        event_id INT UNSIGNED,
        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, event_id)
    )";
    if ($pdo->query($createSchedulesTable) === TRUE) {
        echo "Table Schedule created successfully";
    }

    $createOutlineScheduleTable = "CREATE TABLE OutlineSchedule (
        user_id INT UNSIGNED,
        day INT UNSIGNED,

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, day)
    )";
    if ($pdo->query($createOutlineScheduleTable) === TRUE) {
        echo "Table OutlineSchedule created successfully";
    }

    $createPasswordTokensTable = "CREATE TABLE PasswordTokens (
        user_id INT UNSIGNED,
        token VARCHAR(255),

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createPasswordTokensTable) === TRUE) {
        echo "Table PasswordTokens created successfully";
    }

    $createEmailTokensTable = "CREATE TABLE EmailTokens (
        user_id INT UNSIGNED,
        token VARCHAR(255),
        email VARCHAR(255),

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createEmailTokensTable) === TRUE) {
        echo "Table EmailTokens created successfully";
    }

    $createLoginTokensTable = "CREATE TABLE LoginTokens (
        user_id INT UNSIGNED,
        token VARCHAR(255),

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createLoginTokensTable) === TRUE) {
        echo "Table LoginTokens created successfully";
    }

    $createEventDefaultDataTable = "CREATE TABLE EventDefaultData (
        id INT UNSIGNED PRIMARY KEY,

        type VARCHAR(255),
        time TIME,
        end_time TIME,
        minimum_users INT UNSIGNED,

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createEventDefaultDataTable) === TRUE) {
        echo "Table EventDefaultData created successfully";
    }

    $pdo->query("INSERT INTO EventDefaultData (id, type, time, end_time, minimum_users) VALUES('0', 'Veranstaltung', '20:00', '00:00', '0')");
    $pdo->query("INSERT INTO EventDefaultData (id, type, time, end_time, minimum_users) VALUES('1', 'Veranstaltung', '20:00', '00:00', '0')");
    $pdo->query("INSERT INTO EventDefaultData (id, type, time, end_time, minimum_users) VALUES('2', 'Veranstaltung', '20:00', '00:00', '0')");
    $pdo->query("INSERT INTO EventDefaultData (id, type, time, end_time, minimum_users) VALUES('3', 'Veranstaltung', '20:00', '00:00', '2')");
    $pdo->query("INSERT INTO EventDefaultData (id, type, time, end_time, minimum_users) VALUES('4', 'Veranstaltung', '20:00', '02:00', '4')");
    $pdo->query("INSERT INTO EventDefaultData (id, type, time, end_time, minimum_users) VALUES('5', 'Veranstaltung', '20:00', '02:00', '4')");
    $pdo->query("INSERT INTO EventDefaultData (id, type, time, end_time, minimum_users) VALUES('6', 'Putzdienst', '15:00', '20:00', '2')");


    $createMetaTable = "CREATE TABLE Meta (
        version INT UNSIGNED,

        creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    if ($pdo->query($createMetaTable) === TRUE) {
        echo "Table Meta created successfully";
    }

    $pdo->query("INSERT INTO Meta (version) VALUES('1')");
}

function initialize()
{
    initializeDatabase();
    initializeTables();

    $installInfo = getInstallInfo();
    addUser("admin", $installInfo->adminPassword, $installInfo->adminEmail, "", "");

    // set admin user to invisible & allow everything
    updateUserStatus(1, false, true);
    updateUserPermissions(1, true, true, true, true, true, true, true, true, true, true, true);
}

function createExampleDB()
{
    $users = ["Tascha", "Oli", "Sophia", "Lina", "Andi", "Domi", "Max", "Justus", "Diego", "Ole", "Willy", "Matze", "Sebi", "Tom", "Ben", "Finn", "Johannes", "Martin", "Michl", "Tobi"];
    
    foreach ($users as $name) {
        $displayName = $name; 
        $password = $displayName . "PW";
        
        $first = $displayName;
        $last = strtoupper($displayName)[0] . ".";
        $email = strtolower($name) . "@email.de";
        
        $id = addUser($name, $password, $email, $first, $last);

        if (rand() % 3 == 1) updateOutlineDay($id, 3, true);
        if (rand() % 3 == 1) updateOutlineDay($id, 4, true);
        if (rand() % 3 == 1) updateOutlineDay($id, 5, true);

        if ($name == "Domi"
                || $name == "Sebi"
                || $name == "Ben"
                || $name == "Sophia") {
            updateUserStatus($id, false, false);
        }

        if ($name == "Andi" || $name == "Johannes") {
            updateUserPermission($id, 'manage_schedule', true);
            updateUserPermission($id, 'manage_events', true);
            updateUserPermission($id, 'change_other_outline_schedule', true);
            updateUserPermission($id, 'view_statistics', true);
            updateUserPermission($id, 'manage_users', true);
            updateUserPermission($id, 'manage_permissions', true);
        }

        if ($name == "Oli") {
            updateUserPermission($id, 'manage_schedule', true);
            updateUserPermission($id, 'change_other_outline_schedule', true);
        }

        if ($name == "Max") {
            updateUserPermission($id, 'manage_users', true);
            updateUserPermission($id, 'manage_permissions', true);
            updateUserPermission($id, 'admin_dev_maintenance', true);
        }

        if ($name == "Tascha" || $name == "Michl") {
            updateUserPermission($id, 'manage_events', true);
        }
    }

    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2025-02-13");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2025-02-14");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2025-02-15");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");


    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2025-02-20");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2025-02-21");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2025-02-22");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");


    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2025-02-27");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2025-02-28");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2025-03-01");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");


    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2025-03-06");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2025-03-07");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2025-03-08");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2025-03-13");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Spielmannstreiben", "2025-03-14");
    updateEvent($id, "Veranstaltung", "Spielmannstreiben", "Rudelgedudel", "20:00", "02:00", 4, "DomiTheWall", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Sonstige", "Großputz", "2025-03-16");
    updateEvent($id, "Sonstige", "Großputz", "Wir putzen ihr Spasten", "14:00", "19:00", 8, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
}

// mostly for debugging / database altering
function updateDB()
{
    $pdo = connect();

    try {
        $createLoginTokensTable = "CREATE TABLE LoginTokens (
            user_id INT UNSIGNED,
            token VARCHAR(255),

            creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        if ($pdo->query($createLoginTokensTable) === TRUE) {
            echo "Table LoginTokens created successfully";
        }

        $createMetaTable = "CREATE TABLE Meta (
            version INT UNSIGNED,

            creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        if ($pdo->query($createMetaTable) === TRUE) {
            echo "Table Meta created successfully";
        }

        $pdo->query("INSERT INTO Meta (version) VALUES('1')");

    } catch(Exception $e) {
        $msg = $e->getMessage();
        echo "$msg";
    }

}


?>
