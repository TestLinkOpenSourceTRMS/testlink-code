/*
TestLink Open Source Project - http://testlink.sourceforge.net/
@filesource wbTree.js

Wunderbaum 0.15-based replacement for the ExtJS tree scripts:

- execTree.js         (eager tree: children given as JSON in treeCfg.children)
- execTreeWithMenu.js (eager tree, execution navigator)
- treebyloader.js     (lazy tree: children fetched on demand from treeCfg.loader)
- tcTree.js           (older variant of the above, no longer included by any template)

Behavior preserved:
- node click runs the javascript: action embedded in node href (EP/ETS/ET/ST/EXDS/...)
- rich node titles (HTML spans: execution status colors, help tooltips) are rendered as HTML
- expand/collapse state is saved/restored per cookiePrefix in localStorage
- lazy loading of child nodes via AJAX (treeCfg.loader + &node=<id>)
- drag & drop reordering with beforemovenode-style validation and
  changeParent/doReorder back-end calls (treeCfg.enableDD)
- right-click context menu (node name + API ID) on test suite / test case
  nodes of the lazy navigators (treebyloader.js behavior)

Requires (in this order):
1. ExtJS / Ext.ux.LocalStorageProvider - loaded by the page (templates keep
   {include file="inc_ext_js.tpl"} for the CollapsiblePanel filters UI)
2. jQuery - loaded by inc_head.tpl (used for the AJAX calls)
3. node_modules/wunderbaum/dist/wunderbaum.css
4. node_modules/wunderbaum/dist/wunderbaum.umd.js (global: mar10.Wunderbaum)
5. this file (gui/javascript/wbTree.js)

The page must define the global `treeCfg` object BEFORE loading this script:

  treeCfg = {
    tree_div_id: 'tree_div',
    root_name: 'Project (3)',
    root_id: 1,
    root_href: 'javascript:EP(1)',
    loader: '',                    // non-empty -> lazy tree
    children: null,                // eager tree: JSON array of child nodes
    enableDD: false,
    dragDropBackEndUrl: '',
    cookiePrefix: 'testplan_id_1_',
    root_testlink_node_type: 'testproject',   // lazy mode
    useBeforeMoveNode: false                   // lazy mode
  };

Node data format (same as produced by the PHP tree builders):
  { id: 42, text: 'Suite (2)', href: 'javascript:ETS(42)', leaf: false,
    cls: 'folder', position: 0, testlink_node_type: 'testsuite',
    testlink_node_name: 'Suite', forbidden_parent: 'none',
    children: [ ... ] }
  Wunderbaum node keys are derived from `id`; extra keys are kept in node.data.

Wunderbaum 0.15 API notes:
- tree.getNodeByKey() / tree.collapseAll() were removed; wbFindNodeByKey()
  and tree.collapseAll() below provide the old behavior.
- the 'collapse' event was merged into 'expand' with e.flag=false.

Globals exposed (kept for compatibility with inc_tree_control.tpl buttons):
  tree            - the Wunderbaum instance (tree.expandAll() / tree.collapseAll())
  writeNodePositionToDB(), checkMovement(), TreePanelState - legacy function names
*/

/* =====================================================================
 *  LocalStorage state helper (same storage semantics as the old
 *  Ext.ux.LocalStorageProvider based implementation)
 * ===================================================================== */
function TreePanelState(mytree, cookiePrefix) {
  this.mytree = mytree;
  this.cookiePrefix = cookiePrefix;
  this.storageKey = cookiePrefix + 'tree_div';
  this.state = []; // list of ExtJS-style path strings, e.g. ["/1/42"]
}

/*
 * ExtJS-style path string for a node: '/' + keys from the (project) root
 * down to the node, e.g. root -> '/1', child -> '/1/42'. Identical to the
 * old ExtJS `node.getPath()` (pathSeparator + b.join(pathSeparator)); it
 * differs from Wunderbaum's own `node.getPath()` (no leading separator).
 */
function wbExtPath(node) {
  var keys = [];
  var n = node;
  while (n && !n.isRootNode()) {
    keys.unshift(n.key);
    n = n.parent;
  }
  return '/' + keys.join('/');
}

TreePanelState.prototype._read = function () {
  try {
    var raw = window.localStorage ? window.localStorage.getItem(this.storageKey) : null;
    var arr = raw ? JSON.parse(raw) : null;
    this.state = (arr && arr.length) ? arr : [];
  } catch (e) {
    this.state = [];
  }
};

