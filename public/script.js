// Start
_("#calendar").innerHTML = calendar();
 
// short querySelector
function _(s) {
  return document.querySelector(s);
}
 
// show info
function showInfo(event) {
  
    let obj = {
        "5/7/2023" : {
            type : "Veranstaltung",
            title : "Großputz",
            time : "16:00",
            venue : "NewForce",
            location : "Buckenhofer Weg 69",
            desc : "Wir putzen ihr Spasten",
            more : "Mehr Info gibts net"
        },
        "5/6/2023" : {
            type : "Veranstaltung",
            title : "Blasts In Brucklyn",
            time : "20:00",
            venue : "NewForce",
            location : "Buckenhofer Weg 69",
            desc : "Blasts In Brucklyn",
            more : "Fette Blasts"
        },
        "5/5/2023" : {
            type : "Veranstaltung",
            title : "Masters Of Metal",
            time : "20:00",
            venue : "NewForce",
            location : "Buckenhofer Weg 69",
            desc : "Heavy, Pagan, Power",
            more : "Fette Blasts"
        }

    }; 
    
    console.log(event);

    for (var key in obj) {
        console.log(key);
           
        // if has envent add class
        if (_('[data-id="' + key + '"]')) {
            _('[data-id="' + key + '"]').classList.add("event");
            
            console.log("data id");
        }
        
        if (event === key) {
            _("#calendar_data").classList.toggle("show_data");
            
            value = obj[key];
            // template info
            var data =
                '<a href="#" class="hideEvent" ' +
                'onclick="return hideEvent();">&times;</a>' +
                "<h3>" +
                value.type +
                "</h3>" +
                "<dl>" +
                "<dt><dfn>Title:</dfn></dt><dd>" +
                value.title +
                "</dd>" +
                "<dt><dfn>Hour:</dfn></dt><dd>" +
                value.time +
                "</dd>" +
                "<dt><dfn>Venue:</dfn></dt><dd>" +
                value.venue +
                "</dd>" +
                "<dt><dfn>Location:</dfn></dt><dd>" +
                value.location +
                "</dd>" +
                "<dt><dfn>Description:</dfn></dt><dd>" +
                value.desc +
                "</dd>" +
                '<dt><dfn>More Info:</dfn></dt><dd><a href="' +
                value.more +
                '" title="More info">Here</a></dd>' +
                "</dl>";

            return (_("#calendar_data").innerHTML = data);
        }
    }

    return false;
}
// toggle event show or hide
function hideEvent() {
  _("#calendar_data").classList.toggle("show_data");
}
 
function germanDay(calendar) {
    // input: 0-6 = Sonntag -> Montag
    // output: 0-6 = Montag -> Sonntag
    gday = calendar.getDay() - 1;
    if (gday === -1) gday = 6;
    
    return gday;
}

// simple calendar
function calendar() {
  // show info on init
  showInfo();
  requestEvents();
 
  // vars
  var day_of_week = new Array("Mo", "Di", "Mi", "Do", "Fr", "Sa", "So"),
    month_of_year = new Array(
      "Januar",
      "Februar",
      "März",
      "April",
      "Mai",
      "Juni",
      "Juli",
      "August",
      "September",
      "Oktober",
      "November",
      "Dezember"
    ),
    Calendar = new Date(),
    year = Calendar.getYear(),
    month = Calendar.getMonth(),
    today = Calendar.getDate(),
    weekday = germanDay(Calendar),
    html = "";
 
  // start in 1 and this month
  Calendar.setDate(1);
  Calendar.setMonth(month);
 
  // template calendar
  html = "<table>";
  // head
  html += "<thead>";
  html +=
    '<tr class="head_cal"><th colspan="7">' +
    month_of_year[month] +
    "</th></tr>";
  html +=
    '<tr class="subhead_cal"><th colspan="7">' +
    Calendar.getFullYear() +
    "</th></tr>";
  html += '<tr class="week_cal">';
  for (index = 0; index < 7; index++) {
    if (weekday == index) {
      html += '<th class="week_event">' + day_of_week[index] + "</th>";
    } else {
      html += "<th>" + day_of_week[index] + "</th>";
    }
  }
  html += "</tr>";
  html += "</thead>";
 
  // body
  html += '<tbody class="days_cal">';
  html += "</tr>";
 
  // white zone
  for (index = 0; index < germanDay(Calendar); index++) {
    html += '<td class="white_cal"> </td>';
  }
 
  for (index = 0; index < 31; index++) {
    if (index < Calendar.getDate()) {
      week_day = germanDay(Calendar);
 
      if (week_day === 0) {
        html += "</tr>";
      }
      
      if (week_day !== 7) {
        // this day
        var day = Calendar.getDate();
        var info = Calendar.getMonth() + 1 + "/" + day + "/" + Calendar.getFullYear();
 
        if (today === Calendar.getDate()) {
          html +=
            '<td><a class="today" href="#" data-id="' +
            info +
            '" onclick="return showInfo(\'' +
            info +
            "')\">" +
            day +
            "</a></td>";
 
          showInfo(info);
        } else {
          html +=
            '<td><a href="#" data-id="' +
            info +
            '" onclick="return showInfo(\'' +
            info +
            "')\">" +
            day +
            "</a></td>";
        }
      }
 
      if (week_day == 7) {
        html += "</tr>";
      }
    }
 
    Calendar.setDate(Calendar.getDate() + 1);
  } // end for loop
  
  return html;
}
 
//   Get Json data
function getjson(url, callback) {
  var ajax = new XMLHttpRequest();
  
  ajax.open("GET", url, true);
  ajax.onreadystatechange = function () {
    if (ajax.readyState == 4) {
      if (ajax.status == 200) {
        var data = JSON.parse(ajax.responseText);
        return callback(data);
      } else {
        console.log(ajax.status);
      }
    }
  };
  ajax.send();
}

