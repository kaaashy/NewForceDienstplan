

var month = (new Date()).getMonth();
var year = (new Date()).getFullYear();

if (sessionStorage.getItem('month'))
    month = parseInt(sessionStorage.getItem('month'));
if (sessionStorage.getItem('year'))
    year = parseInt(sessionStorage.getItem('year'));

sessionStorage.setItem('month', month);
sessionStorage.setItem('year', year);


let mode = "week";

// start of the month
var monthStart = new Date();
monthStart.setDate(1);
if (sessionStorage.getItem('monthStart'))
    monthStart = new Date(Date.parse(sessionStorage.getItem('monthStart')));
sessionStorage.setItem('monthStart', getPaddedDateString(monthStart));

// start of the week
var weekStart = new Date();
weekStart = getStartOfWeek(weekStart);
if (sessionStorage.getItem('weekStart'))
    weekStart = new Date(Date.parse(sessionStorage.getItem('weekStart')));
sessionStorage.setItem('weekStart', getPaddedDateString(weekStart));

function refresh() {
    // declare and fill event data
    eventData = {};
    userData = {};

    requestEvents(month + 1, year, function (data) {
        console.log("received events");

        // after querying events, rebuild calendar
        eventData = data;
        _("#calendar").innerHTML = buildCalendarHtml(month, year);

        addEvents(data, month, year);
    });

    requestUsers(function (data) {
        console.log("received users");
        userData = {};
        for (let i in data) {
            let user = data[i];
            userData[user.id] = user;
        }

        console.log(userData);
    });

    // build the calendar
    _("#calendar").innerHTML = buildCalendarHtml(month, year);
}

refresh();

// short querySelector
function _(s) {
    return document.querySelector(s);
}

var endTimes = {};
endTimes[0] = "00:00";
endTimes[1] = "00:00";
endTimes[2] = "00:00";
endTimes[3] = "00:00";
endTimes[4] = "02:00";
endTimes[5] = "02:00";
endTimes[6] = "00:00";

function showEvent(dateStr, id) {
    if (!_("#calendar_data").classList.contains("show_data")) {
        _("#calendar_data").classList.add("show_data");
    }

    let headline = "Neue Veranstaltung";
    let title = "";
    let date = dateStr;
    let time = "20:00";
    let endTime = endTimes[getGermanWeekDay(new Date(dateStr))];
    let venue = "New Force";
    let address = "Buckenhofer Weg 69, 91058 Erlangen";
    let description = "";
    let buttonCaption = "Veranstaltung Anlegen";
    let deleteButton = "";
    let eventUsers = "";
    let nonEventUsers = "";
    let dateFlags = "required";

    if (id && eventData) {
        for (let key in eventData) {
            let event = eventData[key];

            if (event.id === id) {
                title = event.title;
                date = event.date;
                time = event.time;
                endTime = event.end_time;
                venue = event.venue;
                address = event.address;
                description = event.description;
                buttonCaption = "Änderungen Speichern";
                headline = title;
                dateFlags = "disabled";
                deleteButton = '<input class="delete_event" type="submit" name="deleteevent" value="&#x1F5D1; Löschen">';

                let remainingUsers = new Set();
                for (let uid in userData) {
                    remainingUsers.add(userData[uid].id);
                }

                for (let i in event.users) {
                    let eventUser = event.users[i];
                    let user = userData[eventUser.user_id];
                    remainingUsers.delete(eventUser.user_id);

                    if (user) {
                        if (eventUser.deliberate) {
                            eventUsers += '<div class="deliberate_event_user">' + user.display_name + '</div>';
                        } else {
                            eventUsers += '<div class="event_user">' + user.display_name + '</div>';
                        }
                    }
                }

                for (const uid of remainingUsers) {
                    let user = userData[uid];
                    if (user) {
                        nonEventUsers += '<div class="deliberate_event_user">' + user.display_name + '</div>';
                    }
                }
            }
        }
    } else {
        id = "";
    }

    console.log(id);

    // template info
    let data = '<a href="#" class="hideEvent" '
            + 'onclick="return hideEvent();">&times;</a>'
            + '<h3>' + headline + '</h3>'

            + '<div class="create_event_wrapper">'
            + '<form method="POST" action="">'
            + '<div class="input_line">'
            + '<label for="title">Titel:</label>'
            + '<input type="text" id="event_title_input" name="title" placeholder="Titel" value="' + title + '" required>'
            + '</div>'
            + '<div class="input_line">'
            + '<label for="date">Datum:</label>'
            + '<input type="date" id="date" name="date" value="' + date + '" ' + dateFlags + '>'
            + '</div>'
            + '<div class="input_line">'
            + '<label for="time">Start:</label>'
            + '<input type="time" id="time" name="time" value="' + time + '" required>'
            + '</div>'
            + '<div class="input_line">'
            + '<label for="time">Ende:</label>'
            + '<input type="time" id="end_time" name="end_time" value="' + endTime + '" required>'
            + '</div>'
            + '<div class="input_line">'
            + '<label for="venue">Ort:</label>'
            + '<input type="text" id="venue" name="venue" value="' + venue + '" placeholder="Ort">'
            + '</div>'
            + '<div class="input_line">'
            + '<label for="address">Adresse:</label>'
            + '<input type="text" id="address" name="address" placeholder="Adresse" value="' + address + '">'
            + '</div>'
            + '<div class="input_line">'
            + '<textarea id="description" name="description" placeholder="Beschreibung" rows="8">' + description + '</textarea>'
            + '</div>'

            + '<table class="userlist">'
            + '<tr>'
            + '<th>Eingetragen</th>'
            + '<th>Existent</th>'
            + '</tr>'
            + '<tr>'
            + '<td>' + eventUsers + '</td>'
            + '<td>' + nonEventUsers + '</td>'
            + '</tr>'
            + '</table>'


            + '<input type="hidden" id="id" name="id" value="' + id + '">'
            + '<div class="input_line">'
            + '<input class="create_event" type="submit" name="newevent" value="' + buttonCaption + '">'
            + deleteButton
            + '</div>'
            + '</div>'
            + '</form>';

    setTimeout(function () {
        document.getElementById('event_title_input').focus();
    }, 100);

    return (_("#calendar_data").innerHTML = data);
}

