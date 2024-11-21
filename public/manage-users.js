
let userData = {};


// short querySelector
function _(s) {
    return document.querySelector(s);
}

function refresh() {
    // declare and fill event data
    userData = {};

    requestUsers(function (data) {
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

    let checkBoxId = 'outline_check_' + id + '_' + day;
    let checked = _("#" + checkBoxId).checked;

    sendUserOutlineScheduleDay(id, day, checked, function () {
        refresh();
    });
}

function setUserStatus(id) {

    let visibleCheckBoxId = 'visible_check_' + id;
    let visibleChecked = _("#" + visibleCheckBoxId).checked;
    let activeCheckBoxId = 'active_check_' + id;
    let activeChecked = _("#" + activeCheckBoxId).checked;

    sendUserStatus(id, visibleChecked, activeChecked, function () {
        refresh();
    });
}

function buildIndexHtml()
{
    let html = "";

    html += buildNavHtml();

    let sorted = [];
    for (let id in userData) {
        sorted.push(userData[id]);
    }

    sorted.sort(function (a, b) {
        let nameA = a.display_name;
        let nameB = b.display_name;

        return nameA.localeCompare(nameB);
    });

    html += '<h2>Mitarbeitendenübersicht</h2>';
    html += '<table class="user_overview">';
    html += '<tr>';
    html += '<th>Id</th>';
    html += '<th>Anzeigename</th>';
    html += '<th>Vorname</th>';
    html += '<th>Nachname</th>';
    html += '<th>Login</th>';
    html += '<th>Email</th>';
    html += '<th>Sichtbar</th>';
    html += '<th>Aktiv</th>';
    html += '</tr>';

    let toDisplay = function (value) {
        if (value) return value;
        return "-";
    };

    for (const i in sorted) {
        let user = sorted[i];
        let id = user.id;

        html += '<tr>';
        html += '<td>' + id + '</td>';
        html += '<td>' + toDisplay(user.display_name) + '</td>';
        html += '<td>' + toDisplay(user.first_name) + '</td>';
        html += '<td>' + toDisplay(user.last_name) + '</td>';
        html += '<td>' + toDisplay(user.login) + '</td>';
        html += '<td>' + toDisplay(user.email) + '</td>';
        {
            let v = user.visible ? "checked" : "";
            html += '<td><input type="checkbox" id="visible_check_' + id + '" onclick=setUserStatus(' + id + ') ' + v + '></td>';
        }
        {
            let v = user.active ? "checked" : "";
            html += '<td><input type="checkbox" id="active_check_' + id + '" onclick=setUserStatus(' + id + ') ' + v + '></td>';
        }
        html += '</tr>';
    }

    html += '</table>';

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
        let v = value ? "checked" : "";

        return '<input type="checkbox" id="outline_check_' + id + '_' + day + '" onclick=setOutlineForUser(' + id + ',' + day + ') ' + v + '>';
    };

    for (const i in sorted) {
        let user = sorted[i];
        let id = user.id;

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

    html += '</table>';

    html += '<h2>Mitarbeitende Anlegen</h2>';
    html += '<p> Für existierende Logins: Setzt Passwort zurück und ersetzt Email-Adresse. Schickt eine Mail, mit der das Passwort neu gesetzt werden kann. </p>';
    html += '<form method="POST" action="">';
    html += '    <div>';
    html += '        <label for="login">Login:</label>';
    html += '        <input type="text" id="login" name="login" required> </input>';
    html += '    </div>';
    html += '    <div>';
    html += '        <label for="email">Email:</label>';
    html += '        <input type="text" id="email" name="email" required> </input>';
    html += '    </div>';
    html += '    <input type="submit" name="createuser" value="Neu Anlegen"> </input>';
    html += '</form>';

    if (typeof userCreatedInfo !== "undefined") {
        html += '    <div class="info-box">';
        html += `    <p>${userCreatedInfo}</p>`;
        html += '    </div>';
    }

    if (typeof userCreatedError !== "undefined") {
        html += '    <div class="error-box">';
        html += `    <p>${userCreatedError}</p>`;
        html += '    </div>';
    }

    html += '<h2>Mitarbeitende Löschen</h2>';
    html += '<p> <strong>Achtung:</strong> Kann nicht rückgängig gemacht werden. Löscht Mitarbeitende sofort, restlos und <strong>ohne Nachfrage</strong>. Mitarbeitende werden aus allen Veranstaltungen entfernt. </p>';
    html += '<form method="POST" action="">';
    html += '    <div>';
    html += '        <label for="login">Login:</label>';
    html += '        <input type="text" id="login" name="login" required></input>';
    html += '    </div>';
    html += '    <input type="submit" name="deleteuser" value="Löschen"></input>';
    html += '</form>';

    html += '<h2>Als andere Mitarbeitende einloggen</h2>';
    html += '<form method="POST" action="">';
    html += '    <div>';
    html += '        <label for="user_id">Id:</label>';
    html += '        <input type="text" id="user_id" name="user_id" required></input>';
    html += '    </div>';
    html += '    <input type="submit" name="login_as" value="Als andere MA Einloggen"></input>';
    html += '</form>';



    return html;
}
