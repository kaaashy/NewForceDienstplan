/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/ClientSide/javascript.js to edit this template
 */

function getEventsOfDay() {
    
}

function createEvent() {
    
}

function getEventsOfMonth(month, year) {
    return {
        "5/7/2023" : {
            type : "Veranstaltung",
            title : "Großputz",
            time : "16:00",
            venue : "NewForce",
            location : "Buckenhofer Weg 69",
            desc : "Wir putzen ihr Spasten",
            more : "Mehr Info gibts net"
        },
        "5/6/2023" : {
            type : "Veranstaltung",
            title : "Blasts In Brucklyn",
            time : "20:00",
            venue : "NewForce",
            location : "Buckenhofer Weg 69",
            desc : "Blasts In Brucklyn",
            more : "Fette Blasts"
        },
        "5/5/2023" : {
            type : "Veranstaltung",
            title : "Masters Of Metal",
            time : "20:00",
            venue : "NewForce",
            location : "Buckenhofer Weg 69",
            desc : "Heavy, Pagan, Power",
            more : "Fette Blasts"
        }

    }; 
}


function requestEvents() {
                
    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();

    // Set the request URL and method
    var url = 'requests.php'; // Replace with the actual server endpoint URL
    var method = 'POST';

    // Set up the request
    xhr.open(method, url, true);

    // Set the request header if needed
    // xhr.setRequestHeader('Content-Type', 'application/json');

    // Set the request payload
    var data = 'username=' + userName;

    // Define the success callback function
    xhr.onload = function() {
      if (xhr.status === 200) {
        // Request succeeded
        console.log(xhr.responseText);
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


