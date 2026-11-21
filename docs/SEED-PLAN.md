# Fortify Demo Network — Golden-Master Seed Plan

**Date:** 2026-11-18 · Derived from `BUILD-SPEC.md` v2.1. This is the concrete content/data to seed the
golden-master demo network (ENVIRONMENTS.md envs 2 & 3; built on env 1). Apply in build phases 4–6.

## Guardrails (read first)
- **All data here is SAMPLE/demo.** Phones, addresses, hours, brokers, and license numbers are
  placeholders, not real. No reviews or bios are presented as genuine — they are labeled "sample" in the
  UI (BUILD-SPEC §11).
- **MLS/territory confidence is carried, not resolved.** Stellar/Miami are sourced; NABOR boundary is
  lower confidence; **Phoenix (ARMLS) and Nashville (Realtracs) are ASSUMED — confirm before use.**
- **ZIP sets are a representative subset for demo routing only** — not an authoritative coverage map.
- **Before anything resembling this goes public,** check each state's real-estate brokerage advertising
  rules (broker name + license display) — demo only until then.

## 1. Office registry records (seed in phase 4, site 1 `office` CPT)

**Identity & territory** (from BUILD-SPEC §4; `status` = registry visibility, all live for the demo):

| Blog ID | Office | slug | State | MLS (confidence) | Counties | Registry status |
|---|---|---|---|---|---|---|
| 2 | Tampa | tampa | FL | Stellar MLS (sourced) | Hillsborough | live |
| 3 | St. Petersburg–Clearwater | st-petersburg | FL | Stellar MLS (sourced) | Pinellas | live |
| 4 | Orlando | orlando | FL | Stellar MLS (sourced) | Orange, Seminole | live |
| 5 | Sarasota–Bradenton | sarasota | FL | Stellar MLS (sourced) | Sarasota, Manatee | live |
| 6 | Lakeland–Winter Haven | lakeland | FL | Stellar MLS (sourced) | Polk | live |
| 7 | Miami | miami | FL | Miami MLS (sourced) | Miami-Dade | live |
| 8 | Fort Lauderdale | fort-lauderdale | FL | Miami MLS (sourced) | Broward | live |
| 9 | Boca Raton–Delray Beach | boca-raton | FL | Miami MLS (sourced) | Palm Beach (south, by ZIP) | live |
| 10 | West Palm Beach–Jupiter | west-palm-beach | FL | Miami MLS (sourced) | Palm Beach (north, by ZIP) | live |
| 11 | Naples | naples | FL | SW Florida MLS / NABOR (⚠ lower confidence) | Collier | live |
| 12 | Phoenix | phoenix | AZ | ARMLS (⚠ ASSUMED — confirm) | Maricopa | live |
| 13 | Nashville | nashville | TN | Realtracs (⚠ ASSUMED — confirm) | Davidson, Williamson | live |

(Site 1 / blog ID 1 = Fortify corporate, not an office.)

**Sample contact / compliance fields** (placeholders — replace before any public use):

- **Phone:** `(555) 01X-0000` pattern, unique last digits per office (e.g. Tampa `(555) 012-0000`).
- **Address:** `#### Example Ave, {City}, {ST} {zip}` — street name literally "Example", never a real address.
- **Hours:** `Mon–Fri 9:00–5:00; Sat by appointment` (uniform demo default).
- **Broker of record:** `Sample Broker (demo)` — do **not** use a real person's name.
- **State license #:** `{ST}-SAMPLE-{blogID padded}` (e.g. `FL-SAMPLE-00002`) — obvious placeholder.
- **Lat/long:** approximate city-center coordinates (public), flagged approximate — Tampa 27.95/−82.46,
  St. Pete 27.77/−82.64, Orlando 28.54/−81.38, Sarasota 27.34/−82.53, Lakeland 28.04/−81.95,
  Miami 25.76/−80.19, Fort Lauderdale 26.12/−80.14, Boca Raton 26.36/−80.08, West Palm Beach 26.71/−80.05,
  Naples 26.14/−81.79, Phoenix 33.45/−112.07, Nashville 36.16/−86.78.

## 2. ZIP routing (phase 4 finder; representative demo subset, not a coverage map)

Routing resolves a ZIP → office. The Palm Beach County split (offices 9 & 10) is the key demo of
**ZIP-level** routing within one county.

| Office | Representative ZIPs (demo) |
|---|---|
| Tampa | 33602, 33606, 33611, 33629 |
| St. Petersburg–Clearwater | 33701, 33710, 33755, 33762 |
| Orlando | 32801, 32803, 32806, 32771 (Seminole) |
| Sarasota–Bradenton | 34236, 34239, 34205 (Manatee) |
| Lakeland–Winter Haven | 33801, 33803, 33880 |
| Miami | 33101, 33130, 33156, 33186 |
| Fort Lauderdale | 33301, 33308, 33312, 33334 |
| **Boca Raton–Delray Beach (9)** | **33431, 33432, 33486 (Boca); 33444, 33445, 33483 (Delray)** |
| **West Palm Beach–Jupiter (10)** | **33401, 33405, 33409, 33411 (WPB); 33458, 33477 (Jupiter)** |
| Naples | 34102, 34109, 34112 |
| Phoenix | 85003, 85018, 85032, 85251 |
| Nashville | 37201, 37203, 37215, 37027 (Williamson) |

- **Unmatched ZIP →** the registry "not-covered" result → corporate contact route (BUILD-SPEC §6).
- City/county search in the finder resolves via the registry counties field.

## 3. "Add an office" demo record (phase/demo step 5)

