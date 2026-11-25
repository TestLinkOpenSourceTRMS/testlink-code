# Wunderbaum tree theme: configure, activate, deactivate

Optional CSS theme for the left-hand tree navigators (test project, test plan,
build, requirement spec trees). Controlled by a single config variable,
`$tlCfg->tree_css`, following the same convention as `$tlCfg->custom_css`.

## What it does

When set, TestLink `@import`s the given CSS file into every page that renders a
tree, **after** the Wunderbaum library css, so the theme's rules win. The theme
is scoped to `#tree_div` (the element the trees are initialized on), so it can
only restyle the trees — never the rest of the UI.

Because the file is `@import`ed, the theme's relative `url()` paths resolve
against the theme file's own folder. Keep the file inside the theme css folder
or the sprites/paths will break.

## Configuration

The variable is defined in `config.inc.php` (default `null` = disabled) right
below `$tlCfg->custom_css`:

```php
// name of your tree css file, place it in the same folder as the standard TL css.
// null or '' => do not use
$tlCfg->tree_css = null;
```

**Do not edit `config.inc.php`.** As with all TestLink config, overrides go in
`custom_config.inc.php` (included by `config.inc.php` near its end, so later
assignments win).

### The two ready-made themes

| Theme file (in `gui/themes/default/css/`) | Look |
|---|---|
| `tl_wunderbaum.css` | Generic restyle sample (Trebuchet MS, 13px, 20px rows, blue palette). Fully commented: a starting point for your own custom theme. |
| `tl_wunderbaum_extjs.css` | Classic ExtJS 3.4.0 TreePanel look: original `folder.gif` / `folder-open.gif` / `leaf.gif` / `loading.gif` sprites, solid arrow expanders, 11px arial, 18px rows, `#c3daf9` selection bar with `#3399bb` border. |

Both files are self-documenting — every variable they override is commented in
place.

## Activate

1. **Copy the standard config** (one-time, if `custom_config.inc.php` does not
   exist yet):

   ```
   copy custom_config.inc.php.example custom_config.inc.php
   ```

   (On Unix: `cp custom_config.inc.php.example custom_config.inc.php`.)

2. **Add one line** to `custom_config.inc.php` — filename only, no path. The
   app prefixes `TL_THEME_CSS_DIR` (`gui/themes/default/css/`) automatically:

   ```php
   // classic ExtJS look (original TestLink tree sprites)
   $tlCfg->tree_css = 'tl_wunderbaum_extjs.css';

   // ...or the generic sample theme / your own file:
   // $tlCfg->tree_css = 'tl_wunderbaum.css';
   ```

   Place the line anywhere in the file, e.g. directly above the
   `// ----- END OF FILE -----` marker (a plain comment, not special to PHP).

3. **No restart needed.** The value is read on every request. Hard-refresh the
   browser (Ctrl+F5) to clear the cached css; that is all.

### What you should see

- Classic ExtJS theme: folder icons on projects/suites (open folder when
  expanded), document icon on test cases, triangle arrow expanders, 11px arial,
  18px rows, light-blue selection bar.
- Sample theme: Trebuchet MS 13px, 20px rows, darker blue selection.
- Everything else on the page is unchanged (theme is scoped to `#tree_div`).

## Deactivate

Set the variable back to `null` (or remove the line) in `custom_config.inc.php`:

```php
$tlCfg->tree_css = null;
```

or simply comment it out, then hard-refresh the browser. The default library
styling returns immediately. No code changes, no deploy.

## Preview without touching config

The validation harness simulates the full template wiring (real
`inc_wunderbaum.tpl` + real `wunderbaum.umd.js` + `wbTree.js`):

```
php -S 127.0.0.1:8740
```

| URL | Mode |
|---|---|
| `http://127.0.0.1:8740/_harness/harness_tree_theme.php?theme=0` | default (no theme) |
| `...?theme=1` | `tl_wunderbaum.css` sample theme |
| `...?theme=2` | `tl_wunderbaum_extjs.css` classic ExtJS theme |

The page prints the computed `--wb-*` custom properties and reports
`treeInitialized` / `errors` so you can confirm the css is actually being
applied before pointing production at it.

## How it works (internals)

1. `config.inc.php` — `$tlCfg->tree_css = null;` (default, overridable).
2. `lib/functions/tlsmarty.inc.php` — when the value is non-null and non-empty,
   assigns to Smarty:
   - `use_tree_css` = 1
   - `tree_css` = `$basehref . TL_THEME_CSS_DIR . $tlCfg->tree_css`
   (identical pattern to `use_custom_css` / `custom_css`).
3. `gui/templates/tl-classic/inc_wunderbaum.tpl` — when `use_tree_css` is set,
   emits, **after** the `wunderbaum.css` link:

   ```html
   <style media="all" type="text/css">@import "{url}";</style>
   ```

   CSS cascade: later-in-head wins, so the theme overrides the library's
   `:root`/container defaults.

### Why `#tree_div`, and why it is safe

Wunderbaum 0.15 exposes every color/size as a CSS custom property (`--wb-*`)
on the tree **container** element (the `<div>` passed as `element:`), not on
`:root`. All navigator templates create the tree inside `#tree_div`, so a
theme scoped to `#tree_div` restyles exactly the trees. Hover/active/dimmed
variants are derived by the browser from the base colors via `color-mix()`, so
a theme only needs to set the base variables.

## Writing your own theme

1. Copy `gui/themes/default/css/tl_wunderbaum.css` to your own filename in the
   same folder (keeps relative paths working).
2. Edit the commented `--wb-*` variables.
3. Point `custom_config.inc.php` at your filename.
4. Preview via the harness (`?theme=2` is the pattern — or just load your file
   from the same server and inspect `#tree_div` computed styles).

## Troubleshooting

| Symptom | Cause / fix |
|---|---|
| Default look, theme ignored | Line missing, misspelled filename, or value not `null`/`''`. Check `custom_config.inc.php` and confirm no later assignment in `config.inc.php` clobbers it (the `custom_config.inc.php` include happens near the end of `config.inc.php`; anything after that include wins). |
| Right look but no icons | Theme file moved out of `gui/themes/default/css/` — the sprite `url()` paths are relative to the file. Keep it there (or rewrite the paths). |
| Old style after change | Browser cache — hard refresh (Ctrl+F5). |
| Affects more than the tree | Your file is not scoped to `#tree_div`. Both shipped themes are; keep that selector when writing your own. |
| Want the sample theme instead | `$tlCfg->tree_css = 'tl_wunderbaum.css';` — one of the two shipped files, no copy needed. |

## Validation reference

`docs/development/wunderbaum-tree-validation.md` section 9 covers the live
browser harness validation (computed-style table, default vs themed) and the
design rationale for the `@import`-after-library-css ordering.
