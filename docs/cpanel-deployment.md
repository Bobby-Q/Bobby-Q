# cPanel deployment guide for suite.maur.co.ke

This guide explains how to deploy the Laravel app to the cPanel account for `suite.maur.co.ke`.

## Current access status

Deployment from this coding runner is currently blocked because outbound connections to the cPanel host and the target domain are not reachable from the environment. The attempted checks returned network errors for:

- `rs8.rcnoc.com:2083` - cPanel HTTPS.
- `rs8.rcnoc.com:22` - SSH.
- `suite.maur.co.ke:80` and `suite.maur.co.ke:443` - website HTTP/HTTPS.

Because of that, no files were uploaded and no production secret was committed.

## Production database

Use this database configuration in the server `.env` file:

```dotenv
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=crossha1_suite
DB_USERNAME=crossha1_suite
DB_PASSWORD=your-production-password
```

The repository includes `.env.production.example` with the production-safe non-secret values for `suite.maur.co.ke`.

## Recommended cPanel layout

The safest Laravel layout is:

```text
/home3/crossha1/suite-app/              # Laravel application root, not public web root
/home3/crossha1/suite.maur.co.ke/       # domain document root pointing to Laravel public files
```

If cPanel lets you change the domain document root, point `suite.maur.co.ke` directly to:

```text
/home3/crossha1/suite-app/public
```

If cPanel does not allow that, keep the application outside the public directory and copy only the `public/` folder contents into the domain document root. Then edit the copied `index.php` paths to point back to the application root.


## Fixing 404 after cPanel Git deployment

If cPanel successfully pulls files from Git but `https://suite.maur.co.ke` returns **404**, the most likely issue is that Apache is not serving Laravel's `public/index.php` front controller.

Laravel must be deployed one of these two ways:

### Preferred fix: point the subdomain document root to `public/`

In cPanel:

1. Open **Domains** or **Addon Domains**.
2. Find `suite.maur.co.ke`.
3. Click **Manage**.
4. Set the document root to the Git checkout's `public` folder.

Example if the Git checkout is:

```text
/home3/crossha1/repositories/Bobby-Q
```

then the subdomain document root should be:

```text
/home3/crossha1/repositories/Bobby-Q/public
```

If the checkout is instead placed at:

```text
/home3/crossha1/suite-app
```

then use:

```text
/home3/crossha1/suite-app/public
```

After changing the document root, visit:

```text
https://suite.maur.co.ke/
https://suite.maur.co.ke/dashboard
```

### Fallback fix: keep app private and copy only `public/` to the subdomain root

Use this only if cPanel does not let you point the subdomain to the app's `public/` directory.

Recommended layout:

```text
/home3/crossha1/suite-app/              # full Laravel app from Git
/home3/crossha1/suite.maur.co.ke/       # public web root
```

Copy the contents of:

```text
/home3/crossha1/suite-app/public/
```

into:

```text
/home3/crossha1/suite.maur.co.ke/
```

Then edit `/home3/crossha1/suite.maur.co.ke/index.php` so these three paths point to the private app directory:

```php
if (file_exists($maintenance = __DIR__.'/../suite-app/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../suite-app/vendor/autoload.php';

$app = require_once __DIR__.'/../suite-app/bootstrap/app.php';
```

Do **not** copy `.env`, `vendor`, `storage`, `app`, `config`, or database files into a public web root. Only the files from Laravel's `public/` folder should be web-accessible.

### After fixing the document root

Run these from cPanel **Terminal** inside the Laravel application root:

```bash
cd /home3/crossha1/repositories/Bobby-Q
cp .env.production.example .env
php artisan key:generate --force
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If your Git checkout is not `/home3/crossha1/repositories/Bobby-Q`, replace that path with the actual cPanel Git checkout path.

In the server `.env`, set the database values to:

```dotenv
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=crossha1_suite
DB_USERNAME=crossha1_suite
DB_PASSWORD=your-production-password
```

If the page changes from 404 to a Laravel error, that is progress: it means Apache is now reaching Laravel and the remaining problem is usually `.env`, `APP_KEY`, Composer dependencies, permissions, or migrations.

## Build a release archive locally

From the project root:

```bash
composer install
npm install
./scripts/build-cpanel-release.sh
```

The script creates a timestamped archive under `release/`.

## Upload and unpack

Upload the archive to cPanel using File Manager, FTP, SFTP, or SSH if enabled.

Then extract it to the private application directory, for example:

```text
/home/crossha1/suite-app
```

Do not place the full Laravel application inside a public web directory unless the domain document root points to the app's `public/` directory.

## Configure `.env` on cPanel

On the server:

1. Copy `.env.production.example` to `.env`.
2. Fill in `APP_KEY` and `DB_PASSWORD`.
3. Keep `APP_DEBUG=false`.
4. Keep `APP_URL=https://suite.maur.co.ke`.