TreePanelState.prototype._write = function () {
  try {
    if (window.localStorage) {
      window.localStorage.setItem(this.storageKey, JSON.stringify(this.state));
    }
  } catch (e) {
    // storage unavailable (private mode): state is not persisted
  }
};

TreePanelState.prototype.saveState = function (newState) {
  this.state = newState;
  this._write();
};

TreePanelState.prototype.onExpand = function (node) {
  // old ExtJS onExpand: keep stored paths that are not (sub)paths of the
  // expanded node, then append the expanded path
  var currentPath = wbExtPath(node);
  var newState = this.state.slice();
  var out = [];
  for (var i = 0; i < newState.length; i++) {
    if (currentPath.indexOf(newState[i]) === -1) {
      out.push(newState[i]);
    }
  }
  out.push(currentPath);
  this.saveState(out);
};

TreePanelState.prototype.onCollapse = function (node) {
  // old ExtJS onCollapse: keep stored paths that are NOT subpaths of the
  // collapsed node; if that empties the state, remember the parent path
  // (or the bare separator when collapsing the project root itself)
  var closedPath = wbExtPath(node);
  var newState = this.state.slice();
  var out = [];
  for (var i = 0; i < newState.length; i++) {
    if (newState[i].indexOf(closedPath) === -1) {
      out.push(newState[i]);
    }
  }
  if (out.length === 0) {
    out.push(node.parent ? wbExtPath(node.parent) : '/');
  }
  this.saveState(out);
};

/*
 * Restore the saved expansion state.
 * defaultPath: ExtJS path string of the node to expand when no state is
 * available (the project root's path, e.g. '/1').
 *
 * Parity with the old ExtJS restoreState (expandPath per saved entry):
 * - the saved state is NEVER rewritten (except the first-run empty case,
 *   where [defaultPath] is persisted, as the old code did).
 * - a saved entry is an expansion frontier: expandPath() each one, i.e.
 *   load + expand every node of the path shallowest-first, INCLUDING the
 *   target (a saved node was expanded, so it comes back expanded - exactly
 *   what the old ExtJS expandPath() did; a lazy target's children load on
 *   the way down, as before).
 * - events are suppressed while restoring; the initial root expansion is
 *   the one that seeds the state on the first run.
 */
TreePanelState.prototype.restoreState = function (defaultPath) {
  var mytree = this.mytree;
  this._read();

  var toExpand = this.state.slice().filter(Boolean);

  if (!toExpand.length) {
    // first run: remember the root as the expanded state
    toExpand = [defaultPath];
    this.saveState(toExpand);
  }

  // expand one frontier entry: every key of the path, shallowest-first,
  // load + expand with no events (the old tree.expandPath() semantics)
  function expandPath(path) {
    var keys = String(path).split('/');
    var p = Promise.resolve();
    for (var i = 1; i < keys.length; i++) {
      (function (i) {
        p = p.then(function () {
          var node = wbFindNodeByKey(mytree, keys[i]);
          if (!node) {
            return; // stale entry (node removed server-side): ignore, as before
          }
          // `hasChildren() !== false` on purpose: a lazy node whose
          // children are not loaded yet reports `undefined` and must still
          // be expanded - setExpanded() loads the children first (same as
          // the old expandPath did); a loaded empty node reports `false`
          // and is skipped.
          if (node.hasChildren() !== false && !node.isExpanded()) {
            return node.setExpanded(true, { noEvents: true });
          }
        });
      })(i);
    }
    return p.catch(function (err) {
      if (window.console) { window.console.error('wbTree: restoreState failed', path, err); }
    });
  }

  var p = Promise.resolve();
  toExpand.forEach(function (path) {
    p = p.then(function () { return expandPath(path); });
  });
  return p;
};

/* =====================================================================
 *  Wunderbaum 0.15 compatibility helpers
 * ===================================================================== */

/*
 * tree.getNodeByKey() was removed in 0.15: find the first node with the
 * given key by visiting all nodes. `tree.visit()` covers hidden nodes too,
 * unlike `visitRows()` (visible rows only), so keys behind a collapsed
 * ancestor are found as well.
 */
function wbFindNodeByKey(mytree, key) {
  var found = null;
  mytree.visit(function (n) {
    if (n.key === key) {
      found = n;
      return false;
    }
    return true;
  });
  return found;
}

