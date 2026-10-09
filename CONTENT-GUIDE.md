# Updating your XuVerse content

Open [the local website](http://localhost/xuverse/), choose **Connect → Admin login**, then **Dashboard → Website content**. Edit page writing, profile facts, projects, articles, media, skills, contact links or branding there. The editor saves the canonical JSON and Markdown in `content/`, creates a private backup, and updates the local preview. Saving does not publish. The hosted website and shared previews are read-only; make edits in the local source checkout.

Keep the original black/red identity, fonts, portrait frame, navigation, social footer and animation hooks. Rounded controls, cards, frames and menus use the shared `rounded.css` layer. Content edits should extend that presentation. Account, password, account avatar and music tools remain separate; changing an account avatar does not replace the public portrait.

For writing, use blank lines between paragraphs, `## Heading`, `- Bullet text`, `**bold**` and `[link text](https://example.com)`. Keep the author's date precision, references, credits and uncertainty notes. The article editor regenerates its PDF from the same writing. Review both the page and download before publishing. Public portrait and headings are under **Branding, images & page headings**; contact email and socials are under **Contact & social links**.

Media has two main sections: **Photographs** and **Videos**. Credited ANCHOR and Published galleries appear within Photographs. Use **Photographs & videos** in the dashboard for personal photographs and direct YouTube videos. Use **Published work & news** for ANCHOR, news and other published posts. Each Published post can combine **up to 10 images**, YouTube video links and full article text. The listing shows one cover and a heading; click the cover to browse its images or the heading to read the full post. Detail pages retain the existing `/photos/<slug>` route. Upload JPG, PNG or WebP images up to **4 MB and 20 megapixels each**; the server's total upload limit also applies. Keep original files separately. Uploads are optimized into new content assets; retain existing credits and do not remove unrelated assets or runtime uploads. YouTube players use a 16:9 landscape frame.

New entries start as drafts. Draft status hides an entry from public listings, detail pages and the sitemap. Publish checks articles, projects, media and Published work for drafts before building and again before staging or pushing; any draft stops publication. Keep private drafts and private assets outside releasable content. Review every changed source file before Publish. Never put credentials, database exports, backups or the private handoff in public content.

After reviewing the local preview, run this single command from `C:/xampp/htdocs/xuverse`:

```powershell
python scripts/publish.py --message "Describe your content update"
```

Publish validates the source, regenerates affected writing PDFs, commits and pushes to GitHub, verifies the complete hosted release, then activates that revision. Open [the live website](https://xuverse.freehosting.dev/) and its [release marker](https://xuverse.freehosting.dev/release.json) in a browser; verify the changed page, links and PDF. Do not treat a Git push or upload receipt as final browser acceptance.

Use `python scripts/publish.py --prepare` to validate and stage without publishing, then review `git diff --cached`. See [PUBLISH.md](PUBLISH.md) for access, failure handling and rollback. Publish preserves the database, runtime uploads and private configuration. Contact retains direct email; a form appears only when a sender is configured, and delivery still needs verification.
