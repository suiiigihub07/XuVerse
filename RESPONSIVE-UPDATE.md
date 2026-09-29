# Responsive update — September 30, 2026

Deployed to https://xuverse.freehosting.dev/ as a seven-file code-only overlay. The existing database, private configuration and runtime media were preserved.

## Changes

- Fluid page widths, typography, cards, resume columns and admin forms from compact phones to wide displays. Long words, file inputs and media stay within their containers.
- A scrollable mobile navigation menu fits short landscape screens. Escape and breakpoint changes restore sensible keyboard focus; touch controls have larger targets.
- Project and article search filters titles/descriptions, reports result counts, supports Clear, and displays an empty state. All content remains available without JavaScript.
- Fullscreen photo preview fits portrait and landscape screens, with counters, captions, detail links, previous/next controls, arrow keys and swipe handlers. Escape returns focus to the opener.
- Articles with multiple headings receive an expandable section navigation. Contact includes copy-email feedback.
- Native HTML delete dialogs identify the record and initially focus Keep item. Cancellation restores focus; confirmation preserves the existing POST/CSRF flow.
- Reduced-motion styles, safe-area spacing, and print exclusions for interactive controls.

## Verification

- 62 local public page/viewport combinations: widths 320, 390, 768, 1024, 1440 and 1920, plus 667×375 landscape. No measured horizontal overflow or visible PHP errors.
- 30 live public combinations: home, resume, projects, article detail, media and contact at 320×568, 768×1024, 1440×900, 1920×1080 and 667×375. No measured horizontal overflow or visible PHP errors.
- All ten linked administration sections at 320 and 1024 pixels: 20 combinations, without measured horizontal overflow. The experience creation form was also inspected at 320 pixels without submitting.
- Live search match/empty/reset, copy-email feedback, compact account menu bounds, Escape focus, loaded photo preview, and delete-dialog cancellation passed. Five experience records remained after cancellation.
- An isolated local fixture exercised long unbroken content, file/select controls, two-photo next/previous/wrap navigation, article section links, delete cancellation and an approved POST to a harmless test endpoint. No real record was modified by these fixture checks.
- Desktop, tablet and phone screenshots were visually reviewed. Evidence, a release manifest and rollback ZIP are stored in ignored `output/responsive/`.
- All four affected PHP files passed syntax checks; JavaScript source and minified output passed Node syntax checks; all 16 portability checks passed. The live browser reported no JavaScript errors during the checked interactions.

Testing used Chromium viewport emulation, not a physical iOS/Android device fleet. Swipe handlers and reduced-motion rules were implemented, but physical touch gestures and OS motion settings were not independently exercised. This is a responsive and interaction update, not certification of every CMS mutation, mail delivery or hosting uptime.

## Maintenance

Responsive overrides live in `assets/css/responsive.css`, loaded after the existing stylesheet. Edit `assets/js/main.js` and regenerate `main.min.js` with Terser when behavior changes; update asset query versions in `includes/header.php`. Ordinary deployment must preserve host configuration, database contents and uploads. Rollback restores the prior six existing code files; the unreferenced new stylesheet may remain harmlessly on disk.
