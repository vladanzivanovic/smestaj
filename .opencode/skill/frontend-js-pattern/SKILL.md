---
name: frontend-js-pattern
description: Canonical JS layer / wiring conventions for smestaj across all four in-scope JS roots — app/Resources/public/js/, src/SiteBundle/Resources/public/js/, src/AdminBundle/Resources/public/js/, and src/LogBundle/Resources/public/js/ (forward-compat; LogBundle has no JS yet). Use whenever the task touches one of those roots — including new controllers, validators, handlers, mappers, services, dom modules, helpers, filters, html services, inputs, routing files, or wiring through index.js. Describes (does not prescribe) the multiple coexisting shapes per layer so the implementer picks a documented variant and never invents a new one. Triggers on phrases like "create JS controller", "new JS controller", "add validator", "new handler", "JS module", "frontend controller", plus any task whose target path matches app/Resources/public/js/ or src/*/Resources/public/js/.
---

# Frontend JS Pattern — smestaj

Every JS module in smestaj lives in one of four roots and one of the PascalCase layer subdirectories that root defines. Layer shapes are documented from what the codebase actually contains — many layers host more than one coexisting pattern. This skill **describes**; it picks a winner only where the codebase is unanimous on a single shape.

Announce on load: `[SKILL] frontend-js-pattern loaded`.

---

## 0. The four JS roots

| Root | Purpose | Encore entry |
|---|---|---|
| `app/Resources/public/js/` | Project-wide JS shared between bundles | Not a direct Encore entry — consumed via `require()` from bundle entries |
| `src/SiteBundle/Resources/public/js/` | Public site (SiteBundle) | `js/site/app` (`./src/SiteBundle/Resources/public/js/index.js`) |
| `src/AdminBundle/Resources/public/js/` | Admin panel (AdminBundle) | `js/admin/app` (`./src/AdminBundle/Resources/public/js/index.js`) |
| `src/LogBundle/Resources/public/js/` | Internal log viewer (LogBundle) | None — `src/LogBundle/Resources/` does not exist yet. Mentioned only as forward-compatibility. |

`app/Resources/public/js/` is a first-class root with equal standing to the bundle roots — shared helpers, the base `CoreController`, the project-wide `ToastrService`, the base validator, and the FOSJsRouting / Sentry shims live here. Bundle entries `require()` into it explicitly (paths shown below).

---

## 1. Layer matrix — which root contains which layer

The layer enumeration below comes from `src/SiteBundle/Resources/public/js/`. Not every layer exists in every root.

| Layer | `app/` | SiteBundle | AdminBundle | Notes |
|---|---|---|---|---|
| `Constants/` |   | yes |   | SiteBundle only |
| `Controller/` | yes | yes | yes | Base class lives in `app/`; bundles extend |
| `Core/` |   |   | yes | AdminBundle only — template / bootstrap |
| `Dom/` | yes | yes |   | SiteBundle has nested `Dom/Dashboard/` |
| `Filters/` |   | yes |   | SiteBundle only |
| `Handler/` |   | yes | yes | AdminBundle has nested `Handler/Product/`, `Handler/InfoPage/` |
| `Helper/` | yes | yes |   | |
| `HtmlService/` |   | yes |   | SiteBundle only |
| `Inputs/` |   | yes |   | SiteBundle only |
| `Mapper/` | yes | yes | yes | |
| `Routing/` |   | yes |   | SiteBundle only — note: distinct from `app/Resources/public/js/Routing.js` (file, not folder) |
| `Services/` | yes | yes | yes | AdminBundle has nested `Services/DataTables/` |
| `Validation/` (singular) |   | yes |   | SiteBundle only |
| `Validators/` (plural-s) | yes |   | yes | Naming diverges from SiteBundle's `Validation/` |

The `Validation/` (SiteBundle, singular) vs `Validators/` (`app/` + AdminBundle, plural) split is a real codebase divergence — preserve the existing name when adding to a root; do not rename to harmonise.

---

## 2. Layer-by-layer description

Each subsection records the shape(s) the codebase actually contains, with concrete file citations so you can read the canonical example before writing a new one. Where multiple shapes coexist, this skill describes selection criteria but does not pick a winner — the implementer chooses a documented variant.

### 2.1 `Constants/` — SiteBundle only

Two coexisting shapes:

- **Bare globals.** `const FOO = '...';` declared at module scope with no `export`. Consumers rely on the variable being in the bundled scope. Example: `MainConstants.js` (`const APP_MODE = relativPath;`), `UrlConstants.js` (`const ADS_PAGINATION = '/api/product-pagination';`).
- **Named exports.** `const FOO = '...'; export {FOO}` — the modern shape that allows `import {FOO} from '../Constants/MessageConstants';`. Example: `MessageConstants.js` (imported in `AdsHandler.js` via `import {MESSAGE} from "../Constants/MessageConstants";`).

There is no registration index for `Constants/`. Consumers import or rely on the global directly.

Selection criteria: prefer the named-export shape for new constants — it survives bundling reorders and gives the linter something to follow. Use the bare-global shape only when extending an existing bare-global file that consumers already read globally.

### 2.2 `Controller/`

**SiteBundle** and **AdminBundle** both follow the same dispatcher pattern.

- File shape: ES6 `class` with a default export. Privates use either `#privateField` syntax or a `Symbol('private')` closure pattern — both occur in the codebase. You should always use `#privateField`, if that is not possible, BLOCK further development and ask user how to proceed.
- The base `CoreController` lives in `app/Resources/public/js/Controller/CoreController.js` and is extended by both bundles' `Core/` or root controllers (see AdminBundle's `Controller/index.js` line `import CoreController from "../../../../../../app/Resources/public/js/Controller/CoreController";`).

**Registration — `Controller/index.js` is a route-table dispatcher** keyed by the global `ROUTE_NAME`:

```js
import IndexController from "./IndexController";
// ...

let routes = [
    { name: 'site_index', controller: () => IndexController },
    { name: 'site_ads_view', controller: () => { /* conditional */ } },
    // ...
];