Seed one held-back secondary market as the live-demo creation. **Kissimmee** (recommended):
slug `kissimmee`, FL, Stellar MLS, Osceola County, sample contact fields as above, status `coming soon`
→ flipped to `live` during the demo after the site is created and the record added.

## 4. Corporate pages (phase 3, site 1 — ordinary pages)

| Page | Slug | Seed content (registry-driven parts noted) |
|---|---|---|
| Home | (front) | Hero with ZIP address form (routes to office); **markets grid** (registry, grouped by state); value props; **per-state licensing block** (registry); CTA to Locations/Preview. |
| Locations | locations | The **location finder** (ZIP/city/county search, state filter, Leaflet+OSM map, result list). |
| How it works | how-it-works | Steps/process; served on sitelets via 404 fallback. |
| Plans | plans | Pricing tiers + comparison (the corporate source the office template locks mirror). |
| FAQs | faqs | Corporate FAQ. |
| About | about | Company. |
| Contact | contact | Corporate contact + the not-covered fallback target. |
| Legal set | (existing slugs) | Terms, privacy, equal-housing, state disclosures. |
| Preview start | preview | Demo stub only (production flow is in the app, out of scope). |

Nav adds **"Find your market"**. Equity calculator = small custom Elementor widget in `fortify-child`.
**Slug check:** none of the office Sitelet Page slugs (office, service-area, team, reviews, local-faq,
visit) collide with these corporate slugs — required for the 404 fallback (BUILD-SPEC §5).

## 5. Office Sitelet Page templates (phase 5, authored on site 1) — sections, CSS IDs, lock treatment

Give every top-level container a CSS ID (BUILD-SPEC §8). Lock mechanisms used: **control locks** and the
container **"Available on"** setting. **Widget whitelist is NOT used** (bug #21). Registry blocks are
rendered by `fortify-offices` widgets, so they need no per-page editing and survive re-publish (#20).

### Office home — slug `office`
| Container (CSS ID) | Source | Lock treatment |
|---|---|---|
| `#office-hero` | Corporate + registry | Layout/images/buttons: **every control locked**; local **headline** text unlocked, typography/color locked; office name/phone via registry |
| `#office-intro` | Local | Market-intro text unlocked; style locked |
| `#office-sample-listing` | Local | Image + text unlocked; layout locked (card labeled **sample listing**) |
| `#office-howitworks` | Corporate | Every control locked |
| `#office-calculator` | Corporate | Every control locked (calculator widget) |
| `#office-comparison` | Corporate | Every control locked |
| `#office-plans` | Corporate | Every control locked (pricing) |
| `#office-cta` | Corporate | Every control locked |
| `#office-admin-notes` | Corporate | **Top-level** container, "Available on" = **Admin only** (never nested — #22) |

### Service area — slug `service-area`
| `#sa-overview` | Registry | Counties/ZIPs/MLS line rendered dynamically |
| `#sa-neighborhoods` | Local | Text unlocked; style locked |
| `#sa-market-notes` | Local | Text unlocked; style locked |

### Team — slug `team`
| `#team-list` | Registry | Team/broker rendered dynamically (sample data) |
| `#team-compliance` | Corporate | Licensing/compliance text — every control locked |

### Reviews — slug `reviews`
| `#reviews-list` | Local | Fixed set of review widgets; text unlocked, style/layout locked. All reviews labeled **sample**. (Widget-add restriction deferred to the #21 fix.) |
| `#reviews-disclosure` | Corporate | Review disclosure — locked |

### Local FAQ — slug `local-faq`
| `#localfaq-entries` | Local | Entries text unlocked; style locked |
| `#localfaq-corporate-link` | Corporate | Link to corporate FAQ — locked |

### Visit us — slug `visit`
| `#visit-details` | Registry | Office name/address/phone/hours/map rendered dynamically |

## 6. Sample copy sets (placeholders — finalize later)
- **Hero headlines (per office):** `"Sell your {City} home for a flat fee."` (sample default; editable text).
- **Market intro (per office):** 1–2 sample sentences about the market; clearly generic demo copy.
- **Local FAQ:** 3–4 sample Q&A per state (FL/AZ/TN), generic.
- **Reviews:** 3 sample testimonials per office, each visibly tagged **"Sample review — demo."**
- **Team/bios:** `Sample Agent` entries, tagged sample; broker = `Sample Broker (demo)`.
- **Sample listing card:** stock/placeholder image + `"Sample listing — not a real property."`

## 7. Seeding order (ties to BUILD-SPEC build phases)
1. **Phase 4:** install `fortify-offices`; create the 12 `office` records (§1) + ZIP routing (§2);
   build the registry widgets + finder.
2. **Phase 5:** finalize Theme Builder header/footer + global kit; build the 6 office templates (§5) on
   site 1 with CSS IDs; apply the lock map **by script** (locks are settings keys — BUILD-SPEC §5);
   Publish to Sitelets.
3. **Phase 6:** create the 12 office sites; confirm the new-site sync delivered pages/templates/kit; set
   each front page; create one `Sample Editor` local-editor login per office.
4. Capture the **golden master** snapshot (ENVIRONMENTS.md) once it passes `TESTING.md`.
5. Keep **Kissimmee** (§3) un-created as the live "add an office" demo step.

## 8. Dependencies that gate this plan (from BUILD-SPEC §11 — resolve in phase 1)
- Can a **Sitelet Page be a sitelet's front page**? (`#office-*` home depends on it.)
- Do **Theme Builder header/footer** render on a newly created sitelet? (Everything depends on it.)
- Total **widget count < 200** (core + Pro + fortify-offices).
- If any fail, stop and resolve before seeding (BUILD-SPEC phase 1).
