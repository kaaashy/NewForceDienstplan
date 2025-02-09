
function buildNavHtml()
{
    let canViewUserManagement = false;
    let canViewStatistics = false;
    let canViewAdminPage = false;

    if (typeof userData[loggedInUserId] !== 'undefined') {
        canViewStatistics = (userData[loggedInUserId].permissions['view_statistics']
                                || userData[loggedInUserId].permissions['admin_dev_maintenance']);

        canViewAdminPage = (userData[loggedInUserId].permissions['admin_dev_maintenance']);
    }

    let html = "";
    html += '<div class="link-container">';
    html += '<a href="dienstplan.php">Dienstplan</a>';
    html += '<a href="outline-schedule.php">Rahmendienstplan</a>';
    html += '<a href="manage-users.php">MA-Management</a>';
    html += '<a href="statistics.php">Statistiken</a>';
    html += canViewAdminPage ? '<a href="admin.php">Admin</a>' : '';
    html += '<a href="userprofile.php">Mein Profil (' + loggedInUserLogin + ') </a>';

    let overrider = '';
    if (overridingUserId) {
        overrider = `(Als: ${loggedInUserLogin})`;
    }
    html += `<a href="logout.php">⤷🚪Logout ${overrider}</a>`;

    html += '</div>';

    return html;
}

function getPaddedDateString(date) {
    let paddedDay = String(date.getDate()).padStart(2, '0');
    let paddedMonth = String(date.getMonth() + 1).padStart(2, '0');
    return date.getFullYear() + "-" + paddedMonth + "-" + paddedDay;
}

function getGermanWeekDay(date) {
    // input: 0-6 = Sonntag -> Montag
    // output: 0-6 = Montag -> Sonntag
    let gday = date.getDay() - 1;
    if (gday === -1)
        gday = 6;

    return gday;
}

function getStartOfWeek(date) {
    let result = new Date(date);

    for (i = 0; i < 7; ++i)
    {
        if (getGermanWeekDay(result) === 0)
            break;

        result.setDate(result.getDate()-1);
    }

    return result;
}

function getOutlineScheduleUsersOfDayStr(users, day)
{
    let count = 0;

    for (const i in users) {
        let user = users[i];
        if (!user.visible) continue;

        if (user.day_0 && day === 0) count++;
        if (user.day_1 && day === 1) count++;
        if (user.day_2 && day === 2) count++;
        if (user.day_3 && day === 3) count++;
        if (user.day_4 && day === 4) count++;
        if (user.day_5 && day === 5) count++;
        if (user.day_6 && day === 6) count++;
    }

    if (count > 0) {
        return " (" + count + ")";
    }

    return "";
}
