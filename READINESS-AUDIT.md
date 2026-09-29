# XuVerse readiness audit — 2026-09-29

Scope: existing application, without redesign. The readiness audit preceded publication; the source is now on GitHub and the initial deployment is online at https://xuverse.freehosting.dev/.

## Findings and fixes

| Area | Finding and resolution |
| --- | --- |
| URLs | Removed the hardcoded subdirectory fallback and normalized Windows root paths. Existing shared helpers continue to serve navigation, assets and detail links. Removed obsolete static localhost sitemap/robots files; their dynamic endpoints remain. |
| Configuration | Added environment aliases and an ignored private PHP configuration fallback. Production requires explicit credentials. The example contains placeholders only. |
| Error disclosure | Disable displayed PHP errors before config/database loading; catch database exceptions and return a generic 503. Host startup-error settings still require verification. |
| SQL | Original export contained an admin identity/hash, portfolio data and destructive DROP statements. Replaced the public file with current schema and a generic settings row. Original export and local database remain private. No live database migration is run automatically. |
| Authorization | CMS checks now require administrator role. Database access revalidates the role for an authenticated session, including revoked accounts. Password hashing/verification and session regeneration remain. |
| Login | Added CSRF protection and a file-backed, locked IP limit that survives cookie resets. |
| Uploads | Added actual-upload and Fileinfo checks, a 20-megapixel bound and random filenames; retained GD re-encoding to WebP. Restricted deletion to resolved files below uploads and strengthened Apache execution blocking. Music deletion uses the same boundary. |
| XSS/SQL | Retained prepared request-driven operations and escaped text rendering. Added HTTP/HTTPS validation for editable highlight links, matching existing project/social validation. |
| Git | Added ignore rules for private config, uploads, dependencies, backups, logs and temporary files. Static portfolio assets stay in source control. |
| Updates | Added a code-only archive builder. It includes upload security rules but excludes runtime media, SQL and private config. Documented separate initial transfer, backups and non-destructive updates. |

## Verification

- 107 integration assertions passed against a separate temporary database: all 35 non-empty CMS endpoints redirect logged-out users; admin login/logout, CSRF rejection, experience/education/skills/projects/articles/highlights CRUD, photo/video CRUD, valid/invalid image handling, avatar/settings/password updates, music upload/update/delete, revoked-admin rejection and public/PDF responses.
- 16 CLI portability/security assertions cover domain root, subfolders, nested canonical URLs, encoded upload paths, malformed CSRF and deletion boundaries. Re-run with `php scripts/test-portability.php` on a test installation without private URL overrides.
- Checked existing public pages and detail routes at the domain root and the real XAMPP subdirectory. Asset probes completed 166 successful local requests. Real XAMPP subdirectory canonical URLs passed; an artificial Apache Alias requires an explicit APP_PATH rather than relying on script-name inference.
- Protected paths returned 403; missing records/routes returned 404. Production connection failure returned generic text without connection details. Private config fallback/environment precedence passed. Login limiting remained effective across fresh cookie sessions.
- Compared local database INSERT records before/after isolated CRUD tests: unchanged.
- Packagist advisory ranges retrieved on the audit date did not include the six locked dependency versions. No dependency versions were changed.
- PHP lint passed for 80 application PHP files. Reviewed 147 staged files: private paths and known credential/hash patterns were absent. The 775-file release manifest excluded SQL, private config and runtime media; extracting it over test sentinel files preserved them.

## Remaining launch work

GitHub publication and initial InfinityFree deployment are complete. Live public pages loaded over HTTPS with production canonical URLs, and the resume PDF downloaded. Private configuration and SQL paths returned 403; logged-out dashboard access redirected to login. Uploaded-image errors were fixed by removing the host-incompatible ExecCGI override while retaining media allowlist and executable-name blocking. Live PHP and double-extension probes returned 403. That code-only update preserved the existing photo record and file; the avatar and photo rendered correctly. Live CMS acceptance still requires the owner's login, and responsive checks remain pending after a transient hosting 502. Contact uses the existing email fallback until a mail sender is configured. This is a focused readiness review, not a guarantee against every security defect.

Keep backups outside the public web root on production. The local pre-change snapshot and database export are in ignored, HTTP-blocked `tmp/readiness-backup/`; never upload that folder. The code-only archive intentionally omits it.

References used for security decisions: [PHP mysqli exception behavior](https://www.php.net/manual/en/mysqli-driver.report-mode.php), [OWASP upload guidance](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html), and [Packagist advisory API](https://packagist.org/api/security-advisories/).
