# Fortify Demo Network: Build Spec

Version 2.1, 18 November 2026 (revised after cross-check against the repo docs). Replaces the earlier Google Doc draft (six offices, template-subsite architecture), which is obsolete.

Lives in the `fortify-demo-network` repo (moved from the plugin repo on 2026-11-21). Read alongside `ENVIRONMENTS.md` (this repo) and, in the plugin repo (`tfbceo/WP-Sitelets-Plugin-Elementor`), `docs/CODE-REVIEW-2026-11.md`, `docs/MERGE-ON-PUBLISH-SPEC.md` and `docs/TESTING.md`. "The Fortify repo" below means the static marketing site, `tfbceo/Fortify-Listings-FE-Website`.

## 1. Purpose

Rebuild fortifylistings.com as a WordPress Multisite + Elementor Pro network that serves two jobs:

1. A test bed for the Sitelets plugin (Phase 3, Phase 4, merge-on-publish).
2. A demo network for prospects: one corporate site and twelve market-office sitelets.

Fortify is the demo brand and content source. The live fortifylistings.com stays static HTML and never gets the plugin.

## 2. Basis and confidence

| Input | How it was checked |
|---|---|
| Fortify page structure | Live homepage read on 18 Nov 2026. Repo (`tfbceo/Fortify-Listings-FE-Website`, static HTML on Vercel, last commit 2026-07-28) inventoried in phase 0 on 21 Nov 2026. |
| Plugin behavior | 1.1.0 source read twice (two independent reviews). Not executed. |
| MLS footprints | Current coverage sources for Stellar and Miami; IDX vendor pages for NABOR (lower confidence). |
| Phoenix and Nashville MLS names | Assumed. Not confirmed against Fortify's memberships. |

## 3. Network structure

- WordPress Multisite, subdirectory mode.
- Site 1: Fortify corporate.
- Sites 2 to 13: one sitelet per market office.
- Network-activated: Elementor, Elementor Pro, Sitelets 1.1.3 or later (1.1.2 has the sync fixes the phase 1 checks depend on; 1.1.3 adds the Reading-screen fix found on 2026-11-21), and a custom `fortify-offices` plugin (section 6).
- Theme: Hello Elementor with a `fortify-child` child theme.
- Sitelets license: the 25-site tier (Professional, product 2821). The free tier caps at 3 sites and sitelet features do not load on unlicensed subsites. This is the sitelet count inside one network. It is separate from the license's activation limit, which counts install URLs (see open items).
- Keep widget add-on packs out. The 1.1.0 widget wrapper fails above 200 widget types. Count the registered widget types (Elementor, Elementor Pro, WordPress widgets, `fortify-offices` widgets) in phase 1 and confirm the total is under 200. Measured 2026-11-21: 152 (Elementor Pro 84, Elementor 50, WordPress widgets 18), leaving 48 for `fortify-offices` widgets. Re-count after phase 4.
- Whole network set to discourage indexing in every environment. Done 2026-11-21 by the must-use plugin `fortify-noindex.php`: it forces `blog_public` to 0 on every site (core prints `noindex, nofollow` and turns off its XML sitemaps), creates new sites as non-public, and sends an `X-Robots-Tag: noindex, nofollow` header. `robots.txt` still allows crawling; core has done that since 5.3 so crawlers can see the noindex. Remove the must-use plugin only for a real production launch.
- No analytics: Google Tag Manager, Microsoft Clarity and the Meta Pixel from the static site are left out entirely (decision 2026-11-21).

## 4. Offices

Office name and URL slug use the consumer market name. The MLS is a registry field that defines the territory.

