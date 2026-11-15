# Wunderbaum 0.15 Tree Migration - Validation Details

Branch: `testlink-2026-Wunderbaum-tree`
Date: 2026-11-13
Status: all four validation areas green; production fix committed.

> **2026-11-15 addendum — the tree did NOT render on the real pages.**
> The callback/harness validation above was green, but the actual navigator
> pages showed an empty `#tree_div`. Root cause was in the **template wiring**,
> which none of the harnesses exercised (they load the JS via plain
> `<script src>` and bypass Smarty). See §8 for the full write-up and fix.

## 1. Scope

Replacement of the four ExtJS tree scripts with a single Wunderbaum 0.15-based
script:

| Old ExtJS file | Behavior preserved in `wbTree.js` |
|---|---|
| `execTree.js` | eager tree (children as JSON in `treeCfg.children`) |
| `execTreeWithMenu.js` | eager tree, execution navigator |
| `treebyloader.js` | lazy tree (children fetched on demand) + right-click context menu |
| `tcTree.js` | older variant (no longer included by any template) |

Production deliverables:

- `gui/javascript/wbTree.js`
- `gui/templates/tl-classic/inc_wunderbaum.tpl`

## 2. Commit history (this branch)

```
14d8b0ebe DnD: fix dragStart reading absent e.sourceNode (killed every drag)
3aa12383f DnD: fix region-set gate, leaf handling, and lazy load-then-move
b235ecc72 wbTree: restore state as ExtJS path strings; re-add lazy context menu; DnD append-over-parent
cfc752fd4 DnD: allow append-only drop on the project row (match old ExtJS root)
a7c285b0d Fix wbTree.js: lazy-tree restore regression + DnD drop handler
b7c80a80f Fix wbTree.js node lookup and collapseAll for Wunderbaum 0.15
8fc308296 Replace ExtJS tree navigators with Wunderbaum 0.15 (wbTree.js)
```

## 3. The key finding: real 0.15 DnD event shapes

Verified in `node_modules/wunderbaum/dist/wunderbaum.umd.js` (v0.15.0):

- `node._callEvent(type, extra)` (line 3436) spreads `{ node: this, typeInfo }`
  into the event, then delegates to `tree._callEvent`.
- `tree._callEvent(type, extra)` (line 6979) invokes the handler as
  `func.call(this, extend({type, tree, util}, extra))` — so inside a handler
  `this` is the **tree**, not the node. The node is always `e.node`.

| event | `e.node` | `e.sourceNode` | other fields |
|---|---|---|---|
| `dnd.dragStart` (umd.js:2407) | srcNode | **absent** | `event` |
| `dnd.dragEnter` (umd.js:2517) | targetNode | srcNode | `event` (NO region/drop mode) |
| `dnd.drop` (umd.js:2586) | targetNode | srcNode | `event, region, suggestedDropMode, suggestedDropEffect, sourceNodeData, dataTransfer` |

`dragEnter` must return the **set** of allowed regions (or `false`); the
region is only known later in dragover/drop. The library does NOT move the
node itself — the `drop` handler must call `moveTo()`.

### The dragStart bug (commit 14d8b0ebe)

`dnd.dragStart` has no `e.sourceNode`. The handler read
`e.sourceNode.data.draggable`, which throws a TypeError on **every** drag and
silently disabled DnD on real pages. Fixed to read `e.node`.

Root cause of the false "validated" status: the harness's `__fire` helper
hand-built the dragStart event and included a spurious `sourceNode` field the
real library never sends. The harness now invokes handlers through the real
`node._callEvent()` dispatch, so event shapes and `this` binding are exactly
production and cannot drift.

## 4. Validation results (live browser harnesses, php -S 127.0.0.1:8737)

All DnD checks are driven through the real `node._callEvent` dispatch with
faithful 0.15 event objects.

| Area | Harness | Result |
|---|---|---|
| Eager state machine (expand/collapse path strings, localStorage format, first-run seed, restoreState, collapseAll) | `eager.html` | green (prior runs) |
| Lazy restore regression fix + state machinery | `lazy.html` + `router.php` | green (prior runs) |
| Context menu (testsuite/testcase rows: name + API ID, close on click/scroll/Esc, default menu suppressed) | inline checks | green |
| Eager DnD | `dnd.html` + `dnd_driver.js` | **25/25** |
| Lazy DnD | `dnd2.html` + `dnd2_driver.js` + `router.php` | **23/23** |

### Eager DnD contract (25 checks)

- DnD attaches when `enableDD='1'` (Smarty renders PHP `true` as string
  `'1'`; raw-truthy check `!!treeCfg.enableDD`, matching old ExtJS).
