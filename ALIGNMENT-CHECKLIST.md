# XuVerse alignment pass

This records the earlier white and black alignment pass. The follow-up keeps its grid and viewport work and changes the public palette to ink, navy and pink. It uses Spectral for headings and reading text, with Manrope for interface labels. The revised scale and dark surfaces are checked by the same browser suite.

This record maps the 90-point alignment brief to the existing site. The changes preserve the routes, editorial sections, real content, and deployment target. The numbers refer to the attached brief.

The initial localhost review at desktop, tablet, and phone sizes found a 1px navbar/footer drift from the content grid, an 80.5px extra résumé inset at 1440px, and Selected Works starting at y=608/444/576 on 1440×900, 820×900, and 390×844. The baseline hero, navbar, and footer also used a dark hero, charcoal bar, and red footer. The current measurements below are from the rendered replacement, with the major surfaces aligned to one grid.

| Brief | Implementation or disposition |
|---|---|
| 1, 2 | Shared 1320px content grid, 48–72px desktop, 32–40px tablet, 20–24px mobile gutters. Matched navbar and footer interior edges by compensating for their 1px borders. |
| 3, 4 | Hero portrait: aligned to the main right edge on desktop. About: portrait and text group start on the same line; the portrait aligns right on desktop. Main pages share the grid; article prose intentionally narrows to 740px and media can break out when the visual warrants it. |
| 5, 6, 7, 8 | Detached vivid-pink (`#C2185B`) navbar, white brand and links, small darker active state, underline/fade and modest hover movement. Kept the previous navbar structure and visible corners. |
| 9, 10, 11, 12, 13, 14, 15 | Editorial paper `#F8F7F4`, ink `#090909`, vivid `#C2185B`, a sparing `#FF3E7F` highlight, and true red `#B30018` for primary actions. The percentage targets are visual guidance. |
| 16, 17, 18 | Home sections: paper hero → ink Selected Works → paper Writing & Research → ink Featured Media → paper About → deep-pink closing frame. Projects alternates paper header and ink/paper/ink rows. |
| 19, 20 | Paper gets dark text and dark pink details; ink gets light text and an accessible pink highlight. No full pink or red content panels were added. |
| 21, 22, 23, 24 | White hero uses the small viewport height minus the measured navbar and top inset, with a desktop no-script fallback. Content has natural height for enlarged text rather than clipping, and there is no inner scrolling. |
| 25, 26 | Home name reduced from 89.3px to 75.6px at 1440px and to 40px at the two requested phone widths. The identity, Busan, short statement, portrait and two actions remain. |
| 27, 28, 29, 30 | Manrope body/UI and Instrument Serif editorial headings, served locally for the public site. No third public typeface. |
| 31, 32, 33, 34 | Body rhythm uses approximately 16–18px and a 1.62 base line height, with bounded leads/prose. Smaller, fluid Home, About, Connect, Projects, Media, article and résumé heading scales. |
| 35, 36, 37, 38, 39, 40 | About uses a natural-height editorial split. Keeps one short personal paragraph, “Working on it.” plus an optional short context, and résumé/connect links. Short About/Connect layouts distribute spare viewport space around content so the closing frame does not leave a blank tail. |
| 41, 42, 43 | About uses paper, clean white photo background, 24px rounded portrait, mobile centering, and existing mask/group reveal. Pink details and underline interactions remain restrained. |
| 44, 45, 46, 47 | Projects uses one edge-aligned editorial row system, concise copy and a roughly 60:40 visual split. Visuals alternate sides on desktop and come first on mobile. The XuVerse preview shows the current site, and the Hotel Reservation Manager has a labeled illustration of its documented functions. Search preserves dynamic parity among visible rows. |
| 48, 49, 50 | Paper article index, smaller serif titles, pink metadata/dividers, and aligned labels, featured entry and list. Article header and body share a 740px reading rail. List titles move 4px and a pink line grows on hover; no arrows. |
| 51, 52 | Media uses ink, locally served visuals, pink active tabs and retained Photographs/Videos/Published Work routes, history and keyboard behavior. |
| 53 | Connect keeps the mail, copy, social, and configured inquiry structure; applies shared grid, Manrope, smaller heading, pink hover and a true-red primary action. |
| 54, 55, 56, 57 | Résumé heading, control rail and section dividers share the main content edges; desktop keeps a label-and-entry grid with constrained copy and readable dates. Phone stacks naturally. Paper and ink dominate; deep pink labels and a red PDF action. |
| 58, 59, 60 | Footer is `#A90F4F` with white content and animated pale-pink/white underline. It retains the detached frame, email, social links and copyright. |
| 61, 62, 63, 64 | Reused entry, section, image and control motion, plus monochrome grain. Separate neutral grain treatments are subtler on paper and slightly more visible on ink; preference and background visibility controls remain. |
| 65, 66, 67 | Text underline, small image zoom (about 1.02), project/list shift, and button/navbar feedback. Red primary buttons, pink secondary cues, and reduced-motion alternatives. |
| 68, 69, 70, 71, 72 | All five specified first viewports show only the hero, with rounded centered phone portrait, shorter but still pink phone navbar, consistent gutter and the same section rhythm. The 320×568 short-phone case also fits. |
| 73, 74, 75, 76, 77, 78, 79 | One palette, type pair, grid and motion system. No decorative colored cards or repeated arrows. Intentional spacing remains expansive; oversized type, accidental offsets and forced empty zones were reduced. Browser measurements and screenshots are the visual evidence. |
| 80, 81, 82 | The visual pass refines existing routes and section order. Kept the serif, portrait treatment, media interactions, article structure, short Connect page, and substantive spacing. The separately requested dashboard editing controls do not add public content sections. |
| 83, 84 | Local implementation and visual inspection are recorded in the evidence list. The existing validation/build workflow supplies the integrated regression record. |
| 85, 86, 87 | Existing `origin/main` and FreeHosting release workflow remain the destinations. Completion requires matching local, GitHub, and live revision, verified hosted assets, and a rendered live check; the private release receipts are the evidence. |
| 88 | Screenshot sets cover desktop Home, Projects, Articles, Media, About, Résumé and Connect, plus phone Home/About/Résumé. See the evidence list for the local records. |
| 89, 90 | Final review prioritizes grid alignment, then type and spacing, then color and motion. Section rhythm and legibility drive the design rather than large type or visual effects. |