| # | Office | Slug | State | MLS | Territory | Status |
|---|---|---|---|---|---|---|
| 1 | Tampa | tampa | FL | Stellar MLS | Hillsborough | County on live coverage list |
| 2 | St. Petersburg-Clearwater | st-petersburg | FL | Stellar MLS | Pinellas | County not on live coverage list; demo only |
| 3 | Orlando | orlando | FL | Stellar MLS | Orange, Seminole | Seminole not on live coverage list |
| 4 | Sarasota-Bradenton | sarasota | FL | Stellar MLS | Sarasota, Manatee | Manatee not on live coverage list |
| 5 | Lakeland-Winter Haven | lakeland | FL | Stellar MLS | Polk | County on live coverage list |
| 6 | Miami | miami | FL | Miami MLS | Miami-Dade | County on live coverage list |
| 7 | Fort Lauderdale | fort-lauderdale | FL | Miami MLS | Broward | County on live coverage list |
| 8 | Boca Raton-Delray Beach | boca-raton | FL | Miami MLS | South Palm Beach County | Split by ZIP with office 9 |
| 9 | West Palm Beach-Jupiter | west-palm-beach | FL | Miami MLS | North Palm Beach County | Split by ZIP with office 8 |
| 10 | Naples | naples | FL | Southwest Florida MLS (NABOR) | Collier | County on live coverage list; MLS boundary lower confidence |
| 11 | Phoenix | phoenix | AZ | ARMLS (assumed) | Maricopa | Registry status "coming soon" (decision 2026-11-21); MLS assumption to confirm |
| 12 | Nashville | nashville | TN | Realtracs (assumed) | Davidson, Williamson | Registry status "coming soon" (decision 2026-11-21); MLS assumption to confirm |

Routing must work at ZIP level, because offices 8 and 9 share one county.

Secondary markets held back as the "add an office" step in demos: Kissimmee, Pasco, Venice-North Port, Punta Gorda-Port Charlotte, The Villages-Clermont, Ocala, Gainesville, DeLand, Palm Coast, Nature Coast, Port St. Lucie, Stuart, Marco Island, Bonita Springs-Estero.

## 5. How the plugin shapes the build

These come from the 1.1.0 code review and drive the design below.

| Plugin behavior (1.1.0) | Consequence for this build |
|---|---|
| Office pages are a custom post type, Sitelet Pages, authored on site 1 and pushed with Publish to Sitelets | There is no master template subsite. All office page templates live on site 1. |
| A new site automatically receives all published Sitelet Pages, Elementor library templates and the active kit. Verified 2026-11-21, including via `wp site create`. From 1.1.2 the sync also rebuilds the Theme Builder conditions cache (so headers and footers render), gives the new site the main site's theme, and sets the front page (next row). | "Add an office" means create a site. Build and publish templates before creating offices. |
| From 1.1.2, one Sitelet Page on site 1 can be marked as the sitelet home page (checkbox on its edit screen, shown as "Sitelet Home" in the list). The sync, and every publish of that page, sets it as the sitelet's static front page. Sitelet Pages appear in each sitelet's Settings → Reading, so saving that screen keeps it (fixed in 1.1.3: on 1.1.2 the screen hid the section when a sitelet had no regular Pages). | Mark the office Home Sitelet Page before creating offices. No per-office front-page step. |
| Elementor Pro's Notes table (`wp_N_e_notes`) does not exist on a new site until its first wp-admin visit, so the sync logs "table doesn't exist" database errors (issue #25) | Log noise only, nothing lost. Ignore during phase 6; the table appears when the office is first opened in wp-admin. |
| No function clones an existing sitelet | Do not plan on cloning one office into another. |
| Publish overwrites a sitelet page's whole Elementor data, no merge (issue #20) | Hand-edited local content is lost on re-publish until merge Tier 1 lands. Office data must come from the registry (section 6). |
| Locks are per-control settings keys in `_elementor_data` | The lock map in section 8 can be applied by script. |
| Lock enforcement is editor-side only | Guided demos only until Phase 4. No prospect logins. |
| On a sitelet 404, the plugin serves the main-site page with the same slug | Fixed in Sitelets 1.1.4 (broken through 1.1.3): published main-site pages render inside the sitelet's theme, header and footer, canonical to the corporate URL; drafts, private and password-protected pages are not served. Corporate inner pages appear on every sitelet without copying. Sitelet Page slugs must not collide with corporate page slugs. |
| Sitelet pages viewed as single posts output a canonical tag pointing at the main-site original. A page set as the front page is not covered. Tested 2026-11-21: the sitelet root has no canonical tag; the page's own URL (`/<office>/sitelet_page/<slug>/`) also stays live and canonicals to site 1. | Works against local search, and coverage is uneven. Product decision pending. Moot for the demo while the network is no-index. |
| Publish to Sitelets handles Sitelet Pages only | Header, footer and kit changes made after an office is created do not propagate. Finalize them before creating offices (phase 5). See open items. |
| The container widget whitelist is read only by the sitelet editor script, and the stored values (option indexes) do not match what the script compares (widget titles) | Treat the whitelist as not working in 1.1.0. Not used in the lock map until fixed. |
| Admin-only containers are stripped on publish at the top level only | An admin-only container must be a top-level container. A nested one leaks to sitelets. |

