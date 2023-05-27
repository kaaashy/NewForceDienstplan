

var month = (new Date()).getMonth();
var year = (new Date()).getFullYear();

if (sessionStorage.getItem('month'))
    month = parseInt(sessionStorage.getItem('month'));
if (sessionStorage.getItem('year'))
    year = parseInt(sessionStorage.getItem('year'));

sessionStorage.setItem('month', month);
sessionStorage.setItem('year', year);


function refresh() {
    // declare and fill event data
    eventData = {};

    requestEvents(month + 1, year, function(data) {
        console.log("received data");

        // after querying events, rebuild calendar
        eventData = data;
        _("#calendar").innerHTML = buildCalendarHtml(month, year);

        addEvents(data, month, year);
    });

    // build the calendar
    _("#calendar").innerHTML = buildCalendarHtml(month, year);
}

refresh();

// short querySelector
function _(s) {
  return document.querySelector(s);
}
 
// show info
function showInfo(eventId) {
    
    for (var key in eventData) {
        var event = eventData[key];

        if (event.id === eventId) {
            if (!_("#calendar_data").classList.contains("show_data")) {
                _("#calendar_data").classList.add("show_data");
            }
            
            console.log(event);
            // template info
            var data = '<a href="#" class="hideEvent" '
                + 'onclick="return hideEvent();">&times;</a>'
                + "<h3>"
                + event.type
                + "</h3>"
                + "<dl>"
                + "<dt><dfn>Titel:</dfn></dt><dd>"
                + event.title
                + "</dd>"
                + "<dt><dfn>Uhrzeit:</dfn></dt><dd>"
                + event.time
                + "</dd>"
                + "<dt><dfn>Venue:</dfn></dt><dd>"
                + event.venue
                + "</dd>"
                + "<dt><dfn>Address:</dfn></dt><dd>"
                + event.address
                + "</dd>"
                + "<dt><dfn>Description:</dfn></dt><dd>"
                + (event.description !== "" ? event.description : "[Keine Beschreibung]")
                + "</dd>"
        
                + '<dd><a href="#" title="&#xFE0F; Bearbeiten" onclick="return showEventAdder(\'\', ' + event.id + ');"> &#x270F; Bearbeiten</a></dd>'

                + '<form method="POST" action="">'
                + '<input type="hidden" id="id" name="id" value="'+event.id+'">'
                + '<input class="delete_event" type="submit" name="deleteevent" value="&#x1F5D1; Veranstaltung Löschen">'
                + '</form>';
                + "</dl>";
        
            return (_("#calendar_data").innerHTML = data);
        }
    }

    return false;
}

function showEventAdder(dateStr, id) {
    if (!_("#calendar_data").classList.contains("show_data")) {
        _("#calendar_data").classList.add("show_data");
    }

    var headline = "Neue Veranstaltung";
    var title = ""; 
    var date = dateStr; 
    var time = "20:00"; 
    var venue = "New Force"; 
    var address = "Buckenhofer Weg 69, 91058 Erlangen";
    var description = ""; 
    var buttonCaption = "Veranstaltung Anlegen";

    if (id && eventData) {
        for (var key in eventData) {
            var event = eventData[key];

            if (event.id === id) {
                title = event.title;
                date = event.date;
                time = event.time;
                venue = event.venue;
                address = event.address;
                description = event.description;
                buttonCaption = "Änderungen Speichern";
                headline = title;
            }
        }
    } else {
        id = "";
    }

    // template info
    var data = '<a href="#" class="hideEvent" ' 
        + 'onclick="return hideEvent();">&times;</a>' 
        + '<h3>' + headline + '</h3>'
        
        + '<div class="create_event_wrapper">'
        + '<form method="POST" action="">'
        + '<div class="input_line">'
        +   '<label for="title">Titel:</label>'
        +   '<input type="text" id="event_title_input" name="title" placeholder="Titel" value="'+ title +'" required>'
        + '</div>'
        + '<div class="input_line">'
        +   '<label for="date">Datum:</label>'
        +   '<input type="date" id="date" name="date" value="' + date + '" required>'
        + '</div>'
        + '<div class="input_line">'
        +   '<label for="time">Uhrzeit:</label>'
        +   '<input type="time" id="time" name="time" value="' + time + '" required>'
        + '</div>'
        + '<div class="input_line">'
        +   '<label for="venue">Ort:</label>'
        +   '<input type="text" id="venue" name="venue" value="' + venue + '" placeholder="Ort">'
        + '</div>'
        + '<div class="input_line">'
        +   '<label for="address">Adresse:</label>'
        +   '<input type="text" id="address" name="address" placeholder="Adresse" value="' + address + '">'
        + '</div>'
        + '<div class="input_line">'
        +   '<textarea id="description" name="description" value="" rows="8" value="' + description + '" ></textarea>'
        + '</div>'

        + '<input type="hidden" id="id" name="id" value="' + id + '">'
        + '<div class="input_line">'
        + '<input class="create_event" type="submit" name="newevent" value="' + buttonCaption + '">'
        + '</div>'
        + '</div>'
        + '</form>';

    setTimeout(function() {
        document.getElementById('event_title_input').focus();
    }, 100);

    return (_("#calendar_data").innerHTML = data);
}

