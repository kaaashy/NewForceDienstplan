<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TODO-Liste</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        h1, h2 {
            background-color: #f0f0f0;
            padding: 10px;
            border-left: 5px solid #007BFF;
        }
        h1 {
            font-size: 24px;
        }
        h2 {
            font-size: 20px;
        }
        ul {
            list-style-type: disc;
            margin-left: 20px;
        }
        ul ul {
            list-style-type: circle;
            margin-left: 20px;
        }
    </style>
</head>
<body>

<h1>TODO-Liste</h1>

<h2>Core Functionality</h2>
<ul>
    <li>Verantwortliche Person in Wochenansicht anzeigen</li>
    <li>Doppeldienste in einer Woche hervorheben</li>
    <li>MA-Übersicht: Haken für "inaktive MA ausblenden"</li>
    <li>Separater Veranstaltungstyp Putzdienst</li>
    <li>Eingeteilt vs. Eingetragen -> Markierung nicht "DIENST" sondern "EINGETRAGEN"</li>
    <li>Veranstaltung nach Bearbeiten nicht unbedingt schließen</li>
    <li>Error handling: Proper error messages everywhere, kein echo in .php files</li>
    <li>Bug: Switchen zwischen Monat und Woche switcht manchmal in den falschen Monat/Woche</li>
    <li>Datenbank-Infos + Technisches verwalten (Admin)</li>
    <li>Registrierungs-Email: Passwort zurücksetzen</li>
    <li>Berechtigungen für alle einsehbar machen</li>
    <li>Angemeldet bleiben? -> Auswahl</li>
    <li>PW-Reset-Email verfallen lassen</li>
    <li>Do: 2 MA, Fr+Sa: 4 MA</li>
    <li>Email-Verification bei Änderung der Email</li>
    <li>MA löschen+einladen+verwalten -> 1 Berechtigung</li>
    <li>Dienste planen + sperren -> 1 Berechtigung</li>
    <li>Als andere einloggen + Rechte Verwalten -> 1 Berechtigung</li>
    <li>Rahmendienstplan Summe anzeigen</li>
</ul>

<h2>Mobile-Version</h2>
<ul>
    <li>Exakte Details bisher unklar, muss aber vollständig über Mobile benutzbar sein</li>
</ul>

<h2>Doku</h2>
<ul>
    <li>Wie installieren?</li>
    <li>Wo/Was Code?</li>
    <li>Wie konfigurieren?</li>
    <li>Welche Versionen von welcher Software sind nötig?</li>
    <li>Wie pflegen?</li>
</ul>

<h2>Aufhübschen</h2>
<ul>
    <li>Hübsches CSS für Profile</li>
    <li>Hübsches CSS für Admin-Bereich</li>
    <li>User-Farben</li>
    <li>Event-Farben</li>
</ul>

<h2>Fragen:</h2>
<ul>
    <li>Veranstaltungen: Braucht es separate Rechte für Bearbeiten/Erstellen/Löschen? -> Glaube nicht</li>
    <li>Mitarbeitende: Braucht es separate Rechte für Verstecken/Deaktivieren? -> Glaube nicht</li>
</ul>

<h1>Done-Liste</h1>
<ul>
    <li>Wochen-Übersicht für Posting in MatterMost, Senden via Email (html)</li>
    <li>Berechtigungen</li>
    <li>Unterschiedliche Formulare für Eintragen</li>
    <li>Ein-/Austragen bei Veranstaltungen</li>
    <li>Andere MA ein-/austragen bei Veranstaltungen</li>
    <li>Als andere MA einloggen</li>
    <li>Technisches verwalten</li>
    <li>Dienst-Statistiken</li>
    <li>Veranstaltung sperren/entsperren</li>
    <li>Keine doppelten Email-Adressen</li>
    <li>Passwort-Reset</li>
    <li>Mitarbeitende aus Veranstaltungen entfernen</li>
    <li>Mitarbeitende deaktivieren</li>
    <li>Mitarbeitende ausblenden</li>
    <li>Mitarbeitende löschen</li>
    <li>Neue Mitarbeitende einladen</li>
    <li>Email-Versand</li>
    <li>Logout</li>
    <li>Emojis für Verschiedenes</li>
    <li>Tage von Wochen, die in den Monat gehen, werden noch gezeigt</li>
    <li>Eigene Veranstaltungen sind hervorgehoben</li>
    <li>MA nach Namen sortiert</li>
    <li>Bug: Ansicht ist manchmal kaputt und zeigt wenige oder keine Tage</li>
    <li>Bug: Bei löschen müssen alle Formulare korrekt ausgefüllt sein, sollte nicht nötig sein</li>
    <li>Monatsübersicht: Tage von Wochen auch anzeigen, wenn sie streng genommen nicht zum Monat gehören</li>
    <li>Bei Löschen von Events/MA muss auch Schedule/OutlineSchedule aktualisiert werden</li>
    <li>Leute alphabetisch listen</li>
    <li>Veranstaltungen, an denen man selbst eingetragen ist, hervorheben</li>
    <li>Profile </li>
    <li>Kein Austragen wenn Min-MA unterschritten würde</li>
    <li>MA können sich selbst bei Events ein- und austragen</li>
    <li>Warnungen bei nicht-genügend-MA im Event</li>
    <li>Minimum-Anzahl Mitarbeiter</li>
    <li>Übersicht über alle MA bei Veranstaltungen in Wochenansicht</li>
    <li>Wochenansicht</li>
    <li>Eventupdate: Kein Datums-Update mehr möglich (wegen Rahmendienstplan)</li>
    <li>End-Uhrzeit</li>
    <li>Rahmendienstplan</li>
    <li>MA eintragen und austragen</li>
    <li>Rahmendienstplanänderungen an Veranstaltungen aktualisieren</li>
    <li>Monate blättern</li>
    <li>Events erstellen, bearbeiten, löschen</li>
    <li>Hübsches CSS für Kalender</li>
    <li>Logins</li>
    <li>Admin-Bereich</li>
    <li>Kalenderansicht</li>

</ul>


</body>
</html>
