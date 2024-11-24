<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 'on');

include_once 'initdb.php';

if(!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true)){
    echo 'ERROR_NOT_LOGGED_IN';
    exit;
}

if (isset($_POST['startDate']) && isset($_POST['endDate'])) {
    $startDate = filter_input(INPUT_POST, 'startDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $endDate = filter_input(INPUT_POST, 'endDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    respondEvents($startDate, $endDate);
    return;
}

if (isset($_POST['request_event'])) {
    $id = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
    
    if ($id) {
        respondEvent($id);
        return;
    }
}

if (isset($_POST['users'])) {
    respondUsers();
    return;
}

if (isset($_POST['outline_schedule'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $day = filter_input(INPUT_POST, 'day', FILTER_SANITIZE_NUMBER_INT);
    $active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT) > 0;

    $callingUser = $_SESSION['user_id'];
    $managingPermitted = ($userId == $callingUser) || getUserHasPermission($callingUser, 'change_other_outline_schedule');

    if (!$managingPermitted) {
        echo 'ERROR_NO_PERMISSION_FOR_OUTLINE_SCHEDULING';
        return;
    }

    updateOutlineDay($userId, $day, $active);
    return;
}

if (isset($_POST['user_status'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $visible = filter_input(INPUT_POST, 'visible', FILTER_SANITIZE_NUMBER_INT) > 0;
    $active = filter_input(INPUT_POST, 'active', FILTER_SANITIZE_NUMBER_INT) > 0;

    if (!$active && $_SESSION['user_id'] == $userId) {
        echo 'ERROR_CANT_DISABLE_SELF';
        return;
    }

    $callingUser = $_SESSION['user_id'];
    $managingPermitted = getUserHasPermission($callingUser, 'manage_users');
    if (!$managingPermitted) {
        echo 'ERROR_NO_PERMISSION_FOR_USER_MANAGEMENT';
        return;
    }

    if (!$active) $visible = false;

    updateUserStatus($userId, $visible, $active);

    // when a user is set to "inactive" or "invisible", they're removed from the outline schedule
    if (!$active || !$visible) {
        for ($day = 0; $day < 7; ++$day) {
            updateOutlineDay($userId, $day, false);
        }
    }

    return;
}

if (isset($_POST['user_permission'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $permission = trim($_POST['permission']);
    $enabled = filter_input(INPUT_POST, 'enabled', FILTER_SANITIZE_NUMBER_INT) > 0;

    $callingUser = $_SESSION['user_id'];
    $managingPermitted = getUserHasPermission($callingUser, 'manage_permissions');
    if (!$managingPermitted) {
        echo 'ERROR_NO_PERMISSION_FOR_PERMISSIONS';
        return;
    }

    if ($userId == $callingUser && $permission == 'manage_permissions' && !$enabled) {
        echo 'ERROR_NO_SELF_PERMISSION_REMOVAL';
        return;
    }

    updateUserPermission($userId, $permission, $enabled);
    return;
}

if (isset($_POST['event_availability'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
    $available = filter_input(INPUT_POST, 'available', FILTER_SANITIZE_NUMBER_INT) > 0;
    $deliberate = true;
    
    $callingUser = $_SESSION['user_id'];
    $managingPermitted = ($userId == $callingUser) || getUserHasPermission($callingUser, 'manage_other_schedules');

    if (!$managingPermitted) {
        echo 'ERROR_NO_PERMISSION_FOR_EVENT_SCHEDULING';
        return;
    }

    if (getEventLocked($eventId)) {
        echo 'ERROR_EVENT_LOCKED';
        return;
    }

    if (!$available) {
        $users = getNumUsersAvailableAtEvent($eventId);
        $minUsers = getMinUsersAtEvent($eventId);
        if ($users <= $minUsers) {
            echo 'ERROR_NOT_ENOUGH_USERS';
            return;
        }
    }

    updateEventAvailability($userId, $eventId, $deliberate, $available);
    return;
}

if (isset($_POST['event_scheduled'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
    $scheduled = filter_input(INPUT_POST, 'scheduled', FILTER_SANITIZE_NUMBER_INT) > 0;

    $callingUser = $_SESSION['user_id'];
    $managingPermitted = ($userId == $callingUser) || getUserHasPermission($callingUser, 'manage_other_schedules');

    if (!$managingPermitted) {
        echo 'ERROR_NO_PERMISSION_FOR_EVENT_SCHEDULING';
        return;
    }

    if (getEventLocked($eventId)) {
        echo 'ERROR_EVENT_LOCKED';
        return;
    }

    updateUserEventSchedule($userId, $eventId, $scheduled);
    return;
}

if (isset($_POST['event_locked'])) {
    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_NUMBER_INT);
    $locked = filter_input(INPUT_POST, 'locked', FILTER_SANITIZE_NUMBER_INT) > 0;

    $callingUser = $_SESSION['user_id'];
    $managingPermitted = getUserHasPermission($callingUser, 'lock_event_schedule');
    if (!$managingPermitted) {
        echo 'ERROR_NO_PERMISSION_FOR_EVENT_LOCKING';
        return;
    }

    setEventLockedStatus($eventId, $locked);
    return;
}




?>
