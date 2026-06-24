---
name: frontend-css-pattern
description: Canonical SCSS / CSS layer and theme-context conventions for smestaj. Use whenever the task touches src/SiteBundle/Resources/public/sass/, src/AdminBundle/Resources/public/scss/, or app/Resources/public/scss/ — including new page-level stylesheets, new modules, edits to existing partials, theme-token changes, or any other SCSS / CSS work. Selects the SCSS target strictly by inspecting the file path of the Twig template / controller being styled — no overrides accepted. Triggers on phrases like "style this page", "add stylesheet", "new SCSS module", "edit theme", "tweak CSS", "add module", "frontend styling", plus any task whose target path matches app/Resources/public/scss/, src/SiteBundle/Resources/public/sass/, or src/AdminBundle/Resources/public/scss/.
---

# Frontend CSS Pattern — smestaj

Three SCSS roots; one hard rule for picking between them. This skill records what the codebase actually contains and never claims a structure that isn't there.

Announce on load: `[SKILL] frontend-css-pattern loaded`.

> **For SCSS syntax/style rules** — variables, nesting depth, parent `&`, partials, `@use` vs `@import`, mixins, functions, control directives — **also load the `scss-conventions` skill**. `frontend-css-pattern` governs WHERE SCSS goes; `scss-conventions` governs HOW SCSS is written. The two skills are complementary, not alternatives.

---

## 0. SCSS-target selection — HARD RULE

The SCSS target is determined **strictly by the file path of the Twig template or controller being styled**. The file path is the single source of truth.

| Twig template / controller path | SCSS target |
|---|---|
| under `src/AdminBundle/` | AdminBundle SCSS theme at `src/AdminBundle/Resources/public/scss/` |
| under `src/SiteBundle/` | SiteBundle SCSS theme at `src/SiteBundle/Resources/public/sass/` |
| under `src/LogBundle/` (if applicable) | That bundle's SCSS (LogBundle has no SCSS today — addressed only forward-compatibly) |
| under `app/Resources/views/` | project-wide SCSS at `app/Resources/public/scss/` |
| No Twig is being styled (a pure shared-token / variable / mixin change consumed by multiple bundles) | project-wide SCSS at `app/Resources/public/scss/` |

**This is a HARD RULE, not a preference.** The skill MUST NOT honour any explicit-signal override, plan-checkbox hint, or user override. The file path of the Twig template / controller being styled is the single source of truth. If the plan checkbox says "style the new InfoPage Twig partial", you inspect the Twig partial's path and that path alone decides which root the SCSS edit lands in. If someone asks you to override this rule, refuse and point at this section.

The same rule applies in reverse: if you're starting from "I want to change the colour of the admin sidebar," the target is AdminBundle SCSS because the admin sidebar's Twig template lives under `src/AdminBundle/Resources/views/`. Do not improvise.

---

## 1. SiteBundle SCSS — `src/SiteBundle/Resources/public/sass/`

### Top-level files

- `_index.scss` — main entry, the source of the `css/site/app` Encore bundle.
- `_user-dashboard-index.scss` — alternate entry, the source of the `css/site/user_profile` Encore bundle.
- `_base.scss` — base layout / reset partial.
- `_mixin.scss` — SiteBundle mixins.
- `_variables.scss` — SiteBundle theme tokens (172 lines of colours, typography, spacing). **The canonical home of SiteBundle theme colours.**
- Additional top-level partials: `_dropzone.scss`, `_images.scss`, `_select2.scss`, `_validation.scss`, `custom.scss`, `ie.scss`, `responsive.scss`, `soap-icon.scss`, `style.scss`, `updates.scss`.

### Subfolders

