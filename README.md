# How To Install
## Preamble
* Zunächst muss eine SQL-Datenbank erstellt und konfiguriert werden. 
* Der Dienstplan braucht Zugriff auf einen SMTP-Mailserver, um Emails verschicken zu können.
* *Note:* Wir gehen hier auf dem Server von einem Ordner `html/dienstplan/` aus.

## Installation
* Alle Dateien & Ordner aus `public/` in den Ordner `html/dienstplan/` kopieren.
* In `html/dienstplan/DatabaseInfo.php` können nun wichtige Passwort- und Authentifizierungs-Infos konfiguriert werden.
* Aus dem Browser heraus `www.my-url.net/dienstplan/install.php` aufrufen
* **"Auf Werkseinstellungen zurücksetzen"** oder **"Auf Beispiel-DB zurücksetzen"** klicken
* Hier wird das in `html/dienstplan/DatabaseInfo.php` gesetzte `installPassword` benötigt
* Danach kann `www.my-url.net/dienstplan/` aufgerufen werden und sich mit username **admin** und admin-PW eingeloggt werden
* Dann sollte ein Manager-User erstellt werden, der dann weiterhin genutzt wird, um sämtliche weiteren Einstellungen zu konfigurieren
* Fertig!

# Allgemeine Infos
* Der Dienstplan liegt in einem github Repository, unter: https://github.com/kaaashy/NewForceDienstplan (Feel free to clone!)