/*
 * tree.collapseAll() was removed in 0.15: the template buttons call it, so
 * provide the old behavior (collapse every expanded node, recursively).
 * Delegate to the built-in tree.expandAll(false, {deep:true}): collapsing
 * while iterating the visible rows would skip the children of a parent that
 * was collapsed before the iterator reached them, leaving stale expand
 * events (and stale localStorage state) behind. The built-in recurses into
 * already-collapsed nodes and fires the `expand` (e.flag=false) event for
 * every collapsed node. `deep` is a documented ExpandAllOptions member.
 */
function wbCollapseAll() {
  return this.expandAll(false, { deep: true });
}

/* =====================================================================
 *  Context menu (treebyloader.js behavior, lazy navigators only)
 * ===================================================================== */

/*
 * The old ExtJS tree showed a right-click menu on test suite / test case
 * rows: the node name (without the trailing ' (id)') and its API id.
 * Wunderbaum has no built-in context menu, so provide an equivalent
 * floating menu: resolve the row node via Wunderbaum.getNode() (the row
 * element carries the node on `_wb_node`), then show two text items at
 * the cursor position. Closes on any click, scroll, or Escape.
 */
var wbContextMenu = null;

function wbContextMenuClose() {
  if (wbContextMenu && wbContextMenu.parentNode) {
    wbContextMenu.parentNode.removeChild(wbContextMenu);
  }
  wbContextMenu = null;
  // Remove the close listeners. They were registered synchronously on the
  // previous open; leaving them on document made every later right-click
  // close the just-opened menu (a stale `contextmenu` listener fired while
  // the new one was still opening) and they accumulated on every open. The
  // close handlers are named functions (never nulled) so removeEventListener
  // always matches what was added.
  if (typeof document !== 'undefined') {
    document.removeEventListener('click', wbContextMenuClose);
    document.removeEventListener('contextmenu', wbCmContextHandler);
    document.removeEventListener('scroll', wbContextMenuClose, true);
    document.removeEventListener('keydown', wbCmKeydownHandler);
  }
}

function wbShowTreeContextMenu(node, x, y) {
  var type = (node.data && node.data.testlink_node_type) || '';
  if (type !== 'testsuite' && type !== 'testcase') {
    return; // old menuShow switched on these two types only
  }
  wbContextMenuClose();
  var el = document.createElement('div');
  el.className = 'wb-context-menu';
  el.style.cssText = 'position:absolute;z-index:10000;min-width:120px;padding:2px 0;' +
    'background:#fff;border:1px solid #9db8d2;' +
    'box-shadow:2px 2px 5px rgba(0,0,0,.35);font:11px Verdana,Geneva,sans-serif;';
  var head = document.createElement('div');
  // old code: node.text with the trailing ' (id)' stripped
  head.textContent = node.title.replace(/\s*\([^)]*\)$/, '');
  head.style.cssText = 'font-weight:bold;margin:0px 4px 0px 27px;line-height:18px;';
  var api = document.createElement('div');
  api.textContent = 'API ID:' + node.key;
  api.style.cssText = 'margin:0px 4px 0px 27px;line-height:18px;';
  el.appendChild(head);
  el.appendChild(api);
  document.body.appendChild(el);
  wbContextMenu = el;
  // keep the menu on screen
  var maxLeft = (document.documentElement.clientWidth || 1024) - el.offsetWidth - 4;
  var maxTop = (document.documentElement.clientHeight || 768) - el.offsetHeight - 4;
  if (x > maxLeft) { x = Math.max(0, maxLeft); }
  if (y > maxTop) { y = Math.max(0, maxTop); }
  el.style.left = (x + document.documentElement.scrollLeft) + 'px';
  el.style.top = (y + document.documentElement.scrollTop) + 'px';
  // Register the close listeners SYNCHRONOUSLY: the opening `contextmenu`
  // event is stopped from reaching document (see wbAttachTreeContextMenu),
  // so they cannot close the menu while it is being opened. Removed again in
  // wbContextMenuClose(). (Previously they were added in a setTimeout(0)
  // and never removed, so a stale `contextmenu` listener closed the next
  // just-opened menu and they accumulated on every open.)
  if (typeof document !== 'undefined') {
    document.addEventListener('click', wbContextMenuClose);
    document.addEventListener('contextmenu', wbCmContextHandler);
    document.addEventListener('scroll', wbContextMenuClose, true);
    document.addEventListener('keydown', wbCmKeydownHandler);
  }
}

