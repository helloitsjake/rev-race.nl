# RevRace livegang checklist

Deze Laravel-app staat in `revrace/`. De documentatie en het oude HTML-prototype staan een niveau hoger.

## Serververeisten

- PHP 8.3 of nieuwer met PDO, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo
- Composer 2
- MariaDB/MySQL voor productie
- Webserver document root naar `revrace/public`
- HTTPS-certificaat voor `rev-race.nl`
- Cron voor Laravel scheduler:

```bash
* * * * * cd /pad/naar/revrace && php artisan schedule:run >> /dev/null 2>&1
```

## Productie `.env`

Zet minimaal:

```env
APP_NAME=RevRace
APP_ENV=production
APP_DEBUG=false
APP_URL=https://rev-race.nl

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=revrace
DB_USERNAME=revrace_user
DB_PASSWORD=...

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

ANTHROPIC_API_KEY=...
ANTHROPIC_MODEL=claude-sonnet-4-6
```

Mollie staat nog niet zichtbaar actief in de frontend. Bewaar deze keys alvast leeg of apart tot premium live gaat.

## Deploy-commando's

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

## Voor live zetten

- Vul echte `APP_KEY`, databasegegevens en `ANTHROPIC_API_KEY` in.
- Richt webroot naar `public/`, niet naar de projectroot.
- Zet `APP_DEBUG=false`.
- Test registratie, login, simulatie, share-link en garage op productie.
- Laat privacytekst juridisch nalopen voordat accounts breed publiek worden geworven.
- Configureer back-ups voor database en `.env`.