function addEvents(eventData, month, year) {

    for (let key in eventData) {
        let event = eventData[key];

        // if has event add class
        if (_('[data-id="' + event.date + '"]')) {

            let div = document.createElement("div");
            div.classList.add("calendar_event");
            div.innerHTML = '<a href="#" onclick="return showEvent(\'\', ' + event.id + ")\">"
                    + event.title
                    + "</a>";

            _('[data-id="' + event.date + '"]').appendChild(div);
        }
    }

    // add little blobs for creating a new event
    let today = new Date();
    for (let i = 1; i <= 31; ++i) {
        let dateStr = year
                + "-" + String(month + 1).padStart(2, '0')
                + "-" + String(i).padStart(2, '0');

        if (_('[data-id="' + dateStr + '"]')) {
            let div = document.createElement("div");
            div.classList.add("calendar_event_adder");
            div.innerHTML = '<a href="#" onclick="return showEvent(\'' + dateStr + "');\">"
                    + "+ Neue Veranstaltung"
                    + "</a>";

            _('[data-id="' + dateStr + '"]').appendChild(div);
        }
    }

}

function gotoToday() {
    if (mode === "week") {
        weekStart = getStartOfWeek(new Date());
        sessionStorage.setItem('weekStart', getPaddedDateString(weekStart));
    } else {
        let today = new Date();
        month = today.getMonth();
        year = today.getFullYear();

        sessionStorage.setItem('month', month);
        sessionStorage.setItem('year', year);
    }

    refresh();
}

function gotoPrevious() {
    if (mode === "week") {
        weekStart.setDate(weekStart.getDate() - 7);
        sessionStorage.setItem('weekStart', getPaddedDateString(weekStart));
    } else {
        month = month - 1;
        if (month === -1) {
            year = year - 1;
            month = 11;
        }

        sessionStorage.setItem('month', month);
        sessionStorage.setItem('year', year);
    }

    refresh();
}

function gotoNext() {
    if (mode === "week") {
        weekStart.setDate(weekStart.getDate() + 7);
        sessionStorage.setItem('weekStart', getPaddedDateString(weekStart));
    } else {
        month = month + 1;
        if (month === 12) {
            year = year + 1;
            month = 0;
        }

        sessionStorage.setItem('month', month);
        sessionStorage.setItem('year', year);
    }

    refresh();
}

function getPaddedDateString(date) {
    let paddedDay = String(date.getDate()).padStart(2, '0');
    let paddedMonth = String(date.getMonth() + 1).padStart(2, '0');
    return date.getFullYear() + "-" + paddedMonth + "-" + paddedDay;
}


// toggle event show or hide
function hideEvent() {
    _("#calendar_data").classList.remove("show_data");
}

function getGermanWeekDay(date) {
    // input: 0-6 = Sonntag -> Montag
    // output: 0-6 = Montag -> Sonntag
    gday = date.getDay() - 1;
    if (gday === -1)
        gday = 6;

    return gday;
}

function getStartOfWeek(date) {
    let start = new Date(date);
    start.setDate(date.getDate() - getGermanWeekDay(date));
    return start;
}

function buildCalendarHtml(month, year) {
    const startOfMonth = new Date();
    startOfMonth.setDate(1);

    if (mode === "week") {
        return buildWeeklyCalendarHtml(weekStart, year);
    } else {
        return buildMonthlyCalendarHtml(month, year);
    }
}

