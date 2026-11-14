# Updating your XuVerse content

On [localhost](http://localhost/xuverse/) or [the hosted site](https://xuverse.freehosting.dev/), choose **Connect → Admin login**, then **Dashboard → Website content**. Use **Page writing** for the Home, About and Connect copy; **Branding, navigation & labels** for the portrait, navigation, headings, featured items and action labels; **Résumé, education, roles & tools** for résumé facts and lists; and the named sections for projects, articles, media and contact links.

Local saves update canonical JSON and Markdown in `content/`, create a private backup, and update the local preview. The hosted editor instead offers **Download change file**. Import that file in the local dashboard, review the changes, then apply it. The local dashboard can undo the last save from the same session if nothing has changed since. Neither saving nor downloading publishes a release.

For writing, use blank lines between paragraphs, `## Heading`, `- Bullet text`, `**bold**` and `[link text](https://example.com)`. Keep the author's date precision, references, credits and uncertainty notes. The article editor regenerates its PDF from the same writing. Review both the page and download before publishing. Contact email and socials are under **Contact & social links**.

Replace the public portrait in **Branding, navigation & labels**, or add an optimized image and set `portrait` and `portrait_alt` in `content/site.json`. Account avatars have their own upload in the admin dashboard.

Media opens on **Photographs** and includes **Videos** and **Published Work**. Category links support keyboard navigation, direct bookmarks and browser Back/Forward. All three sections remain available without JavaScript. Use **Photographs & videos** in the dashboard for photographs and YouTube videos, and **Published work & news** for credited ANCHOR material. A Published post can combine up to 10 images, YouTube links and article text. Its cover opens the gallery and its heading opens the existing `/photos/<slug>` detail page. Upload JPG, PNG or WebP images up to **4 MB and 20 megapixels each**, subject to the server’s total upload limit. Keep the original files and credits. For video, upload an authentic 16:9 still as the `poster`. XuVerse serves the still locally and requests the YouTube player when a visitor chooses Play.

New entries start as drafts. Draft status hides an entry from public listings, detail pages and the sitemap. Publish checks articles, projects, media and Published work for drafts before building and again before staging or pushing; any draft stops publication. Keep private drafts and private assets outside releasable content. Review every changed source file before Publish. Never put credentials, database exports, backups or the private handoff in public content.

After reviewing the local preview, run this single command from `C:/xampp/htdocs/xuverse`:

```powershell
python scripts/publish.py --message "Describe your content update"
```

Publish validates the source, regenerates affected writing PDFs, commits and pushes to GitHub, verifies the complete hosted release, then activates that revision. Open [the live website](https://xuverse.freehosting.dev/) and its [release marker](https://xuverse.freehosting.dev/release.json) in a browser; verify the changed page, links and PDF. Do not treat a Git push or upload receipt as final browser acceptance.

Use `python scripts/publish.py --prepare` to validate and stage without publishing, then review `git diff --cached`. See [PUBLISH.md](PUBLISH.md) for access, failure handling and rollback. Publish preserves the database, runtime uploads and private configuration. Contact retains direct email; a form appears only when a sender is configured, and delivery still needs verification.

Resume roles use an explicit `category` in `content/profile.json`: `leadership` or `work`. Reordering roles does not change their section. Education keeps its program, level, semester, location, dates and focus as separate fields. The web resume and PDF use the same grouped data.
