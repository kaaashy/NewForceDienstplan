
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

    console.log(statusData);

    if (statusData) {

        let dbIcon = statusData.db_status ? checkmarkEmoji : warningEmoji;
        html += `<p> DB-Status: ${dbIcon} ${statusData.db_status_msg} </p>`;

        if (!statusData.db_status) {
            html += "<p> Fehler bei der Datenbank-Verbindung: In der Javascript-Konsole können Sie ausführliche Fehlermeldungen einsehen. </p>";
        }

        if (statusData.users > 0) {
            html += `<p> Installations-Status: ${checkmarkEmoji} Installiert. ${statusData.users} Benutzer registriert.</p>`;
        } else {
            html += `<p> Installations-Status: ${warningEmoji} Nicht Initialisiert.</p>`;
        }
    } else {
        html += `<p> Installations-Status: ${warningEmoji} Konnte Status nicht abfragen.</p>`;
        html += "<p> Fehler möglicherweise bei PHP: In der Javascript-Konsole können Sie ausführliche Fehlermeldungen einsehen. </p>";
    }

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
    html += '    <label for="password">Installations-Passwort:</label>';
    html += '    <input type="password" name="password" value=""></input><br/><br/>';

    html += '    <input type="submit" name="reinit_clean" value="Auf Werkseinstellungen zurücksetzen"></input>';
    html += '    <input type="submit" name="reinit_with_examples" value="Auf Beispiel-DB zurücksetzen"></input>';
    html += '</form>';

    return html;
}
