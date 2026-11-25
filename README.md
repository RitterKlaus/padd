# Star Trek PADD

A web app to manage and display handouts for Star Trek roleplaying games.

Diese App soll ein "PADD" aus Star Trek simulieren und soll dazu dienen, im Rollenspiel "Star Trek Adventures" Handouts anzuzeigen.

- Der Spielleitende kann sich einloggen und Handouts vorbereiten.
- Die Spieler bekommen ein Gerät wie ein iPad mini oder einen Amazon Kindle im Kioskmodus.
- Der Spielleitende kann nach und nach Handouts freischalten, die auf dem Gerät angezeigt werden.

## Installation auf einem Webserver

1. git clone
2. cp .env.example .env
3. Datenbankverbindung eintragen (beim Webhoster eine neue Datenbank, User und Passwort anlegen)
4. Dabei auch `APP_ENV=production` und `APP_DEBUG=false` setzen.
5. composer install --no-dev
6. Falls das nicht funktioniert, Composer laut https://getcomposer.org/download/ einrichten und `php composer.phar install --no-dev`ausführen
7. Mit `php artisan key:gen` den Key der Laravel-Anwendung generieren (wird in der .env gespeichert)
8. Mit `php artisan migrate` die Datenbank initialisieren.
9. Die LCARS-Dateien in `public/LCARS` kopieren, siehe [public/LCARS/README.md](public/LCARS/README.md).

## Entwicklung

Details folgen noch.

### Testen der Registrierung per Mail

Es hat sich bewährt hier `mailpit` zu nutzen. Unter http://127.0.0.1:8025/ ist eine Oberfläche erreichbar, die alle versendeten E-Mails anzeigt.

Mailpit ist in der `.env.example` vorkonfiguriert.