## 6. Offices registry (`fortify-offices` plugin)

A network-activated plugin. It registers an `office` post type on site 1, one record per sitelet.

Fields: office name, slug, blog ID, state, MLS name, counties, ZIP codes, phone, address, hours, broker of record, state license number, latitude, longitude, status (live, coming soon, hidden).

It provides:

- A function any site can call to read office records, including "the office for the current site".
- Elementor widgets (or shortcodes) that render registry data: office name, phone, address and hours, service-area list, MLS line, license block, team list, local business structured data.
- Corporate widgets: location finder (ZIP, city or county search, state filter, Leaflet map with OpenStreetMap tiles, result list), markets grid grouped by state, footer office directory, per-state licensing block.
- A not-covered result for unmatched ZIPs, pointing to the corporate contact route.

Because these widgets read the registry at render time, the same published page shows each office's own data, and a re-publish cannot erase it. This is the design answer to issue #20, and it stays valid after the merge is built.

## 7. Pages

### Corporate site (site 1), ordinary pages

| Page | Slug | Notes |
|---|---|---|
| Home | (front page) | Rebuilt section by section. Hero and final address forms are a plain ZIP/address field that routes a ZIP to its office: no Google Places, no API key (decision 2026-11-21). The static county list in the site footer becomes the registry-driven footer office directory. Licensing block reads the registry. |
| Locations | locations | New. The location finder. |
| How it works, Plans, FAQs, About, Contact | existing slugs | Rebuilt. Served on sitelets through the 404 fallback. |
| Legal set | existing slugs | Rebuilt. Same fallback. |
| Preview start | preview | Demo stub. The production flow lives in the app and is out of scope. |

Navigation gains "Find your market". The equity calculator is ported as a small custom Elementor widget in the child theme.

### Office pages (Sitelet Pages, authored on site 1)

| Page | Slug | Content |
|---|---|---|
| Office home | office | Corporate hero layout, local headline, market intro, sample listing, how it works, calculator, comparison, plans, closing call to action |
| Service area | service-area | Counties, ZIPs, neighborhoods, MLS line, local market notes |
| Team | team | Broker and agent bios, licensing and compliance text |
| Reviews | reviews | Local reviews, review disclosure text |
| Local FAQ | local-faq | State and market questions; links to the corporate FAQ |
| Visit us | visit | Office name, address, phone, hours, map |

## 8. Locked and editable map

Two mechanisms from the plugin are used: control locks and the container "Available on" setting. The container widget whitelist is left out until it is fixed (section 5).

