
let userData = {};
let eventData = {};

let usersReceived = false;
let eventsReceived = false;

let startDate = new Date();
startDate.setDate(1);
startDate.setMonth(startDate.getMonth() - 2);

let endDate = new Date();
endDate.setDate(1);
endDate.setMonth(endDate.getMonth() + 1);
endDate.setDate(endDate.getDate() - 1);

// short querySelector
function _(s) {
    return document.querySelector(s);
}

function refresh() {
    // declare and fill event data
    userData = {};
    eventData = {};

    usersReceived = false;
    eventsReceived = false;

    requestUsers(function (data) {
        userData = {};
        for (let i in data) {
            let user = data[i];
            userData[user.id] = user;
        }

        usersReceived = true;
        tryBuildPage();
    });

    let startDateInput = document.querySelector("#start-date");
    if (startDateInput)
        startDate = new Date(startDateInput.value);

    let endDateInput = document.querySelector("#end-date");
    if (endDateInput)
        endDate = new Date(endDateInput.value);

    if (startDate > endDate)
        [startDate, endDate] = [endDate, startDate];

    let from = getPaddedDateString(startDate);
    let to = getPaddedDateString(endDate);

    requestEvents(from, to, function (data) {
        eventData = data;

        eventsReceived = true;
        tryBuildPage();
    });

}

refresh();

function tryBuildPage()
{
    // only after receiving both users and events can we build the statistics
    if (usersReceived && eventsReceived)
        _("#user_index").innerHTML = buildIndexHtml();
}

function createStatistics()
{
    let statistics = {};

    for (let id in userData) {
        statistics[id] = {};
        statistics[id].days = 0;
        statistics[id].weekDays = {};
        statistics[id].schedulesByWeek = {};
    }

    for (let i in eventData) {
        let event = eventData[i];

        console.log(event);

        let date = new Date(event.date);
        let dayOfWeek = getGermanWeekDay(date);
        let week = getStartOfWeek(date);

        for (let ui in event.users) {
            let user = event.users[ui];

            let userStats = statistics[user.user_id];
            userStats.days += 1;

            if (userStats.weekDays[dayOfWeek])
                userStats.weekDays[dayOfWeek] += 1;
            else
                userStats.weekDays[dayOfWeek] = 1;

            if (userStats.schedulesByWeek[week])
                userStats.schedulesByWeek[week] += 1;
            else
                userStats.schedulesByWeek[week] = 1;
        }
    }

    for (let sid in statistics) {
        let stats = statistics[sid];

        stats.doubles = 0;
        for (const week in stats.schedulesByWeek) {
            if (stats.schedulesByWeek[week] > 1)
                stats.doubles++;
        }
    }

    console.log(statistics);

    return statistics;
}

function getDateInputFormat(date)
{
    let day = ("0" + date.getDate()).slice(-2);
    let month = ("0" + (date.getMonth() + 1)).slice(-2);
    return date.getFullYear()+"-"+(month)+"-"+(day);
}

function buildIndexHtml()
{
    let statistics = createStatistics();

    const options = {
       weekday: 'long',
       year: 'numeric',
       month: 'long',
       day: 'numeric',
    };

    let html = "";

    let startDateAsInputStr = getDateInputFormat(startDate);
    let endDateAsInputStr = getDateInputFormat(endDate);

    html += buildNavHtml();

    html += '<h2>Statistik</h2>';
    html += '<span> Zeitraum: </span><br/>';

    html += '<label for="start">Startdatum:</label>';
    html += `<input type="date" id="start-date" name="start" value="${startDateAsInputStr}" onchange="refresh();" /><br/>`;
    html += '<label for="start">Enddatum:</label>';
    html += `<input type="date" id="end-date" name="end" value="${endDateAsInputStr}" onchange="refresh();"/><br/><br/>`;

    html += '<table class="statistics">';
    html += '<tr>';
    html += '<th></th>';
    html += '<th>Dienste</th>';
    html += '<th>Doppel+</th>';
    html += '<th>Mo</th>';
    html += '<th>Di</th>';
    html += '<th>Mi</th>';
    html += '<th>Do</th>';
    html += '<th>Fr</th>';
    html += '<th>Sa</th>';
    html += '<th>So</th>';
    html += '</tr>';

    let sorted = [];
    for (let id in userData) {
        sorted.push(userData[id]);
    }

    sorted.sort(function (a, b) {
        let statsA = statistics[a.id];
        let statsB = statistics[b.id];

        let keyA = statsB.days + "-" + statsB.doubles + "-" + a.display_name;
        let keyB = statsA.days + "-" + statsA.doubles + "-" + b.display_name;

        return keyA.localeCompare(keyB);
    });

    for (const i in sorted) {
        let user = sorted[i];
        let id = user.id;

        if (user.visible) {
            let stats = statistics[id];

            html += '<tr>';
            html += '<td>' + user.display_name + '</td>';
            html += '<td>' + stats.days + '</td>';
            html += '<td>' + stats.doubles + '</td>';
            html += '<td>' + (stats.weekDays[0] ? stats.weekDays[0] : "") + '</td>';
            html += '<td>' + (stats.weekDays[1] ? stats.weekDays[1] : "") + '</td>';
            html += '<td>' + (stats.weekDays[2] ? stats.weekDays[2] : "") + '</td>';
            html += '<td>' + (stats.weekDays[3] ? stats.weekDays[3] : "") + '</td>';
            html += '<td>' + (stats.weekDays[4] ? stats.weekDays[4] : "") + '</td>';
            html += '<td>' + (stats.weekDays[5] ? stats.weekDays[5] : "") + '</td>';
            html += '<td>' + (stats.weekDays[6] ? stats.weekDays[6] : "") + '</td>';
            html += '</tr>';
        }
    }

    html += '</table>';

    return html;
}