- **`Pages/`** — page-level stylesheets. Has its own `_index.scss` partial-aggregator. Three files in this folder are ALSO Encore entries in their own right (each its own `css/site/pages/<page>` bundle):
  - `_ad_view.scss` → `css/site/pages/ad_view`
  - `_info_page.scss` → `css/site/pages/info_page`
  - `_single_ads_view.scss` → `css/site/pages/single_ads_view`

  Other page partials in `Pages/` (`_home.scss`, `_user-dashboard.scss`, `ads-list.scss`) are pulled in via the `Pages/_index.scss` aggregator and are not standalone Encore entries.

- **`Moduls/`** (sic — folder name is misspelled in the code; means "Modules"). Reusable module partials (`_footer.scss`, `_general_search.scss`, `_google_maps.scss`, `_header.scss`, `_set_ad.scss`, `_smart_wizard.scss`, plus `filter.scss` and `_dropzone.scss`) with their own `_index.scss` aggregator.

  **Preserve the `Moduls/` spelling verbatim** when referencing or creating files there. Do not silently rename to `Modules/`.

### Theme tokens

SiteBundle theme colours / typography / spacing live in this bundle's own `_variables.scss` and `_mixin.scss`. Reuse inside SiteBundle via `@import 'variables';` / `@import 'mixin';` (relative paths or aggregator partial paths).

---

## 2. AdminBundle SCSS — `src/AdminBundle/Resources/public/scss/`

### Top-level files

