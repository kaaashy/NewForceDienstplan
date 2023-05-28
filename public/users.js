
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

function setOutlineForUser(id, day) {
    
    let checkBoxId = 'outline_check_'+id+'_'+day;
    let checked = _("#"+checkBoxId).checked; 
    
        console.log(id);
    console.log(day);
    console.log(checked);

    sendUserOutlineScheduleDay(id, day, checked, function(){console.log("sent"); refresh();});
}

function buildIndexHtml()
{   
    let html = "";
    
    html += '<a href="dienstplan.php"> &lt;&lt; Zurück zur Übersicht</a>';
    
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
    
    let outlineDay = function(value, id, day) {
        let v = value ? "checked" : ""; 
        
        return '<input type="checkbox" id="outline_check_'+id+'_'+day+'" onclick=setOutlineForUser('+id+','+day+') '+v+'>';
    };
    
    for (let id in userData) {
        let user = userData[id]; 
        
        html += '<tr>';
        html += '<td>'+user.display_name+'</td>';
        html += '<td>'+outlineDay(user.day_0, id, 0)+'</td>';
        html += '<td>'+outlineDay(user.day_1, id, 1)+'</td>';
        html += '<td>'+outlineDay(user.day_2, id, 2)+'</td>';
        html += '<td>'+outlineDay(user.day_3, id, 3)+'</td>';
        html += '<td>'+outlineDay(user.day_4, id, 4)+'</td>';
        html += '<td>'+outlineDay(user.day_5, id, 5)+'</td>';
        html += '<td>'+outlineDay(user.day_6, id, 6)+'</td>';
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