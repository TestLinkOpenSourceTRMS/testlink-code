# Work log: Windows/Explorer tree theme

**Date:** 2026-11-25
**Scope:** Recreate a "Windows / Explorer" look for the left-hand Wunderbaum tree
navigators (test project, test plan, build, requirement spec trees) and make it
the active theme.
**Result:** Shipped and verified end-to-end in a real browser. Icons now render
yellow folders, gray document/chevron glyphs, light-gray frame and a light-blue
selection bar, matching the requested target.

---

## 1. What was requested

Turn the current tree (Image 1) into the Windows/Explorer style (Image 2):

| Element | Target look (Image 2) |
|---|---|
| Closed folder (suite / project) | yellow folder icon |
| Open folder (expanded) | brighter yellow folder icon |
| Test case (leaf) | gray document icon |
| Expander (collapsed / expanded) | gray `>` / `v` chevron |
| Selected row | light-blue bar |
| Frame | light-gray 1px border |

## 2. How the tree is themed (existing mechanism)

TestLink already ships a theming hook for the trees. The tree is a
**Wunderbaum 0.15** instance created **on `#tree_div`** (see
`gui/javascript/wbTree.js`, `wlTreeInit`). The wiring:

1. `config.inc.php` — `$tlCfg->tree_css = null;` (default = disabled,
   overridable).
2. `custom_config.inc.php` — point `$tlCfg->tree_css` at a CSS filename that
   lives in `gui/themes/default/css/`. This file is included *near the end* of
   `config.inc.php`, so it wins over the default.
3. `lib/functions/tlsmarty.inc.php` — when the value is non-empty, assigns to
   Smarty `use_tree_css = 1` and
   `tree_css = $basehref . TL_THEME_CSS_DIR . $tlCfg->tree_css`
   (`TL_THEME_CSS_DIR` = `gui/themes/default/css/`).
4. `gui/templates/tl-classic/inc_wunderbaum.tpl` — when `use_tree_css` is set,
   emits, **after** the library css:
   ```html
   <style media="all" type="text/css">@import "{url}?v=1";</style>
   ```
   CSS cascade: later-in-head wins, so the theme overrides the library's
   `--wb-*` custom-property defaults.

Every color/size that matters is a `--wb-*` CSS custom property on the tree
container, so a theme mostly just sets base values (hover/active/dimmed
variants are derived in the browser via `color-mix()`).

## 3. What was changed

### 3.1 New theme — `gui/themes/default/css/tl_wunderbaum_windows.css` (new file)

A self-contained theme (no sprite images) that re-colors the Font Awesome 4.5
glyphs `wbTree.js` already puts on each node, so it stays portable. Key values:

| Target | Selector | Value |
|---|---|---|
| Base font | `#tree_div` | `'Segoe UI', Tahoma, Verdana, sans-serif`, 12px |
| Row geometry | `#tree_div` | `--wb-row-outer-height: 20px`, 18px icon slot, 3px col padding |
| Frame | `#tree_div` | `--wb-border-color: #d9d9d9`, `border-width: 1px` |
| Selection bar | `#tree_div` | `--wb-active-color: #cce8ff` (+ hover / border variants) |
| Closed folder | `#tree_div .wb-node i.wb-icon.fa-folder` (+ `.fa-folder-o`) | `color: #f7c64b` |
| Open folder | `#tree_div .wb-node i.wb-icon.fa-folder-open` | `color: #ffd966` |
| Test case | `#tree_div .wb-node i.wb-icon.fa-file-o` | `color: #8a8a8a` |
| Expander | `#tree_div .wb-node i.wb-expander.fa-caret-right/down` | `color: #8a8a8a`, 13px |
| Hover expander | `#tree_div:hover div.wb-row i.wb-expander` | `color: #4d4d4d` |

### 3.2 Activate — `custom_config.inc.php`

```php
$tlCfg->tree_css = 'tl_wunderbaum_windows.css';
```

(replaces the previous `tl_wunderbaum.css`). A comment lists the three
available themes so switching is one line.

### 3.3 Cache-bust — `gui/templates/tl-classic/inc_wunderbaum.tpl`

Added `?v=` to the theme `@import` so a browser that already cached an older
`tree_css` value re-fetches it:

```html
<style media="all" type="text/css">@import "{$tree_css}?v={$tree_css_v|default:1}";</style>
```

Bump the stamp whenever `$tlCfg->tree_css` changes. (The shipped sprite themes
never needed this because their look came from gif files; a pure-color theme
needs it or the browser keeps serving the previously-cached sheet.)

### 3.4 Docs

- `tl_wunderbaum_windows.css` header — a note explaining the required selector
  form (see §4).
- `docs/development/wunderbaum-tree-theme.md` — new
  **"Gotcha: how to target node icons / expanders"** section under
  "Writing your own theme".

## 4. Root cause of "icons have not changed"

