# XuVerse

XuVerse is B K Suraj's PHP and MySQL portfolio for software projects, writing, research and selected media. Public content comes from versioned JSON and Markdown; the CMS retains account and music tools and historical private records.

Public site: [xuverse.freehosting.dev](https://xuverse.freehosting.dev/). Source: [suiiigihub07/XuVerse](https://github.com/suiiigihub07/XuVerse).

See [DEPLOYMENT.md](DEPLOYMENT.md) for verified hosting status and [PUBLISH.md](PUBLISH.md) for the single edit-and-publish workflow. Earlier responsive notes are historical; the October handoff supersedes their public content and visual design.

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

Public JSON and Markdown in `content/`, public assets and generated PDFs are versioned in Git. Local and production MySQL retain their own accounts, music and historical records; their uploads and private settings are preserved separately. No SQL import is part of ordinary publication.

Edit JSON/Markdown, preview in XAMPP, then run `python scripts/publish.py --message "Describe your edit"`. Publish validates, regenerates PDFs, commits, pushes and activates a verified isolated release. Verify the hosted browser and revision marker. Public editorial CMS fields are read-only so live database edits cannot create a second source.

`python scripts/build-release.py` creates an optional installation/reference archive in ignored `dist/`: code, canonical content, public assets/PDFs, dependencies and upload security rules. It excludes SQL, private config, runtime media and `output/`. Ordinary updates use Publish.

See [PUBLISH.md](PUBLISH.md) for the short guide and [DEPLOYMENT.md](DEPLOYMENT.md) for tested results and limits.