- `dragStart`: root not draggable (`draggable:false`), TC draggable.
- `dragEnter` guards: before root rejected; append to root allowed as
  container-level `['over']`; suite rows get `['over','before','after']`;
  TC rows (leaf) get `['before','after']` only — leaf-ness is the PHP
  `leaf` flag (`node.data.leaf`), NOT `hasChildren()`, so an EMPTY loaded
  suite still accepts append (matches old `TreeNode.isLeaf()`).
- Cross-parent append → `changeParent`
  `{doAction:'changeParent', nodeid, oldparentid, newparentid, top_or_bottom:'bottom'}`.
- Same-parent before/after → `doReorder`
  `{doAction:'doReorder', nodeid, oldparentid, newparentid, nodelist, nodeorder}`;
  `nodelist` is the NEW sibling order; `nodeorder` is the 0-based position
  among new siblings.
- 3-child top-level sibling reorder allowed and persisted (top-level guard is
  `newParent.isRootNode()` — only drops at the invisible system-root level
  are no-ops).
- before/after the project/root row itself → no-op (no move, no AJAX).
- Self-drop → no-op.
- Empty suite append works; append-already-last persists a `doReorder`
  (faithful: old ExtJS `TreeNode.appendChild` fired the node `move` event for
  any node with an oldParent, including same-parent appends).

### Lazy DnD contract (23 checks)

- `forbidden_parent` gating (real map from `lib/ajax/gettprojectnodes.php`:
  `testproject→none`, `testcase→testproject`, `testsuite→none`):
  - TC → project blocked (dragEnter returns `['over']` only on a suite, so
    the forbidden before/after can never be generated; library
    `_calcDropRegion` forces `over` for a size-1 set).
  - TC before a top-level suite blocked (effective parent = project).
  - suite → project allowed.
- Top-level same-parent reorder does NOT trigger a lazy load of the dragged
  (still-unloaded) suite.
- before project row → no-op.
- **Load-then-move** (old ExtJS `processDrop` parity): dropping a TC
  `over` an UNLOADED lazy suite is legal; the handler loads the target
  (`setExpanded`) first, then `moveTo()` — a direct `moveTo()` would corrupt
  the lazy child list (0.15 clobbers it with just the moved node). Verified:
  unloaded 600 keeps its real child 700 and gains the moved 501, no
  corruption; `preventLazyParents: false` (default `true` would block this
  legal drop).
- `changeParent`/`doReorder` payloads identical to eager contract.

### State machinery details

- Expansion state stored per `cookiePrefix + 'tree_div'` in localStorage as
  an array of ExtJS-style path strings (`'/1/42'` = leading separator +
  slash-joined keys, identical to old `node.getPath()`).
- `restoreState()` expands each saved path shallowest-first (load + expand
  with `noEvents`), parity with old `expandPath()`; state is never rewritten
  except the first-run seed `[defaultPath]`.
- `init` triggers the initial root lazy load (skipped when saved state is
  `['/']`), then restores.
- `collapseAll()` polyfilled (removed in 0.15) via `expandAll(false, {deep:true})`.
- `wbFindNodeByKey()` polyfilled via `tree.visit()` (covers hidden nodes).

## 5. Back-end / storage contracts preserved byte-for-byte

- DnD AJAX to `dragDropBackEndUrl` (`lib/ajax/dragdroptprojectnodes.php`):
  `changeParent` / `doReorder` param sets as above.
- Lazy loader: `treeCfg.loader` + `&node=<id>` → ExtJS-style JSON array
  (`id/text/href/leaf/position/testlink_node_type/forbidden_parent`),
  remapped by `wbMapChildren`.
- localStorage expansion-state format unchanged (old ExtJS
  `Ext.ux.LocalStorageProvider` compatible).
- Lazy loader response handling: `forbidden_parent` values flow through
  unchanged; `leaf` flag carried into `node.data.leaf` (0.15 has no built-in
  `leaf` node-data property; leaf-ness is derived from `children`).

## 6. Browser-bridge harness pattern (for reproducibility)

- `browser.eval` runs in an ISOLATED JS world: page globals (`window.tree`,
  `treeCfg`) are invisible there, and title writes after an `await` are not
  reflected in `get_content`.
- Working pattern: fetch the driver source, inject it as a `<script>`
  element so it runs in the PAGE MAIN WORLD; driver writes JSON to `#result`
  (or a compact summary to `document.title`); read via `browser.get_content`
  (`format=title` for compact summaries, `textFast` otherwise — note it
  echoes `<script>` bodies).
- Synthetic native drag events are unreliable; driving the real
  library-attached callbacks via `node._callEvent` with faithful event
  objects is the reliable, production-faithful approach.

