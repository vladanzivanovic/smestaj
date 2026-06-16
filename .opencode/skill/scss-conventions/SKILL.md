---
name: scss-conventions
description: Project-wide SCSS syntax and style conventions for smestaj. Use whenever the task touches a *.scss file under app/Resources/public/scss/, src/SiteBundle/Resources/public/sass/, or src/AdminBundle/Resources/public/scss/ — including new partials, new module styles, new page styles, edits to existing partials, or a retrofit of legacy SCSS. Enforces two mandatory project rules on every non-vendor non-theme SCSS file: nesting depth never exceeds 3 levels, and `@use` is the only import mechanism (`@import` is forbidden in new code). Companion to the `frontend-css-pattern` skill — `frontend-css-pattern` decides WHERE SCSS goes (file-path routing, Encore entries, layout); `scss-conventions` decides HOW SCSS is written. Load both for any SCSS task. Triggers on phrases like "write SCSS", "edit SCSS", "new partial", "new module style", "new page style", "SCSS conventions", "@use vs @import", "flatten nesting", "scss-conventions", plus any task whose target path matches the three SCSS roots above.
---

# SCSS Conventions — smestaj

Every SCSS file in the project follows the same syntactic conventions, no exceptions. Two of them are MANDATORY rules with project-wide enforcement: nesting depth never exceeds 3 levels, and `@use` is the only import mechanism (`@import` is forbidden in new code). The remaining sections are best-practice references for variables, the parent selector `&`, partial naming, mixins, functions, and control directives. Pair this skill with `frontend-css-pattern` for any SCSS task — that one decides WHERE SCSS goes; this one decides HOW it is written.

Announce on load: `[SKILL] scss-conventions loaded`.

---

## 0. Mandatory rules — non-negotiable

Two rules apply to every SCSS file under `app/Resources/public/scss/`, `src/SiteBundle/Resources/public/sass/`, or `src/AdminBundle/Resources/public/scss/`:

1. **Mandatory: nesting depth NEVER exceeds 3 levels.** Counting starts at 1 for the top-level selector; each nested block adds 1. A selector at depth 4 or deeper is a hard violation. See § 2 for the canonical pattern.
2. **Mandatory: `@use` is the only import mechanism.** `@import` is forbidden in new files. See § 5 for migration patterns and namespacing.

**Two exemptions** — and only two — apply:

- **Theme files**: any SCSS file under `src/AdminBundle/Resources/public/scss/dark/**`, `src/AdminBundle/Resources/public/scss/light/**`, or `src/AdminBundle/Resources/public/scss/demo_1/**`. These directories contain AdminBundle's theme-variant overrides (vendored from a Bootstrap admin template); they keep their existing structure.
- **Vendor files**: any SCSS file located OUTSIDE the three project SCSS roots — typically third-party packages imported via `~package-name/...` or shipped under `src/<Bundle>/Resources/public/components/` — OR an in-tree SCSS file that is a verbatim copy / thin override of a third-party library. No in-tree vendor SCSS file currently exists; this clause is reserved for future use.

**Existing pre-mandate files** (e.g. `_index.scss`, `style.scss`, the other entries in `Pages/`, `Moduls/`, and `common/`) are grandfathered as-is — they are NOT retouched solely to enforce the mandate. The rules apply going forward to (a) every NEW file and (b) any explicit retrofit target named in a plan.

---

## 1. Variables

Use the `$` symbol to store reusable values like colours, font sizes, and spacing.

```scss
$primary-color: #333;
```

Prefer declaring shared tokens in a partial (e.g. `_variables.scss`) and accessing them via `@use` (see § 5). Hard-coded literals are not forbidden, but a token in the right partial is preferred when the value is used more than once.

---

## 2. Nesting

Nested selectors mirror the HTML hierarchy. **Mandatory: never exceed 3 levels of nesting** (§ 0 rule 1). Over-nesting produces overly specific selectors that are hard to override and tightly coupled to the markup tree.

```scss
// OK — depth 3
.card {
  .card-header {
    .card-title { font-weight: 700; }
  }
}

// WRONG — depth 4 (a hard violation)
.card {
  .card-header {
    .card-title {
      i { color: red; }
    }
  }
}

// FIX — promote the depth-4 child to a sibling of its parent, with the parent's class spelled out
.card {
  .card-header {
    .card-title { font-weight: 700; }

    .card-title i { color: red; }
  }
}
```

The promotion pattern is the canonical, visually-neutral flatten: the compiled selector chain is identical, specificity is identical, and the rule's logical grouping is preserved. Do not switch to a `&` placeholder, an `@extend`, or a separately-extracted top-level selector unless a plan step explicitly asks for it.

---

## 3. Parent selector `&`

Use `&` to reference the parent selector. Especially useful for pseudo-classes (`:hover`, `:focus`, `:active`) and BEM-style modifiers (`&--large`, `&--disabled`).

```scss
a {
  color: blue;

  &:hover { color: red; }
}

.button {
  padding: 8px 16px;

  &--large { padding: 12px 24px; }
  &--disabled { opacity: 0.5; pointer-events: none; }
}
```

`&` does NOT add to nesting depth in the way a fresh descendant selector does — `&:hover` inside `.a` resolves to `.a:hover`, a single compound selector at the same depth. Use it freely within the 3-level cap.

---

## 4. Partials

SCSS files intended for import (rather than direct compilation) are **partials** and have filenames starting with an underscore — for example `_variables.scss`, `_mixin.scss`, `_footer.scss`. The underscore tells Sass not to emit the file as a standalone `.css` output.