/* Closes the menu on any second right-click (the opening one never reaches
   document because the attach handler stops its propagation). */
function wbCmContextHandler() {
  wbContextMenuClose();
}

function wbCmKeydownHandler(ev) {
  if (ev && (ev.key === 'Escape' || ev.keyCode === 27)) {
    wbContextMenuClose();
  }
}

/*
 * Attach the right-click menu to a lazy tree (only the lazy navigators,
 * treebyloader.js, had the context menu).
 */
function wbAttachTreeContextMenu(tree) {
  var el = tree.element || (tree.elementDom && tree.elementDom.node);
  if (!el || el._wbTreeContextMenu) {
    return;
  }
  el._wbTreeContextMenu = true;
  el.addEventListener('contextmenu', function (event) {
    // old ExtJS tree.on({contextmenu:{...stopEvent:true}}): the browser's
    // default context menu is suppressed for the whole tree, not only for
    // the rows that show the custom menu.
    event.preventDefault();
    var node = mar10.Wunderbaum.getNode(event);
    if (!node || node.isRootNode() || node.isStatusNode()) {
      return; // no custom menu on the project row or system status rows
    }
    // Keep the opening `contextmenu` from bubbling to document: the document
    // `contextmenu` close handler is registered (synchronously, on the
    // previous open), and letting it see THIS event would close the menu we
    // are about to create. A second right-click (different event) still
    // reaches document and closes the open menu.
    event.stopImmediatePropagation();
    wbShowTreeContextMenu(node, event.clientX, event.clientY);
  });
}

/* =====================================================================
 *  Drag & drop back-end calls (same contract as the old ExtJS scripts)
 * ===================================================================== */

/*
 * Persist a node move/reorder on the server.
 *
 * args: newparent, nodeid, oldparentid, newparentid, nodeorder
 *       (nodeid/oldparentid/newparentid are the node keys/ids as strings)
 */
function writeNodePositionToDB(newparent, nodeid, oldparentid, newparentid, nodeorder) {
  var nodelist = '';
  if (newparent && newparent.children) {
    nodelist = newparent.children.map(function (c) { return c.key; }).join(',');
  }

  var params;
  if (String(oldparentid) !== String(newparentid)) {
    params = { doAction: 'changeParent', nodeid: nodeid,
               oldparentid: oldparentid, newparentid: newparentid,
               top_or_bottom: 'bottom' };
  } else {
    params = { doAction: 'doReorder', nodeid: nodeid,
               oldparentid: oldparentid, newparentid: newparentid,
               nodelist: nodelist, nodeorder: nodeorder };
  }
  jQuery.ajax({ url: treeCfg.dragDropBackEndUrl, data: params });
}

/*
 * beforemovenode-style validation.
 * returns true -> movement allowed.
 */
function checkMovement(newparent, node, oldparentid, newparentid, nodeorder) {
  var status = true;
  var newparent_node_type = (newparent.data && newparent.data.testlink_node_type) || '';
  var node_type = (node.data && node.data.testlink_node_type) || '';

  switch (node_type) {
    case 'requirement':
    case 'requirement_spec':
    case 'testcase':
    case 'testsuite':
      if (node.data.forbidden_parent && node.data.forbidden_parent === newparent_node_type) {
        status = false;
      }
    break;
  }
  return status;
}

/* =====================================================================
 *  Node data mapping: TL node format -> Wunderbaum node data
 * ===================================================================== */

/*
 * text/href may contain HTML (status colors, help tooltips) produced by the
 * PHP tree builders. Return the same string if it is plain text, so that
 * Wunderbaum escapes it; the `render` event detects the HTML and injects it.
 */
function wbIsHtml(text) {
  return typeof text === 'string' && text.indexOf('<') !== -1;
}