The first viewport evidence explicitly checks 1366×768, 1440×900, 1920×1080, 390×844, and 430×932. In each case the top edge of Selected Works matches the viewport height exactly; the full portrait and both actions remain visible. The short 320×568 case also starts Selected Works at y=568; its portrait ends at y≈534. At 1440, 820 and 390, the main, navbar and footer share left edges at x≈66.23, 32 and 20px respectively. The larger-text checks allow the hero to grow without clipping or horizontal overflow.

The additional dashboard work covers homepage copy, branding/navigation, section labels/actions, featured picks, portrait, social/contact destinations, résumé information, projects, articles, photographs, videos and published gallery content. The hosted dashboard exports an edit package for review and approval in the local dashboard. Local import applies after validation and a backup. Dashboard workflow verification is recorded separately from this presentation audit.

The visual review drew on Frank Chimero’s editorial structure and type, Emil Kowalski’s restrained interaction feedback, and Raissa Pardini’s deliberate accent color. [Frank Chimero’s design work](https://design.frankchimero.com/), [Emil Kowalski’s site](https://emilkowal.ski/), [Raissa Pardini’s portfolio](https://raissapardini.com/portfolio/).

Locally generated evidence stays outside the release:

- `output/alignment-pass/personal-alignment-report.json` and `viewport-home-*.png`: 16 focused viewport and personal-page checks, desktop/tablet/phone Home captures, and before/after About, Connect and Résumé screenshots.
- `output/alignment-audit/aligned-work-smoke.json` and `aligned-*.png`: 187 focused Projects, Articles and Media route/width checks and captures.
- `scripts/editorial-qa.cjs` generates the integrated local/hosted route and resize reports under `tmp/editorial-qa/`; `output/verification/` records publication separately.