Generate the app key on the server if SSH/Terminal is available:

```bash
php artisan key:generate --force
```

If SSH/Terminal is unavailable, generate the key locally and paste only the generated `APP_KEY` into cPanel's `.env` file.

## Run production commands

If cPanel Terminal or SSH is available, run:

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If Terminal is unavailable, use cPanel's Setup Python App/Terminal alternatives, ask the host to enable Terminal, or provide FTP/SFTP plus cPanel Terminal access so migrations can be executed safely.

## Verification

After deployment, visit:

```text
https://suite.maur.co.ke/
https://suite.maur.co.ke/dashboard
```

The first page should show the dashboard shell.

Also verify that security headers are present:

```bash
curl -I https://suite.maur.co.ke/
```

Expected headers include:

- `X-Frame-Options: SAMEORIGIN`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy: ...`

## What I need if you want me to deploy directly

The current runner cannot reach the cPanel host. To deploy directly, I need one of these to be reachable from the runner:

1. cPanel API access on port `2083`.
2. SSH access on port `22` or the custom SSH port used by the host.
3. FTP/SFTP details and a way to run `php artisan migrate --force` on the server.

If the host has IP allowlisting, ask them to allow this runner's outbound environment or provide a deployment method that is reachable over standard HTTPS/SSH.

## No-terminal Git deployment at `/git`

If cPanel deploys this repository into a web-accessible folder such as:

```text
https://suite.maur.co.ke/git/
```

then two separate things must be true before the real Laravel app can run.

### 1. The URL must enter through `public/`

Laravel's browser entry point is:

```text
public/index.php
```

This repository now includes a small root `index.php` and root `.htaccess` helper so a request to `/git/` redirects to `/git/public/` instead of showing cPanel's directory 404. This is a compatibility helper for Git-based shared hosting only; the preferred production setup is still to point the subdomain document root directly to the `public/` folder.

### 2. Composer dependencies must exist

A cPanel Git pull copies source files, but it does **not** automatically create Laravel's `vendor/` directory. If `vendor/autoload.php` is missing, Laravel cannot boot and `/git/public/` will return a server error.

The updated `public/index.php` detects this case and shows a deployment checklist instead of a blank 500 error. Seeing that checklist means the code reached the server correctly, but the production dependencies still need to be installed.

### Required PHP version

This project currently targets Laravel 13 and requires PHP 8.3 or newer. Do not switch the cPanel site to PHP 8.0 for this app; PHP 8.0 is too old for the framework version in `composer.json`.

### If cPanel has no Terminal

Use one of these deployment paths:

1. **Best manual option:** build the release locally with `scripts/build-cpanel-release.sh`, upload the generated archive through cPanel File Manager, and extract it on the server. The release archive includes production Composer dependencies and built frontend assets.
2. **cPanel feature option:** check whether your hosting panel has **Setup PHP App**, **Composer**, **Softaculous Laravel**, or **Git Version Control deployment commands**. Use that feature to run `composer install --no-dev --optimize-autoloader` after each Git pull.
3. **Hosting support option:** ask the host to enable cPanel Terminal, SSH, or a Git deployment hook for this account so migrations and dependency installs can be run safely.

Do not commit the server `.env` file or production passwords to Git. Keep `.env.example` as a template only and create the real `.env` on cPanel.

## Correct `.env` values for current `/git/public` hosting

`APP_URL` should match the public base URL where Laravel is actually being served. If the app is currently reachable only through:

```text
https://suite.maur.co.ke/git/public/
```

then use `.env.cpanel-git.example` as the template and set these values in the real server `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://suite.maur.co.ke/git/public
ASSET_URL=https://suite.maur.co.ke/git/public
SESSION_DOMAIN=suite.maur.co.ke
SESSION_SECURE_COOKIE=true
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=crossha1_suite
DB_USERNAME=crossha1_suite
DB_PASSWORD=your-real-database-password
```

If the subdomain document root is later changed to Laravel's `public/` folder directly, switch back to:

```dotenv
APP_URL=https://suite.maur.co.ke
ASSET_URL=https://suite.maur.co.ke
```

A wrong `APP_URL` normally causes incorrect links/assets/redirects, not a blank HTTP 500. For the current 500, first confirm that the latest Git branch has been pulled to cPanel, `vendor/autoload.php` exists, `APP_KEY` is not blank, and the selected PHP version is 8.3+.
