
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
}

refresh();

function setOutlineForUser(id, day) {

    let checkBoxId = 'outline_check_' + id + '_' + day;
    let checked = _("#" + checkBoxId).checked;

    sendUserOutlineScheduleDay(id, day, checked, function () {
        refresh();
    });
}

function setPermissionForUser(id, permission) {

    let checkBoxId = 'permission_check_' + id + '_' + permission;
    let checked = _("#" + checkBoxId).checked;

    sendUserPermission(id, permission, checked, function () {
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

        if (!a.active) nameA = "zzzzzz" + nameA;
        else if (!a.visible) nameA = "zzzzzy" + nameA;

        if (!b.active) nameB = "zzzzzz" + nameB;
        else if (!b.visible) nameB = "zzzzzy" + nameB;

        return nameA.localeCompare(nameB);
    });

    let canViewUserManagement = (userData[loggedInUserId].permissions['invite_users']
                                || userData[loggedInUserId].permissions['manage_users']
                                || userData[loggedInUserId].permissions['delete_users']
                                || userData[loggedInUserId].permissions['login_as_others']
                                || userData[loggedInUserId].permissions['manage_permissions']
                                || userData[loggedInUserId].permissions['admin_dev_maintenance']
                                || userData[loggedInUserId].permissions['change_other_outline_schedule']);

    if (userData[loggedInUserId].permissions['change_other_outline_schedule'])
        html += buildOutlineScheduleHtml(sorted);

    if (canViewUserManagement) {
        html += buildUsersOverviewHtml(sorted);
    }

    if (userData[loggedInUserId].permissions['invite_users']
            || userData[loggedInUserId].permissions['manage_users'])
        html += buildInviteUsersHtml();

    if (userData[loggedInUserId].permissions['delete_users'])
        html += buildDeleteUsersHtml();

    if (userData[loggedInUserId].permissions['login_as_others'])
        html += buildLoginAsOthersHtml();

    html += buildPermissionsHtml();

    return html;
}

