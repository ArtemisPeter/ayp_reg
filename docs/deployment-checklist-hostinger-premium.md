# Deployment checklist — Hostinger Premium (shared hosting)

Use this instead of `deployment-checklist.md`, which is written for a VPS. Premium is shared hosting: there is no root access, no Redis, and the web root is fixed at `public_html`.

Work through it once, in order. Nothing here has been run against the real Hostinger account. Steps marked **(check)** depend on how Hostinger has set up your account, so confirm them there rather than trusting this page.

## 1. In hPanel, before uploading anything

- [ ] Add the domain or subdomain the system will use. The app must sit at the root of it (`https://reg.example.com/`), not in a subfolder (`https://example.com/reg/`), because the pages link to `/dashboard`, `/attendees` and so on.
- [ ] Turn on the free SSL certificate for that domain and switch on "Force HTTPS". The gate scanner's camera only works on `https://` pages.
- [ ] PHP Configuration: choose PHP 8.3 or 8.4.
- [ ] PHP Configuration, extensions: make sure `zip`, `pdo_mysql`, `mbstring`, `fileinfo` and `openssl` are ticked. `zip` matters most: without it every page fails, not only backups.
- [ ] Databases: create a MySQL database and a database user with a strong password. Write down the database name, username, password and host that hPanel shows.
- [ ] Advanced, SSH Access: enable SSH and note the host, port, username and password.

## 2. Upload the application

The project must live **beside** `public_html`, never inside it. If the whole project goes into `public_html`, the `.env` file with the database password becomes downloadable.

- [ ] Connect over SSH and go to the domain's folder. **(check)** On Hostinger this is normally `~/domains/your-domain/`, with `public_html` inside it.
- [ ] Put the project in a folder next to `public_html`, for example `~/domains/your-domain/ayp_reg`. Either `git clone` your repository there, or upload a zip and extract it.
- [ ] Leave these out of the upload; the server does not need them: `vendor/`, `node_modules/`, `sneat-template/`, the root `images/` folder, `tests/`, `.env`.

## 3. Point the web root at `public/`

- [ ] Replace `public_html` with a link to the app's `public` folder:

  ```
  cd ~/domains/your-domain
  mv public_html public_html_original
  ln -s ~/domains/your-domain/ayp_reg/public public_html
  ```

- [ ] If the link is not allowed on your account **(check)**, use the fallback: copy everything inside `ayp_reg/public/` into `public_html/`, then edit `public_html/index.php` so its two `__DIR__.'/../` paths point at the app folder (`__DIR__.'/../ayp_reg/`). You then have to re-copy `public/` after every update.

## 4. Install and configure

Run these over SSH from inside the `ayp_reg` folder.

- [ ] Check the command-line PHP version with `php -v`. It must be 8.3 or newer. **(check)** If it is older, Hostinger provides versioned binaries; use that full path in place of `php` in every command below and in the cron job.
- [ ] `composer install --no-dev --optimize-autoloader` **(check)** On Hostinger shared hosting the Composer 2 command may be `composer2`.
- [ ] `cp .env.example .env`, then edit `.env` and set:

  ```
  APP_ENV=production
  APP_DEBUG=false
  APP_URL=https://your-domain

  DB_CONNECTION=mysql
  DB_HOST=localhost
  DB_DATABASE=(from hPanel)
  DB_USERNAME=(from hPanel)
  DB_PASSWORD=(from hPanel)

  SESSION_DRIVER=database
  SESSION_SECURE_COOKIE=true
  CACHE_STORE=database
  QUEUE_CONNECTION=sync

  LOG_LEVEL=warning
  ```

  `SESSION_DRIVER` and `CACHE_STORE` are the important difference from the VPS checklist. `.env.example` sets both to `redis`, and Premium has no Redis server, so leaving them unchanged makes every page fail. The `REDIS_*` lines can be deleted. **(check)** `DB_HOST=localhost` is the usual value; use whatever hPanel shows.

- [ ] `php artisan key:generate --force`
- [ ] `php artisan migrate --force`
- [ ] Do **not** run `php artisan db:seed`, and do not import your local database. Both would create the demo accounts (`demo.admin` and the rest) with the password `Demo-Pass-1`.
- [ ] `php artisan app:create-admin` and answer the prompts. This creates the first real Admin.
- [ ] `chmod -R 775 storage bootstrap/cache`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`

## 5. First check in the browser

- [ ] Open `https://your-domain`. The public page should load, and `http://` should redirect to `https://`.
- [ ] Sign in as the Admin you created.
- [ ] Add the real Circuits and their Local Churches.
- [ ] Add the Youth Camp, then click "Make current" on it. Registration stays closed until a camp is current.
- [ ] Create the Secretary, Circuit President, Security and Tabulator accounts.
- [ ] Open the gate scanner on a phone and confirm the camera starts.
- [ ] Open `https://your-domain/.env`. It must show "not found", not the file.

## 6. Backups

The app schedules a database backup at 02:00, a cleanup at 02:30 and a health check at 02:45, Philippine time. On shared hosting these may not be able to run, because they need two things Hostinger may not allow:

- [ ] `php -r "echo ini_get('disable_functions');"` — if the output lists `proc_open`, the scheduled backups cannot run.
- [ ] `which mysqldump` — if it prints nothing, the backups cannot run.

If both are fine:

- [ ] In hPanel, Advanced, Cron Jobs, add a job that runs every minute: `cd ~/domains/your-domain/ayp_reg && php artisan schedule:run >> /dev/null 2>&1`
- [ ] Test once by hand: `php artisan backup:run --only-db`, then look in `storage/app/private/AYP Registration/` for a new `.zip`.
- [ ] To be emailed when a backup fails, set the `MAIL_*` values in `.env` to a real mailbox and `BACKUP_NOTIFICATION_EMAIL` to your address, then run `php artisan config:cache` again. With the default `MAIL_MAILER=log`, failures are silent.

If either check fails:

- [ ] Skip the cron job and rely on hPanel's own database backups (Files, Backups). Download a copy by hand after each big registration day.

Either way, backups made by the app stay on the same hosting account as the site. Copy them somewhere else from time to time.

## 7. Updating the site later

- [ ] Upload or `git pull` the new code.
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan migrate --force`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`

## 8. Before telling people the URL

- [ ] The privacy page (`/privacy`) still shows a red "Draft for SMD to review" notice and `[SMD contact name]` placeholders. Replace them with the real contact details.
- [ ] Confirm the Sneat template licence (see section 7 of `deployment-checklist.md`).
