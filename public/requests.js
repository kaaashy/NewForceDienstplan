/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/ClientSide/javascript.js to edit this template
 */

function getEventsOfDay() {
    
}

function createEvent() {
    
}

function requestEvents(month, year, callback) {
                
    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();

    // Set the request URL and method
    var url = 'requests.php'; // Replace with the actual server endpoint URL
    var method = 'POST';

    // Set up the request
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Set the request header if needed
    // xhr.setRequestHeader('Content-Type', 'application/json');

    // Set the request payload
    var data = 'username=' + userName;
    data = data + "&month=" + month;
    data = data + "&year=" + year;
    
    console.log(data);
    
    // Define the success callback function
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Request succeeded
            console.log(xhr.responseText);
            
            return callback(JSON.parse(xhr.responseText));            
        } else {
            // Request failed
            console.log('Error:', xhr.status);
        }
    };

    // Define the error callback function
    xhr.onerror = function() {
        console.log('Request failed.');
    };

    // Send the request
    xhr.send(data);
}