## 7. Remaining limitations

1. DnD is validated at the callback level through the real dispatch path,
   not via pointer-driven native `dragenter`/`dragover`/`drop` in a live
   browser (native synthetic drag events are unreliable). The handler
   contract is proven; the native DOM pipeline end-to-end is not.
2. `_harness/lazy_state_smoke.js` exists but was never run (redundant with
   the already-green `lazy.html` state validation). Delete or run later.
3. `_harness/` scaffolding intentionally stays uncommitted.

## 8. 2026-11-15: "no tree displayed" on the real pages — root cause and fix

### Symptom

After the migration, every navigator page (e.g. `lib/testcases/listTestCases.php?
tproject_id=1&feature=edit_tc`) rendered an empty `#tree_div`. No console
errors, no `mar10` global, no `window.tree`. The `treeCfg` inline script
rendered fine; the three tags that `{include file="inc_wunderbaum.tpl"}` should
have emitted (wunderbaum.css, wunderbaum.umd.js, wbTree.js) were **absent from
the raw HTML** — the include rendered nothing.

### Root cause: `guard_header_smarty(__FILE__)` key collision

`inc_wunderbaum.tpl` guarded its output with
`{if guard_header_smarty(__FILE__)}`, copying the pattern from
`inc_ext_js.tpl`. But Smarty 3.1 (3.1.48 here) compiles a bare `__FILE__`
inside a template **function argument** to the *literal string* `'__FILE__'`
(it only resolves the constant for unquoted `{$...}`-style usage, not for
registered-plugin arguments). Both templates therefore guarded on the **same
static key**, and `guard_header_smarty()` keeps a `static $guarded` map per
request:

```
sequence in the rendered head:
  1. {include file="inc_ext_js.tpl"}   -> guard_header_smarty("__FILE__") -> true  (consumes the key)
  2. {include file="inc_wunderbaum.tpl"} -> guard_header_smarty("__FILE__") -> false -> renders NOTHING
```

Every navigator template includes `inc_ext_js.tpl` first (for the
CollapsiblePanel filters UI), so `inc_wunderbaum.tpl` was **always** the losing
side. The collision is deterministic, page-independent, and invisible in the
JS harnesses: those pages load the library with plain `<script src>` tags and
never run the Smarty template, so the guard was never on the test path.

Proof: `_harness/guard_proof.php` reproduces the static-key behavior
(first call true, second call false for the same key).

### Fix

Guard on a **unique, quoted string per template file** (the documented intent
of the guard is "at most once per page, per template"):

- `gui/templates/tl-classic/inc_wunderbaum.tpl`: `guard_header_smarty('inc_wunderbaum.tpl')`
- `gui/templates/tl-classic/inc_ext_js.tpl`: `guard_header_smarty('inc_ext_js.tpl')`
  (updated for consistency; the two keys can no longer collide)
- `lib/functions/tlsmarty.inc.php`: `guard_header_smarty()` docblock now warns
  against the bare `__FILE__` form and documents the unique-key requirement.

No `wbTree.js` or library changes were needed — the JS was correct all along.

### Re-validation (live pages, logged-in browser, project "GAGA" / suite "TS100")

`_harness/live_tree_check.js` injected into the main world of the real
`listTestCases.php` page — 19/19 green:

| Check | Result |
|---|---|
| `mar10` / `WunderbaumClass` / `wbTreeLoaded` (wrapper fns) | ok |
| `wonderCss` / `umdScript` / `wbTreeScript` tags present in head | ok |
| `treeGlobal` = real Wunderbaum instance | ok |
| `rootRow` "GAGA (0)" rendered | ok |
| `lazySuiteRow` "TS100 (0)" **lazy-loaded via gettprojectnodes.php** | ok |
| `rootHref` `javascript:EP(1)` / `suiteHref` `javascript:ETS(2)` | ok |
| `rootExpandedInitially` / `rootLoaded` (restoreState + initial lazy load) | ok |
| `expandAllBtn` (Expand tree button -> `tree.expandAll()`) | ok |
| `collapseAllFn` / `collapseAllWorked` (`tree.collapseAll()` polyfill) | ok |
| `stateSaved` / `stateFormat` (ExtJS path-string localStorage state) | ok |

The eager branch (planTCNavigator / execNavigator / reqSpecListTree /
printDocOptions) shares the same `inc_wunderbaum.tpl` include, so the same fix
covers it; the lazy branch above is the one with the strictest load path.

### Lesson

Callback-level harness validation does not cover **template wiring**.
When a change touches `*.tpl` include structure, re-verify on a real
logged-in page (raw HTML + DOM + console), not only in the static harness.
