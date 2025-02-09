
let userData = {};
let eventDefaultData = {};
let dataReceived = 0;

// short querySelector
function _(s) {
    return document.querySelector(s);
}

function refresh() {
    // declare and fill event data
    userData = {};
    eventDefaultData = {};
    dataReceived = 0;

    requestUsers(function (data) {
        userData = {};
        for (let i in data) {
            let user = data[i];
            userData[user.id] = user;
        }

        if (++dataReceived == 2)
            _("#outline_schedule").innerHTML = buildHtml();
    });

    requestEventDefaultData(function (data) {
        eventDefaultData = data;

        if (++dataReceived == 2)
            _("#outline_schedule").innerHTML = buildHtml();
    });
}

refresh();

function buildHtml()
{
    let html = "";

    html += buildNavHtml();
    html += buildOutlineScheduleHtml();
    html += buildOutlineEventsHtml();

    return html;
}

function buildOutlineScheduleHtml()
{
    let html = "";

    html += '<h2>Rahmendienstplan</h2>';
    html += '<table class="outline_schedule">';
    html += '<tr>';
    html += '<th></th>';
    html += '<th>Montag' + getOutlineScheduleUsersOfDayStr(userData, 0) + '</th>';
    html += '<th>Dienstag' + getOutlineScheduleUsersOfDayStr(userData, 1) + '</th>';
    html += '<th>Mittwoch' + getOutlineScheduleUsersOfDayStr(userData, 2) + '</th>';
    html += '<th>Donnerstag' + getOutlineScheduleUsersOfDayStr(userData, 3) + '</th>';
    html += '<th>Freitag' + getOutlineScheduleUsersOfDayStr(userData, 4) + '</th>';
    html += '<th>Samstag' + getOutlineScheduleUsersOfDayStr(userData, 5) + '</th>';
    html += '<th>Sonntag' + getOutlineScheduleUsersOfDayStr(userData, 6) + '</th>';
    html += '</tr>';

    let outlineDay = function (value, id, day) {
        return value ? "&#x2705" : "";
    };

    let sorted = [];
    for (let id in userData) {
        sorted.push(userData[id]);
    }

    sorted.sort(function (a, b) {
        let nameA = a.display_name;
        let nameB = b.display_name;

        return nameA.localeCompare(nameB);
    });

    for (const i in sorted) {
        let user = sorted[i];
        let id = user.id;

        if (user.visible) {
            html += '<tr>';
            html += '<td>' + user.display_name + '</td>';
            html += '<td>' + outlineDay(user.day_0, id, 0) + '</td>';
            html += '<td>' + outlineDay(user.day_1, id, 1) + '</td>';
            html += '<td>' + outlineDay(user.day_2, id, 2) + '</td>';
            html += '<td>' + outlineDay(user.day_3, id, 3) + '</td>';
            html += '<td>' + outlineDay(user.day_4, id, 4) + '</td>';
            html += '<td>' + outlineDay(user.day_5, id, 5) + '</td>';
            html += '<td>' + outlineDay(user.day_6, id, 6) + '</td>';
            html += '</tr>';
        }
    }

    html += '</table>';

    return html;
}

function sendEventDefaultDataChange(day)
{
    let typeSelectId = 'select-' + day;
    let typeSelect = _("#" + typeSelectId);

    let startTimeInputId = 'start-' + day;
    let startTimeInput = _("#" + startTimeInputId);

    let endTimeInputId = 'end-' + day;
    let endTimeInput = _("#" + endTimeInputId);

    let usersInputId = 'users-' + day;
    let usersInput = _("#" + usersInputId);

    let eventType = typeSelect.value;
    let users = usersInput.value;
    let startTime = startTimeInput.value;
    let endTime = endTimeInput.value;

    sendEventDefaultData(day, eventType, startTime, endTime, users, function () {
        refresh();
    });
}

function buildOutlineEventsHtml()
{
    let html = "";

    html += '<h2>Event-Daten</h2>';
    html += '<p>Angaben, die bei neuen Veranstaltungen bereits voreingefüllt sind.</p>';
    html += '<table class="outline_schedule">';
    html += '<tr>';
    html += '<th></th>';
    html += '<th>Veranstaltungstyp</th>';
    html += '<th>Start</th>';
    html += '<th>Ende</th>';
    html += '<th>Mindest-MA</th>';
    html += '</tr>';

    let makeTypeSelect = function (value, day) {
        let veranstaltungSelected = (value === 'Veranstaltung') ? "selected" : "";
        let putzdienstSelected = (value === 'Putzdienst') ? "selected" : "";
        let mvSelected = (value === 'MV') ? "selected" : "";
        let sonstigeSelected = (value === 'Sonstige') ? "selected" : "";

        return `<select id="select-${day}" onchange="sendEventDefaultDataChange(${day});">
                  <option value="Veranstaltung" ${veranstaltungSelected}>Veranstaltung</option>
                  <option value="Putzdienst" ${putzdienstSelected}>Putzdienst</option>
                  <option value="MV" ${mvSelected}>Mitarbeitenden-Versammlung</option>
                  <option value="Sonstige" ${sonstigeSelected}>Sonstige</option>
                </select>`;
    }

    let makeTimeSelect = function (id, time, day) {
        return `<input type="time" id="${id}-${day}" name="time" value="` + time + `" onchange="sendEventDefaultDataChange(${day});">`;
    }

    let makeNumberSelect = function (id, value, day) {
        return `<input type="number" id="${id}-${day}" name="minimum_users" min="0" value="` + value + `" onchange="sendEventDefaultDataChange(${day});">`;
    }

    let daysOfWeek = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];

    for (let i = 0; i < 7; ++i) {
        let data = eventDefaultData[i];

        html += '<tr>';
        html += '<td>' + daysOfWeek[i] + '</td>';
        html += '<td>' + makeTypeSelect(data.type, i) + '</td>';
        html += '<td>' + makeTimeSelect("start", data.time, i) + '</td>';
        html += '<td>' + makeTimeSelect("end", data.end_time, i) + '</td>';
        html += '<td>' + makeNumberSelect("users", data.minimum_users, i) + '</td>';
        html += '</tr>';
    }

    html += '</table>';

    return html;
}
