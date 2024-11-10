

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

    console.log(id);
    console.log(day);
    console.log(checked);

    sendUserOutlineScheduleDay(id, day, checked, function () {
        console.log("sent");
        refresh();
    });
}

function buildIndexHtml()
{
    let html = "";

    html += buildNavHtml();

    html += '<h2>Mein Rahmendienstplan</h2>';
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

    let outlineDay = function (value, id, day) {
        let v = value ? "checked" : "";

        return '<input type="checkbox" id="outline_check_' + id + '_' + day + '" onclick=setOutlineForUser(' + id + ',' + day + ') ' + v + '>';
    };

    for (let id in userData) {
        let user = userData[id];

        if (user.id === loggedInUserId) {
            html += '<tr>';
            html += '<td>' + user.display_name + '</td>';
            html += '<td>' + outlineDay(user.day_0, loggedInUserId, 0) + '</td>';
            html += '<td>' + outlineDay(user.day_1, loggedInUserId, 1) + '</td>';
            html += '<td>' + outlineDay(user.day_2, loggedInUserId, 2) + '</td>';
            html += '<td>' + outlineDay(user.day_3, loggedInUserId, 3) + '</td>';
            html += '<td>' + outlineDay(user.day_4, loggedInUserId, 4) + '</td>';
            html += '<td>' + outlineDay(user.day_5, loggedInUserId, 5) + '</td>';
            html += '<td>' + outlineDay(user.day_6, loggedInUserId, 6) + '</td>';
            html += '</tr>';
        }
    }

    html += '</table>';

    html += '<h2>Mein Profil</h2>';

    let makeElement = function (fieldId, type, caption, value, properties) {
        let usedValue = value;

        if (value === null)
            usedValue = "-";

        if (!properties)
            properties = "";

        let result = "";
        result += '<tr>';
        result += '<td><label for="' + fieldId + '">' + caption + ':</label></td>';
        result += '<td><input type="' + type + '" id="' + fieldId + '" name="' + fieldId + '" value="' + usedValue + '" ' + properties + '/></td>';
        result += '</tr>';

        return result;
    };

    let user = userData[loggedInUserId];

    html += '<form method="POST" action="">';
    html += '<table class="profiletable">';
    html += makeElement("userid", "text", "Interne ID", user.id, "disabled");
    html += makeElement("login", "text", "Log-In", user.login, "disabled");
    html += '<tr class="spacer-row"></tr>';

    html += makeElement("display_name", "text", "Anzeigename", user.display_name, "required");
    html += makeElement("first_name", "text", "Vorname", user.first_name);
    html += makeElement("last_name", "text", "Nachname", user.last_name);
    html += makeElement("email", "text", "Email", user.email, "required");
    html += '<tr class="spacer-row"></tr>';

    html += '<tr>';
    html += '<td><label for="new_password">Neues Passwort:</label></td>';
    html += '<td><input type="password" id="new_password" name="new_password" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,}" title="Must contain at least one lowercase letter, one uppercase letter, and one number. Minimum length: 8 characters."></td>';

    //html += makeElement("new_password", "password", "Neues Passwort", "");
    html += makeElement("new_password_repeat", "password", "Passwort Wiederholen", "");
    html += '<tr class="spacer-row"></tr>';

    html += makeElement("password", "password", "Aktuelles Passwort zur Bestätigung", "");


    html += '</table>';

    if (typeof phpError !== 'undefined' && phpError !== "") {
        html += '<div class="error-box">';
        html += '<p>' + phpError + '</p>';
        html += '</div>';
    }

    html += '<input type="submit" name="update_userprofile" value="Speichern">';
    html += '</form>';

    html += '<h2>Logout</h2>';
    html += '<form method="POST" action="">'
    html += '<input type="submit" name="logout" value="Ausloggen"></input>'
    html += '</form>'

    return html;
}
