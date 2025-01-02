
let userData = {};


// short querySelector
function _(s) {
    return document.querySelector(s);
}

function refresh() {
    // declare and fill event data
    userData = {};

    requestUsers(function (data) {
        userData = {};
        for (let i in data) {
            let user = data[i];
            userData[user.id] = user;
        }

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
    html += '<th>Montag</th>';
    html += '<th>Dienstag</th>';
    html += '<th>Mittwoch</th>';
    html += '<th>Donnerstag</th>';
    html += '<th>Freitag</th>';
    html += '<th>Samstag</th>';
    html += '<th>Sonntag</th>';
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

function sendEventDefaultDataChange()
{
    console.log("send change");
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

    let makeTypeSelect = function(value, day) {
        let veranstaltungSelected = (value === 'Veranstaltung') ? "selected" : "";
        let putzdienstSelected = (value === 'Putzdienst') ? "selected" : "";
        let mvSelected = (value === 'MV') ? "selected" : "";
        let sonstigeSelected = (value === 'Sonstige') ? "selected" : "";

        return `<select onchange="sendEventDefaultDataChange();">
                  <option value="Veranstaltung" ${veranstaltungSelected}>Veranstaltung</option>
                  <option value="Putzdienst" ${putzdienstSelected}>Putzdienst</option>
                  <option value="MV" ${mvSelected}>Mitarbeitenden-Versammlung</option>
                  <option value="Sonstige" ${sonstigeSelected}>Sonstige</option>
                </select>`;
    }

    let makeTimeSelect = function(time, day) {
        return '<input type="time" id="time" name="time" value="' + time + '" onchange="sendEventDefaultDataChange();">';
    }

    let makeNumberSelect = function(value, day) {
        return '<input type="number" id="" name="minimum_users" min="0" value="' + value + '" onchange="sendEventDefaultDataChange();">';
    }

    let daysOfWeek = ["Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag"];

    for (let i = 0; i < 7; ++i) {
        html += '<tr>';
        html += '<td>' + daysOfWeek[i] + '</td>';
        html += '<td>' + makeTypeSelect("Veranstaltung", 0) + '</td>';
        html += '<td>' + makeTimeSelect("20:00", 0) + '</td>';
        html += '<td>' + makeTimeSelect("02:00", 0) + '</td>';
        html += '<td>' + makeNumberSelect("2", 0) + '</td>';
        html += '</tr>';
    }

    html += '</table>';

    return html;
}