function buildUsersOverviewHtml(users)
{
    let html = '';

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

    for (const i in users) {
        let user = users[i];
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
    return html;
}

function buildOutlineScheduleHtml(users)
{
    let html = '';

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

    let outlineDay = function (value, id, day, attributes) {
        let v = value ? "checked" : "";

        return `<input type="checkbox" ${attributes} id="outline_check_${id}_${day}" onclick=setOutlineForUser(${id},${day}) ${v}>`;
    };

    let attributes = "";
    if (!userData[loggedInUserId].permissions['change_other_outline_schedule'])
        attributes = "disabled";

    for (const i in users) {
        let user = users[i];
        if (!user.visible) continue;

        let id = user.id;

        html += '<tr>';
        html += '<td>' + user.display_name + '</td>';
        html += '<td>' + outlineDay(user.day_0, id, 0, attributes) + '</td>';
        html += '<td>' + outlineDay(user.day_1, id, 1, attributes) + '</td>';
        html += '<td>' + outlineDay(user.day_2, id, 2, attributes) + '</td>';
        html += '<td>' + outlineDay(user.day_3, id, 3, attributes) + '</td>';
        html += '<td>' + outlineDay(user.day_4, id, 4, attributes) + '</td>';
        html += '<td>' + outlineDay(user.day_5, id, 5, attributes) + '</td>';
        html += '<td>' + outlineDay(user.day_6, id, 6, attributes) + '</td>';
        html += '</tr>';
    }

    html += '</table>';

    return html;
}

function buildInviteUsersHtml()
{
    let html = '';

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

    return html;
}

function buildDeleteUsersHtml()
{
    let html = '';

    html += '<h2>Mitarbeitende Löschen</h2>';
    html += '<p> <strong>Achtung:</strong> Kann nicht rückgängig gemacht werden. Löscht Mitarbeitende sofort, restlos und <strong>ohne Nachfrage</strong>. Mitarbeitende werden aus allen Veranstaltungen entfernt. </p>';
    html += '<form method="POST" action="">';
    html += '    <div>';
    html += '        <label for="login">Login:</label>';
    html += '        <input type="text" id="login" name="login" required></input>';
    html += '    </div>';
    html += '    <input type="submit" name="deleteuser" value="Löschen"></input>';
    html += '</form>';

    if (typeof userDeletedInfo !== "undefined") {
        html += '    <div class="info-box">';
        html += `    <p>${userDeletedInfo}</p>`;
        html += '    </div>';
    }

    if (typeof userDeletedError !== "undefined") {
        html += '    <div class="error-box">';
        html += `    <p>${userDeletedError}</p>`;
        html += '    </div>';
    }

    return html;
}

function buildLoginAsOthersHtml()
{
    let html = '';

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

function buildPermissionsHtml()
{
    let html = '';

    let names = Array();
    names['lock_event_schedule'] = "Dienste Sperren";
    names['manage_other_schedules'] = "Dienste Planen";
    names['manage_events'] = "Veranstaltungen Planen";
    names['change_other_outline_schedule'] = "Rahmendienstplan Ändern";
    names['view_statistics'] = "Statistiken Einsehen";
    names['invite_users'] = "MA Einladen";
    names['manage_users'] = "MA Verwalten";
    names['delete_users'] = "MA Löschen";
    names['login_as_others'] = "Als andere Einloggen &#x1F3C6;";
    names['manage_permissions'] = "Rechte Verwalten &#x1F3C6;";
    names['admin_dev_maintenance'] = "Dev/Admin Maintenance";

    let titles = Array();
    titles['lock_event_schedule'] = "Dienste von Veranstaltungen sperren und entsperren, sodass sich niemand mehr ein/austragen kann";
    titles['manage_other_schedules'] = "Andere Mitarbeitende in Veranstaltungen ein- oder austragen";
    titles['manage_events'] = "Veranstaltungen erstellen, bearbeiten und löschen";
    titles['change_other_outline_schedule'] = "Rahmendienstplan aller Mitarbeitenden ändern";
    titles['view_statistics'] = "Statistiken über wer hat wann wie viele Dienste gemacht";
    titles['invite_users'] = "Neue Mitarbeitende einladen, Email-Adressen neu setzen";
    titles['manage_users'] = "Email-Adressen neu setzen, Mitarbeitende ausblenden oder deaktivieren";
    titles['delete_users'] = "Mitarbeitende löschen";
    titles['login_as_others'] = "Als andere Mitarbeitende einloggen (&#x1F3C6; Super-Berechtigung: Alle anderen Berechtigungen könnten hiermit indirekt ebenfalls möglich sein)";
    titles['manage_permissions'] = "Berechtigungen anderer Mitarbeitender können hierdurch erlaubt oder entzogen werden (&#x1F3C6; Super-Berechtigung: Alle anderen Berechtigungen könnten hiermit indirekt ebenfalls möglich sein)";
    titles['admin_dev_maintenance'] = "Zugriff auf Dev/Admin Maintenance Seite";

    html += '<h2>Berechtigungen</h2>';
    html += '<p>&#x1F3C6; Super-Berechtigung: Alle anderen Berechtigungen könnten hiermit indirekt ebenfalls möglich sein.</p>';
    html += '<p>Tip: Für mehr Infos mit der Maus über die entsprechende Berechtigung hovern.</p>';
    html += '<table class="permissions">';
    html += '<tr>';
    html += '<th></th>';

    html += '<th>Eigenes Profil Bearbeiten</th>';
    html += '<th>Dienste Ein- / Austragen</th>';

    for (const n in names) {
        html += '<th title = "'+titles[n]+'">'+names[n]+'</th>';
    }

    html += '</tr>';

    let permissionCheck = function (value, id, permission, attributes) {
        let v = value ? "checked" : "";

        return `<input type="checkbox" ${attributes} id="permission_check_${id}_${permission}" onclick=setPermissionForUser(${id},"${permission}") ${v}>`;
    };

    let sorted = [];
    for (let id in userData) {
        sorted.push(userData[id]);
    }

    sorted.sort(function (a, b) {
        let nameA = a.display_name;
        let nameB = b.display_name;

        if (a.id === 1) nameA = "aaaaaaa";
        if (b.id === 1) nameB = "aaaaaaa";

        return nameA.localeCompare(nameB);
    });

    for (const i in sorted) {
        let user = sorted[i];
        if (!user.active) continue;

        html += '<tr>';
        html += '<td>' + user.display_name + '</td>';
        html += '<td> <input type="checkbox" checked disabled title="Diese Berechtigung kann nicht entzogen werden."> </td>';
        html += '<td> <input type="checkbox" checked disabled title="Diese Berechtigung kann nicht entzogen werden."> </td>';

        for (const pn in names) {
            let attributes = "";

            if (user.id === 1) attributes = 'disabled title="Admin-Berechtigungen können aus Sicherheitsgründen nicht entzogen werden."';
            if (user.id === loggedInUserId && pn === 'manage_permissions') attributes = 'disabled title="Diese Berechtigung kannst du dir aus Sicherheitsgründen nicht selbst entziehen. Jemand anderes muss diese Berechtigung entfernen."';

            if (!userData[loggedInUserId].permissions['manage_permissions'])
                attributes = 'disabled';

            html += '<td>' + permissionCheck(user.permissions[pn], user.id, pn, attributes) + '</td>';
        }

        html += '</tr>';
    }

    html += '</table>';

    return html;
}
