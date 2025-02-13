
const checkmarkEmoji = "✅";
const warningEmoji = "⚠️";


let statusData = null;

// short querySelector
function _(s) {
    return document.querySelector(s);
}

function refresh() {
    // declare and fill event data
    statusData = {};

    requestInstallationStatus(function (data) {
        statusData = data;
        tryBuildPage();
    });

}

refresh();

function tryBuildPage()
{
    _("#mainpage").innerHTML = buildIndexHtml();
}

function buildIndexHtml()
{
    let html = "";

    html += '<h1>Dienstplan-Installer</h1>';
    html += '<p>In <code>DatabaseInfo.php</code> können sämtliche wichtigen Informationen konfiguriert werden.</p>';

    let message = "";

    if (statusData) {
        if (statusData.users > 0) {
            message = `Installations-Status: ${checkmarkEmoji} Installiert. ${statusData.users} Benutzer registriert.`;
        } else {
            message = `Installations-Status: ${warningEmoji} Nicht Initialisiert.`;
        }
    } else {
        message = `Installations-Status: ${warningEmoji} Nicht installiert.`;
    }

    html += `<p> ${message}</p>`;

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

    html += '<h2>Datenbanken initialisieren & zurücksetzen</h2>';
    html += '<form method="POST" action="">';
    html += '    <input type="password" name="password" value=""></input><br/><br/>';

    html += '    <input type="submit" name="reinit_clean" value="Auf Werkseinstellungen zurücksetzen"></input>';
    html += '    <input type="submit" name="reinit_with_examples" value="Auf Beispiel-DB zurücksetzen"></input>';
    html += '</form>';

    return html;
}