function wbMapChildren(children, seenKeys, lazyMode) {
  var out = [];
  if (!children) {
    return out;
  }
  children.forEach(function (raw) {
    if (!raw || raw.id === undefined || raw.id === null) {
      return;
    }
    var key = String(raw.id);
    if (seenKeys[key]) {
      // node ids must be unique tree keys; guard against duplicates
      key = key + '_' + (++seenKeys[key]);
    } else {
      seenKeys[key] = 1;
    }
    var node = {
      key: key,
      title: raw.text != null ? raw.text : (raw.testlink_node_name || ''),
      // keep the original (possibly HTML) text: the `render` event injects it
      text: raw.text != null ? raw.text : '',
      href: raw.href || '',
      testlink_node_name: raw.testlink_node_name || '',
      testlink_node_type: raw.testlink_node_type || '',
      forbidden_parent: raw.forbidden_parent || '',
      // the PHP builders set leaf=true only for test cases and leaf=false for
      // every suite/project ("used on custom code on drag and drop"). `leaf`
      // is NOT a built-in Wunderbaum node-data property in 0.15 (leaf-ness is
      // derived from `children`), so carry the flag for the DnD dragEnter gate,
      // which mirrors the old ExtJS getDropPoint() `tn.isLeaf()` check.
      leaf: raw.leaf === true,
      position: raw.position != null ? raw.position : 0,
      cls: raw.cls || ''
    };
    if (raw.children && raw.children.length) {
      // fully nested (eager tree): map the whole subtree
      node.children = wbMapChildren(raw.children, seenKeys, lazyMode);
    } else if (lazyMode && raw.leaf !== true) {
      // lazy tree: expandable node whose children are fetched on demand.
      // Without `lazy: true` Wunderbaum would not render an expander.
      node.lazy = true;
    }
    out.push(node);
  });
  return out;
}

/* =====================================================================
 *  Tree initialization
 * ===================================================================== */