Until merge Tier 1 lands, every row marked Local is editable but not durable: the next corporate publish of that page erases the edit (issue #20). Treat local edits as throwaway in 1.1.0.

| Content | Source | Treatment |
|---|---|---|
| Header, footer, legal footer, equal housing statement | Corporate | Not editable locally |
| Plans and pricing, comparison, calculator, how it works | Corporate | Every control locked |
| Hero layout, images, buttons | Corporate | Every control locked |
| Hero headline | Corporate default | Text control unlocked; typography and color locked |
| Office name, phone, address, hours, counties, ZIPs, MLS line, license number | Registry | Rendered dynamically; nothing to edit in the page |
| Team list | Office Profile (on the office site) | Team List widget; edited on the office's Office Profile screen, nothing to edit in the page |
| Market intro paragraph | Office Profile | Market Intro widget; edited on the Office Profile screen |
| Hero headline, neighborhoods, local market notes, local FAQ entries | Local | Text control unlocked (Heading title, Text Editor content, FAQ item titles and answers); everything else locked |
| Local reviews | Office Profile | Local Reviews widget; edited on the Office Profile screen, sample entries labelled |
| Sample listing card | Local | Listing Card widget: photo, details and note sections unlocked; layout, style and Advanced sections locked |
| Template-only notes for corporate editors | Corporate | Top-level container set to Admin only. Never nested. |

Give every top-level container a clear CSS ID so locks and tests can target it.

**Office Profile** (added 2026-11-22): office-owned content stored on each office site (option `fortify_office_profile`): market intro, team members (name, title, bio, photo URL) and local reviews (name, location, rating, text, sample flag). Edited on the sitelet's own Office Profile admin screen by users with `edit_pages` there (local editors); the screen does not exist on site 1, and local editors have no role on site 1. Photos are stored as URLs because office sites use the main site's media library (Sitelets blocks uploads there). Rendered by the Market Intro, Team List and Local Reviews widgets. Like registry data, it is read at render time, so a corporate re-publish cannot erase it (issue #20).

**Anything marked Local or Registry above is a native widget or a custom widget with its own controls, never an HTML widget** (decision 2026-11-22). An HTML widget has one code control: unlocking it hands the office the whole block, layout included, and locking it leaves nothing to edit. This covers the sample listing card on office pages, which becomes a Listing Card widget in `fortify-child` (image, price, address, beds/baths/sqft, savings line as separate controls). HTML widgets stay acceptable only inside fully locked corporate sections.

Custom widgets get Sitelets lock toggles like native ones. Checked 2026-11-22 on Sitelets 1.1.3: in a Sitelet Page context the plugin re-registers every widget through its wrapper, and the per-control toggle is added to each control the widget declares (Savings Calculator 336 toggles, Address Field 327, against 351 on the native Heading). Two conditions follow: a Fortify widget must declare its content as controls inside `start_controls_section`, and must not be `final`. The wrapper has 200 slots, so the widget-type ceiling stays at 200. Not yet checked by hand in the editor UI.

## 9. Demo script

### With 1.1.0 (guided demos only)

1. On corporate, enter a ZIP and land on the matching office.
2. Show corporate brand, pricing and legal content alongside that office's own data.
3. Log in as a local editor. Show that locked controls are absent and unlocked text can be changed. Do not save hand edits you intend to keep.
4. As corporate, change a plan price and Publish to Sitelets. Reload two offices to show it landed, with office data intact.
5. Create a thirteenth office and add its registry record. It appears on the finder with all pages in place.

### After Phase 4 and merge Tier 1

Step 3 becomes a saved local edit, step 4 shows that edit surviving the publish, and prospects can be given their own logins on the online demo.

Not to be shown before merge Tier 2: corporate changes to unlocked content reaching existing sitelets.

## 10. Build phases

0. **Inventory, no changes.** Read the Fortify repo: pages, sections, fonts, colors, spacing, breakpoints, scripts (calculator, address form), images.
   **Done 2026-11-21.** 11 pages, one font (Plus Jakarta Sans 400–800), 14 named colors plus about 20 unnamed ones, no spacing scale (about 200 distinct values), breakpoints 980/860/760/560/520, 1180px container. Scripts: analytics (dropped), Google Places address form (replaced by a plain field), savings calculator (3% vs a fixed $2,495), plans-table toggle and FAQ accordion. The sample listing agreement PDF is linked but missing from the repo.
1. **Environment.** LocalWP site, Multisite in subdirectory mode, plugins, child theme, license activation, no-index. Then two critical-path checks with one throwaway subsite, before anything else is built: (a) how a Sitelet Page becomes a sitelet's front page, and (b) whether a Theme Builder header and footer render on a newly created sitelet. If either fails, stop and resolve it first. Also count widget types against the 200 limit.
   **Gating checks passed 2026-11-21** on Sitelets 1.1.2 with the Reading-screen fix (released as 1.1.3), plus browser confirmation: (1) `wp site create` runs the new-site sync, which delivers Sitelet Pages, templates and the kit; (2) the Sitelet home page checkbox makes the marked Sitelet Page the sitelet's front page, and Settings → Reading on the sitelet shows and keeps it; (3) Theme Builder header and footer render on the new sitelet; (4) 152 of 200 widget types. On 1.1.1 checks 2 and 3 failed (double-serialized meta, missing theme, no front-page path); fixed in 1.1.2. Known issue #25 (`wp_N_e_notes`) noted above. **Phase 1 complete 2026-11-21:** `fortify-child` (child of Hello Elementor 3.5.2) is network-enabled and active on site 1, and a throwaway sitelet created afterwards got it from the main site; the network is no-index (section 3), verified on site 1 and on the throwaway sitelet. The child theme and the must-use plugin live in the LocalWP install only.
2. **Design system.** Elementor global colors, fonts and spacing from the Fortify CSS.
   **Applied 2026-11-21** to the site 1 kit by `scripts/apply-kit.php` (re-runnable): Fortify's four main colors in Elementor's system slots (Primary indigo, Secondary ink, Text slate, Accent emerald) plus 23 named colors (14 originally unnamed values folded in, 4 translucent), Plus Jakarta Sans throughout, spacing scale 8/12/16/24/32/48/64/88px as `--space-*` variables in the kit custom CSS, 1132px content inside 24px side padding, breakpoints Mobile 560 / Mobile Landscape 760 / Tablet 980 (Fortify's 860 folded into Tablet).
   **The kit reaches a sitelet only when the sitelet is created** (the new-site sync copies it; Publish to Sitelets does not). Changes made to the kit after an office exists do not reach that office. The kit must be final before phase 6.
3. **Corporate rebuild.** Home, then inner pages, as native containers and widgets.
   **Homepage built 2026-11-21** by `scripts/build-home.php`, with the Theme Builder header and footer it needs (finalized in phase 5). Top-level containers: `#home-hero`, `#home-sources`, `#home-savings`, `#home-preview`, `#home-how`, `#home-compare`, `#home-plans`, `#home-trust`, `#home-review`, `#home-cta`; header `#site-header`, footer `#site-footer` with `#footer-offices`. Native widgets throughout, except HTML widgets for the two sample-listing cards, the hero "how it works" strip, the review label and the feature table. `fortify-child` adds two widgets: Savings Calculator and Address Field (plain GET form to `/preview/?address=…`; phase 4 adds ZIP routing through the `fortify_address_field_action` filter). Widget types now 154 of 200. Not yet built: the `/preview/` stub and the inner pages, so the footer and header links to them return 404 for now. The footer office directory is static text until the `fortify-offices` widget replaces it.
   **Inner pages built 2026-11-22** by `scripts/build-pages.php` (re-runnable; helpers shared with build-home in `scripts/lib/fx-elements.php`): How it works, Plans, FAQs, About, Contact, Sample listing agreement, the four legal pages (equal housing, privacy, terms, payment terms) at the live slugs, and the `/preview/` stub. Top-level container IDs are prefixed by page (`hiw-`, `plans-`, `faqs-`, `about-`, `contact-`, `sla-`, `legal-ehs-`/`-privacy-`/`-terms-`/`-payment-`, `preview-`). Native widgets throughout; FAQ lists use Elementor's nested accordion (one Text Editor per answer). The only HTML widget is the Plans comparison table (a fully locked corporate block). Styles in `fortify-child/assets/css/fortify-pages.css`; a small scrollspy in `fortify.js` highlights the Plans anchor bar and the FAQs sidebar. Header menu now links to the inner pages. Calls to action that went to the Fortify app go to the homepage address field (`/#start`); Help Center links go to help.fortifylistings.com. `/preview/` shows the submitted address (`[fortify_preview_address]` shortcode in the child theme) and stores nothing. The sample agreement PDF is missing from the source repo, so that page shows a placeholder until `$sla_pdf` is set in the script. About's coverage list and Contact's brokerage block are static text; registry-driven versions come with phase 4. Widget types 155 of 200.
4. **Registry.** `fortify-offices` plugin, widgets, finder; seed the twelve records.
   **Built 2026-11-22.** `plugins/fortify-offices` (network-activated): `office` post type on site 1 with the section 6 fields, a network-wide cached read, and the API `fortify_offices()`, `fortify_office()`, `fortify_office_for_site()`, `fortify_office_for_zip()`, `fortify_offices_search()`. Seeded by `scripts/seed-offices.php`: ten live Florida offices, Phoenix and Nashville coming soon, sample contact fields per SEED-PLAN, `blog_id` 0 until phase 6 (office links go to `/{slug}/` and 404 until then).
   ZIP coverage replaces SEED-PLAN's representative subset: 628 ZIPs from the Census 2020 ZCTA-to-county relationship file (`scripts/tools/build-zip-map.py`, output `data/office-zips.json`). A ZCTA belongs to the county with most of its land area. Palm Beach County is split by ZIP (approved 2026-11-22): Boynton Beach, Delray Beach and Boca Raton ZIPs (20) to boca-raton, the other 30 plus 33469 Tequesta to west-palm-beach. Border ZIPs whose land is mostly in an uncovered county are not covered (for example 32757, 33438 Canal Point). ZCTAs approximate ZIPs: PO-box-only ZIPs that have no ZCTA get the not-covered result.
   Six registry widgets (Elementor category "Fortify registry"): Office Directory (footer list or markets grid), State Coverage, Licensing Block (office's broker and license on an office site; corporate brokerage from the network option `fortify_offices_brokerage` on site 1), Office Info (one field of the current office; covers name, phone, address, hours, MLS line, counties, ZIPs, broker, license), Location Finder (ZIP/city/county search, state filter, Leaflet 1.9.4 from unpkg with OpenStreetMap tiles, not-covered result), Address Match (routes `?address=` on `/preview/` to the office, a coming-soon note, or the not-covered result linking to `/contact/`). Office-aware widgets have a "Preview with office" control for authoring templates on site 1. Office sites get RealEstateAgent JSON-LD on the front page. Team list waits for phase 5 (no team data shape yet).
   Replaced static pieces: footer office directory, About coverage list (now lists the registry's counties, 11, where the live site lists 20), Contact brokerage block, and the address form's routing (result on `/preview/`, continue button to the office's `/preview/`). New page `/locations/` with the finder; header menu gains "Find your market". Widget types 161 of 200 in the Sitelet Page editor context (160 on the front end); each registry widget gets Sitelets lock toggles (326–336).
5. **Office templates.** Finalize the Theme Builder header and footer and the global kit. Build the six Sitelet Pages on site 1; tick "Sitelet home page" on the office Home page; apply the lock map; publish. Before applying it, check that no Local or Registry item is an HTML widget (section 8). Nothing in this phase can be changed cheaply once offices exist.
   **Built 2026-11-22.** Header: nav is plain links (icon list), not a WordPress menu, because Sitelets does not copy menus to new sites; footer phone is the office's phone (Office Info) with the corporate line on site 1. On office sites the theme points root-relative links and form actions at the office's own path, so visitors stay on the office site; corporate pages appear there through the Sitelets fallback (1.1.4), with the office's registry and profile data. Kit unchanged from phase 2 and final; favicon comes from the theme. Six Sitelet Pages built by `scripts/build-office-templates.php`: `office` (Office Home, marked Sitelet home page), `service-area`, `team`, `reviews`, `local-faq`, `visit`, with the CSS IDs from SEED-PLAN section 5. The office home's corporate sections are copied from the corporate home page by the script. Lock map applied by the script as Sitelets lock keys: 185 elements fully locked (every control section), 7 with only their text editable, 1 Listing Card with its content sections editable. Template notes sit in a top-level Admin-only container (`#office-admin-notes`), removed on copy. Published (status publish); there are no office sites yet, so Publish to Sitelets has nothing to push; the new-site sync copies the pages in phase 6. New widgets: Market Intro, Team List, Local Reviews (fortify-offices), Listing Card (fortify-child). Widget types 165 of 200 in the Sitelet Page editor context.
   Checked on a throwaway sitelet (created, linked to the Tampa record, then deleted with its tables, uploads, user and license slot): pages, templates and front page arrived; registry and profile data rendered for Tampa; the admin-only notes were removed; a local editor could edit the profile and had no access on site 1; profile input was sanitized; lock keys arrived on the copies. Not checked: the lock behaviour in the office editor UI (needs a signed-in browser).
6. **Offices.** Check first that the kit is final (phase 2 note). For each office: set the registry record's site ID, seed its Office Profile with `Profile::sample()`, and check the office front end loads `custom-frontend.min.css` (Elementor's custom-breakpoint styles; if missing, run `Breakpoints\Manager::compile_stylesheet_templates()` on that site). Create the twelve sites; confirm the new-site sync delivered pages, templates and kit, and that each front page is the office Home (set by the plugin from the Sitelet home page checkbox); create one local editor per office. Expect issue #25 log noise.
   **6a done 2026-11-22: Tampa** (site #8, `/tampa/`) created by `scripts/create-offices.php tampa` on Sitelets 1.1.4: Sitelet Pages, templates and kit copied, Office Home is the front page, registry record linked, Office Profile seeded with sample content, breakpoint CSS compiled, local editor `tampa-editor` (editor on Tampa only; password set by Ralph in Network Admin). Corporate pages render at `/tampa/{slug}/` with Tampa's data and links. Waiting for Ralph's check of the editor locks as the local user before 6b (the other eleven: `scripts/create-offices.php --all`).
7. **Smoke test.** Run `docs/TESTING.md` from the plugin repo.
8. **Clone.** Copy the network to the local demo environment; later push the online demo.

## 11. Open items

Decisions for Ralph:

- Confirm MLS names for Phoenix and Nashville, and the NABOR boundary.
- Confirm the Elementor Pro plan covers a 13-site network, plus an activation for the online demo domain.
- ~~Confirm EDD download IDs 1056 (free) and 1333 (pro, 25 sites) against the store.~~ Done 2026-11-19: the store's IDs are 1056 (free, 3 sites), 2821 (25), 2824 (99), 2829 (500); `1333` is retired. The local install is activated on 2821.
- Confirm the license's activation limit covers the install URLs in use. There are three installs (local test, local demo, online demo). Check in the store whether local development URLs count toward the limit; the online demo certainly does.
- Decide whether sitelet pages should point their canonical tag at corporate.

Sitelets plugin issues found in phase 5 (plugin repo CLAUDE.md §8, 2026-11-22):

- ~~The 404 fallback does not render the main-site page.~~ Fixed in 1.1.4 (#26).
- ~~Container "Available on: Sitelet only" did not work (shown on both front ends, hidden in the editor preview).~~ Fixed in 1.1.4 (#27): hidden on the main site, shown on sitelets.
- Publishing Theme Builder templates and the kit to existing sitelets: held for the merge Tier 1 release (#28). Until then, header, footer and kit changes reach only offices created afterwards.
- Menus are not copied to sitelets: held for the merge Tier 1 release (#29). The header uses plain links meanwhile.

To verify in the test environment:

- ~~How a Sitelet Page becomes a sitelet's front page.~~ Resolved 2026-11-21: Sitelets 1.1.2's Sitelet home page checkbox (see section 5 table).
- ~~Whether Sitelet Pages are publicly reachable on site 1.~~ Yes: at `/blog/sitelet_page/{slug}/` (2026-11-22). Whether the "Only show on" document setting hides them is still open.
- ~~Whether Elementor Pro Theme Builder display conditions carry to new sites.~~ Resolved 2026-11-21: they did not on 1.1.1 (double-serialized); they do from 1.1.2, which also rebuilds the conditions cache.
- How header, footer and kit changes reach existing sitelets after creation.
- ~~Whether `wp site create` triggers the new-site sync.~~ Resolved 2026-11-21: yes.
- ~~License activation against the store's current domain.~~ Resolved: the local install is activated against store.sitelets.com on product 2821 (valid, 25 sites).

Rules for content:

- Local reviews, bios and listings are labelled as sample. No invented reviews presented as real.
- Office pages name brokers and license numbers by state. In the demo this is sample data; before anything like it goes public, each state's brokerage advertising rules need checking.
