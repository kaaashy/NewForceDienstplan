<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include_once 'DatabaseInfo.php';


function connect()
{
    $dbInfo = getDatabaseInfo();

    // Create connection
    $dsn = "mysql:host={$dbInfo->serverName};dbname={$dbInfo->dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbInfo->userName, $dbInfo->password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);

    return $pdo;
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

function addUser($login, $password, $email, $first, $last)
{
    // Create a connection
    $pdo = connect();

    // Generate a salt
    $salt = generateSalt();
    // Hash the password with the salt
    $hashedPassword = hash('sha256', $password . $salt);

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('INSERT INTO Users (login, display_name, password, salt, first_name, last_name, email, active, visible) '
            . 'VALUES (:login, :display_name, :password, :salt, :first_name, :last_name, :email, true, true)');
    $stmt->bindValue(':login', $login);
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindValue(':salt', $salt);
    $stmt->bindValue(':display_name', $login);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':first_name', $first);
    $stmt->bindValue(':last_name', $last);
    $stmt->execute();

    $id = $pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO Permissions (user_id) '
            . 'VALUES (:user_id)');
    $stmt->bindValue(':user_id', $id);
    $stmt->execute();

    return $id;
}

function deleteUser($login) {

    if ($login == "admin") {
        return 'Cannot delete admin for safety reasons.';
    }

    $pdo = connect();

    // Retrieve the user id
    $sql = "SELECT id FROM Users WHERE login = :login";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':login', $login);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row)
    {
        $user_id = $row["id"];

        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare('DELETE FROM Schedule WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();

        $stmt = $pdo->prepare('DELETE FROM Availabilities WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();

        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare('DELETE FROM OutlineSchedule WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();

        // Prepare and execute the SQL statement
        $stmt = $pdo->prepare('DELETE FROM PasswordTokens WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();
    }

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('DELETE FROM Users WHERE login = :login');
    $stmt->bindValue(':login', $login);
    $stmt->execute();
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
        $sql = "INSERT IGNORE INTO Availabilities (user_id, event_id, deliberate)
            VALUES (:user_id, :event_id, false)";

        $stmt2 = $pdo->prepare($sql);
        $stmt2->bindValue(":user_id", $row["user_id"]);
        $stmt2->bindValue(":event_id", $eventId);
        $stmt2->execute();
    }

    return $eventId;
}

function updateEvent($event_id, $type, $title, $description, $time, $end_time, $minUsers, $organizer, $venue, $address)
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
                minimum_users = :minimum_users,
                organizer = :organizer,
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
    $stmt->bindParam(':minimum_users', $minUsers);
    $stmt->bindParam(':organizer', $organizer);
    $stmt->bindParam(':venue', $venue);
    $stmt->bindParam(':address', $address);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Event updated successfully.";
    } else {
        echo "Error updating event.";
    }
}

function setEventLockedStatus($event_id, $locked)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    $sql = "UPDATE Events
            SET locked = :locked
            WHERE id = :id";

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $event_id);
    $stmt->bindValue(':locked', $locked ? 1 : 0);

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
        WHERE locked != 1 AND date >= CURDATE() AND DAYOFWEEK(date) = :day;";

    // $day is 0 -> 6 for mon -> sun
    // we must convert it to 1 -> 7 for sun -> sat
    $sqlDay = $day + 2;
    if ($sqlDay == 8) $sqlDay = 1;

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":day", $sqlDay);
    $stmt->execute();

    if ($active) {
        $sql = "INSERT IGNORE INTO Availabilities (user_id, event_id, deliberate)
            VALUES (:user_id, :event_id, false)";

    } else {
        $sql = "DELETE IGNORE FROM Availabilities
            WHERE user_id = :user_id AND event_id = :event_id AND deliberate = false;";
    }


    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt2 = $pdo->prepare($sql);
        $stmt2->bindValue(":user_id", $userId);
        $stmt2->bindValue(":event_id", $row["id"]);
        $stmt2->execute();
    }
}