function addEvents(eventData, month, year) {
    
    for (var key in eventData) {
        var event = eventData[key];
        
        // if has event add class
        if (_('[data-id="' + event.date + '"]')) {
            
            var div = document.createElement("div");
            div.classList.add("calendar_event");
            div.innerHTML = '<a href="#" onclick="return showInfo(' + event.id + ")\">"
                        + event.title
                        + "</a>";
                
            _('[data-id="' + event.date + '"]').appendChild(div);
        }
    }
    
    for (var i = 1; i <= 31; ++i) {        
        var dateStr = year
                + "-" + String(month + 1).padStart(2, '0') 
                + "-" + String(i).padStart(2, '0');

        if (_('[data-id="' + dateStr + '"]')) {
            
            var div = document.createElement("div");
            div.classList.add("calendar_event_adder");
            div.innerHTML = '<a href="#" onclick="return showEventAdder(\'' + dateStr + "');\">"
                + "+ Neue Veranstaltung"
                + "</a>";
                
            _('[data-id="' + dateStr + '"]').appendChild(div);
        }
    }

}


function todayMonth() {
    var today = new Date();
    month = today.getMonth();
    year = today.getFullYear();
    
    sessionStorage.setItem('month', month);
    sessionStorage.setItem('year', year);
    
    refresh();
}

function previousMonth() {
    month = month - 1; 
    if (month === -1) {
        year = year - 1; 
        month = 11;
    }
    
    sessionStorage.setItem('month', month);
    sessionStorage.setItem('year', year);
    
    refresh();
}

function nextMonth() {
    month = month + 1; 
    if (month === 12) {
        year = year + 1; 
        month = 0;
    }
    
    sessionStorage.setItem('month', month);
    sessionStorage.setItem('year', year);
    
    refresh();
}

// toggle event show or hide
function hideEvent() {
  _("#calendar_data").classList.remove("show_data");
}
 
function germanDay(calendar) {
    // input: 0-6 = Sonntag -> Montag
    // output: 0-6 = Montag -> Sonntag
    gday = calendar.getDay() - 1;
    if (gday === -1) gday = 6;
    
    return gday;
}

// simple calendar
function buildCalendarHtml(month, year) {
    // vars
    var day_of_week = new Array("Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");
    var month_of_year = new Array("Januar", "Februar", "März", "April", "Mai", "Juni", 
                                  "Juli", "August", "September", "Oktober", "November", "Dezember");
    
    var Calendar = new Date();
    var today = new Date(); 
    var weekday = germanDay(Calendar);
    var html = "";
 
    console.log(today);
 
    // start in 1 and this month
    Calendar.setDate(1);
    Calendar.setMonth(month);

    // template calendar
    html = "<table>";
    // head
    html += "<thead>";
    html += '<tr class="head_cal">';
    html += '<th colspan="2"><table>'; 
    html += '<th><a href="#" class="goto_today" onclick="return todayMonth();">Heute</a></th>';
    html += '<th><a href="#" class="cycle_month" onclick="return previousMonth();">&lt;</a></th>';
    html += '<th><a href="#" class="cycle_month" onclick="return nextMonth();">&gt;</a></th>'; 
    html += '</table></th>';
    
    html += '<th colspan="3">' + month_of_year[month] + "</th>";
    html += '<th colspan="2"></th>';
    html += '</tr>';
    
    html += '<tr class="subhead_cal"><th colspan="7">' + year + "</th></tr>";
    html += '<tr class="week_cal">';
    for (index = 0; index < 7; index++) {
        if (weekday === index) {
            html += '<th class="week_event">' + day_of_week[index] + "</th>";
        } else {
            html += "<th>" + day_of_week[index] + "</th>";
        }
    }
    
    html += "</tr>";
    html += "</thead>";

    // body
    html += '<tbody class="days_cal">';
    html += "</tr>";

    // white zone
    for (index = 0; index < germanDay(Calendar); index++) {
        html += '<td class="white_cal"> </td>';
    }

    for (index = 0; index < 31; index++) {
        if (index < Calendar.getDate()) {
            week_day = germanDay(Calendar);

            if (week_day === 0) {
                html += "</tr>";
            }

            if (week_day !== 7) {
                // this day
                var day = Calendar.getDate();
                var dateStr = year + "-" + String(month + 1).padStart(2, '0') + "-" + String(day).padStart(2, '0');

                if (today.getTime() === Calendar.getTime()) {
                    html +=
                        '<td><div class="today calendar_day" data-id="' + dateStr + '">'
                        + "<span>"+day+"</span>";
                } else {
                    html +=
                        '<td><div class="calendar_day" data-id="' + dateStr + '">'
                        + "<span>"+day+"</span>";
                }
                
                html += "</div></td>";
            }

            if (week_day == 7) {
                html += "</tr>";
            }
        }

        Calendar.setDate(Calendar.getDate() + 1);
    } // end for loop

    return html;
}
 