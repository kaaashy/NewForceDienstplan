
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

        _("#mainpage").innerHTML = buildIndexHtml();
    });

}

refresh();

function buildIndexHtml()
{
    let html = "";

    html += buildNavHtml();

    html += '<h1>Admin Maintenance</h1>';

    if (typeof infoMessage !== "undefined") {
        html += '    <div class="info-box">';
        html += `    <p>${infoMessage}</p>`;
        html += '    </div>';
    }

    if (typeof errorMessage !== "undefined") {
        html += '    <div class="error-box">';
        html += `    <p>${errorMessage}</p>`;
        html += '    </div>';
    }

    html += '<h2>Test-Email</h2>';
    html += '<p>Verschickt eine Test-Email an den Admin-Account.</p>';
    html += '<form method="POST" action="">';
    html += '    <input type="submit" name="send_testmail" value="Test-Email verschicken"></input>';
    html += '</form>';

    return html;
}