function wlTreeInit() {
  if (!window.mar10 || !mar10.Wunderbaum || !document.getElementById(treeCfg.tree_div_id)) {
    // wunderbaum not loaded (or target div missing): do nothing
    return;
  }

  var rootKey = String(treeCfg.root_id);
  // The templates render PHP booleans into quoted strings: enabled=true
  // becomes the string '1', enabled=false becomes '' (Smarty). The old
  // ExtJS code passed treeCfg.enableDD straight to TreePanel (raw truthy),
  // so keep raw-truthy semantics here instead of a strict 'true' compare,
  // which would have disabled DnD on every real page.
  var enableDD = !!treeCfg.enableDD;
  var isLazy = !!treeCfg.loader;

  var root = {
    key: rootKey,
    title: treeCfg.root_name || '',
    // keep the raw name: the `render` event injects it as HTML when it
    // contains markup (planAddTCNavigator builds the root from wrapOpen/wrapClose)
    text: treeCfg.root_name || '',
    href: treeCfg.root_href || '',
    testlink_node_type: treeCfg.root_testlink_node_type || 'testproject',
    draggable: false
  };
  if (isLazy) {
    root.lazy = true;
  } else {
    root.children = wbMapChildren(treeCfg.children, {});
  }

  // created before the constructor: the lazy initial load fires the
  // `init` event, which must be able to reach treeState.restoreState()
  var treeState = new TreePanelState(undefined, treeCfg.cookiePrefix);

  var wbOptions = {
    id: 'tree',
    element: '#' + treeCfg.tree_div_id,
    source: [root],
    checkbox: false,
    dnd: enableDD ? {
      effectAllowed: 'move',
      dropEffectDefault: 'move',
      // The old ExtJS tree let you drop a TC onto a collapsed lazy suite:
      // processDrop() expanded the target (triggering the lazy load) and then
      // completed the move. Keep that - the `drop` handler below loads an
      // unloaded lazy target before moving into it.
      preventLazyParents: false,
      preventRecursion: true,
      preventSameParent: false,
      preventVoidMoves: false,
      dragStart: function (e) {
        if (!e.sourceNode.data || e.sourceNode.data.draggable === false) {
          return false;
        }
        return true;
      },
      // 0.15 fires dragEnter when the pointer ENTERS a node, with
      // {event, sourceNode, node} and NO drop region yet (the region is
      // only known in dragover/drop). It must return the SET of allowed
      // regions, so every candidate region is validated here against the
      // effective new parent - the port of the old beforemovenode gate.
      dragEnter: function (e) {
        if (!e.sourceNode || !e.sourceNode.data || !e.node) {
          return false;
        }
        var src = e.sourceNode, tgt = e.node;
        // dropping a node on itself is meaningless; the old ExtJS code only
        // emitted a movenode with nodeorder -1 (garbage), so gate it here.
        if (tgt === src) {
          return false;
        }
        // The project row is append-only, exactly like the old ExtJS
        // getDropPoint() root case (before/after the project row is a
        // system-root-level move with no back-end meaning; the drop
        // handler below no-ops it anyway).
        if (String(tgt.key) === rootKey) {
          if (treeCfg.useBeforeMoveNode &&
              !checkMovement(tgt, src, src.key, tgt.key, 0)) {
            return false;
          }
          return ['over'];
        }
        // before/after: the effective new parent is the target's parent
        var allowBeforeAfter = !treeCfg.useBeforeMoveNode ||
          !tgt.parent ||
          checkMovement(tgt.parent, src, src.key, tgt.parent.key, 0);
        // over (append into the target): leaf rows never accept children.
        // The old ExtJS getDropPoint() computed `noAppend = ... || tn.isLeaf()`,
        // and TreeNode.isLeaf() is just the node's `leaf` flag - it does NOT
        // consult hasChildren(). The PHP builders set leaf=false for every
        // suite/project and leaf=true only for test cases, so an EMPTY suite
        // (loaded, hasChildren()===false) must still accept an append; keying
        // leaf-ness off hasChildren() wrongly blocked drops into empty suites.
        var tgtIsLeaf = !!tgt.data.leaf;
        var allowOver = !tgtIsLeaf &&
          (!treeCfg.useBeforeMoveNode ||
           checkMovement(tgt, src, src.key, tgt.key, 0));
        if (allowOver && allowBeforeAfter) {
          return ['over', 'before', 'after'];
        }
        if (allowOver) { return ['over']; }
        if (allowBeforeAfter) { return ['before', 'after']; }
        return false;
      },
      drop: function (e) {
        var srcNode = e.sourceNode;
        if (!srcNode || !srcNode.data || srcNode.data.draggable === false || !e.node) {
          return; // not draggable (root has draggable:false; guarded in dragStart too)
        }
        // capture the old parent BEFORE the move mutates it
        var oldParent = srcNode.parent;
        var oldParentId = oldParent ? String(oldParent.key) : rootKey;
        // effective parent + moveTo target by drop region (0.15
        // suggestedDropMode: 'appendChild' | 'before' | 'after')
        var newParent, target, mode;
        if (e.suggestedDropMode === 'appendChild') {
          newParent = e.node; target = e.node; mode = 'appendChild';
        } else {
          newParent = e.node.parent; target = e.node;
          mode = (e.suggestedDropMode === 'after') ? 'after' : 'before';
        }
        // The only meaningless drop is one at the invisible Wunderbaum
        // system-root level (before/after the project row, where
        // e.node.parent is that system root). Reordering the project's
        // own children (the top-level suites) IS allowed: newParent is
        // then the project node - matching the old ExtJS `movenode`
        // handler, which fired for those moves.
        if (!newParent || newParent.isRootNode()) {
          return; // drop at the system-root level: ignore
        }
        // self-drop: the dragEnter gate normally prevents this, but guard
        // anyway so no garbage doReorder is ever persisted.
        if (target === srcNode) {
          return;
        }
        // the dragEnter gate normally prevents this, but guard anyway:
        // never move a node into its own subtree
        if (srcNode.isAncestorOf && srcNode.isAncestorOf(target)) {
          return;
        }
        // Wunderbaum 0.15 does NOT move the node itself: the `drop` event
        // is the terminal step (returns void, nothing re-parents
        // afterwards). Perform the move first so the UI updates AND the
        // persisted nodelist below reflects the NEW sibling order. This
        // matches the old ExtJS `movenode` handler: TreeNode.appendChild /
        // insertBefore fire the node `move` event whenever oldParent is set,
        // which includes same-parent moves, so the old code persisted those
        // too (no isNoOp skip - a faithful mirror).
        function persist() {
          // nodeorder: 0-based position of the moved node among its NEW
          // siblings (the old code passed an ExtJS index; the back-end
          // reads it as an int).
          var nodeorder = 0;
          if (newParent.children) {
            for (var i = 0; i < newParent.children.length; i++) {
              if (newParent.children[i] === srcNode) { nodeorder = i; break; }
            }
          }
          writeNodePositionToDB(newParent, srcNode.key,
                                oldParentId, newParent.key, nodeorder);
        }
        if (mode === 'appendChild' &&
            newParent.lazy && newParent.hasChildren() === undefined) {
          // Target is an unloaded lazy parent. The old ExtJS processDrop()
          // expanded the target (triggering its lazy load) and then did the
          // move - replicate that. A direct moveTo() here would corrupt the
          // target (0.15 would set its children to just the moved node and
          // mark it loaded, clobbering the real children).
          newParent.setExpanded(true, { noEvents: false }).then(function () {
            srcNode.moveTo(target, 'appendChild');
            persist();
          }).catch(function (err) {
            if (window.console) { window.console.error('wbTree: lazy drop load failed', err); }
            newParent.setExpanded(true, { noEvents: true });
          });
          return;
        }
        srcNode.moveTo(target, mode);
        persist();
      }
    } : { enabled: false },
    lazyLoad: function (e) {
      // 0.15 `lazyLoad` contract: must return a node list (or {url}, or false);
      // the library then calls node.load(returned). The PHP loader answers an
      // ExtJS-style JSON array (id/leaf), so fetch and remap it.
      var url = treeCfg.loader;
      var sep = (url.indexOf('?') === -1) ? '?' : '&';
      url = url.replace(/([?&])node=[^&]*/, '$1');
      return jQuery.ajax({
        url: url + sep + 'node=' + encodeURIComponent(e.node.key),
        dataType: 'json'
      }).then(function (data) {
        var list = Array.isArray(data) ? data : (data && data.children);
        return wbMapChildren(list, {}, true);
      });
    },
    init: function (e) {
      if (e.error) {
        if (window.console) { window.console.error('wbTree: init failed', e.error); }
        return;
      }
      // a data-level `expanded: true` alone does not fetch a lazy node's
      // children; trigger the first load explicitly and wait for it, so that
      // restoreState() can find the first-level children (as the old ExtJS
      // expandPath() did, loading the missing lazy levels on the way down).
      // Skip it when the saved state is ['/'] (project row collapsed and
      // saved by onCollapse): the old expandPath('/') was a no-op, so the
      // tree must come back collapsed.
      var p = Promise.resolve();
      if (isLazy) {
        var rawState = [];
        try {
          rawState = JSON.parse(window.localStorage.getItem(
            treeCfg.cookiePrefix + 'tree_div')) || [];
        } catch (err) { rawState = []; }
        if (rawState.indexOf('/') === -1) {
          var rootNode = wbFindNodeByKey(treeState.mytree, rootKey);
          if (rootNode && rootNode.lazy && rootNode.isUnloaded()) {
            p = rootNode.setExpanded(true, { noEvents: true }).catch(function (err) {
              if (window.console) { window.console.error('wbTree: initial lazy load failed', err); }
            });
          }
        }
      }
      // restore last state (or expand the root by default); the old code
      // passed `tree.root.getPath()` - the ExtJS path string of the root
      p.then(function () { treeState.restoreState('/' + rootKey); });
    },
    activate: function (e) {
      var href = e.node.data && e.node.data.href;
      if (href && href.indexOf('javascript:') === 0) {
        // TestLink node actions (EP/ETS/ET/ST/EXDS/...) are embedded as
        // javascript: urls and are executed on node click/activation
        try {
          window.eval(href.slice('javascript:'.length));
        } catch (err) {
          if (window.console) { window.console.error('wbTree: action failed', href, err); }
        }
      }
    },
    expand: function (e) {
      // 0.15 fires a single `expand` event with e.flag for both directions
      if (e.flag) {
        treeState.onExpand(e.node);
      } else {
        treeState.onCollapse(e.node);
      }
    },
    render: function (e) {
      var raw = e.node.data;
      if (raw && wbIsHtml(raw.text)) {
        // PHP tree builders embed HTML in 'text' (status colors, tooltips)
        e.node.getColElem(0).querySelector('.wb-title').innerHTML = raw.text;
      }
    }
  };

  var myTree = new mar10.Wunderbaum(wbOptions);
  treeState.mytree = myTree;

  // keep the ExtJS-era global name: inc_tree_control.tpl uses
  // tree.expandAll() and tree.collapseAll()
  myTree.collapseAll = wbCollapseAll;

  // the old treebyloader.js (lazy navigators) had the right-click context menu
  if (isLazy) {
    wbAttachTreeContextMenu(myTree);
  }
  window.tree = myTree;
}

// wbTree.js is included in <head> by the templates, while the target div is
// in <body>: wait for DOM readiness.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', wlTreeInit);
} else {
  wlTreeInit();
}
