# RevRace livegang & deploy

Deze Laravel-app staat in `revrace/`. De documentatie en het oude HTML-prototype staan een niveau
hoger. Live op **www.rev-race.nl**, gehost bij **Hostnet shared hosting via Plesk** (geen VPS,
geen root-toegang).

## Hosting realiteit

- Plesk-abonnement, domein `rev-race.nl` (met www als hoofddomein, non-www redirect via `.htaccess`)
- Documentroot: `/rev-race-app/public` (ingesteld in Plesk Hostinginstellingen)
- SSH-toegang staat op **chrooted bash** (`/bin/bash (chrooted)`), afgeschermd tot de eigen
  hosting-map. Deze shell heeft **geen PHP, geen composer, geen git**: alleen `bash, cat, chmod,
  cp, curl, grep, ls, mkdir, mv, rm, scp, tar, touch, unzip, vi, wget, zip`.
- Plesk's eigen Git-extensie is **niet in gebruik**: die liep vast op een kapotte
  working-tree-configuratie zodra het publicatiepad na de eerste keer werd gewijzigd. GitHub
  blijft wel de bron van waarheid voor de code, alleen niet als deploy-mechanisme.
- Database is **SQLite** (`database/database.sqlite`), bewust gekozen boven MySQL: scheelt
  database-aanmaken in Plesk en credentials beheren, en is ruim voldoende voor het huidige
  verkeersvolume.

## Deploy-werkwijze (omdat composer/artisan niet op de server draaien)

1. Lokaal wijzigen in `revrace/`, testen met `php artisan test`.
2. `git commit` + `git push` naar `github.com/helloitsjake/rev-race.nl` (branch `main`).
3. Gewijzigde bestanden direct via `scp` naar de server kopiëren, bijvoorbeeld:
   ```bash
   scp -i <ssh-key> resources/views/home.blade.php \
     rev-race.nl_rifnwrwo9e@37.128.144.80:/rev-race-app/resources/views/home.blade.php
   ```
4. CSS/JS in `public/` hebben cache busting via `filemtime()` in de querystring
   (`layouts/app.blade.php`, `partials/simulation-panel.blade.php`) — geen handmatige
   cache-clear nodig na een update.
5. Bij wijzigingen die composer-dependencies of database-migraties nodig hebben: dat **lokaal**
   uitvoeren (`composer install --no-dev`, `php artisan migrate`), en de resulterende bestanden
   (`vendor/`, `database/database.sqlite`) via `scp` naar de server overzetten. Er is geen manier
   om `composer`/`artisan` rechtstreeks op de server te draaien.

## Productie `.env` (staat al op de server, niet in git)

```env
APP_NAME=RevRace
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.rev-race.nl

DB_CONNECTION=sqlite

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

ANTHROPIC_API_KEY=...
ANTHROPIC_MODEL=claude-sonnet-4-6
```

Mollie staat nog niet zichtbaar actief in de frontend; MOLLIE_KEY leeg laten tot premium live gaat.

## Nog openstaand

- **Geplande taak / cron** voor `php artisan schedule:run` (ruimt oude `simulation_logs` op) is nog
  niet bevestigd ingesteld in Plesk. Chroot-shell heeft geen PHP, dus dit moet via Plesk's eigen
  "Geplande taken"-scherm, niet via de SSH-shell.
- Privacytekst laten juridisch nalopen voordat accounts breed publiek worden geworven.
- Back-ups voor `database.sqlite` en `.env` regelen (niet in git, alleen op de server zelf).
