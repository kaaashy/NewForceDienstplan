
// start date
var startDate = new Date();
startDate = getStartOfWeek(startDate);
if (sessionStorage.getItem('startDate'))
    startDate = new Date(Date.parse(sessionStorage.getItem('startDate')));
sessionStorage.setItem('startDate', getPaddedDateString(startDate));

const monthMode = "month";
const weekMode = "week";
let mode = monthMode;

if (sessionStorage.getItem('mode') && mode !== sessionStorage.getItem('mode')) {
    mode = sessionStorage.getItem('mode');
}

var refreshCounter = 0;
var dataReceived = 0;

var currentEventId = null;


function onEventsReceived(refresh) {
    console.log(refresh);
    console.log(refreshCounter);

    if (refresh !== refreshCounter)
        return;

    // it's important we only start adding events once all data was received
    // to avoid flickering and wrong events
    dataReceived++;
    if (dataReceived === 2) {
        _("#calendar").innerHTML = buildCalendarHtml();
        addEvents(eventData, startDate);
    }
}

function refresh() {
    // declare and fill event data
    eventData = {};
    userData = {};

    if (mode === weekMode) {
        endDate = new Date(startDate);
        endDate.setDate(endDate.getDate() + 7);
    } else {
        endDate = new Date(startDate);
        endDate.setMonth(endDate.getMonth() + 1);
    }

    let start = getPaddedDateString(startDate);
    let end = getPaddedDateString(endDate);

    refreshCounter++;
    let counter = refreshCounter;
    dataReceived = 0;

    requestUsers(function (data) {
        console.log("received users");
        userData = {};
        for (let i in data) {
            let user = data[i];
            userData[user.id] = user;
        }

        onEventsReceived(counter);
    });

    requestEvents(start, end, function (data) {
        console.log("received events");

        // after querying events, rebuild calendar
        eventData = data;

        onEventsReceived(counter);
    });

    // build the calendar
    _("#calendar").innerHTML = buildCalendarHtml();
}

refresh();

function refreshEvent(eventId) {
    requestEvent(eventId, function (receivedEvents) {
        console.log("received event");

        let receivedEvent = receivedEvents[0];

        for (let key in eventData) {
            let event = eventData[key];

            if (event.id === receivedEvent.id) {
                eventData[key] = receivedEvent;
            }
        }

        let element = _('[data-id="' + receivedEvent.id + '"]');
        if (element)
            element.innerHTML = buildCalendarEventHtml(receivedEvent);

        showEvent(receivedEvent.date, receivedEvent.id);
    });
}


// short querySelector
function _(s) {
    return document.querySelector(s);
}

function insertIntoSchedule(userId) {
    if (!userId) {
        userId = loggedInUserId;
    }

    sendUserEventActivity(userId, currentEventId, true, function () {
        refreshEvent(currentEventId);
    });

    console.log("insert into event " + currentEventId);
}

function removeFromSchedule(userId) {
    if (!userId) {
        userId = loggedInUserId;
    }

    sendUserEventActivity(userId, currentEventId, false, function () {
        refreshEvent(currentEventId);
    });

    console.log("remove from event " + currentEventId);
}

