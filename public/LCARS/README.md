# LCARS-Dateien

Das PADD zeigt Handouts in einem LCARS-Design. Die Dateien dafür stammen von https://www.thelcars.com und werden nicht mit dieser App ausgeliefert. Sie müssen separat heruntergeladen und in dieses Verzeichnis kopiert werden:

- `classic.html`
- `lower-decks.html`
- `lower-decks-padd.html`
- `nemesis-blue.html`
- `picard.html`
- `voyager.html`
- `assets/` mit allen Dateien

Die Dateien bleiben unverändert: Die App setzt den Text des Handouts erst bei der Anzeige ein. Der Hinweis auf TheLCARS.com im Fußbereich darf nicht entfernt werden.

Auf einen Server kopieren (Beispiel):

```bash
rsync -az public/LCARS/ user@server.example.de:/home/user/padd/public/LCARS/
```
