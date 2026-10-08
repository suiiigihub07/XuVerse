# Deploying XuVerse without losing content

## October handoff acceptance — 2026-10-09 (Korea time)

The approved pages, three complete essays, independent research paper, credited ANCHOR cards and selected videos use canonical JSON/Markdown under `content/`. No database migration or SQL import was needed. Historical records and runtime data are retained; public editorial CMS fields explain the single source and are read-only.

The accepted baseline `d14ba4d4533021a830678ee455941dfb74d935ca` rendered on the live site at 2026-10-08 16:22:41 UTC. That actual first-publication timestamp is separate from the supplied month/year authored dates. The article test revision `831bb7ec2ba2b11830ca0d35f2d326225d986bfc` was published at 16:31:13 UTC. Its KAGE body, structured metadata and browser-downloaded PDF matched the local content and committed GitHub bytes. Rollback restored the baseline at 16:34:10 UTC; the note disappeared and the downloaded PDF matched the baseline bytes. The temporary note is removed through the same Publish workflow for the final release. `/release.json` reports the authoritative active revision/content hash; private receipts and browser evidence remain in excluded `output/verification/`.

An intentional failure after three staged files left the original site active. An early candidate failed browser acceptance because isolated configuration loading was wrong; original routing was restored and checked before the corrected baseline. Transfer hash verification also caught a host overwrite problem, fixed with temporary-file upload/rename. Idle FTP reconnects were added. A Windows line-ending mismatch was caught during the article drill and that staging run was stopped before activation; Publish now normalizes source text and checks exact committed hashes before contacting the host. None of these failures imported SQL or replaced uploads.

Local verification covered 72 combinations across eight pages and nine widths (320–3440 px), safe Markdown, canonical/PDF hashes, research references, protected paths, redirects, PHP syntax, URL portability, and isolated authentication/music CRUD with temporary records. The existing authenticated live dashboard retained original counts (5 experience, 2 education, 13 skills, 8 projects, 3 historical articles, 8 media, 16 highlights, 2 music); editorial read-only explanations and music tools rendered. This does not certify every live mutation/upload operation.

Private backups outside the web root contain the complete local database/uploads/code and the original host uploads/configuration/routing. The handoff, safety ZIP, audits, SQL, credentials and `output/` are excluded from Git/release and blocked over HTTP. Existing presentation folders are untouched.

Contact uses the verified email link; no mail-delivery success is claimed. Existing locked host dependencies are shared by releases. InfinityFree's browser challenge requires actual browser acceptance after FTPS activation; an upload receipt alone is insufficient. Historical notes below are retained as context; [PUBLISH.md](PUBLISH.md) supersedes their ordinary-update procedure.

## Historical deployment — 2026-09-29

- Site: https://xuverse.freehosting.dev/ (InfinityFree, HTTPS).
- Source: https://github.com/suiiigihub07/XuVerse, branch `main`.
- Initial code, Composer dependencies, media and database were transferred. Private production configuration exists only on the host; private exports remain outside Git.
- Public pages and production URLs were checked. The live resume PDF downloaded successfully; private configuration returned 403.
- Uploaded-image 500 errors were fixed by removing the host-incompatible `Options -ExecCGI` override. The media allowlist and executable-name deny rules remain; live PHP and double-extension probes return 403. The avatar and existing photo render correctly.
- A code-only update of `uploads/.htaccess` preserved the existing photo record and uploaded file. No database import or runtime-media overwrite was performed for this update.
- Public acceptance passed after the transient 502 cleared: main pages and project/article/photo/video details, 390 px mobile layout without horizontal overflow, mobile navigation, uploaded photo and video thumbnails, missing-page and missing-record handling. The hosting panel confirms Display Errors is Off.
- Live experience create/edit/delete was subsequently verified using a temporary record, then removed; the five original records remain. Other CMS mutation/upload acceptance remains incomplete. Logged-out dashboard access redirects to login. Local isolated CMS tests do not certify every authenticated production workflow.
- Contact currently uses the email fallback; no production mail sender is configured. The host's 10 MB file limit is lower than the application's optional 25 MB audio limit.

These September records are historical. Ordinary updates now use Publish below; never repeat the initial SQL/media transfer over existing live content.

## Responsive update — 2026-09-30

Seven application files were overlaid into `/htdocs` using the hosting file manager. No SQL, private configuration or runtime upload files were included. The responsive stylesheet is versioned `20260930-3`; JavaScript is versioned `20260930-1`. A prior-code rollback archive and SHA-256 release manifest were retained locally under ignored `output/responsive/`.

Live verification passed 30 public page/viewport combinations and 20 authenticated admin section/viewport combinations, plus search, email-copy, photo preview, menu keyboard behavior and cancellation of the new delete dialog. The existing experience records and uploaded photo remain available. Details and testing limits are recorded in [RESPONSIVE-UPDATE.md](RESPONSIVE-UPDATE.md).

## GitHub repository

The existing repository is connected as `origin` on `main`. Commit and push normal code changes there; do not create another repository or force push. Never put tokens in the repository or chat.

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

Use the single Publish action documented in [PUBLISH.md](PUBLISH.md). Edit canonical JSON/Markdown, preview locally, then run `powershell -File scripts/Publish.ps1 -Message "Describe your edit"`. It normalizes canonical text to Git's LF bytes, validates content, regenerates affected PDFs, commits/pushes, checks the committed hashes, stages a complete FTPS release, verifies every uploaded file, and atomically activates the pointer. Complete browser acceptance after activation.

Do not import SQL, overlay the whole root, mirror directories, replace runtime uploads, or edit public records independently in live MySQL. Accounts/music remain in each environment's database. Shared configuration, uploads and dependencies are preserved. Back up before any future schema migration; none was required for this file-reader architecture.

Rollback uses `python scripts/publish.py --rollback <retained-full-SHA>` followed by browser verification. Installation archives are reference/fresh-install tools, not the ordinary update workflow.

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