$(document).ready(() => {
    const route = matchRoute();
    const core = new CoreController();
    core.showFlashMsg();

    if (route) {
        const controller = route.controller();
        new controller();
    }
});
```

**Adding a new page controller ⇒ add an entry to that route table.** Both SiteBundle's `Controller/index.js` and AdminBundle's `Controller/index.js` use this exact pattern. The AdminBundle variant uses a direct reference (`controller: DashboardController`) instead of a thunk; the SiteBundle variant uses a thunk (`controller: () => IndexController`) so it can branch on global state at dispatch time (see `site_ads_view`). Pick the shape the bundle already uses.

`app/Resources/public/js/Controller/` contains only the base `CoreController` — no dispatcher, no `index.js`.

### 2.3 `Core/` — AdminBundle only

Bootstrap / template setup files. `Core/index.js` is a side-effect import (`import './template';`). Adding a new core module ⇒ `import './<NewFile>';` to `Core/index.js`.

### 2.4 `Dom/`

ES6 class shape. Two coexisting variants:

- **Default-export the class; consumer instantiates with DI.** Example: `PaginationDom.js` (consumer passes selectors / config when constructing).
- **Pre-instantiate a singleton, `Object.freeze()` the instance, default-export the instance.** Example: `AdsPageDom.js`.

There is a nested subfolder `Dom/Dashboard/` in SiteBundle. There is no `Dom/index.js` — consumers import individual files.

`app/Resources/public/js/Dom/` hosts shared dom modules (`DropZoneDom.js`, `Loader.js`); the same two shapes are present.

Selection criteria: use the pre-instantiated singleton when the module owns no per-instance state (it just queries the DOM globally); use the class-with-DI shape when the consumer needs to point the module at a specific subtree or pass per-page selectors.

### 2.5 `Filters/` — SiteBundle only

**Unanimous shape: ES6 `class` with static methods only, default-export of the class.** No instances are constructed. Example: `ArrayFilters.js`, `StringFilter.js`. Call sites do `ArrayFilters.someMethod(...)`.

`Filters/index.js` is a side-effect aggregator: `import './ArrayFilters'; import './StringFilter';`. The parent `src/SiteBundle/Resources/public/js/index.js` currently has the `import './Filters'` line commented out — so adding a new filter file requires both adding the file AND uncommenting the parent import IF you actually need the side-effect import. If consumers `import StringFilter from "../Filters/StringFilter"` directly, the aggregator import is not required.

### 2.6 `Handler/`

Two coexisting shapes — both equally canonical:

- **IIFE-Public/Private pattern.** Default-export an IIFE returning `Public`. Consumer calls `AdsHandler()` (with the parens — it's a factory). Example: `AdsHandler.js`:

  ```js
  export default (() => {
      let Public = {}, Private = {};

      Private.mapper = adsEditMapper;

      Public.save = function (cityService) { /* ... */ };

      return Public;
  })();
  ```

- **ES6 class with `#private` fields.** Consumer does `new ResetPasswordHandler()`. Example: `ResetPasswordHandler.js`:

  ```js
  class ResetPasswordHandler {
      #mapper;
      #toastr;
      #validator;

      constructor() { /* ... */ }
      doReset() { /* ... */ }
  }
  ```

