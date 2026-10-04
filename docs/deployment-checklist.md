# Deployment checklist — Hostinger KVM 2 VPS

Work through this once, in order, when setting up the VPS. Steps marked **(you)** need hosting/DNS access I don't have; the rest are commands to run on the server. Nothing here has been run against the real VPS — I verified the underlying commands work on this dev machine only (PHP 8.3, MySQL 8, the backup commands), not on Hostinger itself.

## 1. Server and domain **(you)**

- [ ] Provision the KVM 2 VPS, ideally with a data centre near the Philippines, using the Laravel or LAMP template.
- [ ] Point your domain's DNS A record at the VPS's IP address.
- [ ] Open only ports 22 (SSH), 80 and 443 in the firewall.

## 2. System packages

Confirm each is present; install whatever the template didn't already include.

- [ ] PHP 8.3 or newer, with these extensions: `pdo_mysql mbstring openssl fileinfo gd zip intl curl bcmath ctype tokenizer xml`. Check with `php -m`. `zip` matters here specifically — the backup command builds a zip archive and fails without it.
- [ ] A MySQL or MariaDB **client** package (gives you `mysqldump`), separate from the server package if they're split. The backup command shells out to `mysqldump`; check with `which mysqldump`.
- [ ] MySQL 8 server, Composer, and Nginx or Apache.
- [ ] A Redis server (`apt install redis-server` on Ubuntu, or your distro's equivalent), for sessions and cache. No PHP Redis extension needed — the app uses `predis/predis`, a pure-PHP client (see `docs/adr/0005-redis-for-cache-and-sessions-via-predis.md`).
- [ ] Node/npm is **not** required. Every page links Sneat's CSS/JS directly from `public/sneat`; nothing here goes through `npm run build`.

## 3. Database

- [ ] Create the database and a dedicated MySQL user (don't use `root`) with a strong password, and grant it access to only that database.

## 4. Application

- [ ] Upload or `git clone` the repository to the server.
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] Copy `.env.example` to `.env` and set at least:
  - `APP_ENV=production`, `APP_DEBUG=false`
  - `APP_URL=https://your-domain` (must be `https`, not `http` — phone cameras only scan QR codes on secure pages)
  - `DB_*` to the database from step 3
  - `REDIS_HOST` (usually `127.0.0.1` if Redis runs on the same VPS) and `REDIS_PASSWORD` if you set one on the Redis server itself
  - `MAIL_*` to a real SMTP account. It's currently `log` in dev, which silently swallows the backup-failure emails from `config/backup.php`. Without this, nobody finds out if a backup fails.
  - `BACKUP_NOTIFICATION_EMAIL` to whoever should get those emails (defaults to `MAIL_FROM_ADDRESS` if unset)
  - `SESSION_LIFETIME` is set to 720 minutes (12 hours) instead of Laravel's 120-minute default, since a Security gate shift or a long registration day shouldn't get logged out mid-shift. Adjust to match your longest real shift.
- [ ] `php artisan key:generate --force`
- [ ] `php artisan migrate --force`
- [ ] Make `storage/` and `bootstrap/cache/` writable by the web server's user.
- [ ] Point the web server's document root at `public/`, not the repository root.
- [ ] Get an HTTPS certificate (for example via certbot) and confirm plain `http://` redirects to `https://`.
- [ ] Create the first Admin: `php artisan app:create-admin` (it prompts for username and password — don't script the password into a file).
- [ ] Confirm Redis is actually being used: `redis-cli ping` should return `PONG`, and after visiting the site once, `redis-cli keys '*'` should show session and cache keys.

## 5. Scheduled backups

`routes/console.php` schedules three commands: `backup:run --only-db` at 02:00, `backup:clean` at 02:30, `backup:monitor` at 02:45. Laravel's scheduler doesn't run by itself — it needs one cron entry that fires every minute and lets Laravel decide what's actually due:

- [ ] Add this cron entry for the app's user: `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`
- [ ] After that's in place, confirm a backup actually lands: `php artisan backup:run --only-db`, then check `storage/app/private/AYP Registration/` for a new `.zip`.
- [ ] Backups stay on the VPS's own disk only (see `docs/adr/0003-database-backups-local-disk-for-now.md`) — if the VPS is lost, its backups go with it. Until offsite storage is added, periodically copy the backup folder somewhere else by hand (for example `scp` it down after a big registration day).

## 6. No queue worker needed

Nothing in the app dispatches a queued job today, so a `queue:work` process (and the supervisor config to keep it alive) isn't needed. If that changes later, add one then.

## 7. Before telling people the URL

- [ ] Confirm the Sneat licence. The downloaded template's own header says "Pro" and requires a purchased licence, which conflicts with the "free" description on the page it came from — this was flagged earlier and never confirmed.
- [ ] Add the privacy notice once it exists (not yet built).
- [ ] Sign in as the Admin you created, add the real Circuits and Local Churches, and create the Circuit President accounts.