Where partials go is governed by the `frontend-css-pattern` skill (which root, which subfolder). The naming convention is governed here: **every SCSS file that is imported by another SCSS file MUST start with an underscore**. The only exception is a file that is a direct Webpack Encore entry — and even then the project chooses both conventions case-by-case (e.g. `src/AdminBundle/Resources/public/scss/style.scss` is an Encore entry without an underscore, while every file under `src/SiteBundle/Resources/public/sass/Pages/` is an entry with an underscore).

---

## 5. `@use` over `@import`

**Mandatory: `@use` is the only import mechanism in new code** (§ 0 rule 2). `@import` is forbidden because it makes every variable, mixin, and function in the imported file globally visible, which causes collisions and silent overrides at scale. `@use` brings the imported file into a namespaced scope.

```scss
// WRONG — @import (forbidden in new code)
@import 'variables';
.button { color: $primary-color; }

// RIGHT — @use with default namespace (the filename without leading underscore / extension)
@use 'variables';
.button { color: variables.$primary-color; }

// RIGHT — @use with an explicit namespace alias
@use 'variables' as v;
.button { color: v.$primary-color; }
```

When migrating a previously-`@import`ed file:

1. Replace `@import 'foo';` with `@use 'foo';` (or `@use 'foo' as f;` for a shorter alias).
2. Update every previously-global symbol (`$colour`, `@include mixin-name(...)`, `function-name(...)`) to its namespaced form (`foo.$colour`, `@include foo.mixin-name(...)`, `foo.function-name(...)`).
3. Rebuild and verify the compiled CSS is identical.

Note: the project's pre-mandate files (e.g. `src/SiteBundle/Resources/public/sass/_index.scss`, `src/AdminBundle/Resources/public/scss/style.scss`) currently use `@import` heavily. They are grandfathered per § 0; new code MUST use `@use`.

---

## 6. Mixins

Use `@mixin` + `@include` to reuse entire blocks of CSS declarations.

```scss
@mixin clearfix {
  &::after {
    content: "";
    display: table;
    clear: both;
  }
}

.container {
  @include clearfix;
}
```

Mixins may take parameters and default values, and can pass through `@content` blocks. Keep mixin definitions in dedicated `_mixin*.scss` or `_mixins/*.scss` partials and import them with `@use` (§ 5).

---

## 7. Functions

Use `@function` + `@return` to compute and return a single value (e.g. a colour, a font size, a margin).

```scss
@function rem($pixels, $base: 16) {
  @return ($pixels / $base) * 1rem;
}

.title {
  font-size: rem(24);
}
```

Functions return ONE value; mixins emit declaration blocks. If you need multiple values or a whole block, use a mixin.

---

## 8. Control directives

SCSS supports programmatic logic via `@if`, `@else`, `@for`, `@each`, and `@while`. These are documented here as **general SCSS reference** — the project does not currently use them to generate utility-class sets or themes, and this skill does NOT recommend introducing such a generator.

```scss
@if $debug == true { body { outline: 1px solid red; } }

@for $i from 1 through 3 { .col-#{$i} { width: ($i * 25%); } }

@each $name, $colour in (primary: #333, accent: #f55d3e) {
  .badge--#{$name} { background: $colour; }
}
```

Use these only when a plan step explicitly calls for them. Do not refactor existing static rules into generated ones as an incidental "cleanup".

---

## 9. Webpack Encore rebuild — frontend agent reminder

Any SCSS change requires a Webpack Encore rebuild before the change is visible in the browser. **The frontend agent — not this skill — runs the build.** This section is a reminder of the canonical commands; the skill itself never executes them.

| Purpose | Command |
|---|---|
| Dev rebuild | `docker exec smestaj-app npm run dev` |
| Production rebuild | `docker exec smestaj-app npm run build` |

Both commands run inside the `smestaj-app` container per `AGENTS.md`. Bare `npm`, `npx`, or `node` on the host is forbidden. Encore rebuilds CSS as part of `npm run dev` — there is no SCSS-only sub-command.

---

## 10. Pre-completion checklist

Before ticking a SCSS step in IMPLEMENTATION_PLAN.md, every applicable item below must be true:

- [ ] The file is in a project SCSS root (`app/Resources/public/scss/`, `src/SiteBundle/Resources/public/sass/`, `src/AdminBundle/Resources/public/scss/`) — `frontend-css-pattern` confirmed the exact location.
- [ ] No selector in the file is nested deeper than 3 levels (§ 0 rule 1, § 2).
- [ ] The file contains zero `@import` statements; every import uses `@use` with explicit namespacing (§ 0 rule 2, § 5). EXCEPTION: the file falls under the vendor/theme exemption (§ 0).
- [ ] If the file is meant for import only (a partial), its filename starts with an underscore (§ 4).
- [ ] Tokens accessed from a `@use`d partial use the namespaced form (`variables.$primary-color`, not `$primary-color`).
- [ ] The frontend agent ran `docker exec smestaj-app npm run dev` and the build completed cleanly with no new warnings or errors (§ 9).
- [ ] No bare `npm` / `node` / `php` command was executed on the host.
- [ ] No change to `webpack.config.js`, the Encore entries, or the SASS loader configuration (those are out of scope here).

---

## 11. When to load this skill

Load with `skill({ name: "scss-conventions" })` **before** writing or editing ANY file under:

- `app/Resources/public/scss/`
- `src/SiteBundle/Resources/public/sass/`
- `src/AdminBundle/Resources/public/scss/`

Pair it with `frontend-css-pattern` — that skill decides which root and subfolder the file belongs in; this skill decides how the SCSS inside the file is written. If you started a SCSS change without loading the skill, stop, load it now, and bring the already-written code into compliance with §§ 0–8 before continuing.