AdminBundle's `Handler/` follows the same split and has nested subfolders `Handler/Product/`, `Handler/InfoPage/` for feature grouping. `Handler/index.js` (AdminBundle) is a side-effect aggregator.

Selection criteria: match the bundle's neighbouring handlers. New SiteBundle handlers tend to use the ES6-class `#private` shape; new AdminBundle handlers follow whatever the per-feature subfolder already uses. Do not mix paradigms within one file.

### 2.7 `Helper/`

**Unanimous shape: IIFE-Public pattern** in SiteBundle (`SearchHelper.js`):

```js
export default (() => {
    let Public = {};
    Public.someMethod = function () { /* ... */ };
    return Public;
})();
```

`app/Resources/public/js/Helper/` hosts shared helpers (`AppHelperService.js`, `CacheHelper.js`, `FormHelperService.js`) using **ES6 class with static methods only**. The shapes differ between roots — match the root.

Selection criteria when writing into `app/Resources/public/js/Helper/`: ES6 class with static methods, default-export the class (call as `AppHelperService.redirect('reload')`). When writing into `src/SiteBundle/Resources/public/js/Helper/`: IIFE-Public, default-export the instance (call as `SearchHelper.someMethod(...)`).

### 2.8 `HtmlService/` — SiteBundle only

**IIFE-Public/Private pattern, composed via `tjq.extend(true, Public, PopupCoreService())`** to mix in shared behaviour from a base service. Example: `PopupCityService.js` composes from `PopupCoreService`.

`HtmlService/index.js` is a side-effect aggregator (`import './PopupCoreService'; import './PopupCityService';`).

### 2.9 `Inputs/` — SiteBundle only

**Single observed shape: ES6 class, pre-instantiated singleton, default-export of the instance.** Single file: `DateTimePicker.js`. No `index.js`.

### 2.10 `Mapper/`

**Single observed shape: ES6 class with a `Class.instance` singleton guard, `Object.freeze` of the instance, default-export of the instance.** Pattern:

```js
class AdsEditMapper {
    static instance;

    constructor() {
        if (AdsEditMapper.instance) {
            return AdsEditMapper.instance;
        }
        // ... populate this.field = $('#selector') ...
        AdsEditMapper.instance = Object.freeze(this);
    }
}

export default new AdsEditMapper();
```

`Mapper/index.js` (SiteBundle) is a side-effect aggregator that imports a subset of the mappers (currently 4 of 14: `ReservationMapper`, `RegistrationMapper`, `LoginMapper`, `ContactMapper`). The other 10 are imported on demand by consumers (e.g. `import adsEditMapper from "../Mapper/AdsEditMapper";` inside `AdsHandler.js`).

`app/Resources/public/js/Mapper/` and `src/AdminBundle/Resources/public/js/Mapper/` follow the same singleton+freeze shape.