**Symptom:** after the theme was enabled, the frame/row colors changed but all
icons (folders, file, chevrons) stayed black — i.e. the icon `color` rules did
nothing.

**Investigation (real Chrome via CDP, `document.styleSheets` + `getComputedStyle`):**

1. The theme file was being served (HTTP 200, `text/css`).
2. The theme's `#tree_div { --wb-* }` custom properties **were** computed on
   `#tree_div` (border `#d9d9d9`, selection `#cce8ff`, Segoe, 20px rows) — so
   the `@import` loaded and the container block applied.
3. But `getComputedStyle(i.fa-folder).color` was `rgb(0,0,0)` and no rule from
   the theme file matched the icon nodes.
4. Inspecting the DOM: `#tree_div` **itself** carries the `wunderbaum` class
   (`document.getElementById('tree_div').classList.contains('wunderbaum') ===
   true`), and there is **no `.wunderbaum` descendant** inside it.
5. The icon selectors were written as
   `#tree_div .wunderbaum .wb-node i.wb-icon.fa-folder` — a descendant
   `.wunderbaum` — which matched **0 elements**
   (`document.querySelectorAll('#tree_div .wunderbaum .wb-node ...').length === 0`).

**Why the container block worked but the icon rules didn't:** the
`#tree_div { --wb-* }` block targets `#tree_div` directly (specificity 1-0-0,
same as the library's `div.wunderbaum`), so it wins. The icon rules needed the
extra `.wunderbaum` class, but that class is on `#tree_div` itself, not on a
child — so `#tree_div .wunderbaum` was an empty match and the whole rule was
silently dropped.

**Fix:** anchor icon/expander rules on `#tree_div .wb-node ...` (and
`#tree_div div.wb-row ...`), dropping the redundant `.wunderbaum` descendant:

```css
/*  CORRECT — matches the node:  */
#tree_div .wb-node i.wb-icon.fa-folder   { color: #f7c64b; }
#tree_div .wb-node i.wb-expander.fa-caret-down { color: #8a8a8a; }

/*  WRONG — matches nothing, silently dropped:  */
#tree_div .wunderbaum .wb-node i.wb-icon.fa-folder { color: #f7c64b; }
```

**Note on the other shipped themes:** `tl_wunderbaum.css` (sample) only sets
custom properties, so it never hit this. `tl_wunderbaum_extjs.css` uses the
same `.wunderbaum` descendant form but applies its look via `background-image`
sprites on the element; its `color: transparent` + background swap still works
regardless, which is why it looked fine while a pure-`color` theme broke.

## 5. Verification

Built a throwaway preview harness (`_harness/harness_windows_theme.php`, since
removed) that renders the **real** `inc_wunderbaum.tpl` through Smarty with
`tree_css` pointed at `tl_wunderbaum_windows.css`, boots the real
`wunderbaum.umd.js` + `wbTree.js` on an eager tree mirroring the screenshot
(Auchan → PT/02/SCM → QQA → TS-100 → TSWA → QA-1:eee), and reads the computed
styles.

Final `getComputedStyle` results in the live browser:

| Element | Class | Computed color |
|---|---|---|
| Closed folder | `fa-folder` | `rgb(247,198,75)` = `#f7c64b` ✓ |
| Open folders | `fa-folder-open` | `rgb(255,217,102)` = `#ffd966` ✓ |
| Test case | `fa-file-o` | `#8a8a8a` ✓ |
| Chevron (down) | `fa-caret-down` | `#8a8a8a` ✓ |
| Frame | `#tree_div` | 1px solid light-gray ✓ |
| Selection | `.wb-active` | light-blue `#cce8ff` ✓ |

All icon rules now match and paint; no JS errors.

## 6. Files touched

| File | Change |
|---|---|
| `gui/themes/default/css/tl_wunderbaum_windows.css` | **new** — Windows/Explorer theme (fixed selectors) |
| `custom_config.inc.php` | set `$tlCfg->tree_css = 'tl_wunderbaum_windows.css';` |
| `gui/templates/tl-classic/inc_wunderbaum.tpl` | added `?v=` cache-bust to the theme `@import` |
| `docs/development/wunderbaum-tree-theme.md` | documented the icon-selector gotcha |
| `docs/development/wunderbaum-tree-windows-theme-work.md` | this file |

## 7. To enable / disable / switch

- **Enable:** `$tlCfg->tree_css = 'tl_wunderbaum_windows.css';` in
  `custom_config.inc.php`, then hard-refresh (Ctrl+F5).
- **Disable:** set it back to `null;` (or comment out the line).
- **Switch themes:** change the filename to one of
  `tl_wunderbaum_extjs.css` / `tl_wunderbaum.css` / a new file of your own.
  Bump the `?v=` stamp in `inc_wunderbaum.tpl` when changing files so the
  browser cache is invalidated.

No restart needed — the value is read on every request.
