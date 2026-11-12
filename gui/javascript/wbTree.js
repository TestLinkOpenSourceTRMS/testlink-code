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
  this.state = []; // list of expanded node keys
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
  var key = node.key;
  var newState = this.state.slice();
  if (newState.indexOf(key) === -1) {
    newState.push(key);
    this.saveState(newState);
  }
};

TreePanelState.prototype.onCollapse = function (node) {
  var key = node.key;
  var newState = this.state.slice();
  var idx = newState.indexOf(key);
  if (idx !== -1) {
    newState.splice(idx, 1);
    this.saveState(newState);
  }
};

/*
 * Restore the saved expansion state.
 * defaultPath: key of the node to expand when no state is available.
 *
 * Parity with the old ExtJS restoreState (expandPath per saved entry):
 * - the saved state is NEVER rewritten (except the first-run empty case,
 *   where [defaultPath] is persisted, as the old code did). In lazy trees
 *   the saved keys may point at nodes that are not loaded yet; rewriting
 *   would permanently drop them.
 * - expanding a key loads unloaded lazy children first, because
 *   setExpanded(true) triggers the lazy load before applying the flag
 *   (hasChildren() is `undefined`, not `false`, for such nodes).
 */
TreePanelState.prototype.restoreState = function (defaultPath) {
  var self = this;
  var mytree = this.mytree;
  this._read();

  var toExpand = this.state.slice();

  if (!toExpand.length) {
    // first run: remember the root as the expanded state
    toExpand = [defaultPath];
    this.saveState(toExpand);
  }

  // expand deepest first: parent expansions make deeper nodes visible
  toExpand.sort(function (a, b) {
    var na = wbFindNodeByKey(mytree, a), nb = wbFindNodeByKey(mytree, b);
    var la = na ? na.getLevel() : 0, lb = nb ? nb.getLevel() : 0;
    return lb - la;
  });

  var done = 0;
  function next() {
    if (done >= toExpand.length) {
      return; // keep the saved state untouched (stale keys are harmless)
    }
    var key = toExpand[done++];
    var node = wbFindNodeByKey(mytree, key);
    // `!== false` on purpose: a lazy node whose children are not loaded yet
    // reports `undefined` and must still be expanded - setExpanded() loads
    // the children first (same as the old expandPath did).
    if (node && node.hasChildren() !== false && !node.isExpanded()) {
      node.setExpanded(true, { noEvents: true }).then(next, next);
    } else {
      next();
    }
  }
  next();
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
  var enableDD = (treeCfg.enableDD === true || treeCfg.enableDD === 'true');
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

  // created before the constructor: expand events may fire during the
  // initial load (root is expanded by default)
  var treeState = new TreePanelState(undefined, treeCfg.cookiePrefix);

  var wbOptions = {
    id: 'tree',
    element: '#' + treeCfg.tree_div_id,
    source: [root],
    expanded: true,
    checkbox: false,
    dnd: enableDD ? {
      effectAllowed: 'move',
      dropEffectDefault: 'move',
      preventLazyParents: true,
      preventRecursion: true,
      preventSameParent: false,
      preventVoidMoves: false,
      dragStart: function (e) {
        if (!e.sourceNode.data || e.sourceNode.data.draggable === false) {
          return false;
        }
        return true;
      },
      dragEnter: function (e) {
        if (!e.sourceNode || !e.sourceNode.data) {
          return false;
        }
        // root can not be a drop target
        if (String(e.node.key) === rootKey) {
          return false;
        }
        // lazy parents without loaded children must be expanded first
        if (e.node.lazy && !e.node.isLoaded() && !e.node.isExpanded()) {
          return false;
        }
        if (treeCfg.useBeforeMoveNode) {
          var effParent = (e.suggestedDropMode === 'appendChild') ? e.node : e.node.parent;
          if (effParent && !checkMovement(effParent, e.sourceNode,
                                          e.sourceNode.key, effParent.key, 0)) {
            return false;
          }
        }
        return ['over', 'before', 'after'];
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
        // No reordering the project itself: it is TestLink's root and not
        // draggable (dragStart returns false for it). The only meaningless drop
        // is one at the invisible Wunderbaum system-root level (dropping
        // before/after the project row, where e.node.parent is that system root).
        // Reordering the project's own children (the top-level suites) IS allowed:
        // newParent is then the project node, not the system root - matching the
        // old ExtJS `movenode` handler, which fired for those moves.
        if (!newParent || newParent.isRootNode()) {
          return; // drop at the system-root level: ignore
        }
        // Wunderbaum 0.15 does NOT move the node itself: the `drop` event is
        // the terminal step (returns void, nothing re-parents afterwards).
        // Perform the move first so the UI updates AND the persisted nodelist
        // below reflects the NEW sibling order - same as the old ExtJS
        // `movenode` handler, which fired after the UI move and read
        // newparent.childNodes in the new order.
        var sameParent = oldParent && oldParent === newParent;
        if (!sameParent || mode !== 'appendChild') {
          srcNode.moveTo(target, mode);
        }
        // nodeorder: 0-based position of the moved node among its NEW siblings
        // (the old code passed an ExtJS index; the back-end reads it as an int).
        var nodeorder = 0;
        if (newParent.children) {
          for (var i = 0; i < newParent.children.length; i++) {
            if (newParent.children[i] === srcNode) { nodeorder = i; break; }
          }
        }
        writeNodePositionToDB(newParent, srcNode.key,
                              oldParentId, newParent.key, nodeorder);
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
      // restoreState() can find the first-level children (the old ExtJS code
      // called restoreState right after render and expandPath() then loaded
      // the missing lazy levels on the way down)
      var p = Promise.resolve();
      if (isLazy) {
        var rootNode = wbFindNodeByKey(treeState.mytree, rootKey);
        if (rootNode && rootNode.lazy && rootNode.isUnloaded()) {
          p = rootNode.setExpanded(true, { noEvents: true }).catch(function (err) {
            if (window.console) { window.console.error('wbTree: initial lazy load failed', err); }
          });
        }
      }
      // restore last state (or expand root by default)
      p.then(function () { treeState.restoreState(rootKey); });
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
  window.tree = myTree;
}

// wbTree.js is included in <head> by the templates, while the target div is
// in <body>: wait for DOM readiness.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', wlTreeInit);
} else {
  wlTreeInit();
}