Selection criteria: every new mapper follows the singleton+freeze pattern. The decision is only whether to add the `import './NewMapper';` line to `Mapper/index.js` (do this when the page wants the mapper available even before its handler/service is imported) or leave it to on-demand import.

### 2.11 `Routing/` — SiteBundle only (folder)

**Single observed shape: ES6 class with default-export of the class.** Uses globals `ROUTE_NAME`, `EXTRA_PARAMS`, `SEARCH_CRITERIA`, `CATEGORY`. Single file: `AdsPageRouting.js`.

**Disambiguation:** `app/Resources/public/js/Routing.js` (file, no folder) is the project-wide FOSJsRouting bootstrap shim, NOT the same thing as SiteBundle's `Routing/` folder. The shim is `require()`'d once per bundle entry and is a framework shim — see § 5 below.

### 2.12 `Services/`

**Three coexisting shapes — heavily mixed.** All three are canonical:

- **ES6 class with `#private` fields.** Example: `LoginService.js`.
- **IIFE-Public pattern.** Example: `AdsService.js`.
- **Legacy pre-ES6 function-constructor with NO exports.** Example: `NotifyService.js` (uses `function NotifyService(){...}` and relies on the symbol being globally available after bundling). This shape is legacy; new services should not use it.

`app/Resources/public/js/Services/` has its own canonical shape: ES6 class with `#private` fields and a singleton+freeze pattern. Example: `ToastrService.js` (consumed as `import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";`).

AdminBundle's `Services/` includes a nested `Services/DataTables/` subfolder for DataTables config services and has a `Services/index.js` side-effect aggregator.

Selection criteria: prefer ES6 class with `#private` for new services in any root. Use IIFE-Public only when extending a neighbouring file that already uses it. Do NOT introduce new legacy function-constructor services.

### 2.13 `Validation/` (SiteBundle, singular) and `Validators/` (`app/` + AdminBundle, plural)

Two coexisting shapes inside SiteBundle's `Validation/`:

- **ES6 class singleton with `Object.freeze` instance, default-export of the instance.** Examples: `LoginValidator.js`, `ResetPasswordValidator.js`. The instance exposes a `.validate()` method that wires up `jquery-validation` rules.
- **IIFE-Public pattern.** Examples: `BackendValidator.js`, `AdsValidation.js`.

All variants build on the `jquery-validation` plugin (`import validate from 'jquery-validation';` is done once per bundle entry).

`app/Resources/public/js/Validators/` hosts the shared base — `BaseValidator.js` (ES6 class, singleton instance), plus `CustomMethods.js` and `ValidationRuleHelper.js`.

AdminBundle's `Validators/` follows the SiteBundle `Validation/` pattern shapes (the file naming convention is `<Feature>EditValidator.js`).

Selection criteria for new validators: match the neighbouring file's shape. If you're adding a singleton-style validator (preferred for page-bound `valid()` calls), use the ES6-class + singleton + freeze shape and import shared helpers from `app/Resources/public/js/Validators/`.

---

## 3. Wiring — `index.js` patterns

### 3.1 Bundle-entry composition

`src/SiteBundle/Resources/public/js/index.js`:

```js
require('../../../../../app/Resources/public/js/Sentry');
import validate from 'jquery-validation';

require('flexslider');
require('../../../../../app/Resources/public/js/Routing');
require('webpack-jquery-ui');
require('webpack-jquery-ui/css');
require('./theme-scripts');
require('./scripts');

import './Mapper';
import './Controller';
// ... other layers commented out (Validators, Services, Handler, Filters, HtmlService) ...
```

`src/AdminBundle/Resources/public/js/index.js`:

```js
require('../../../../../app/Resources/public/js/Sentry');
require('../../../../../app/Resources/public/js/Routing');

import '../vendors/core/core';
import '../vendors/feather-icons/feather';

import './Core';
import './Controller';
```

**The bundle entry's job:** pull in the project-wide framework shims (Sentry + Routing) from `app/Resources/public/js/`, then import each layer's `index.js` (or aggregator file) that the page needs.

