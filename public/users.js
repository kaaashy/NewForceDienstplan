
let userData = {};


// short querySelector
function _(s) {
  return document.querySelector(s);
}

function refresh() {
    // declare and fill event data
    userData = {};

    requestUsers(function(data) {
        console.log("received users");
        userData = {};
        for (let i in data) {
            let user = data[i];
            userData[user.id] = user;
        }
        
        _("#user_index").innerHTML = buildIndexHtml();
    });

    // build the calendar
    _("#user_index").innerHTML = buildIndexHtml();
}

refresh();

function buildIndexHtml()
{   
    let html = "";
    
    html += '<h2>Rahmendienstplan</h2>';
    html += '<table class="outline_schedule">';
    html += '<tr>';
    html += '<th>Name</th>';
    html += '<th>Montag</th>';
    html += '<th>Dienstag</th>';
    html += '<th>Mittwoch</th>';
    html += '<th>Donnerstag</th>';
    html += '<th>Freitag</th>';
    html += '<th>Samstag</th>';
    html += '<th>Sonntag</th>';
    html += '</tr>';
    
    let outlineDay = function(value) {
        if (value) return "Ja";
        return "";
    };
    
    for (let id in userData) {
        let user = userData[id]; 
        
        html += '<tr>';
        html += '<td>'+user.display_name+'</td>';
        html += '<td>'+outlineDay(user.day_0)+'</td>';
        html += '<td>'+outlineDay(user.day_1)+'</td>';
        html += '<td>'+outlineDay(user.day_2)+'</td>';
        html += '<td>'+outlineDay(user.day_3)+'</td>';
        html += '<td>'+outlineDay(user.day_4)+'</td>';
        html += '<td>'+outlineDay(user.day_5)+'</td>';
        html += '<td>'+outlineDay(user.day_6)+'</td>';
        html += '</tr>';
    }
    
    html += '</table>';
    
    html += '<h2>Mitarbeitendenübersicht</h2>';
    html += '<table class="user_overview">';
    html += '<tr>';
    html += '<th>Id</th>';
    html += '<th>Username</th>';
    html += '<th>Anzeigename</th>';
    html += '<th>Vorname</th>';
    html += '<th>Nachname</th>';
    html += '<th>Email</th>';
    html += '</tr>';
    
    let toDisplay = function(value) {
        if (value) return value;
        return "-";
    };

    for (let id in userData) {
        let user = userData[id]; 
        
        html += '<tr>';
        html += '<td>'+id+'</td>';
        html += '<td>'+toDisplay(user.display_name)+'</td>';
        html += '<td>'+toDisplay(user.display_name)+'</td>';
        html += '<td>'+toDisplay(user.first_name)+'</td>';
        html += '<td>'+toDisplay(user.last_name)+'</td>';
        html += '<td>'+toDisplay(user.email)+'</td>';
        html += '</tr>';
    }
    
    html += '</table>';
    
    
    
    return html; 
}