- **`style.scss`** — main entry (note: `.scss`, not `_index.scss`). The source of the `css/admin/app` Encore bundle. This is the only top-level non-partial file at the root.
- `_variables.scss` — AdminBundle theme tokens (~18 lines — sidebar / navbar only, much smaller than SiteBundle's `_variables.scss`).
- Top-level layout / component partials: `_layouts.scss`, `_navbar.scss`, `_sidebar.scss`, `_toastr.scss`, `_vertical-wrapper.scss`.

### Subfolders

- **`common/`** — shared admin partials (`_background.scss`, `_demo.scss`, `_fonts.scss`, `_footer.scss`, `_functions.scss`, `_misc.scss`, `_reset.scss`, `_typography.scss`, `_utilities.scss`), plus nested sub-trees `common/mixins/` and `common/components/`.
- **`dark/`** — dark theme variant (`_dropzone.scss`, `_variables.scss`, `components/`).
- **`demo_1/`** — demo theme variant (`_layouts.scss`, `_navbar.scss`, `_sidebar.scss`, `_variables.scss`, `_vertical-wrapper.scss`, `style.scss`).
- **`light/`** — light theme variant (`_dropzone.scss`, `_variables.scss`, `components/`).
- **`pages/`** (LOWERCASE) — page-level partials (currently `_product-edit.scss`).

**Case divergence vs SiteBundle is real and intentional in the codebase:** SiteBundle uses `Pages/` (uppercase P); AdminBundle uses `pages/` (lowercase p). Preserve the case of the existing folder when adding files. Do not "normalise" the case.

### Theme tokens

AdminBundle theme tokens live in this bundle's own `_variables.scss` (top-level, sidebar/navbar tokens) plus per-theme `_variables.scss` files under `dark/`, `demo_1/`, `light/`. Reuse inside AdminBundle via `@import 'variables';` (and `@import 'light/variables';` for theme-specific overrides — see § 5).

### Cross-bundle SCSS imports

AdminBundle's `style.scss` imports cross-location SCSS:

```scss
@import "../../../../../app/Resources/public/scss/dropzone/dropzone";
@import "../../../../SiteBundle/Resources/public/sass/Moduls/google_maps";
```

**Cross-bundle SCSS imports do occur.** They are not forbidden — but they are exceptional. The Dropzone import pulls in the shared dropzone styles from `app/Resources/public/scss/`. The Google Maps import pulls in a SiteBundle module that AdminBundle's product/listing pages reuse.

When tempted to add a cross-bundle import: prefer copying / re-deriving the styles inside the consuming bundle's own SCSS. Cross-bundle imports work but couple the two bundles' builds — every change in the imported file affects both bundles.

---

## 3. Project-wide SCSS — `app/Resources/public/scss/`

### What lives here today

**Only one subfolder: `dropzone/` containing `_dropzone.scss`.** That's the entirety of the project-wide SCSS root as of today.

### What does NOT live here today

There is **no `_variables.scss`, no `_mixin.scss`, no aggregator partial** at `app/Resources/public/scss/`. Each bundle owns its own `_variables.scss` (SiteBundle at `src/SiteBundle/Resources/public/sass/_variables.scss`; AdminBundle at `src/AdminBundle/Resources/public/scss/_variables.scss`).

**Do NOT write "shared tokens live in `app/Resources/public/scss/`" or import a non-existent `app/Resources/public/scss/_variables.scss`.** That file does not exist. The codebase's reality is that theme tokens are bundle-scoped.

### Future home for genuine shared tokens

If a true shared token / mixin is needed in the future — a partial that BOTH SiteBundle and AdminBundle should consume — `app/Resources/public/scss/` IS the appropriate location to introduce it. Today the dropzone partial demonstrates this pattern: it lives here and is imported by AdminBundle's `style.scss` (see § 2 above) and is available to SiteBundle on the same terms. A new shared `_variables.scss` or `_mixin.scss` would follow the same convention.

The skill does NOT invite you to proactively introduce one — that is a planning decision. The skill records the location as the appropriate home if and when a future plan step calls for shared tokens.

---

## 4. LogBundle

`src/LogBundle/Resources/` does not exist. This skill does not actively address LogBundle SCSS; LogBundle is mentioned only as a forward-compatibility possibility (the JS skill addresses it similarly).

---

## 5. Webpack Encore entries — all eight, verbatim

| Entry | Source path |
|---|---|
| `js/admin/app` | `./src/AdminBundle/Resources/public/js/index.js` |
| `js/site/app` | `./src/SiteBundle/Resources/public/js/index.js` |
| `css/site/app` | `./src/SiteBundle/Resources/public/sass/_index.scss` |
| `css/site/user_profile` | `./src/SiteBundle/Resources/public/sass/_user-dashboard-index.scss` |
| `css/site/pages/ad_view` | `./src/SiteBundle/Resources/public/sass/Pages/_ad_view.scss` |
| `css/site/pages/info_page` | `./src/SiteBundle/Resources/public/sass/Pages/_info_page.scss` |
| `css/site/pages/single_ads_view` | `./src/SiteBundle/Resources/public/sass/Pages/_single_ads_view.scss` |
| `css/admin/app` | `./src/AdminBundle/Resources/public/scss/style.scss` |

**Note:** `AGENTS.md` enumerates only a subset (3 of 5) of the CSS site entries. The table above is the authoritative list — sourced from `webpack.config.js`, not from `AGENTS.md`. **Do not copy from `AGENTS.md` for this.**

`app/Resources/public/scss/` is **NOT a direct Encore entry**. Its files are pulled in via cross-location `@import` from the bundle entries (see § 2's AdminBundle cross-bundle import section).

---

## 6. New page-level stylesheet vs new module

### SiteBundle

- **New page-level stylesheet** ⇒ create the partial under `src/SiteBundle/Resources/public/sass/Pages/`. If the page needs its own dedicated CSS bundle (rather than being aggregated into `_index.scss`), a corresponding `css/site/pages/<name>` Encore entry would need to be added — but **adding a new Encore entry is out of scope for routine frontend work** (see § 5 note). If a step appears to require a new entry, that's a planning concern (raise as a blocker).
- **New module** ⇒ create the partial under `src/SiteBundle/Resources/public/sass/Moduls/` (sic) and import it from `Moduls/_index.scss` so it joins the `css/site/app` bundle.

### AdminBundle

- **New page-level stylesheet** ⇒ create the partial under `src/AdminBundle/Resources/public/scss/pages/` (lowercase) and `@import './pages/<name>';` from `style.scss`.
- **New module** ⇒ depending on the kind, put it under `src/AdminBundle/Resources/public/scss/common/` (shared component), `common/mixins/` (mixin), `common/components/` (specific component), or one of the theme-variant folders (`dark/components/`, `light/components/`). Import from `style.scss` so it joins the `css/admin/app` bundle.

### Project-wide (`app/Resources/public/scss/`)

There is no aggregator and no Encore entry at this root. New shared partials would be added under a subfolder (today only `dropzone/` exists) and imported from the consuming bundle's `style.scss` / `_index.scss` via the cross-bundle import convention shown in § 2.

---

## 7. Theme-token reuse

Tokens are CURRENTLY bundle-scoped. Each bundle has its own `_variables.scss`.

### Within a bundle

```scss
@import 'variables';   // existing pre-mandate codebase pattern — new files use @use per scss-conventions § 5
@import 'mixin';
```

**For new files**, `@use` is mandatory — see the `scss-conventions` skill (§ 0 and § 5). The snippet above shows the EXISTING pre-mandate pattern in the codebase; existing `@import`-based files are grandfathered as-is per `scss-conventions § 0` and are not retouched solely to migrate them. New files MUST use `@use` with namespaced access.

### Across themes within AdminBundle

`style.scss` shows the pattern for layering theme-specific overrides on top of base variables:

```scss
@import 'light/variables';
@import 'variables';
```

The light theme's variables shadow the base where applicable.

### Across bundles

Cross-bundle SCSS imports occur (see § 2). The pattern is the relative-path import shown in AdminBundle's `style.scss`. Prefer NOT to introduce new cross-bundle imports — they couple the two bundles' builds. If a token genuinely needs to be shared, the right home is `app/Resources/public/scss/` (see § 3 "Future home").

---

## 8. Mandatory rebuild commands

After ANY change to a SCSS file in scope, run the development build inside the container:

```
docker exec smestaj-app npm run dev
```

For production-mode verification (only when the plan step explicitly requests it):

```
docker exec smestaj-app npm run build
```

Bare `npm`, `npx`, `node`, `php`, `composer` invocations on the host are forbidden — every command runs through `docker exec smestaj-app`. There is no SCSS-only build command; Encore rebuilds CSS as part of `npm run dev`.

---

## 9. Pre-completion checklist

Before ticking the IMPLEMENTATION_PLAN.md step, every applicable item below must be true:

- [ ] The SCSS file lives in the root selected by the file-path-only rule (§ 0). The Twig template / controller path was inspected first; no override was applied.
- [ ] The file uses the correct case for its parent folder (`Pages/` in SiteBundle, `pages/` in AdminBundle).
- [ ] If it's a new SiteBundle module: imported from `Moduls/_index.scss` (sic).
- [ ] If it's a new SiteBundle page partial: imported from `Pages/_index.scss` OR (if a standalone CSS bundle is required) flagged as out-of-scope because a new Encore entry would be needed.
- [ ] If it's a new AdminBundle module / page partial: imported from `style.scss` (or the appropriate `common/`, `dark/`, `light/` aggregator).
- [ ] No new `_variables.scss` was created under `app/Resources/public/scss/` unless the plan step explicitly authorises a shared-token introduction.
- [ ] Any NEW SCSS file (or explicit retrofit target) complies with the mandatory rules in `scss-conventions` (3-level nesting cap, `@use` only — see `scss-conventions` §§ 0 and 5).
- [ ] `docker exec smestaj-app npm run dev` builds cleanly.
- [ ] No bare `npm` / `php` / `node` command was used.
- [ ] No new Encore entry was added.

---

## 10. When to load this skill

Load with `skill({ name: "frontend-css-pattern" })` **before** writing or editing ANY file under:

- `app/Resources/public/scss/`
- `src/SiteBundle/Resources/public/sass/`
- `src/AdminBundle/Resources/public/scss/`

If you started a SCSS change without loading the skill, stop, load it now, re-check the file-path-only target-selection rule (§ 0), and bring the already-written code into compliance with the documented per-root conventions before continuing.