### 3.2 Controller route-table dispatcher

Both `src/SiteBundle/Resources/public/js/Controller/index.js` and `src/AdminBundle/Resources/public/js/Controller/index.js` are a route-table dispatcher keyed by the global `ROUTE_NAME` (set in the rendered HTML). See § 2.2 for the canonical shape and the rule: **adding a page controller ⇒ add a route-table entry**.

### 3.3 Side-effect aggregators

These `index.js` files exist solely to ensure their members are bundled. They contain only `import './<File>';` lines, no logic:

- `src/SiteBundle/Resources/public/js/Mapper/index.js` — imports 4 of 14 mappers (the always-needed ones).
- `src/SiteBundle/Resources/public/js/Filters/index.js` — imports both filter files.
- `src/SiteBundle/Resources/public/js/HtmlService/index.js` — imports both popup services.
- `src/AdminBundle/Resources/public/js/Core/index.js` — imports `./template`.
- `src/AdminBundle/Resources/public/js/Services/index.js` — admin services aggregator.
- `src/AdminBundle/Resources/public/js/Handler/index.js` — admin handlers aggregator.

When adding a file that consumers will import directly (e.g. a new Handler that another file already references via `import AdsHandler from "../Handler/AdsHandler";`), you do NOT need to add it to the aggregator. Add it to the aggregator only when the page needs the side-effect of the file's top-level code without any consumer importing it explicitly.

### 3.4 Cross-bundle / cross-root import paths

Bundle files reaching into `app/Resources/public/js/` use relative paths. The depth depends on whether the importing file is at the layer level or at the bundle root.

- **From a SiteBundle layer file** (e.g. `src/SiteBundle/Resources/public/js/Handler/AdsHandler.js`):
  ```js
  import AppHelperService from "../../../../../../app/Resources/public/js/Helper/AppHelperService";
  import toastrService from "../../../../../../app/Resources/public/js/Services/ToastrService";
  ```
  Six `..` segments.

- **From the SiteBundle root `index.js`** (`src/SiteBundle/Resources/public/js/index.js`):
  ```js
  require('../../../../../app/Resources/public/js/Sentry');
  require('../../../../../app/Resources/public/js/Routing');
  ```
  Five `..` segments.

- **From an AdminBundle layer file** (e.g. `src/AdminBundle/Resources/public/js/Controller/index.js`):
  ```js
  import CoreController from "../../../../../../app/Resources/public/js/Controller/CoreController";
  ```
  Six `..` segments.

Match these path depths exactly when adding a new file — miscounted `..` segments are a common source of "module not found" errors at build time.

---

## 4. Webpack Encore wiring

Entry points (verbatim from `webpack.config.js`):

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

**`app/Resources/public/js/` is NOT a direct Encore entry.** Files in this root are pulled in via `require()` paths from each bundle's `index.js` (see § 3.1) or via deep imports from layer files (see § 3.4).

Encore configures `autoProvideVariables` so the following identifiers are globally available without `import`:

```js
$: 'jquery',
tjq: 'jquery',
jQuery: 'jquery',
'window.jQuery': 'jquery',
'window.$': 'jquery',
```

`tjq` is the project's jQuery alias. Existing code uses both `$` and `tjq` (see `AdsHandler.js` for both). Match the surrounding file.

**Adding new Encore entries is out of scope for routine frontend work** — the entries above are documented as-is. If a step appears to need a new entry, that's a planning concern (raise as a blocker).

---

## 5. Vendor and glue exclusions per root

Files outside the PascalCase project subdirectories are vendor / glue / framework shims. They are NOT in scope for layer-pattern advice.

### SiteBundle (`src/SiteBundle/Resources/public/js/`)

Out-of-scope vendor / glue files in this directory:

