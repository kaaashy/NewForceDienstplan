/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/ClientSide/javascript.js to edit this template
 */

function sendUserOutlineScheduleDay(userId, day, active, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&outline_schedule=y";
    data = data + "&user_id=" + userId;
    data = data + "&day=" + day;
    data = data + "&active=" + (active ? 1 : 0);

    sendRequest(data, callback);
}

function sendUserStatus(userId, visible, active, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&user_status=y";
    data = data + "&user_id=" + userId;
    data = data + "&visible=" + (visible ? 1 : 0);
    data = data + "&active=" + (active ? 1 : 0);

    sendRequest(data, callback);
}

function sendUserEventAvailability(userId, eventId, available, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&event_availability=y";
    data = data + "&user_id=" + userId;
    data = data + "&event_id=" + eventId;
    data = data + "&available=" + (available? 1 : 0);

    sendRequest(data, callback);
}

function sendUserEventSchedule(userId, eventId, scheduled, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&event_scheduled=y";
    data = data + "&user_id=" + userId;
    data = data + "&event_id=" + eventId;
    data = data + "&scheduled=" + (scheduled? 1 : 0);

    sendRequest(data, callback);
}

function sendUserPermission(userId, permission, enabled, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&user_permission=y";
    data = data + "&user_id=" + userId;
    data = data + "&permission=" + permission;
    data = data + "&enabled=" + (enabled ? 1 : 0);

    sendRequest(data, callback);
}

function sendEventLockedStatus(eventId, locked, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&event_locked=y";
    data = data + "&event_id=" + eventId;
    data = data + "&locked=" + (locked ? 1 : 0);

    sendRequest(data, callback);
}

function sendEventDefaultData(day, type, start, end, users, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&update_event_default_data=y";
    data = data + "&day=" + day;
    data = data + "&type=" + type;
    data = data + "&start=" + start;
    data = data + "&end=" + end;
    data = data + "&minimum_users=" + users;

    sendRequest(data, callback);
}

function requestEvents(start, end, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&startDate=" + start;
    data = data + "&endDate=" + end;

    sendRequest(data, callback);
}

function requestEvent(eventId, callback) {

    var data = 'login=' + loggedInUserLogin;
    data = data + "&request_event=y";
    data = data + "&event_id=" + eventId;

    sendRequest(data, callback);
}

function requestUsers(callback) {

    // Set the request payload
    var data = 'login=' + loggedInUserLogin;
    data += "&users=y";

    sendRequest(data, callback);
}

function requestEventDefaultData(callback) {

    // Set the request payload
    var data = 'login=' + loggedInUserLogin;
    data += "&event_default_data=y";

    sendRequest(data, callback);
}

function sendRequest(data, callback) {
    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();

    // Set the request URL and method
    var url = 'js-requests.php';
    var method = 'POST';

    // Set up the request
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Define the success callback function
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Request succeeded
            let response = xhr.responseText.trim();
            // console.log("response text: '" + response + "'");

            let result = response;
            if (response !== "") {
                try {
                    result = JSON.parse(xhr.responseText);
                } catch (e) {
                    console.log(e);
                    console.log("response text: '" + response + "'");
                }
            }

            return callback(result);
        } else {
            // Request failed
            console.log('Error:', xhr.status);
        }
    };

    // Define the error callback function
    xhr.onerror = function () {
        console.log('Request failed.');
    };

    // Send the request
    xhr.send(data);
}


