# Sitelets — Environments Plan

**Out of scope:** the **live fortifylistings.com** site stays **static HTML and gets no plugin** — it is
**not** part of this and is never a deployment target. "Fortify" here is only the **demo brand and content
source**, not a production deployment.

**Network shape:** a Sitelets network models one brand's **market offices** — Fortify has **12 offices = 12
sitelets** (one sitelet **per office**, *not* per listing). All three environments below build that same
12-office network from Fortify content.

| # | Environment | Purpose | Basis |
|---|---|---|---|
| 1 | **Local test** | Plugin development + validation of **Phase 3, Phase 4, merge-on-publish** | **LocalWP** multisite, rebuilt with **this repo's README**, content from the **Fortify site repo** |
| 2 | **Local demo** | Clean network for **screen-share** demos (no dev mess) | A **clone of #1's** network, kept clean |
| 3 | **Online demo** | **Guided demos now; prospect logins after Phase 4 + merge Tier 1** | Password-protected, Sitelets-branded domain, **isolated hosting** |

## 1. Local test (LocalWP)
- A LocalWP WordPress **Multisite** rebuilt by following this repo's **README** (content from the Fortify site repo, `tfbceo/Fortify-Listings-FE-Website`) — so it's reproducible and a clean
  reset is just a rebuild. This is where plugin code is developed and where Phase 3 / Phase 4 /
  merge-on-publish are validated before anything else sees them.
- Install target: `sitelets-for-multisite.1.1.3.zip` (current release), then the phase builds as they land.

### LocalWP web server: Apache, with a hand-added `.htaccess`
- The LocalWP site (`fortify-demo.local`, subdirectory multisite) runs **Apache**, not nginx. URL rewriting
  comes from `app/public/.htaccess`.
- **WordPress does not write this file on multisite.** Re-saving Settings → Permalinks won't create it;
  Network Admin → Settings → Network Setup only *shows* the rules to copy in by hand. A clone, import or
  migration can leave the site without it.
- **Symptom when it's missing:** `/wp-json/` returns Apache's own small 404 page (not WordPress's), and block
  editor saves fail with *"not a valid JSON response"* — with or without Sitelets active. `?rest_route=/`
  still works, which is the giveaway that only rewriting is broken.
- **First check after any clone or migration:** `http://<site>.local/wp-json/` returns **200 JSON**
  (`Content-Type: application/json`). If not, check for `.htaccess` first.
- The rules (standard WordPress subdirectory multisite), as added to `app/public/.htaccess`. A copy to drop in is `config/htaccess-multisite-subdirectory` in this repo:

```apache
# BEGIN WordPress Multisite
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]

# add a trailing slash to /wp-admin
RewriteRule ^([_0-9a-zA-Z-]+/)?wp-admin$ $1wp-admin/ [R=301,L]

RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(wp-(content|admin|includes).*) $2 [L]
RewriteRule ^([_0-9a-zA-Z-]+/)?(.*\.php)$ $2 [L]
RewriteRule . index.php [L]
# END WordPress Multisite
```

## 2. Local demo
- A **clone of the same 12-office network**, kept pristine for live screen-shares. Don't develop here.
- Reset to clean before a demo (restore the clone, or re-clone from #1's golden state).

## 3. Online demo (spec)
- **Domain:** a Sitelets-branded demo host (e.g. `demo.sitelets.com` / `try.sitelets.com`) showing the
  **Fortify** 12-office content. **Isolated hosting** — separate from the production store (sitelets.com)
  and from anything real.
- **Password-protected:** host-level HTTP basic auth (blocks casual access + indexing; also `noindex`).
  Later, per-prospect WordPress logins **behind** that gate.
- **Build:** the full/pro build, activated with a **dedicated demo/internal license** (not a customer
  license; not counted against production limits). Do **not** use the `SITELETS_DEV_STORE` flag.
- **Access gating:**
  - **Now (pre-Phase 4):** **guided demos only** — sales drives the screen. No prospect self-login, because
    locks are still editor-side only (a prospect could bypass them and misrepresent the product) and
    publishes aren't yet safe/rollbackable.
  - **After Phase 4 + merge Tier 1:** open **prospect logins** (self-serve sandboxes) — locks hold
    server-side, publishes are lock-aware and rollbackable, and the reset below wipes tinkering.

## Snapshot-and-reset procedure (demo environments)
Environment-level restore to a known-good **golden** state — distinct from the plugin's **per-publish,
per-site** snapshot in the plugin repo's `docs/MERGE-ON-PUBLISH-SPEC.md` (use both). Applies to envs **2 and 3**; env 1 resets by
rebuilding from this repo's README.

**One-time: capture the golden master**
- [ ] Stand up the seeded 12-office network (per the forthcoming build spec: offices, pages,
      locked/editable map, demo script).
- [ ] Snapshot it: DB dump + `wp-content/uploads` (e.g. `wp db export golden.sql` + `tar` of uploads), or a
      hosting snapshot, or a migration archive. Store **versioned** (`golden-YYYYMMDD`).
- [ ] Record the exact restore steps for the host.

**Reset (on demand — before/after a demo or prospect session — and, for the online demo, nightly)**
- [ ] Enable maintenance mode / hold the basic-auth gate.
- [ ] Restore DB from the golden dump; restore `uploads`; drop any prospect-created subsites.
- [ ] Check `/wp-json/` returns **200 JSON**. On a LocalWP (Apache) copy, a 404 means `.htaccess` is
      missing — see *LocalWP web server* above. Flushing rewrite rules does **not** recreate it on multisite.
- [ ] Flush object cache + rewrite rules; re-activate the demo license if needed.
- [ ] Quick smoke check (a locked section stays locked; a sample publish works).
- [ ] Lift maintenance mode.
- [ ] **Cadence:** online demo = automated **nightly** reset + **one-click/manual** reset between prospect
      sessions; local demo = reset before each screen-share.

**Refreshing the golden master** (when demo content should change)
- [ ] Update content, verify, re-capture a new `golden-YYYYMMDD`, point the reset at it.

## Notes
- Prospect-login phase (env 3) depends on **Phase 4** + **merge-on-publish Tier 1**. Until both land,
  keep it guided-only.
- The **golden-master seed content plan** (offices, pages, locked/editable map, demo script) is being
  provided by Ralph as a build spec added to the repo — work from that, don't invent one.
