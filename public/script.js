
// declare and fill event data
eventData = {};

requestEvents(5, 2023, function(data) {
    console.log("received data");
    
    // after querying events, rebuild calendar
    eventData = data;
    _("#calendar").innerHTML = buildCalendarHtml();
    
    addEvents(data);
});

// build the calendar
_("#calendar").innerHTML = buildCalendarHtml();


 
// short querySelector
function _(s) {
  return document.querySelector(s);
}
 
// show info
function showInfo(event) {
    
    for (var key in eventData) {
        var value = eventData[key];

        if (value.date === event) {
            _("#calendar_data").classList.toggle("show_data");
            console.log(value);
            // template info
            var data = '<a href="#" class="hideEvent" '
                + 'onclick="return hideEvent();">&times;</a>'
                + "<h3>"
                + value.type
                + "</h3>"
                + "<dl>"
                + "<dt><dfn>Titel:</dfn></dt><dd>"
                + value.title
                + "</dd>"
                + "<dt><dfn>Uhrzeit:</dfn></dt><dd>"
                + value.time
                + "</dd>"
                + "<dt><dfn>Venue:</dfn></dt><dd>"
                + value.venue
                + "</dd>"
                + "<dt><dfn>Address:</dfn></dt><dd>"
                + value.address
                + "</dd>"
                + "<dt><dfn>Description:</dfn></dt><dd>"
                + (value.description !== "" ? value.description : "[Keine Beschreibung]")
                + "</dd>"
        
                + '<dd><a href="#" title="&#xFE0F; Bearbeiten"> &#x270F; Bearbeiten</a></dd>'

                + '<form method="POST" action="">'
                + '<input type="hidden" id="id" name="id" value="'+value.id+'">'
                + '<input class="delete_event" type="submit" name="deleteevent" value="&#x1F5D1; Veranstaltung Löschen">'
                + '</form>';
                + "</dl>";
        
            return (_("#calendar_data").innerHTML = data);
        }
    }

    return false;
}

function showEventAdder(dateStr) {
    _("#calendar_data").classList.toggle("show_data");

    // template info
    var data = '<a href="#" class="hideEvent" ' 
        + 'onclick="return hideEvent();">&times;</a>' 
        + '<h3>Neue Veranstaltung</h3>'
        
        + '<div class="create_event_wrapper">'
        + '<form method="POST" action="">'
        + '<div class="input_line">'
        +   '<label for="title">Titel:</label>'
        +   '<input type="text" id="title" name="title" placeholder="Titel" required>'
        + '</div>'
        + '<div class="input_line">'
        +   '<label for="date">Datum:</label>'
        +   '<input type="date" id="date" name="date" value="'+ dateStr +'" required>'
        + '</div>'
        + '<div class="input_line">'
        +   '<label for="time">Uhrzeit:</label>'
        +   '<input type="time" id="time" name="time" value="20:00" required>'
        + '</div>'
        + '<div class="input_line">'
        +   '<label for="venue">Ort:</label>'
        +   '<input type="text" id="venue" name="venue" value="New Force" placeholder="Ort">'
        + '</div>'
        + '<div class="input_line">'
        +   '<label for="address">Adresse:</label>'
        +   '<input type="text" id="address" name="address" placeholder="Adresse" value="Buckenhofer Weg 69, 91058 Erlangen">'
        + '</div>'
        + '<div class="input_line">'
        +   '<textarea id="description" name="description" value="" rows="8" placeholder="Beschreibung"></textarea>'
        + '</div>'

        + '<div class="input_line">'
        + '<input class="create_event" type="submit" name="newevent" value="Veranstaltung Anlegen">'
        + '</div>'
        + '</div>'
        + '</form>';

    return (_("#calendar_data").innerHTML = data);
}

function addEvents(eventData) {
    
    for (var key in eventData) {
        var value = eventData[key];
        var dateStr = value.date;
        
        // if has event add class
        if (_('[data-id="' + value.date + '"]')) {
            
            var div = document.createElement("div");
            div.classList.add("calendar_event");
            div.innerHTML = '<a href="#" onclick="return showInfo(\'' + dateStr + "')\">"
                        + value.title
                        + "</a>";
                
            _('[data-id="' + value.date + '"]').appendChild(div);
        }
    }
    
    for (var i = 1; i <= 31; ++i) {
        var Calendar = new Date();
        var year = Calendar.getFullYear();
        var month = Calendar.getMonth();
        
        var dateStr = Calendar.getFullYear() 
                + "-" + String(Calendar.getMonth() + 1).padStart(2, '0') 
                + "-" + String(i).padStart(2, '0');

        if (_('[data-id="' + dateStr + '"]')) {
            
            var div = document.createElement("div");
            div.classList.add("calendar_event_adder");
            div.innerHTML = '<a href="#" onclick="return showEventAdder(\'' + dateStr + "')\">"
                + "+ Neue Veranstaltung"
                + "</a>";
                
            _('[data-id="' + dateStr + '"]').appendChild(div);
        }
    }

}




// toggle event show or hide
function hideEvent() {
  _("#calendar_data").classList.toggle("show_data");
}
 
function germanDay(calendar) {
    // input: 0-6 = Sonntag -> Montag
    // output: 0-6 = Montag -> Sonntag
    gday = calendar.getDay() - 1;
    if (gday === -1) gday = 6;
    
    return gday;
}

// simple calendar
function buildCalendarHtml() {
    // vars
    var day_of_week = new Array("Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");
    var month_of_year = new Array("Januar", "Februar", "März", "April", "Mai", "Juni", 
                                  "Juli", "August", "September", "Oktober", "November", "Dezember");
    var Calendar = new Date();
    var year = Calendar.getYear();
    var month = Calendar.getMonth();
    var today = Calendar.getDate();
    var weekday = germanDay(Calendar);
    var html = "";
 
    // start in 1 and this month
    Calendar.setDate(1);
    Calendar.setMonth(month);

    // template calendar
    html = "<table>";
    // head
    html += "<thead>";
    html +=
        '<tr class="head_cal"><th colspan="7">' +
        month_of_year[month] +
        "</th></tr>";
    html +=
        '<tr class="subhead_cal"><th colspan="7">' +
        Calendar.getFullYear() +
        "</th></tr>";
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
                var dateStr = Calendar.getFullYear() + "-" + String(Calendar.getMonth() + 1).padStart(2, '0') + "-" + String(day).padStart(2, '0');

                if (today === Calendar.getDate()) {
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
 