# Deploying XuVerse without losing content

## Current deployment — 2026-09-29

- Site: https://xuverse.freehosting.dev/ (InfinityFree, HTTPS).
- Source: https://github.com/suiiigihub07/XuVerse, branch `main`.
- Initial code, Composer dependencies, media and database were transferred. Private production configuration exists only on the host; private exports remain outside Git.
- Public pages and production URLs were checked. The live resume PDF downloaded successfully; private configuration returned 403.
- **Acceptance is incomplete:** uploaded images show the provider's .htaccess error page. Hosting sign-in expired during diagnosis. Repair that directory's rules and recheck images before considering launch complete.
- Live administrator CRUD/uploads, responsive verification and code-update preservation still need acceptance testing. Local isolated tests passed; they do not certify the production host.
- Contact currently uses the email fallback; no production mail sender is configured. The host's 10 MB file limit is lower than the application's optional 25 MB audio limit.

For ordinary updates, follow the code-only procedure below. Never repeat the initial SQL/media transfer over existing live content.

## GitHub handoff

Create an empty repository named **XuVerse** in your GitHub account. Do not initialize it with a README, license or gitignore. Provide the repository URL and authenticate on your computer when Git requests it. Never put tokens in the repository or chat. Do not force push.

## First deployment

1. Choose hosting with Apache rewriting, PHP 8.2+, MySQL/MariaDB, HTTPS, phpMyAdmin and file upload access. Confirm README's extensions, storage/database quotas, upload limits and backup/export access. Mail delivery is a separate capability.
2. Create the account and site/subdomain. Provide the public URL, provider name and available access methods: SFTP/FTP, file manager, phpMyAdmin, SSH or Composer. Keep passwords in the panel or private configuration.
3. Create a separate production database and user. Configure credentials privately. Set PRODUCTION=true, BASE_URL to the HTTPS site URL and APP_PATH=/ for domain-root hosting. Configure HTTPS redirects at the host, including behind a TLS proxy.
4. Choose initial content: privately transfer a fresh export of the current local database and matching uploads once, or import the sanitized schema into an empty database and create an admin. Preserve relative media paths. Runtime media is not in Git.
5. Back up first. Import only into the new empty database. Never import starter SQL into an established site. The historical migrate-content.php and refresh-technical-copy.php scripts are setup/editorial tools, not routine deployment hooks.
6. Install locked Composer dependencies locally if needed, then run `python scripts/build-release.py`. Upload/extract to the document root, including `.htaccess` and `uploads/.htaccess`. Transfer initial media and private config separately.
7. Make uploads, application tmp and PHP temporary/session storage writable by PHP. Follow host ownership guidance; avoid world-writable permissions. Configure upload/post limits for images and optional 25 MB audio. Also set display_errors=Off, display_startup_errors=Off and log_errors=On in host PHP settings: runtime code cannot hide startup errors.
8. Complete live verification below and use a unique live admin password. Rotate it if a private export was shared outside trusted storage.

## Every subsequent update

1. Develop/test in XAMPP, inspect the diff, stage intended files, commit and push.
2. Back up the live database, uploads and private config outside the public document root. Confirm restoration is possible.
3. Install Composer dependencies and build/review the code-only archive.
4. Extract/upload over existing code, preserving other files. Do not use delete/mirror sync, fresh-folder replacement or FTP options that remove files absent from the archive.
5. **Do not import SQL for ordinary updates. Do not overwrite live uploads with local uploads.** Schema changes need a separately reviewed migration and backup; a full SQL dump is not a migration.
6. Test and verify pre-existing records/media survive. Restore the prior code archive if necessary. Restoring data is a separate operation, never a routine code rollback.

The supplied archive cannot replace live database content or runtime media. Importing dumps or deleting the server directory can still destroy data; retain private backups.

## Live acceptance checklist

- Home, resume, projects, articles, media, contact and desktop/mobile navigation
- Project/article/photo/video details, missing-record 404s, responsive assets and uploaded media
- PDF download from current CMS content
- Login/logout; every CMS page rejects logged-out and non-admin users
- Temporary-record CRUD in experience, education, skills, projects, articles, media, highlights and music; remove only those test records
- Settings, password, avatar, project/article image and video thumbnail uploads
- Invalid uploads rejected, valid images display, mutations require CSRF
- SQL/config/Git/internal paths return 403; HTTPS cookies and error hiding work
- Contact mail delivers, or the existing honest fallback appears
- A code-only redeployment preserves a live test record and uploaded file

Run `scripts/launch-preflight.ps1 -BaseUrl 'https://your-domain.example'` for automated public checks. Manual CMS and preservation tests remain required. Local tests cannot certify an unconfigured host.

## Operations

Login limiting permits five attempts per connecting IP per 15 minutes in tmp/login-attempts. Make that directory writable; periodically remove only counters older than a day. Behind a proxy, configure the host to supply the real client IP as REMOTE_ADDR; arbitrary forwarded headers are not trusted. Shared IPs share the limit.

Use supported, patched PHP and audit Composer dependencies before launch. Contact delivery needs a working mail transport and domain sender settings. Host-specific checks await the provider.
