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
   scp -i ~/.ssh/rev-race-nl resources/views/home.blade.php \
     rev-race.nl_rifnwrwo9e@37.128.144.80:/rev-race-app/resources/views/home.blade.php
   ```
   SSH-key: `~/.ssh/rev-race-nl` (ed25519, aangemaakt 16 juli 2026). Deze key staat **niet** in
   Google Drive/git (privékey hoort niet gesynct te worden), dus op een nieuw apparaat moet een
   nieuwe key gegenereerd en de public key via Plesk (SSH Keys instellingen van de subscription)
   toegevoegd worden aan `rev-race.nl_rifnwrwo9e`. Fingerprint check: `ssh-keygen -E md5 -lf
   ~/.ssh/rev-race-nl.pub` moet matchen met wat Plesk toont.
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
ANTHROPIC_MODEL=claude-sonnet-4-6   # Fable 5 / Mythos 5 worden in code geblokkeerd

# AI-kosten en misbruikbescherming, zie config/ai.php
AI_DAILY_BUDGET_USD=2.20
AI_BUDGET_WARNING_AT=0.5
AI_LOOKUPS_PER_USER_PER_DAY=5
AI_ALERT_EMAIL=jake@helloitsme.online
AI_DASHBOARD_TOKEN=...        # eigen token voor productie, niet die van lokaal
AI_ALLOW_REMOTE_MIGRATE=false
```

## Wat er op de server NIET beschikbaar is

De chroot van de Plesk-subscription is extreem uitgekleed. Ga niet uit van standaard
Unix-tools. Wat er wel is: `grep`, `cat`, `cp`, `mv`, `rm`, `printf`, `echo`, `head`,
`tail`, `ln`, `touch`, `tar`, `ls`, `mkdir`.

Wat er **niet** is: `php` (en dus geen `artisan`), `composer`, `sed`, `awk`, `tr`, `sort`,
`wc`, `tee`, `diff`, `cmp`, `stat`, `date`, `md5sum`, `sha1sum`, `cksum`, `openssl`.

`curl` is er wel, maar zonder CA-certificaten (fout 77). Een HTTPS-call vanuit de SSH-shell,
bijvoorbeeld om de Anthropic-key te testen, zegt dus niets. Test zoiets via een Plesk-taak
(die draait buiten de chroot) of via de Laravel-log.

Praktisch gevolg: een regel in `.env` wijzigen kan niet met `sed`. Doe het zo:

```bash
cd /rev-race-app \
  && grep -v '^SLEUTEL=' .env > .env.tmp \
  && printf 'SLEUTEL=nieuwe-waarde\n' >> .env.tmp \
  && cp .env.tmp .env && rm -f .env.tmp
```

Schrijf de echte `.env` pas over als het tijdelijke bestand compleet is, dan laat een
halve mislukking je `.env` intact.

Let ook op: een `if diff ... ; then` constructie geeft hier stilzwijgend de verkeerde tak,
omdat `diff` niet bestaat en dus non-zero exit geeft. Vergelijken kan met
`grep -c -F -x -f bestand1 bestand2`.

## Migraties naar de server (let op)

Stap 5 hierboven zegt dat je bij migraties de lokale `database.sqlite` meestuurt. **Doe dat
niet meer.** Dat bestand overschrijft de productiedatabase, dus alle echte gebruikers,
garages, meldingen en AI-logs zijn dan weg. Dat was al zo, het viel alleen niet op zolang er
weinig live data was.

Migraties draaien op de server gaat zo, omdat `artisan` daar niet beschikbaar is:

1. De nieuwe migratiebestanden via `scp` naar `database/migrations/` op de server.
2. In de productie-`.env` tijdelijk `AI_ALLOW_REMOTE_MIGRATE=true` zetten.
3. `https://www.rev-race.nl/ai-migratie/<AI_DASHBOARD_TOKEN>` één keer openen. Die geeft de
   uitvoer van `migrate --force` terug als JSON.
4. `AI_ALLOW_REMOTE_MIGRATE` weer op `false` zetten. Een route die migraties kan draaien
   hoort niet open te staan, ook niet achter een token.

## AI-verbruik bekijken

`https://www.rev-race.nl/ai-gebruik/<AI_DASHBOARD_TOKEN>` toont per dag wat de Anthropic API
gekost heeft, welke IP's en accounts het verbruiken, welke zoekopdrachten het vaakst
afgewezen zijn, en de laatste 50 aanroepen met echte token-aantallen. De pagina staat op
`noindex` en geeft een 404 als het token niet klopt of niet ingesteld is.

Mollie staat nog niet zichtbaar actief in de frontend; MOLLIE_KEY leeg laten tot premium live gaat.

## Nog openstaand

- **Geplande taak / cron** voor `php artisan schedule:run` (ruimt oude `simulation_logs` op) is nog
  niet bevestigd ingesteld in Plesk. Chroot-shell heeft geen PHP, dus dit moet via Plesk's eigen
  "Geplande taken"-scherm, niet via de SSH-shell.
- Privacytekst laten juridisch nalopen voordat accounts breed publiek worden geworven.
- Back-ups voor `database.sqlite` en `.env` regelen (niet in git, alleen op de server zelf).
