
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

        _("#user_index").innerHTML = buildIndexHtml();
    });
}

refresh();

function buildIndexHtml()
{
    let html = "";

    html += buildNavHtml();

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