function updateEventAvailability($userId, $eventId, $deliberate, $available)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    if ($available) {
        $sql = "INSERT INTO Availabilities (user_id, event_id, deliberate)
            VALUES (:user_id, :event_id, :deliberate)
            ON DUPLICATE KEY UPDATE deliberate = :deliberate;";
    } else {
        $sql = "DELETE IGNORE FROM Availabilities
            WHERE user_id = :user_id AND event_id = :event_id;";
    }

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':event_id', $eventId);

    if ($available) {
        $stmt->bindParam(':deliberate', $deliberate);
    }

    // Execute the statement
    if ($stmt->execute()) {
        echo "Availabilities updated successfully.";
    } else {
        echo "Error updating availabilities.";
    }
}

function updateUserEventSchedule($userId, $eventId, $scheduled)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    if ($scheduled) {
        $sql = "INSERT INTO Schedule (user_id, event_id)
            VALUES (:user_id, :event_id)
            ON DUPLICATE KEY UPDATE event_id = :event_id;";
    } else {
        $sql = "DELETE IGNORE FROM Schedule
            WHERE user_id = :user_id AND event_id = :event_id;";
    }

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':event_id', $eventId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Schedule updated successfully.";
    } else {
        echo "Error updating Schedule.";
    }
}

function updateUserPassword($login, $new_password)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    $sql = "UPDATE Users
            SET password = :password,
                salt = :salt
            WHERE login = :login";

    // Generate a salt
    $salt = generateSalt();
    // Hash the password with the salt
    $hashedPassword = hash('sha256', $new_password . $salt);

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':login', $login);
    $stmt->bindValue(':password', $hashedPassword);
    $stmt->bindValue(':salt', $salt);
    $stmt->execute();

    // Execute the statement
    if ($stmt->execute()) {
        echo "User password updated successfully.";
    } else {
        echo "Error updating user password.";
    }
}

function updateUserProfileData($login, $display_name, $first_name, $last_name, $email)
{
    // Create a connection
    $pdo = connect();

    // Prepare the SQL statement
    $sql = "UPDATE Users
            SET display_name = :display_name,
                first_name = :first_name,
                last_name = :last_name,
                email = :email
            WHERE login = :login";

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':login', $login);
    $stmt->bindValue(':display_name', $display_name);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':first_name', $first_name);
    $stmt->bindValue(':last_name', $last_name);
    $stmt->execute();

    // Execute the statement
    if ($stmt->execute()) {
        echo "User updated successfully.";
    } else {
        echo "Error updating user.";
    }
}

function updateUserStatus($id, $visible, $active)
{
    if (!$active && $id == 1) {
        echo 'Cannot deactivate admin for safety reasons.';
        return;
    }

    $pdo = connect();

    // Prepare the SQL statement
    $sql = "UPDATE Users
            SET active = :active,
                visible = :visible
            WHERE id = :id";

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->bindValue(':visible', $visible ? 1 : 0);
    $stmt->bindValue(':active', $active ? 1 : 0);
    $stmt->execute();

    // Execute the statement
    if ($stmt->execute()) {
        echo "User updated successfully.";
    } else {
        echo "Error updating user.";
    }
}

