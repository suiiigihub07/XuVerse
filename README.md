# XuVerse

XuVerse is B K Suraj's PHP and MySQL portfolio with an integrated CMS for resume records, projects, articles, photography, videos, music and settings.

## Requirements

- Apache 2.4, mod_rewrite and enabled `.htaccess`
- PHP 8.2+ with mysqli/mysqlnd, GD/WebP, Fileinfo, EXIF, DOM/XML and mbstring
- MySQL/MariaDB and Composer for locked PDF dependencies
- HTTPS in production; writable uploads, application tmp and PHP session/temp storage

GitHub Pages cannot run PHP or MySQL. Use PHP/MySQL hosting.

## Local development

1. Keep the project in XAMPP's `htdocs/xuverse`; start Apache and MySQL.
2. Existing installation: keep your database and uploads. No SQL import or migration is needed for this readiness update.
3. Fresh installation only: create an empty `xuverse` database and import `database/xuverse.sql`. It contains the current schema and a generic settings row, without private records or credentials.
4. Run `composer install --no-dev --prefer-dist --optimize-autoloader`.
5. Open `http://localhost/xuverse/`; sign in at `/xuverse/login`.

XAMPP defaults remain localhost, database xuverse, user root and an empty password. Custom local values belong in ignored `config.local.php` with `PRODUCTION => false`, or environment variables. Do not copy production settings unchanged into local development.

### First administrator

For a fresh database, `scripts/create-admin.php` reads JSON on stdin and refuses to replace an existing administrator. In PowerShell 7:

```powershell
$adminName = Read-Host 'Admin name'
$adminEmail = Read-Host 'Admin email'
$adminSecret = Read-Host 'Unique password (14-72 bytes)' -AsSecureString
@{ name=$adminName; email=$adminEmail; password=ConvertFrom-SecureString $adminSecret -AsPlainText } |
    ConvertTo-Json -Compress | & C:\xampp\php\php.exe scripts/create-admin.php
Remove-Variable adminSecret
```

For hosting without terminal access, prepare an installation locally and privately transfer its database once using phpMyAdmin. Never commit that export.

## Configuration

Environment variables take priority over the array in `config.local.php`. Prefer placing the private file outside the document root and selecting it with `XUVERSE_CONFIG_FILE`. The in-project fallback is ignored by Git and blocked by Apache.

Copy `config.example.php` on the host and enter its values. `.env.example` is documentation; `.env` files are not parsed.

| Setting | Purpose |
| --- | --- |
| DB_HOST, DB_NAME, DB_USER, DB_PASSWORD | Independent database connection |
| BASE_URL | Public origin plus optional installation path |
| APP_PATH | `/` at domain root or `/xuverse` in a subfolder |
| PRODUCTION | true only on the HTTPS live site |
| CONTACT_TO, CONTACT_FROM | Optional recipient and domain sender |

Environment variables accept these names and the existing XUVERSE_ prefix; the prefixed form wins. PHP array keys are unprefixed. Production refuses incomplete credentials instead of falling back to the local root account. Shared URL helpers support root and subdirectory installations.

## Source and live content

| Location | Owns | Ordinary update |
| --- | --- | --- |
| GitHub | Code, static assets, schema, Composer lockfile | Commit and push |
| Local MySQL | Development content | Keep locally |
| Production MySQL | Live CMS content | Never re-import starter SQL |
| Production uploads | Live media | Preserve |
| Host private config | Production credentials | Preserve |

Runtime uploads, dependencies, secrets, backups and temporary files are excluded from Git. Existing media stays on disk. Transfer required uploads privately during the first deployment; static assets remain in Git.

Develop in XAMPP → test → git add → git commit → git push → build code archive → upload/extract without deleting existing files.

Run `python scripts/build-release.py` after staging intended files and installing Composer dependencies. It packages current working files into ignored `dist/xuverse-code-*.zip`: code, assets, dependencies and upload security rules. It excludes SQL, private config and runtime media.

See [DEPLOYMENT.md](DEPLOYMENT.md) for deployment/backups and [READINESS-AUDIT.md](READINESS-AUDIT.md) for findings and verification limits.