function buildWeeklyCalendarHtml(weekStart) {
    let html = "";

    // template calendar
    html = "<table>";

    let weekEnding = new Date(weekStart);
    weekEnding.setDate(weekEnding.getDate() + 6);
    html += buildCalendarHead(weekStart.getMonth(), weekEnding.getMonth(), weekStart.getFullYear());

    // body
    html += '<tbody class="days_cal">';
    html += "</tr>";

    const today = new Date();

    // start in 1 and this month
    let date = new Date(weekStart);

    // white zone
    for (let i = 0; i < getGermanWeekDay(date); i++) {
        html += '<td class="white_cal"> </td>';
    }

    html += "<tr>";

    for (let i = 0; i < 7; i++) {

        // this day
        let paddedDay = String(date.getDate()).padStart(2, '0');
        let paddedMonth = String(date.getMonth() + 1).padStart(2, '0');
        let dateStr = getPaddedDateString(date);

        if (getPaddedDateString(today) === getPaddedDateString(date)) {
            html += '<td><div class="today calendar_day" data-id="' + dateStr + '">'
                    + "<span>" + paddedDay + "." + paddedMonth + "." + "</span>";
        } else {
            html += '<td><div class="calendar_day" data-id="' + dateStr + '">'
                    + "<span>" + paddedDay + "." + paddedMonth + "." + "</span>";

            if (today.getTime() > date.getTime()) {
                html += '<div class="past_day_overlay"></div>';
            }
        }

        html += "</div></td>";

        date.setDate(date.getDate() + 1);
    } // end for loop

    html += '</table>';

    return html;
}

function buildCalendarHead(month1, month2, year) {
    let html = "";

    const today = new Date();
    const weekday = getGermanWeekDay(today);

    var day_of_week = new Array("Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");
    var month_of_year = new Array("Januar", "Februar", "März", "April", "Mai", "Juni",
            "Juli", "August", "September", "Oktober", "November", "Dezember");

    // head
    html += "<thead>";
    html += '<tr class="head_cal">';
    html += '<th colspan="2"><table>';
    html += '<th><a href="#" class="goto_today" onclick="return gotoToday();">Heute</a></th>';
    html += '<th><a href="#" class="cycle_month" onclick="return gotoPrevious();">&lt;</a></th>';
    html += '<th><a href="#" class="cycle_month" onclick="return gotoNext();">&gt;</a></th>';
    html += '</table></th>';

    if (month1 === month2) {
        html += '<th colspan="3">' + month_of_year[month1] + "</th>";
    } else {
        html += '<th colspan="3">' + month_of_year[month1] + " - " + month_of_year[month2] + "</th>";
    }

    html += '<th colspan="2"></th>';
    html += '</tr>';

    html += '<tr class="subhead_cal"><th colspan="7">' + year + "</th></tr>";
    html += '<tr class="week_cal">';
    for (let index = 0; index < 7; index++) {
        if (weekday === index && (month1 === today.getMonth() || month2 === today.getMonth())) {
            html += '<th class="week_event">' + day_of_week[index] + "</th>";
        } else {
            html += "<th>" + day_of_week[index] + "</th>";
        }
    }

    html += "</tr>";
    html += "</thead>";

    return html;
}

function buildMonthlyCalendarHtml(month, year) {
    let html = "";

    // template calendar
    html = "<table>";

    // head
    html += buildCalendarHead(month, month, year);

    // body
    html += '<tbody class="days_cal">';
    html += "</tr>";

    const today = new Date();

    // start in 1 and this month
    let date = new Date();
    date.setDate(1);
    date.setMonth(month);

    // white zone
    for (index = 0; index < getGermanWeekDay(date); index++) {
        html += '<td class="white_cal"> </td>';
    }

    for (index = 0; index < 31; index++) {
        if (index < date.getDate()) {
            week_day = getGermanWeekDay(date);

            if (week_day === 0) {
                html += "</tr>";
            }

            if (week_day !== 7) {
                // this day
                let day = date.getDate();
                let dateStr = year + "-" + String(month + 1).padStart(2, '0') + "-" + String(day).padStart(2, '0');

                if (today.getTime() === date.getTime()) {
                    html +=
                            '<td><div class="today calendar_day" data-id="' + dateStr + '">'
                            + "<span>" + day + "</span>";
                } else {
                    html +=
                            '<td><div class="calendar_day" data-id="' + dateStr + '">'
                            + "<span>" + day + "</span>";

                    if (today.getTime() > date.getTime()) {
                        html += '<div class="past_day_overlay"></div>';
                    }
                }

                html += "</div></td>";
            }

            if (week_day == 7) {
                html += "</tr>";
            }
        }

        date.setDate(date.getDate() + 1);
    } // end for loop

    html += "</table>";

    return html;
}


