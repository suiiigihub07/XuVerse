# Edit and Publish XuVerse

Public content has one source: the JSON files and Markdown in `content/`. The public website and downloadable writing PDFs use this source. Historical CMS records remain in MySQL privately; editorial CMS pages explain why they are read-only. Account, password, avatar and music tools continue using MySQL and uploads.

1. Edit page copy in `content/copy.json`, profile information in `content/profile.json`, and records in the corresponding JSON collection. Edit full writing in `content/writing/<slug>.md`. Keep the initial title/subtitle/date blocks, authored-date precision, references, caveats and stable slugs. `published_at` is separate from the authored date.
2. Preview at `http://localhost/xuverse/`. Other computers must pull the repository to receive changes.
3. In the project terminal run `powershell -File scripts/Publish.ps1 -Message "Describe your edit"`. This validates, regenerates affected PDFs, checks PHP and content hashes, stages only application files, commits, pushes to existing `origin/main`, uploads and verifies a complete isolated release over FTPS, then switches the active pointer.
4. Open the hosted site and `release.json` in an authenticated browser. Check the rendered writing and PDF. InfinityFree's browser challenge can prevent ordinary HTTP clients from reading the site; a transport success alone is not final acceptance. The saved private receipt distinguishes activation from browser verification.

`-Prepare` runs validation and stages the allowed changes without committing, pushing or deploying. Review `git diff --cached` before publishing. Unrelated presentation files are never staged by Publish. A push or upload failure stops publication; rerunning resumes the same verified revision. No SQL import, directory mirror or upload replacement is part of Publish.

## Private access and release storage

Existing Git Credential Manager handles GitHub authentication. FTPS configuration lives outside this repository at `~/.codex/private-config/xuverse-deploy.json`, or select a private file with `python scripts/publish.py --config <path>`. Keys: `host`, optional `connect_ip`, `user`, `password`, `root`, `base_url`. Never copy it into Git or the web root. The confirmed account is InfinityFree, document root `/htdocs`, hostname `ftpupload.net`, port 21. TLS certificate verification is required even when the provider-documented IP is used for DNS recovery.

The local Publish workflow uses the existing authorised credentials. No new GitHub token, Actions access or deployment secret is required. Composer dependencies remain the existing locked host installation; releases share that directory. Updating dependencies needs a separately reviewed backup and installation.

Code, public content and public assets are staged in hidden `.xuverse-releases/<full-commit-SHA>/` directories. Every uploaded file is read back and SHA-256 checked before `.ready` is written. A single FTP rename replaces `.xuverse-active`. The stable router reads that pointer once per request; revision-specific asset URLs keep a page's assets on its release. Runtime uploads, database, private configuration and login counters live outside release directories. The original root code is retained.

Before the first routing installation, Publish privately backs up the host's configuration, routing and uploads. Database migrations are unnecessary for this architecture. The complete local database/upload/code backup is outside the web root. `output/`, the handoff, audit records, SQL exports, private configuration and backups are excluded from release and denied HTTP access.

## Rollback and failure drill

Run `python scripts/publish.py --rollback <previous-full-commit-SHA>`. It verifies every retained release file and then atomically switches the pointer. It changes no MySQL records, runtime uploads or account settings. Verify the browser and `release.json` after rollback.

Run `python scripts/publish.py --deploy <GitHub-main-SHA> --fail-after 3` for the staging-failure drill. It intentionally stops before routing/pointer changes. Verify the active revision and existing data are unchanged. Then rerun without `--fail-after`.

The initial original root site can be restored by privately restoring the saved original `.htaccess` through an atomic FTP rename; this is a one-time routing rollback, not a SQL restore. Ordinary rollbacks use retained release SHAs. Do not delete old releases until you have intentionally retired their rollback window.

## Verification record

Actual publication and rollback results are recorded in `output/verification/` locally and summarised in `DEPLOYMENT.md`. Never interpret the existence of this guide or an archive as deployment evidence.