function updateUserPermissions($user_id,
        $lock_event_schedule,
        $manage_other_schedules,
        $manage_events,
        $change_other_outline_schedule,
        $view_statistics,
        $invite_users,
        $manage_users,
        $delete_users,
        $login_as_others,
        $manage_permissions,
        $admin_dev_maintenance)
{
    $pdo = connect();

    // Prepare the SQL statement
    $sql = "UPDATE Permissions
            SET
                lock_event_schedule = :lock_event_schedule,
                manage_other_schedules = :manage_other_schedules,
                manage_events = :manage_events,
                change_other_outline_schedule = :change_other_outline_schedule,
                view_statistics = :view_statistics,
                invite_users = :invite_users,
                manage_users = :manage_users,
                delete_users = :delete_users,
                login_as_others = :login_as_others,
                manage_permissions = :manage_permissions,
                admin_dev_maintenance = :admin_dev_maintenance
            WHERE user_id = :id";

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $user_id);
    $stmt->bindValue(':lock_event_schedule', $lock_event_schedule ? 1 : 0);
    $stmt->bindValue(':manage_other_schedules', $manage_other_schedules ? 1 : 0);
    $stmt->bindValue(':manage_events', $manage_events ? 1 : 0);
    $stmt->bindValue(':change_other_outline_schedule', $change_other_outline_schedule ? 1 : 0);
    $stmt->bindValue(':view_statistics', $view_statistics ? 1 : 0);
    $stmt->bindValue(':invite_users', $invite_users ? 1 : 0);
    $stmt->bindValue(':manage_users', $manage_users ? 1 : 0);
    $stmt->bindValue(':delete_users', $delete_users ? 1 : 0);
    $stmt->bindValue(':login_as_others', $login_as_others ? 1 : 0);
    $stmt->bindValue(':manage_permissions', $manage_permissions ? 1 : 0);
    $stmt->bindValue(':admin_dev_maintenance', $admin_dev_maintenance ? 1 : 0);
    $stmt->execute();

    // Execute the statement
    if ($stmt->execute()) {
        echo "User Permissions updated successfully.";
    } else {
        echo "Error updating user permissions.";
    }
}

function isChangeablePermission($permission)
{
    if ($permission == 'lock_event_schedule') return true;
    if ($permission == 'manage_other_schedules') return true;
    if ($permission == 'manage_events') return true;
    if ($permission == 'change_other_outline_schedule') return true;
    if ($permission == 'view_statistics') return true;
    if ($permission == 'invite_users') return true;
    if ($permission == 'manage_users') return true;
    if ($permission == 'delete_users') return true;
    if ($permission == 'login_as_others') return true;
    if ($permission == 'manage_permissions') return true;
    if ($permission == 'admin_dev_maintenance') return true;

    return false;
}

function updateUserPermission($user_id, $permission, $value)
{
    if (!isChangeablePermission($permission)) {
        echo "User Permission $permission unchangeable.";
        return;
    }

    if ($user_id == 1 && !$value) {
        echo 'Cannot disable admin permissions for safety reasons.';
        return;
    }

    $pdo = connect();

    // Prepare the SQL statement
    $sql = "UPDATE Permissions
            SET $permission = :value
            WHERE user_id = :id";

    // Prepare the statement and bind the parameters
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $user_id);
    $stmt->bindValue(':value', $value ? 1 : 0);
    $stmt->execute();

    // Execute the statement
    if ($stmt->execute()) {
        echo "User Permissions updated successfully.";
    } else {
        echo "Error updating user permissions.";
    }
}

function getUserHasPermission($user_id, $permission)
{
    $pdo = connect();

    // Retrieve the permissions
    $sql = "SELECT *
            FROM Permissions
            WHERE user_id = :user_id;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue("user_id", $user_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row[$permission];
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

function checkLogin($login, $password)
{
    $pdo = connect();

    // Retrieve the salt from the database
    $sql = "SELECT password, id, salt FROM Users WHERE login = :login";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':login', $login);
    $stmt->execute();

    $loginCorrect = false;
    $id = 0;
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row)
    {
        $hashedPassword = $row["password"];
        $id = $row["id"];
        $salt = $row["salt"];

        // Verify the password
        $hashedInput = hash('sha256', $password . $salt);

        $loginCorrect = ($hashedInput === $hashedPassword);
    }

    return array($loginCorrect, $id);
}

