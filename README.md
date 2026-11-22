# Fortify demo network

The Sitelets demo network: a WordPress Multisite with one Fortify corporate site and one sitelet per market office, built with Elementor and the Sitelets plugin. Fortify is only the demo brand and content source. The live fortifylistings.com stays static HTML and never runs this.

| Repo | Holds |
|---|---|
| this repo | Everything needed to rebuild the demo network: theme, must-use plugins, server config, build docs, and (as they are built) the `fortify-offices` plugin and seed scripts |
| `tfbceo/WP-Sitelets-Plugin-Elementor` | The Sitelets plugin, its code review, merge spec and `docs/TESTING.md` |
| `tfbceo/Fortify-Listings-FE-Website` | The static fortifylistings.com site: the content and design source |

## Layout

```
docs/BUILD-SPEC.md      what is built, in which phase, and why
docs/SEED-PLAN.md       office records, routing and template content to seed
docs/ENVIRONMENTS.md    local test, local demo, online demo; snapshot and reset
themes/fortify-child/   child theme of Hello Elementor
mu-plugins/             fortify-noindex.php: keeps the whole network out of search engines
plugins/fortify-offices/ office registry: post type, ZIP routing, finder and registry widgets
  data/                 offices.json (seed records) and office-zips.json (generated ZIP map)
config/                 htaccess-multisite-subdirectory: rewrite rules for Apache
scripts/apply-kit.php   phase 2: design system on the site 1 Elementor kit
scripts/build-home.php  phase 3: corporate homepage + Theme Builder header and footer
scripts/build-pages.php phase 3: corporate inner pages, legal pages, /preview/ stub
scripts/lib/            element helpers shared by the build scripts
scripts/seed-offices.php phase 4: seed the twelve office records
scripts/tools/build-zip-map.py  regenerate office-zips.json from the Census ZIP-to-county file
```

Scripts run with `wp eval-file` on site 1 and are re-runnable: each replaces what it made before.

Coming as they are built: more `scripts/` (office templates and the lock map).

## Rebuild from a fresh LocalWP site

Tested against: WordPress 7.1.5, PHP 8.5, Elementor 4.4.1, Elementor Pro 4.4.0, Hello Elementor 3.5.2, Sitelets 1.1.3.

1. **Create the site in Local.** Name `fortify-demo` (domain `fortify-demo.local`), custom environment, web server **Apache**, a current PHP and MySQL/MariaDB. Set an admin user of your own.
2. **Convert to subdirectory multisite.** In Local's site shell:
   ```
   wp core multisite-convert --title="Fortify Listings"
   ```
   Check that `wp-config.php` now has `MULTISITE` true, `SUBDOMAIN_INSTALL` false, `DOMAIN_CURRENT_SITE` `fortify-demo.local` and `PATH_CURRENT_SITE` `/`. Also add `define( 'WP_ENVIRONMENT_TYPE', 'local' );`.
3. **Replace `app/public/.htaccess`** with `config/htaccess-multisite-subdirectory`. WordPress does not write this file on multisite. Without it `/wp-json/` returns 404 and the block editor cannot save. Details in `docs/ENVIRONMENTS.md`.
4. **Install plugins and theme.**
   - Hello Elementor (from WordPress.org), then network-enable it.
   - Elementor and Elementor Pro, network-activated. Activate the Elementor Pro license.
   - Sitelets: upload `sitelets-for-multisite.1.1.3.zip` (or later), network-activate.
5. **Copy this repo's code into the site** (copy, or symlink from `wp-content` for active development):
   - `themes/fortify-child` to `wp-content/themes/fortify-child`
   - `mu-plugins/fortify-noindex.php` to `wp-content/mu-plugins/fortify-noindex.php` (create the folder)
   - `plugins/fortify-offices` to `wp-content/plugins/fortify-offices`, then network-activate it (Network Admin → Plugins)
6. **Activate the child theme.**
   ```
   wp theme enable fortify-child --network
   wp theme activate fortify-child
   ```
   New sitelets copy the main site's theme, so do this before creating any office.
7. **Activate the Sitelets license.** Network Admin → Sitelets License, using the Professional (25-site) key. It should read "Currently using: 0 of 25".
8. **Check the environment.**
   - `http://fortify-demo.local/wp-json/` returns 200 JSON.
   - The home page source has `<meta name='robots' content='noindex, nofollow' />` and the response has an `X-Robots-Tag: noindex, nofollow` header.
   - The page loads `fortify-child/style.css`.
9. **Seed the registry, apply the design system and build the pages** (from `app/public`, in Local's site shell, in this order):
   ```
   wp eval-file <repo>/scripts/seed-offices.php
   wp eval-file <repo>/scripts/apply-kit.php
   wp eval-file <repo>/scripts/build-home.php
   wp eval-file <repo>/scripts/build-pages.php
   ```
   The home page becomes the static front page, the header and footer apply to the entire site, the inner pages, legal pages, `/locations/` finder and `/preview/` stub are created at the live site's slugs.
10. **Continue with the build phases** in `docs/BUILD-SPEC.md` section 10. Phase 1's gating checks have been passed on Sitelets 1.1.3 and do not need repeating on a rebuild.

## Working rules

- Every throwaway subsite counts against the Sitelets license until it is deleted. After deleting one, drop its leftover `wp_N_e_*` tables and `uploads/sites/N`, and open the license screen so the count is cleaned up.
- The network is no-index in every environment. Remove `fortify-noindex.php` only for a real production launch.
- No analytics (Tag Manager, Clarity, Meta Pixel) and no Google Maps or Places keys. See BUILD-SPEC.