- Vendor: `bootstrap.js`, `bootstrap.min.js`, `gmap3.infobox.js`, `gmap3.js`, `gmap3.min.js`, `ie8.js`, `isotope.pkgd.js`, `isotope.pkgd.min.js`, `jquery.cookie.js`, `jquery.fitvids.js`, `jquery.fitvids.min.js`, `jquery.noconflict.js`, `jquery.placeholder.js`, `jquery.stellar.min.js`, `modernizr.2.7.1.min.js`, `pace.min.js`, `waypoints.js`, `waypoints.min.js`.
- Bundle-level glue: `globals.js`, `scripts.js`, `theme-scripts.js`, `page-loading.js`, `MobileDetection.js`, `calendar.js`.

In scope at the root: `index.js` (the bundle entry — see § 3.1).

### AdminBundle (`src/AdminBundle/Resources/public/js/`)

No `*.min.js` vendor files at this root. The only root-level file is `index.js`, which is in scope (same role as SiteBundle's entry — see § 3.1).

### `app/Resources/public/js/` (project-wide)

No vendor files in the subfolders. The two root files `Routing.js` and `Sentry.js` are **framework shims** — `Routing.js` initialises FOSJsRouting, `Sentry.js` initialises the Sentry SDK. Both are in scope to mention as "framework shims required at every bundle entry's top", but **out of scope to prescribe patterns for** — they have no peers and no layer convention. Do not add new framework-shim files to this root unless a plan step explicitly calls for it.

### LogBundle (`src/LogBundle/Resources/public/js/`)

Does not exist. The skill addresses LogBundle only as forward-compatibility — when LogBundle grows JS, it will adopt the same root + layer conventions described here.

---

## 6. Mandatory rebuild commands

After ANY change to a JS file in scope, run the development build inside the container:

```
docker exec smestaj-app npm run dev
```

For production-mode verification (only when the plan step explicitly requests it):

```
docker exec smestaj-app npm run build
```

If the step adds, changes, or removes a route that any JS file calls via `Routing.generate(...)`:

```
docker exec smestaj-app php bin/console fos:js-routing:dump
```

If the step adds, changes, or removes a translation that any JS file calls via `Translator.trans(...)`:

```
docker exec smestaj-app php bin/console bazinga:js-translation:dump
```

Bare `npm`, `npx`, `node`, `php`, `composer` invocations on the host are forbidden — every command runs through `docker exec smestaj-app`.

---

## 7. Pre-completion checklist

Before ticking the IMPLEMENTATION_PLAN.md step, every applicable item below must be true:

- [ ] The new file lives in the correct root (`app/Resources/public/js/` vs `src/<Bundle>/Resources/public/js/`).
- [ ] The new file is in the correct PascalCase layer subdirectory for its role.
- [ ] The file's shape matches one of the documented shapes for that layer (see § 2). For layers with coexisting shapes, the choice matches the neighbouring file in the same bundle.
- [ ] If the file is a new page controller, `Controller/index.js` has a new route-table entry keyed by its `ROUTE_NAME`.
- [ ] If the file relies on a side-effect aggregator (`Mapper/index.js`, `Filters/index.js`, `HtmlService/index.js`, AdminBundle `Core/index.js`, AdminBundle `Services/index.js`, AdminBundle `Handler/index.js`), the aggregator imports it.
- [ ] Cross-root imports use the correct relative depth (six `..` from a layer file; five from a bundle root `index.js`).
- [ ] `docker exec smestaj-app npm run dev` builds cleanly.
- [ ] If routes changed: `docker exec smestaj-app php bin/console fos:js-routing:dump` was run.
- [ ] If translations changed: `docker exec smestaj-app php bin/console bazinga:js-translation:dump` was run.
- [ ] No `console.log` left in committed code.
- [ ] No new vendor / minified file was added (vendor is out of scope).
- [ ] No new Encore entry was added (out of scope).

---

## 8. When to load this skill

Load with `skill({ name: "frontend-js-pattern" })` **before** writing or editing ANY file under:

- `app/Resources/public/js/`
- `src/SiteBundle/Resources/public/js/`
- `src/AdminBundle/Resources/public/js/`
- `src/LogBundle/Resources/public/js/` (when LogBundle grows JS)

If you started a JS change without loading the skill, stop, load it now, and bring the already-written code into compliance with the documented shape for its layer before continuing.