function initializeUser($login, $email, $password)
{
    // Create a connection
    $pdo = connect();

    // check if there is already a user with that name
    $stmt = $pdo->prepare('SELECT id, email, display_name, first_name, last_name FROM Users WHERE login = :login');
    $stmt->bindValue(':login', $login);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $user_id = $row["id"];
        $display_name = $row["display_name"];
        $first_name = $row["first_name"];
        $last_name = $row["last_name"];

        updateUserProfileData($login, $display_name, $first_name, $last_name, $email);
    } else {
        $user_id = addUser($login, $password, $email, "", "");
    }

    // remove any previous tokens
    $stmt = $pdo->prepare('DELETE FROM PasswordTokens WHERE user_id = :user_id');
    $stmt->bindValue(':user_id', $user_id);
    $stmt->execute();

    // create an initialization token so the user can define their password
    $token = addInitializationToken($user_id);

    return array($token, $user_id);
}

function getUserActive($user_id)
{
    $row = getUserDetails($user_id);
    if (!$row) return false;

    return $row['active'];
}

function getUserDetails($user_id)
{
    $pdo = connect();

    $sql = "SELECT *
            FROM Users
            WHERE id = :user_id;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue("user_id", $user_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row;
}

function getUserId($login)
{
    $pdo = connect();

    $sql = "SELECT id
            FROM Users
            WHERE login = :login;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue("login", $login);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row)
        return $row['id'];
}

function fetchUserCredentialsByEmail($email)
{
    $pdo = connect();

    $sql = "SELECT id, login
            FROM Users
            WHERE email = :email;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue("email", $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) return;

    return array($row['id'], $row['login']);
}

function addInitializationToken($userId)
{
    $pdo = connect();

    // Encode some random bytes using base64
    $token = base64_encode(random_bytes(32));

    // Remove characters that are not URL-safe
    $token = str_replace(['+', '/', '='], ['-', '_', ''], $token);

    // create a registration token to change the users password
    $stmt = $pdo->prepare('INSERT INTO PasswordTokens (user_id, token) '
            . 'VALUES (:user_id, :token)');
    $stmt->bindValue(':user_id', $userId);
    $stmt->bindValue(':token', $token);
    $stmt->execute();

    return $token;
}

function fetchInitializationToken($token)
{
    $pdo = connect();

    $sql = "SELECT user_id
            FROM PasswordTokens
            WHERE
                token = :token
                AND last_updated >= NOW() - INTERVAL 7 DAY
            ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':token', $token);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row)
    {
        $user_id = $row["user_id"];

        $sql = "SELECT login, email FROM Users WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row)
        {
            $login = $row["login"];
            $email = $row["email"];

            return array($login, $email);
        }
    }
}

function removeInitializationToken($userId, $token)
{
    $pdo = connect();

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('DELETE FROM PasswordTokens WHERE token = :token');
    $stmt->bindValue(':token', $token);
    $stmt->execute();

    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('DELETE FROM PasswordTokens WHERE user_id = :user_id');
    $stmt->bindValue(':user_id', $userId);
    $stmt->execute();
}

function getNumUsersAvailableAtEvent($eventId)
{
    $pdo = connect();

    $sql = "SELECT COUNT(*) as row_count
            FROM Availabilities
            WHERE event_id = :event_id;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue("event_id", $eventId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row['row_count'];
}

function getMinUsersAtEvent($eventId)
{
    $row = getEventDetails($eventId);
    if (!$row) return false;

    return $row['minimum_users'];
}

function getEventLocked($eventId)
{
    $row = getEventDetails($eventId);
    if (!$row) return false;

    return $row['locked'];
}

function getEventDetails($eventId)
{
    $pdo = connect();

    $sql = "SELECT *
            FROM Events
            WHERE id = :event_id;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue("event_id", $eventId);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row;
}


function respondEvents($startDate, $endDate) {
    $pdo = connect();

    // Retrieve the salt from the database
    $sql = "SELECT *
            FROM Events
            WHERE date >= :start AND date <= :end;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':start', $startDate);
    $stmt->bindValue(':end', $endDate);
    $stmt->execute();

    echoEvents($pdo, $stmt);
}

