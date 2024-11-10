
function buildNavHtml()
{
    let html = "";
    html += '<div class="link-container">';

    html += '<a href="dienstplan.php">Dienstplan</a>';
    html += '<a href="admin.php">Admin</a>';
    html += '<a href="users.php">Mitarbeitende</a>';
    html += '<a href="userprofile.php">Mein Profil (' + loggedInUserLogin + ') </a>';

    html += '</div>';

    return html;
}
