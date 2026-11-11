# Updating your XuVerse content

Open [the local website](http://localhost/xuverse/), choose **Connect → Admin login**, then **Dashboard → Website content**. Edit page writing, profile facts, projects, articles, media, skills, contact links or branding there. The editor saves the canonical JSON and Markdown in `content/`, creates a private backup, and updates the local preview. Saving does not publish. The hosted website and shared previews are read-only; make edits in the local source checkout.

The public layout uses Inter for navigation and interface text, Instrument Serif for editorial headlines, and restrained red and pink accents. Shared tokens and layout live in `assets/css/editorial.css`; writing and project styles are in `assets/css/editorial-work.css`, and profile and media styles are in `assets/css/editorial-personal.css`. Layout references include [Craig Mod’s essay archive](https://craigmod.com/essays/), [Pentagram’s work index](https://www.pentagram.com/work) and the image-led publishing of [It’s Nice That](https://www.itsnicethat.com/).

For writing, use blank lines between paragraphs, `## Heading`, `- Bullet text`, `**bold**` and `[link text](https://example.com)`. Keep the author's date precision, references, credits and uncertainty notes. The article editor regenerates its PDF from the same writing. Review both the page and download before publishing. Contact email and socials are under **Contact & social links**.

Replace the public portrait in **Branding, images & page headings**, or add `assets/images/public/portrait-suraj-final.webp` and set `portrait` and `portrait_alt` in `content/site.json`. The current portrait is `assets/images/public/portrait.webp`. Account avatars have their own upload in the admin dashboard.

Media opens on **Photographs** and includes **Videos** and **Published Work**. Category links support keyboard navigation, direct bookmarks and browser Back/Forward. All three sections remain available without JavaScript. Use **Photographs & videos** in the dashboard for photographs and YouTube videos, and **Published work & news** for credited ANCHOR material. A Published post can combine up to 10 images, YouTube links and article text. Its cover opens the gallery and its heading opens the existing `/photos/<slug>` detail page. Upload JPG, PNG or WebP images up to **4 MB and 20 megapixels each**, subject to the server’s total upload limit. Keep the original files and credits. For video, upload an authentic 16:9 still as the `poster`. XuVerse serves the still locally and requests the YouTube player when a visitor chooses Play.

New entries start as drafts. Draft status hides an entry from public listings, detail pages and the sitemap. Publish checks articles, projects, media and Published work for drafts before building and again before staging or pushing; any draft stops publication. Keep private drafts and private assets outside releasable content. Review every changed source file before Publish. Never put credentials, database exports, backups or the private handoff in public content.

After reviewing the local preview, run this single command from `C:/xampp/htdocs/xuverse`:

```powershell
python scripts/publish.py --message "Describe your content update"
```

Publish validates the source, regenerates affected writing PDFs, commits and pushes to GitHub, verifies the complete hosted release, then activates that revision. Open [the live website](https://xuverse.freehosting.dev/) and its [release marker](https://xuverse.freehosting.dev/release.json) in a browser; verify the changed page, links and PDF. Do not treat a Git push or upload receipt as final browser acceptance.

Use `python scripts/publish.py --prepare` to validate and stage without publishing, then review `git diff --cached`. See [PUBLISH.md](PUBLISH.md) for access, failure handling and rollback. Publish preserves the database, runtime uploads and private configuration. Contact retains direct email; a form appears only when a sender is configured, and delivery still needs verification.

Resume roles use an explicit `category` in `content/profile.json`: `leadership` or `work`. Reordering roles does not change their section. Education keeps its program, level, semester, location, dates and focus as separate fields. The web resume and PDF use the same grouped data.

The decorative XS appears in the home hero. `includes/background-signature.php` centers the S over the X at 69% of its cap height. The initial portrait and text transitions respect the browser’s reduced-motion setting.