function showEvent(dateStr, id) {
    if (!_("#calendar_data").classList.contains("show_data")) {
        _("#calendar_data").classList.add("show_data");
    }

    let endTimes = {};
    endTimes[0] = "00:00";
    endTimes[1] = "00:00";
    endTimes[2] = "00:00";
    endTimes[3] = "00:00";
    endTimes[4] = "02:00";
    endTimes[5] = "02:00";
    endTimes[6] = "00:00";

    let minUsersOfDay = {};
    minUsersOfDay[0] = "3";
    minUsersOfDay[1] = "3";
    minUsersOfDay[2] = "3";
    minUsersOfDay[3] = "3";
    minUsersOfDay[4] = "4";
    minUsersOfDay[5] = "4";
    minUsersOfDay[6] = "3";

    let headline = "Neue Veranstaltung";
    let title = "";
    let date = dateStr;
    let time = "20:00";
    let endTime = endTimes[getGermanWeekDay(new Date(dateStr))];
    let users = 0;
    let minUsers = minUsersOfDay[getGermanWeekDay(new Date(dateStr))];
    let organizer = "";
    let venue = "New Force";
    let address = "Buckenhofer Weg 69, 91058 Erlangen";
    let description = "";
    let buttonCaption = "Veranstaltung Anlegen";
    let deleteButton = "";
    let eventUsers = "";
    let nonEventUsers = "";
    let dateFlags = "required";
    let selfInUserList = false;

    currentEventId = null;

    if (id && eventData) {
        for (let key in eventData) {
            let event = eventData[key];

            if (event.id === id) {
                currentEventId = id;

                title = event.title;
                date = event.date;
                time = event.time;
                endTime = event.end_time;
                users = event.users.length;
                minUsers = event.minimum_users;
                organizer = event.organizer;
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

                    if (eventUser.user_id === loggedInUserId)
                        selfInUserList = true;

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

    let addRemoveButtons = "";
    if (id !== "") {

        let addDisabled = "";
        if (selfInUserList)
            addDisabled = "disabled";

        let removeDisabled = "";
        if (!selfInUserList || users <= minUsers)
            removeDisabled = "disabled";

        addRemoveButtons += '<tr>'
                + '<td><button type="button" class="schedule_insert" onclick="return insertIntoSchedule();"' + addDisabled + '>Für Dienst Eintragen</button></td>'
                + '<td><button type="button" class="schedule_remove" onclick="return removeFromSchedule();"' + removeDisabled + '>Aus Dienst Austragen</button></td>'
                + '</tr>';
    }

    // template info
    let data = '<a href="#" class="hideEvent" '
            + 'onclick="return hideEvent();">&times;</a>'
            + '<h3>' + headline + '</h3>'

            + '<div class="create_event_wrapper">'
            + '<form method="POST" action="">'

            + '<table class="userlist">'
            + '<tr>'
            + '<th>Eingetragen</th>'
            + '<th>Rest</th>'
            + '</tr>'
            + '<tr>'
            + '<td>' + eventUsers + '</td>'
            + '<td>' + nonEventUsers + '</td>'
            + '</tr>'
            + addRemoveButtons
            + '</table>'

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
            + '<label for="organizer">Verantwortlich:</label>'
            + '<input type="text" id="organizer" name="organizer" placeholder="Verantwortlich" value="' + organizer + '">'
            + '</div>'
            + '<div class="input_line">'
            + '<label for="minimum_users">Mindest-Mitarbeitende:</label>'
            + '<input type="number" id="minimum_users" name="minimum_users" min="0" value="' + minUsers + '">'
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

function buildEventAssigneeOverview(event) {
    let html = "";

    console.log(event);

    html += '<table class="user_listing">';

    for (let i in event.users) {
        let eventUser = event.users[i];
        let user = userData[eventUser.user_id];

        if (user) {
            if (eventUser.deliberate) {
                html += '<tr><td><div class="deliberate_event_user">' + user.display_name + '</div></td></tr>';
            } else {
                html += '<tr><td><div class="event_user">' + user.display_name + '*</div></td></tr>';
            }
        }
    }
    html += "</table>";

    return html;
}

function buildCalendarEventHtml(event) {

    let readableTime = function (str) {
        if (!str)
            return "";

        return str.slice(0, -3);
    };

    let usersOverview = function (event) {
        let users = event.users.length;
        let min = event.minimum_users;

        let allGood = "good";
        if (users === min - 1)
            allGood = "warning";
        if (users < min - 1)
            allGood = "bad";

        let html = '<span class="' + allGood + '">';
        if (users > min)
            html += users;
        else
            html += users + '/' + min;

        html += ' MA</span>';
        return html;
    };

    let assignedUsers = "";
    if (mode === weekMode)
        assignedUsers = buildEventAssigneeOverview(event);

    return '<a href="#" onclick="return showEvent(\'\', ' + event.id + ")\">"
            + '<span class="event_title">' + event.title + '</span>'
            + '<span class="event_time">' + readableTime(event.time)
            + " bis " + readableTime(event.end_time) + '</span>'
            + usersOverview(event)
            + assignedUsers
            + "</a>";

}

function addEvents(eventData, startDate) {

    for (let key in eventData) {
        let event = eventData[key];

        // if has event add class
        if (_('[data-id="' + event.date + '"]')) {

            let div = document.createElement("div");
            div.classList.add("calendar_event");
            div.setAttribute("data-id", event.id);

            div.innerHTML = buildCalendarEventHtml(event);

            _('[data-id="' + event.date + '"]').appendChild(div);
        }
    }

    // add little blobs for creating a new event
    let date = new Date(startDate);
    for (let i = 0; i <= 45; ++i) {
        let dateStr = getPaddedDateString(date);

        if (_('[data-id="' + dateStr + '"]')) {
            let div = document.createElement("div");
            div.classList.add("calendar_event_adder");
            div.innerHTML = '<a href="#" onclick="return showEvent(\'' + dateStr + "');\">"
                    + "+ Neue Veranstaltung"
                    + "</a>";

            _('[data-id="' + dateStr + '"]').appendChild(div);
        }

        date.setDate(date.getDate() + 1);
    }

}

function gotoToday() {
    if (mode === weekMode) {
        startDate = getStartOfWeek(new Date());
    } else {
        startDate = new Date();
        startDate.setDate(1);
    }

    sessionStorage.setItem('startDate', getPaddedDateString(startDate));

    refresh();
}

function gotoPrevious() {
    if (mode === weekMode) {
        startDate.setDate(startDate.getDate() - 7);
    } else {
        startDate.setMonth(startDate.getMonth() - 1);
    }
    sessionStorage.setItem('startDate', getPaddedDateString(startDate));

    refresh();
}

function gotoNext() {
    if (mode === weekMode) {
        startDate.setDate(startDate.getDate() + 7);
    } else {
        startDate.setMonth(startDate.getMonth() + 1);
    }
    sessionStorage.setItem('startDate', getPaddedDateString(startDate));

    refresh();
}

function setMode(newMode) {
    mode = newMode;

    if (mode === weekMode) {
        let today = new Date();
        if (today.getMonth() === startDate.getMonth()) {
            startDate = getStartOfWeek(today);
        } else {
            startDate = getStartOfWeek(startDate);
        }
    } else {
        startDate.setDate(1);
    }

    sessionStorage.setItem('mode', mode);
    sessionStorage.setItem('startDate', getPaddedDateString(startDate));

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

function buildCalendarHtml() {
    if (mode === weekMode) {
        return buildWeeklyCalendarHtml(startDate);
    } else {
        return buildMonthlyCalendarHtml(startDate);
    }
}

function buildWeeklyCalendarHtml(startDate) {
    let html = "";

    html += buildNavHtml();

    // template calendar
    html += "<table>";

    let weekEnding = new Date(startDate);
    weekEnding.setDate(weekEnding.getDate() + 6);
    html += buildCalendarHead(startDate.getMonth(), weekEnding.getMonth(), startDate.getFullYear());

    // body
    html += '<tbody class="days_cal">';

    const today = new Date();

    // start in 1 and this month
    let date = new Date(startDate);

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
            html += '<td class="today"><div class="calendar_day" data-id="' + dateStr + '">'
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
    }

    html += '</table>';

    return html;
}

function buildMonthlyCalendarHtml() {
    let html = "";

    // template calendar
    html += "<table>";

    html += buildNavHtml();

    // head
    html += buildCalendarHead(startDate.getMonth(), startDate.getMonth(), startDate.getFullYear());

    // body
    html += '<tbody class="days_cal">';

    const today = new Date();

    // start in 1 and this month
    let date = new Date(startDate);

    // white zone
    for (index = 0; index < getGermanWeekDay(date); index++) {
        html += '<td class="white_cal"> </td>';
    }

    for (index = 0; index < 31; index++) {
        if (index < date.getDate()) {
            let weekDay = getGermanWeekDay(date);

            // this day
            let day = date.getDate();
            let dateStr = getPaddedDateString(date);

            if (getPaddedDateString(today) === getPaddedDateString(date)) {
                html += '<td class="today"><div class="calendar_day" data-id="' + dateStr + '">'
                        + "<span>" + day + "</span>";
                html += "</div>";
            } else {
                html += '<td><div class="calendar_day" data-id="' + dateStr + '">'
                        + "<span>" + day + "</span>";
                html += "</div>";

                if (today.getTime() > date.getTime()) {
                    html += '<div class="past_day_overlay"></div>';
                }
            }

            html += "</td>";

            if (weekDay === 6) {
                html += "</tr>";
            }
        }

        date.setDate(date.getDate() + 1);
    } // end for loop

    html += "</table>";

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

    let week = (mode === weekMode) ? " enabled" : " ";
    let month = (mode === monthMode) ? " enabled" : " ";

    let weekOrMonth = '<a href="#" class="week_or_month' + week + '" onclick="setMode(weekMode);">Woche <br/> </a>'
            + '<a href="#" class="week_or_month' + month + '" onclick="setMode(monthMode);">Monat</a>';

    html += '<th colspan="2">' + weekOrMonth + '</th>';
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
