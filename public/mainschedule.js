
// index date
let indexDate = new Date();
indexDate = getStartOfWeek(indexDate);
if (sessionStorage.getItem('indexDate'))
    indexDate = new Date(Date.parse(sessionStorage.getItem('indexDate')));
sessionStorage.setItem('indexDate', getPaddedDateString(indexDate));

const monthMode = "month";
const weekMode = "week";
let mode = monthMode;

if (sessionStorage.getItem('mode') && mode !== sessionStorage.getItem('mode')) {
    mode = sessionStorage.getItem('mode');
}

let refreshCounter = 0;
let dataReceived = 0;

let currentEventId = null;


function onEventsReceived(refresh, callback) {
    if (refresh !== refreshCounter)
        return;

    // it's important we only start adding events once all data was received
    // to avoid flickering and wrong events
    dataReceived++;
    if (dataReceived === 2) {
        _("#calendar").innerHTML = buildCalendarHtml();
        addEvents(eventData, indexDate);

        if (callback) callback();
    }
}

function refresh(callback) {
    // declare and fill event data
    eventData = {};
    userData = {};

    let fromDate = getStartOfWeek(indexDate)
    let endDate = new Date(indexDate);

    if (mode === weekMode) {
        endDate.setDate(endDate.getDate() + 7);
    } else {
        fromDate.setDate(1);

        endDate.setDate(7);
        endDate.setMonth(endDate.getMonth() + 1);
    }

    let from = getPaddedDateString(fromDate);
    let to = getPaddedDateString(endDate);

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

        onEventsReceived(counter, callback);
    });

    requestEvents(from, to, function (data) {
        console.log("received events");

        // after querying events, rebuild calendar
        eventData = data;

        onEventsReceived(counter, callback);
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

function findEvent(id) {
    if (id && eventData) {
        for (let key in eventData) {
            let event = eventData[key];

            if (event.id === id)
                return event;
         }
    }
}

function showEvent(dateStr, id, edit) {
    if (!_("#calendar_data").classList.contains("show_data")) {
        _("#calendar_data").classList.add("show_data");
    }

    let startTimes = {};
    startTimes[0] = "20:00";
    startTimes[1] = "20:00";
    startTimes[2] = "20:00";
    startTimes[3] = "20:00";
    startTimes[4] = "20:00";
    startTimes[5] = "20:00";
    startTimes[6] = "15:00";

    let endTimes = {};
    endTimes[0] = "00:00";
    endTimes[1] = "00:00";
    endTimes[2] = "00:00";
    endTimes[3] = "00:00";
    endTimes[4] = "02:00";
    endTimes[5] = "02:00";
    endTimes[6] = "19:00";

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
    let time = startTimes[getGermanWeekDay(new Date(dateStr))];
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
    let locked = false;

    currentEventId = null;

    let allUsersSorted = [];
    for (let i in userData) {
        allUsersSorted.push(userData[i]);
    }

    allUsersSorted.sort(function (a, b) {
        let nameA = a.display_name;
        let nameB = b.display_name;
        return nameA.localeCompare(nameB);
    });

    if (id && eventData) {
        for (let key in eventData) {
            let event = eventData[key];

            if (event.id === id) {
                currentEventId = id;

                title = event.title;
                date = event.date;
                time = event.time;
                endTime = event.end_time;
                users = 0;
                minUsers = event.minimum_users;
                organizer = event.organizer;
                venue = event.venue;
                address = event.address;
                description = event.description;
                locked = event.locked;
                buttonCaption = "Änderungen Speichern";
                headline = title;
                dateFlags = "disabled";
                deleteButton = '<input class="delete_event" type="submit" name="deleteevent" value="&#x1F5D1; Löschen" formnovalidate/>';

                let remainingUsers = new Set();
                for (let uid in userData) {
                    remainingUsers.add(userData[uid].id);
                }

                let eventUsersSorted = [];
                for (let i in event.users) {
                    eventUsersSorted.push(event.users[i]);
                }

                eventUsersSorted.sort(function (a, b) {
                    let nameA = userData[a.user_id].display_name;
                    let nameB = userData[b.user_id].display_name;
                    return nameA.localeCompare(nameB);
                });

                let handStyle = '';
                if (!edit && userData[loggedInUserId].permissions['manage_other_schedules']) {
                    handStyle = ' style="cursor: pointer;" '
                }

                eventUsers += "<table>";
                for (let i in eventUsersSorted) {
                    let eventUser = eventUsersSorted[i];
                    let user = userData[eventUser.user_id];
                    remainingUsers.delete(eventUser.user_id);

                    if (eventUser.user_id === loggedInUserId)
                        selfInUserList = true;

                    if (user) {
                        let removeOnClick = ` onclick="removeFromSchedule(${user.id}); "`;
                        if (edit) removeOnClick = "";

                        eventUsers += '<tr><td>';
                        if (eventUser.deliberate) {
                            eventUsers += '<div ' + removeOnClick + handStyle + ' class="deliberate_event_user" title="Hat sich selbst eingetragen"> ✅ ' + user.display_name + '</div>';
                            users++;
                        } else if (user.active && user.visible){
                            eventUsers += '<div ' + removeOnClick + handStyle + ' class="event_user" title="Ist durch Rahmendienstplan eingetragen"> 📅 ' + user.display_name + '</div>';
                            users++;
                        }
                        eventUsers += '</td></tr>';
                    }
                }
                eventUsers += "</table>";

                nonEventUsers += "<table>";
                for (let i in allUsersSorted) {
                    let user = allUsersSorted[i];

                    if (remainingUsers.has(user.id) && user.active && user.visible) {
                        let addOnClick = ` onclick="insertIntoSchedule(${user.id}); "`;
                        if (edit) addOnClick = "";

                        nonEventUsers += '<tr><td>';
                        nonEventUsers += '<div ' + addOnClick + handStyle + ' class="event_user">' + user.display_name + '</div>';
                        nonEventUsers += '</td></tr>';
                    }
                }
                nonEventUsers += "</table>";
            }
        }
    } else {
        id = "";
    }

    let addRemoveButtons = "";
    if (id !== "") {

        let addDisabled = "";
        if (selfInUserList)
            addDisabled = "disabled";

        let removeDisabled = "";
        if (!selfInUserList || users <= minUsers)
            removeDisabled = "disabled";

        addRemoveButtons += '<tr>'
                + '<td><button type="button" class="schedule_insert" onclick="return insertIntoSchedule();"' + addDisabled + '>Mich Eintragen</button></td>'
                + '<td><button type="button" class="schedule_remove" onclick="return removeFromSchedule();"' + removeDisabled + '>Mich Austragen</button></td>'
                + '</tr>';
    }

    let lockButton = "";
    if (id !== "") {
        let editable = edit ? "true" : "false";
        let sentEventLockedStatus = !locked;
        let lockIcon = locked ? "&#128274" : "&#128275";
        let callback = `function() {refresh(function() {showEvent('${dateStr}', ${id}, ${editable})});}`;

        lockButton = `<a href="#" onclick="return sendEventLockedStatus(${id}, ${sentEventLockedStatus}, ${callback});"> ${lockIcon} </a>`;
    }

    let data = '';

    if (edit) {

        // template info
        data = ''
                + '<div class="headline-container">'
                + `<span> ${headline} </span>`
                + lockButton
                + `<a class="close" href="#" onclick="return hideEvent();"> &times</a>`
                + '</div>'

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
                + '<input class="create_event" type="submit" name="newevent" value="' + buttonCaption + '"/>'
                + '</div>'
                + '<div class="input_line">'
                + deleteButton
                + '</div>'
                + '</form>'
                + '</div>'
                ;

        if (id && minUsers > 0) {
            data += ''
                + '<div class="create_event_wrapper" style="margin-top: 50px">'
                + `<h4>Dienste (${users} von ${minUsers})</h4>`
                + '<table class="userlist">'
                + '<tr>'
                + `<th>Eingetragen</th>`
                + '<th>Ausgetragen</th>'
                + '</tr>'
                + '<tr>'
                + '<td>' + eventUsers + '</td>'
                + '<td>' + nonEventUsers + '</td>'
                + '</tr>'
                + '</table>'
                + '</div>'
                ;
        }

        setTimeout(function () {
            document.getElementById('event_title_input').focus();
        }, 100);

    } else {
        time = String(time);
        endTime = String(endTime);

        if (time.endsWith(":00:00")) time = time.replace(":00:00", ":00");
        if (endTime.endsWith(":00:00")) endTime = endTime.replace(":00:00", ":00");

        const options = {
           weekday: 'long',
           year: 'numeric',
           month: 'long',
           day: 'numeric',
        };

        date = new Date(date);
        date = date.toLocaleDateString("de-DE", options);

        let eventId = event.id;

        let editButton = "";
        if (userData[loggedInUserId].permissions['manage_events']) {
            editButton = `<a href="#" onclick="return showEvent('${dateStr}', ${id}, true);"> ✏️ </a>`
        }

        // template info
        data = ''
                + '<div class="headline-container">'
                + `<span> ${headline} </span>`
                + editButton
                + lockButton
                + `<a class="close" href="#" onclick="return hideEvent();"> &times</a>`
                + '</div>'

                + '<div class="create_event_wrapper">'

                + `<div class="data_line"> <p> ${date} - ${time}-${endTime} Uhr </p></div>`
                + (organizer != "" ? `<div class="data_line"> <p> Verantwortlich: ${organizer} </p></div>` : "")
                + `<div class="data_line"> <p> ${venue} - ${address} </p></div>`;

        if (minUsers > 0) {
            data += ''
                    + (minUsers > 0 ? `<h4>Dienste (${users} von ${minUsers})</h4>` : '<h4>Dienste</h4>')
                    + '<table class="userlist">'
                    + '<tr>'
                    + `<th>Eingetragen</th>`
                    + '<th>Ausgetragen</th>'
                    + '</tr>'
                    + '<tr>'
                    + '<td>' + eventUsers + '</td>'
                    + '<td>' + nonEventUsers + '</td>'
                    + '</tr>'
                    + addRemoveButtons
                    + '</table>';
        }

        data += ''
                + `<div class="data_line"> <p> ${description} </p></div>`
                + '</div>';

    }

    return (_("#calendar_data").innerHTML = data);
}

function buildEventAssigneeOverview(event) {
    let html = "";

    console.log(event);

    html += '<table class="user_listing">';

    let sorted = [...event.users];
    sorted.sort(function (a, b) {
        let userA = userData[a.user_id];
        let userB = userData[b.user_id];

        return userA.display_name.localeCompare(userB.display_name);
    });

    for (let i in sorted) {
        let eventUser = sorted[i];
        let user = userData[eventUser.user_id];

        let selfClass = "";
        if (eventUser.user_id === loggedInUserId)
            selfClass = "assigned-highlight";

        if (user) {
            if (eventUser.deliberate) {
                html += '<tr><td><div title="Hat sich selbst eingetragen" class="deliberate_event_user ' + selfClass + '"> ✅ ' + user.display_name + '</div></td></tr>';
            } else if (user.active && user.visible){
                html += '<tr><td><div title="Ist durch Rahmendienstplan eingetragen" class="event_user ' + selfClass + '"> 📅 ' + user.display_name + '</div></td></tr>';
            }
        }
    }
    html += "</table>";

    return html;
}

function buildCalendarEventHtml(event) {

    let selfHighlightClass = "";
    let selfAssigned = false;
    for (let i in event.users) {
        let eventUser = event.users[i];

        if (eventUser.user_id === loggedInUserId) {
            selfHighlightClass = "assigned-highlight";
            selfAssigned = true;
            break;
        }
    }

    let readableTime = function (str) {
        if (!str)
            return "";

        return str.slice(0, -3);
    };

    let usersOverview = function (event) {
        let min = event.minimum_users;
        if (min <= 0) return "";

        let users = 0;

        for (const i in event.users) {
            let user = userData[event.users[i].user_id];

            if (event.users[i].deliberate || (user.visible && user.active))
                users++;
        }

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

        let locked = "";
        if (event.locked)
            locked = "&#128274";

        html += ` MA ${locked}</span>`;

        return html;
    };

    let assignedUsers = "";
    if (mode === weekMode)
        assignedUsers = buildEventAssigneeOverview(event);

    return '<a class="' + selfHighlightClass + '" href="#" onclick="return showEvent(\'\', ' + event.id + ', false)">'
            + '<span class="event_title">' + event.title + '</span>'
            + '<span class="event_time">' + readableTime(event.time)
            + " bis " + readableTime(event.end_time) + '</span>'
            + usersOverview(event)
            + (selfAssigned ? '<span class="event_title">Dienst!</span>' : '')
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

    if (userData[loggedInUserId].permissions['manage_events']) {
        // add little blobs for creating a new event
        let date = new Date(startDate);
        for (let i = 0; i <= 45; ++i) {
            let dateStr = getPaddedDateString(date);

            if (_('[data-id="' + dateStr + '"]')) {
                let div = document.createElement("div");
                div.classList.add("calendar_event_adder");
                div.innerHTML = '<a href="#" onclick="return showEvent(\'' + dateStr + "', '', true);\">"
                        + "+ Neue Veranstaltung"
                        + "</a>";

                _('[data-id="' + dateStr + '"]').appendChild(div);
            }

            date.setDate(date.getDate() + 1);
        }
    }
}

function gotoToday() {
    indexDate = new Date();

    if (mode === weekMode) {
        indexDate = getStartOfWeek(new Date());
    } else {
        indexDate.setDate(1);
    }

    sessionStorage.setItem('indexDate', getPaddedDateString(indexDate));

    refresh();
}

function gotoPrevious() {
    if (mode === weekMode) {
        indexDate.setDate(indexDate.getDate() - 7);
    } else {
        indexDate.setMonth(indexDate.getMonth() - 1);
    }

    sessionStorage.setItem('indexDate', getPaddedDateString(indexDate));

    refresh();
}

function gotoNext() {
    if (mode === weekMode) {
        indexDate.setDate(indexDate.getDate() + 7);
    } else {
        indexDate.setMonth(indexDate.getMonth() + 1);
    }

    sessionStorage.setItem('indexDate', getPaddedDateString(indexDate));

    refresh();
}

function setMode(newMode) {
    mode = newMode;

    if (mode === weekMode) {
        let today = new Date();
        if (today.getMonth() === indexDate.getMonth()) {
            indexDate = getStartOfWeek(today);
        } else {
            indexDate = getStartOfWeek(indexDate);
        }
    } else {
        indexDate.setDate(1);
    }

    sessionStorage.setItem('mode', mode);
    sessionStorage.setItem('indexDate', getPaddedDateString(indexDate));

    refresh();
}

// toggle event show or hide
function hideEvent() {
    _("#calendar_data").classList.remove("show_data");
}

function buildCalendarHtml() {
    if (mode === weekMode) {
        return buildWeeklyCalendarHtml(indexDate);
    } else {
        return buildMonthlyCalendarHtml(indexDate);
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

function buildMonthlyCalendarHtml(indexDate) {
    let html = "";

    // template calendar
    html += "<table>";

    html += buildNavHtml();

    // head
    html += buildCalendarHead(indexDate.getMonth(), indexDate.getMonth(), indexDate.getFullYear());

    let displayedMonth = indexDate.getMonth();

    // body
    html += '<tbody class="days_cal">';

    const today = new Date();

    // start in 1 and this month
    let date = getStartOfWeek(indexDate);

    let endDate = new Date(indexDate);
    endDate.setMonth(endDate.getMonth()+1);

    for (i = 0; i < 7; ++i)
    {
        if (getGermanWeekDay(endDate) === 0)
            break;

        endDate.setDate(endDate.getDate()+1);
    }

    for (i = 0; i < 42; i++) {
        if (date.getTime() >= endDate.getTime())
            break;

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

            if (today.getTime() > date.getTime()
                    || date.getMonth() !== displayedMonth) {
                html += '<div class="past_day_overlay"></div>';
            }
        }

        html += "</td>";

        if (weekDay === 6) {
            html += "</tr>";
        }

        date.setDate(date.getDate() + 1);
    }

    html += "</table>";

    return html;
}

function buildCalendarHead(month1, month2, year) {
    let html = "";

    const today = new Date();
    const weekday = getGermanWeekDay(today);

    let day_of_week = Array("Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");
    let month_of_year = Array("Januar", "Februar", "März", "April", "Mai", "Juni",
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