function respondEvent($id) {
    $pdo = connect();

    // Retrieve the salt from the database
    $sql = "SELECT *
            FROM Events
            WHERE id = :id;";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    echoEvents($pdo, $stmt);
}

function echoEvents($pdo, $stmt)
{
    $rows = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // select users
        $sql = "SELECT
                    A.user_id,
                    A.deliberate,
                    S.event_id
                FROM
                    Availabilities A
                LEFT JOIN
                    Schedule S
                ON
                    A.user_id = S.user_id AND A.event_id = S.event_id
                WHERE
                    A.event_id = :event_id;";

        $stmt2 = $pdo->prepare($sql);
        $stmt2->bindValue("event_id", $row["id"]);
        $stmt2->execute();

        $row['id'] = intval($row['id']);
        $row['minimum_users'] = intval($row['minimum_users']);
        $row['locked'] = intval($row['locked']);

        $row["users"] = array();
        while ($userRow = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            //var_dump($userRow);

            $userRow['user_id'] = intval($userRow['user_id']);
            $userRow['deliberate'] = intval($userRow['deliberate']);
            $userRow['scheduled'] = $userRow['event_id'] ? 1 : 0;
            unset($userRow['event_id']);

            $row["users"][] = $userRow;
        }

        $rows[] = $row;
    }

    // var_dump($rows);

    echo json_encode($rows);
}

function respondUsers() {
    $pdo = connect();

    // Retrieve the data from the database
    $sql = "SELECT id, login, display_name, first_name, last_name, email, visible, active
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

        $row['id'] = intval($row['id']);
        $row['active'] = intval($row['active']);
        $row['visible'] = intval($row['visible']);

        // Retrieve oermissions
        $sql = "SELECT *
                FROM Permissions
                WHERE user_id = :user_id;";

        $stmt3 = $pdo->prepare($sql);
        $stmt3->bindValue("user_id", $row["id"]);
        $stmt3->execute();

        $permissions = array();
        while ($permissionRow = $stmt3->fetch(PDO::FETCH_ASSOC)) {
            $permissions['lock_event_schedule'] = intval($permissionRow['lock_event_schedule']);
            $permissions['manage_other_schedules'] = intval($permissionRow['manage_other_schedules']);
            $permissions['manage_events'] = intval($permissionRow['manage_events']);
            $permissions['change_other_outline_schedule'] = intval($permissionRow['change_other_outline_schedule']);
            $permissions['view_statistics'] = intval($permissionRow['view_statistics']);
            $permissions['invite_users'] = intval($permissionRow['invite_users']);
            $permissions['manage_users'] = intval($permissionRow['manage_users']);
            $permissions['delete_users'] = intval($permissionRow['delete_users']);
            $permissions['login_as_others'] = intval($permissionRow['login_as_others']);
            $permissions['manage_permissions'] = intval($permissionRow['manage_permissions']);
            $permissions['admin_dev_maintenance'] = intval($permissionRow['admin_dev_maintenance']);
        }

        $row['permissions'] = $permissions;

        $rows[] = $row;
    }

    echo json_encode($rows);
}

function dumpUsers() {
    $pdo = connect();

    // Retrieve the data from the database
    $sql = "SELECT * FROM Users;";

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

    echo '<table>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $key => $value) {
            echo "<td> $key $value </td>";
        }
        echo '</tr>';
    }
    echo '</table>';
}

function dumpPendingUsers() {
    $pdo = connect();

    // Retrieve the data from the database
    $sql = "SELECT * FROM PasswordTokens;";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $rows = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }

    echo '<table>';
    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($row as $key => $value) {
            echo "<td> $key $value </td>";
        }
        echo '</tr>';
    }
    echo '</table>';
}


?>
