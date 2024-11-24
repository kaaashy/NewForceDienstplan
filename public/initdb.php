
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

}

function initialize()
{
    initializeDatabase();
    initializeTables();

    addUser("admin", "adminPW", "admin@newforcedienstplan.de", "", "");

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

        if ($name == "Domi") {
            updateUserStatus($id, false, false);
        }

        if ($name == "Andi" || $name == "Johannes") {
            updateUserPermission($id, 'lock_event_schedule', true);
            updateUserPermission($id, 'manage_other_schedules', true);
            updateUserPermission($id, 'manage_events', true);
            updateUserPermission($id, 'change_other_outline_schedule', true);
            updateUserPermission($id, 'view_statistics', true);
            updateUserPermission($id, 'invite_users', true);
            updateUserPermission($id, 'manage_users', true);
            updateUserPermission($id, 'delete_users', true);
            updateUserPermission($id, 'login_as_others', true);
            updateUserPermission($id, 'manage_permissions', true);
        }

        if ($name == "Oli") {
            updateUserPermission($id, 'lock_event_schedule', true);
            updateUserPermission($id, 'manage_other_schedules', true);
            updateUserPermission($id, 'change_other_outline_schedule', true);
        }

        if ($name == "Max") {
            updateUserPermission($id, 'invite_users', true);
            updateUserPermission($id, 'manage_users', true);
            updateUserPermission($id, 'delete_users', true);
            updateUserPermission($id, 'login_as_others', true);
            updateUserPermission($id, 'manage_permissions', true);
            updateUserPermission($id, 'admin_dev_maintenance', true);
        }

        if ($name == "Tascha" || $name == "Michl") {
            updateUserPermission($id, 'manage_events', true);
        }
    }

    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2024-11-14");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2024-11-15");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2024-11-16");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");


    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2024-11-21");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2024-11-22");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2024-11-23");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");


    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2024-11-28");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2024-11-29");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2024-11-30");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");


    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2024-12-05");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Masters Of Metal", "2024-12-06");
    updateEvent($id, "Veranstaltung", "Masters Of Metal", "Heavy, Pagan, Power", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Blasts in Brucklyn", "2024-12-07");
    updateEvent($id, "Veranstaltung", "Blasts in Brucklyn", "Death, Black, Core & More", "20:00", "02:00", 3,"", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
    
    $id = addEvent("Veranstaltung", "Donnerstagsgedöns", "2024-12-12");
    updateEvent($id, "Veranstaltung", "Donnerstagsgedöns", "Jede Woche wechselnde Specials", "20:00", "00:00", 3, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Spielmannstreiben", "2024-12-13");
    updateEvent($id, "Veranstaltung", "Spielmannstreiben", "Rudelgedudel", "20:00", "02:00", 4, "DomiTheWall", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");

    $id = addEvent("Veranstaltung", "Großputz", "2024-12-15");
    updateEvent($id, "Veranstaltung", "Großputz", "Wir putzen ihr Spasten", "14:00", "19:00", 8, "", "New Force", "Buckenhofer Weg 69, 91058 Erlangen");
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
