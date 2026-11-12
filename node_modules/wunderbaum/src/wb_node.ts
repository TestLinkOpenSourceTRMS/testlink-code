/*!
 * Wunderbaum - wunderbaum_node
 * Copyright (c) 2021-2025, Martin Wendt. Released under the MIT license.
 * @VERSION, @DATE (https://github.com/mar10/wunderbaum)
 */

import * as util from "./util";

import { Wunderbaum } from "./wunderbaum";
import {
  AddChildrenOptions,
  ApplyCommandOptions,
  ApplyCommandType,
  ChangeType,
  CheckboxOption,
  ColumnEventInfoMap,
  ExpandAllOptions,
  IconOption,
  InsertNodeType,
  MakeVisibleOptions,
  MatcherCallback,
  NavigateOptions,
  NavigationType,
  NodeAnyCallback,
  NodeStatusType,
  NodeStringCallback,
  NodeToDictCallback,
  NodeVisitCallback,
  NodeVisitResponse,
  RenderOptions,
  ResetOrderOptions,
  ScrollIntoViewOptions,
  SetActiveOptions,
  SetExpandedOptions,
  SetSelectedOptions,
  SetStatusOptions,
  SortByPropertyOptions,
  SortCallback,
  SortOptions,
  SourceType,
  TooltipOption,
  TristateType,
  WbNodeData,
} from "./types";

import {
  decompressSourceData,
  ICON_WIDTH,
  KEY_TO_NAVIGATION_MAP,
  makeNodeTitleMatcher,
  NODE_TYPE_FOLDER,
  nodeTitleSorter,
  RESERVED_TREE_SOURCE_KEYS,
  TEST_FILE_PATH,
  TEST_HTML,
  TITLE_SPAN_PAD_Y,
} from "./common";
import { Deferred } from "./deferred";

/** WunderbaumNode properties that can be passed with source data.
 * (Any other source properties will be stored as `node.data.PROP`.)
 */
const NODE_PROPS = new Set<string>([
  "checkbox",
  "classes",
  "expanded",
  "icon",
  "iconTooltip",
  "key",
  "lazy",
  "_partsel",
  "radiogroup",
  "refKey",
  "selected",
  "statusNodeType",
  "title",
  "tooltip",
  "type",
  "unselectable",
]);
/** WunderbaumNode properties that will be returned by `node.toDict()`.)
 */
const NODE_DICT_PROPS = new Set<string>(NODE_PROPS);
NODE_DICT_PROPS.delete("_partsel");
NODE_DICT_PROPS.delete("unselectable");

// /** Node properties that are of type bool (or boolean & string).
//  *  When parsing, we accept 0 for false and 1 for true for better JSON compression.
//  */
// export const NODE_BOOL_PROPS: Set<string> = new Set([
//   "checkbox",
//   "colspan",
//   "expanded",
//   "icon",
//   "iconTooltip",
//   "radiogroup",
//   "selected",
//   "tooltip",
//   "unselectable",
// ]);

/**
 * A single tree node.
 *
 * **NOTE:** <br>
 * Generally you should not modify properties directly, since this may break
 * the internal bookkeeping.
 */
export class WunderbaumNode {
  static sequence = 0;

  /** Reference to owning tree. */
  public tree: Wunderbaum;
  /** Parent node (null for the invisible root node `tree.root`). */
  public parent: WunderbaumNode;
  /** Name of the node.
   * @see Use {@link setTitle} to modify. */
  public title: string;
  /** Unique key. Passed with constructor or defaults to `SEQUENCE`.
   * @see Use {@link setKey} to modify. */
  public readonly key: string;
  /** Reference key. Unlike {@link key}, a `refKey` may occur multiple
   * times within a tree (in this case we have 'clone nodes').
   * @see Use {@link setKey} to modify.
   */
  public readonly refKey: string | undefined = undefined;
  /**
   * Array of child nodes (null for leaf nodes).
   * For lazy nodes, this is `null` or ùndefined` until the children are loaded
   * and leaf nodes may be `[]` (empty array).
   * @see {@link hasChildren}, {@link addChildren}, {@link lazy}.
   */
  public children: WunderbaumNode[] | null = null;
  /** Render a checkbox or radio button @see {@link selected}. */
  public checkbox?: CheckboxOption;
  /** If true, this node's children are considerd radio buttons.
   * @see {@link isRadio}.
   */
  public radiogroup?: boolean;
  /** If true, (in grid mode) no cells are rendered, except for the node title.*/
  public colspan?: boolean;
  /** Icon definition. */
  public icon?: IconOption;
  /** Lazy loading flag.
   * @see {@link isLazy}, {@link isLoaded}, {@link isUnloaded}.
   */
  public lazy?: boolean;
  /** Expansion state.
   * @see {@link isExpandable}, {@link isExpanded}, {@link setExpanded}. */
  public expanded?: boolean;
  /** Selection state.
   * @see {@link isSelected}, {@link setSelected}, {@link toggleSelected}. */
  public selected?: boolean;
  public unselectable?: boolean;
  /** Node type (used for styling).
   * @see {@link Wunderbaum.types}.
   */
  public type?: string;
  /** Tooltip definition (`true`: use node's title). */
  public tooltip?: TooltipOption;
  /** Icon tooltip definition (`true`: use node's title). */
  public iconTooltip?: TooltipOption;
  /** Additional classes added to `div.wb-row`.
   * @see {@link hasClass}, {@link setClass}. */
  public classes: Set<string> | null = null; //new Set<string>();
  /** Custom data that was passed to the constructor */
  public data: any = {};
  // --- Node Status ---
  public statusNodeType?: NodeStatusType;
  _isLoading = false;
  _requestId = 0;
  _errorInfo: any | null = null;
  _partsel = false;
  _partload = false;
  // --- FILTER ---
  /**
   * > 0 if matched (-1 to keep system nodes visible);
   * Added and removed by filter code.
   */
  public match?: number;
  public subMatchCount?: number = 0;
  // public subMatchBadge?: HTMLElement;
  /** @internal */
  public titleWithHighlight?: string;
  public _filterAutoExpanded?: boolean;

  _rowIdx: number | undefined = 0;
  /** Position among non-status siblings, see `_render_aria()`. @internal */
  _ariaPosInSet?: number;
  /** Number of non-status children, see `_render_aria()`. @internal */
  _ariaSetSize?: number;
  /** Render pass of `_ariaSetSize`, see `_render_aria()`. @internal */
  _ariaSetPass?: number;
  _rowElem: HTMLDivElement | undefined = undefined;

  constructor(tree: Wunderbaum, parent: WunderbaumNode, data: WbNodeData) {
    util.assert(!parent || parent.tree === tree, `Invalid parent: ${parent}`);
    util.assert(!data.children, "'children' not allowed here");

    this.tree = tree;
    this.parent = parent;
    this.key = tree._calculateKey(data, parent);
    this.title = "" + (data.title ?? "<" + this.key + ">");
    this.expanded = !!data.expanded;
    this.lazy = !!data.lazy;

    // We set the following node properties only if a matching data value is
    // passed
    data.refKey != null ? (this.refKey = "" + data.refKey) : 0;
    data.type != null ? (this.type = "" + data.type) : 0;
    data.icon != null ? (this.icon = util.intToBool(data.icon)) : 0;
    data.tooltip != null ? (this.tooltip = util.intToBool(data.tooltip)) : 0;
    data.iconTooltip != null
      ? (this.iconTooltip = util.intToBool(data.iconTooltip))
      : 0;
    data.statusNodeType != null
      ? (this.statusNodeType = ("" + data.statusNodeType) as NodeStatusType)
      : 0;
    data.colspan != null ? (this.colspan = !!data.colspan) : 0;

    // Selection
    data.checkbox != null
      ? (this.checkbox = util.intToBool(data.checkbox) as CheckboxOption)
      : 0;
    data.radiogroup != null ? (this.radiogroup = !!data.radiogroup) : 0;
    data.selected != null ? (this.selected = !!data.selected) : 0;
    data.unselectable != null ? (this.unselectable = !!data.unselectable) : 0;

    if (data.classes) {
      this.setClass(data.classes);
    }
    // Store custom fields as `node.data`
    for (const [key, value] of Object.entries(data)) {
      if (!NODE_PROPS.has(key)) {
        this.data[key] = value;
      }
    }

    if (parent && !this.statusNodeType) {
      // Don't register root node or status nodes
      tree._registerNode(this);
    }
  }

  /**
   * Return readable string representation for this instance.
   * @internal
   */
  toString() {
    return `WunderbaumNode@${this.key}<'${this.title}'>`;
  }

  /**
   * Iterate all descendant nodes depth-first, pre-order using `for ... of ...` syntax.
   * More concise, but slightly slower than {@link WunderbaumNode.visit}.
   *
   * Example:
   * ```js
   * for(const n of node) {
   *   ...
   * }
   * ```
   */

  *[Symbol.iterator](): IterableIterator<WunderbaumNode> {
    // let node: WunderbaumNode | null = this;
    const cl = this.children;
    if (cl) {
      for (let i = 0, l = cl.length; i < l; i++) {
        const n = cl[i];
        yield n;
        if (n.children) {
          yield* n;
        }
      }
      // Slower:
      // for (let node of this.children) {
      //   yield node;
      //   yield* node : 0;
      // }
    }
  }

  // /** Return an option value. */
  // protected _getOpt(
  //   name: string,
  //   nodeObject: any = null,
  //   treeOptions: any = null,
  //   defaultValue: any = null
  // ): any {
  //   return evalOption(
  //     name,
  //     this,
  //     nodeObject || this,
  //     treeOptions || this.tree.options,
  //     defaultValue
  //   );
  // }

  /** Call event handler if defined in tree.options.
   * Example:
   * ```js
   * node._callEvent("edit.beforeEdit", {foo: 42})
   * ```
   */
  _callEvent(type: string, extra?: any): any {
    return this.tree?._callEvent(
      type,
      util.extend(
        {
          node: this,
          typeInfo: this.type ? this.tree.types[this.type] : {},
        },
        extra
      )
    );
  }

  /**
   * Append a 'paging' status node ("More...") after the loaded children,
   * replacing an existing one.
   *
   * Clicking it, or pressing Enter or Space on it, fires the `clickPaging`
   * event. The handler typically fetches the next page, removes the paging
   * node, calls `addChildren()` and adds a new paging node if more items
   * remain:
   * ```js
   * clickPaging: async (e) => {
   *   const parent = e.node.parent;
   *   const page = await fetchPage(parent.key, parent.children.length - 1);
   *   e.node.remove();
   *   parent.addChildren(page.items);
   *   if (page.more) {
   *     parent.addPagingNode();
   *   }
   * },
   * ```
   * If the handler returns a promise (e.g. an `async` function), the paging
   * node shows its loading state and ignores further clicks until it settles.
   * Paging nodes are status nodes, so they are not registered by key, and
   * `sort()` keeps them at the end.
   *
   * @param node title string or node data (`statusNodeType` is forced to 'paging')
   * @returns the new paging node
   */
  addPagingNode(
    node?: WbNodeData | string | null,
    options?: AddChildrenOptions
  ): WunderbaumNode {
    const given: Partial<WbNodeData> =
      typeof node === "string" ? { title: node } : { ...(node ?? {}) };
    const data: WbNodeData = {
      ...given,
      title: given.title ?? this.tree.options.strings.moreItems ?? "More...",
      checkbox: false,
      colspan: given.colspan ?? true,
      statusNodeType: NodeStatusType.paging,
    };
    this.children
      ?.filter((n) => n.statusNodeType === NodeStatusType.paging)
      .forEach((n) => n.remove());
    return this.addChildren(data, options);
  }

  /**
   * Fire the `clickPaging` event for a paging node (click, Enter, or Space).
   * If the handler returns a promise, the node shows its loading state and
   * ignores further clicks until the promise settles.
   * @internal
   */
  _clickPaging(event: Event): void {
    if (this._isLoading) {
      return;
    }
    const res = this._callEvent("clickPaging", { event: event });
    if (res instanceof Promise) {
      this._isLoading = true;
      this.update(ChangeType.status);
      res.finally(() => {
        this._isLoading = false;
        if (this.tree) {
          // (the handler may have removed this node)
          this.update(ChangeType.status);
        }
      });
    }
  }

  /**
   * Append (or insert) a list of child nodes.
   *
   * Tip: pass `{ before: 0 }` to prepend new nodes as first children.
   *
   * @returns first child added
   */
  addChildren(
    nodeData: WbNodeData | WbNodeData[],
    options?: AddChildrenOptions
  ): WunderbaumNode {
    const tree = this.tree;
    let { before = null, applyMinExpanLevel = true, _level } = options ?? {};
    // let { before, loadLazy=true, _level } = options ?? {};
    // const isTopCall = _level == null;
    _level ??= this.getLevel();
    const nodeList = [];

    try {
      tree.enableUpdate(false);

      if (util.isPlainObject(nodeData)) {
        nodeData = [<WbNodeData>nodeData];
      }
      const forceExpand =
        applyMinExpanLevel && _level < tree.options.minExpandLevel;
      for (const child of <WbNodeData[]>nodeData) {
        const subChildren = child.children;
        // Remove children property from source data because it should not be
        // passed to the constructor of WunderbaumNode:
        delete child.children;

        const n = new WunderbaumNode(tree, this, child);

        // Set `children` property again, so it can be used in `reload()`
        if (subChildren != null) {
          child.children = subChildren;
        }
        if (forceExpand && !n.isUnloaded()) {
          n.expanded = true;
        }
        nodeList.push(n);
        if (subChildren) {
          n.addChildren(subChildren, { _level: _level + 1 });
        }
      }

      if (!this.children) {
        this.children = nodeList;
      } else if (before == null || this.children.length === 0) {
        this.children = this.children.concat(nodeList);
      } else {
        // Returns null if before is not a direct child:
        before = this.findDirectChild(before)!;
        const pos = this.children.indexOf(before);
        util.assert(
          pos >= 0,
          `options.before must be a direct child of ${this}`
        );
        // insert nodeList after children[pos]
        this.children.splice(pos, 0, ...nodeList);
      }
      // this.triggerModifyChild("add", nodeList.length === 1 ? nodeList[0] : null);
      tree.update(ChangeType.structure);
    } finally {
      // if (tree.options.selectMode === "hier") {
      //   if (this.parent && this.parent.children) {
      //     this.fixSelection3FromEndNodes();
      //   } else {
      //     // may happen when loading __root__;
      //   }
      // }
      tree.enableUpdate(true);
    }
    // if(isTopCall && loadLazy){
    //   this.logWarn("addChildren(): loadLazy is not yet implemented.")
    // }
    return nodeList[0];
  }

  /**
   * Append or prepend a node, or append a child node.
   *
   * This a convenience function that calls addChildren()
   *
   * @param nodeData node definition
   * @param [mode=child] 'before', 'after', 'firstChild', or 'child' ('over' is a synonym for 'child')
   * @returns new node
   */
  addNode(
    nodeData: WbNodeData,
    mode: InsertNodeType = "appendChild"
  ): WunderbaumNode {
    if (<string>mode === "over") {
      mode = "appendChild"; // compatible with drop region
    }
    switch (mode) {
      case "after":
        return this.parent.addChildren(nodeData, {
          before: this.getNextSibling(),
        });
      case "before":
        return this.parent.addChildren(nodeData, { before: this });
      case "prependChild":
        // Insert before the first child if any
        // let insertBefore = this.children ? this.children[0] : undefined;
        return this.addChildren(nodeData, { before: 0 });
      case "appendChild":
        return this.addChildren(nodeData);
    }
    util.assert(false, `Invalid mode: ${mode}`);
    return (<unknown>undefined) as WunderbaumNode;
  }

  /**
   * Apply a modification (or navigation) operation.
   *
   * @see {@link Wunderbaum.applyCommand}
   */
  applyCommand(cmd: ApplyCommandType, options: ApplyCommandOptions): any {
    return this.tree.applyCommand(cmd, this, options);
  }

  /**
   * Collapse all expanded sibling nodes if any.
   * (Automatically called when `autoCollapse` is true.)
   */
  collapseSiblings(options?: SetExpandedOptions): any {
    for (const node of this.parent.children!) {
      if (node !== this && node.expanded) {
        node.setExpanded(false, options);
      }
    }
  }

  /**
   * Add/remove one or more classes to `<div class='wb-row'>`.
   *
   * This also maintains `node.classes`, so the class will survive a re-render.
   *
   * @param className one or more class names. Multiple classes can be passed
   *     as space-separated string, array of strings, or set of strings.
   */
  setClass(
    className: string | string[] | Set<string>,
    flag: boolean = true
  ): void {
    const cnSet = util.toSet(className);
    if (flag) {
      if (this.classes === null) {
        this.classes = new Set<string>();
      }
      cnSet.forEach((cn) => {
        this.classes!.add(cn);
        this._rowElem?.classList.toggle(cn, flag);
      });
    } else {
      if (this.classes === null) {
        return;
      }
      cnSet.forEach((cn) => {
        this.classes!.delete(cn);
        this._rowElem?.classList.toggle(cn, flag);
      });
      if (this.classes.size === 0) {
        this.classes = null;
      }
    }
  }

  /** Start editing this node's title. */
  startEditTitle(): void {
    this.tree._callMethod("edit.startEditTitle", this);
  }

  /**
   * Call `setExpanded()` on all descendant nodes.
   *
   * @param flag true to expand, false to collapse.
   * @param options Additional options.
   * @see {@link Wunderbaum.expandAll}
   * @see {@link WunderbaumNode.setExpanded}
   */
  async expandAll(flag: boolean = true, options?: ExpandAllOptions) {
    const tree = this.tree;
    const {
      collapseOthers,
      deep,
      depth,
      force,
      keepActiveNodeVisible = true,
      loadLazy,
      resetLazy,
    } = options ?? {};
    // limit expansion level to `depth` (or tree.minExpandLevel). Default: unlimited
    const treeLevel = this.tree.options.minExpandLevel || null; // 0 -> null
    const minLevel = depth ?? (force ? null : treeLevel);
    const expandOpts = {
      deep: deep,
      force: force,
      loadLazy: loadLazy,
      resetLazy: resetLazy,
      scrollIntoView: false, // don't scroll every node while iterating
    };

    this.logInfo(`expandAll(${flag}, depth=${depth}, minLevel=${minLevel})`);

    util.assert(
      !(flag && deep != null && !collapseOthers),
      "Expanding with `deep` option is not supported (implied by the `depth` option)."
    );

    // Expand all direct children in parallel:
    async function _iter(n: WunderbaumNode, level: number) {
      // n.logInfo(`  _iter(level=${level})`);
      const promises: Promise<unknown>[] = [];
      n.children?.forEach((cn) => {
        if (flag) {
          if (
            !cn.expanded &&
            (minLevel == null || level < minLevel) &&
            (cn.children || (loadLazy && cn.lazy))
          ) {
            // Node is collapsed and may be expanded (i.e. has children or is lazy)
            // Expanding may be async, so we store the promise.
            // Also the recursion is delayed until expansion finished.
            const p = cn.setExpanded(true, expandOpts);
            promises.push(p);
            if (depth == null) {
              p.then(async () => {
                await _iter(cn, level + 1);
              });
            }
          } else {
            // We don't expand the node, but still visit descendants.
            // There we may find lazy nodes, so we
            promises.push(_iter(cn, level + 1));
          }
        } else {
          // Collapsing is always synchronous, so no promises required
          // Do not collapse until minExpandLevel
          if (minLevel == null || level >= minLevel) {
            cn.setExpanded(false, expandOpts);
          }
          if ((minLevel != null && level < minLevel) || deep) {
            _iter(cn, level + 1); // recursion, even if cn was already collapsed
          }
        }
      });
      return new Promise((resolve) => {
        Promise.all(promises).then(() => {
          resolve(true);
        });
      });
    }

    const tag = tree.logTime(`${this}.expandAll(${flag}, depth=${depth})`);
    try {
      tree.enableUpdate(false);

      await _iter(this, 0);

      if (collapseOthers) {
        util.assert(flag, "Option `collapseOthers` requires flag=true");
        util.assert(
          minLevel != null,
          "Option `collapseOthers` requires `depth` or `minExpandLevel`"
        );
        this.expandAll(false, { depth: minLevel! });
      }
    } finally {
      tree.enableUpdate(true);
      tree.logTimeEnd(tag);
    }
    if (tree.activeNode && keepActiveNodeVisible) {
      tree.activeNode.scrollIntoView();
    }
  }

  /**
   * Find all descendant nodes that match condition (excluding self).
   *
   * If `match` is a string, search for exact node title.
   * If `match` is a RegExp expression, apply it to node.title, using
   * [RegExp.test()](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/RegExp/test).
   * If `match` is a callback, match all nodes for that the callback(node) returns true.
   *
   * Returns an empty array if no nodes were found.
   *
   * Examples:
   * ```js
   * // Match all node titles that match exactly 'Joe':
   * nodeList = node.findAll("Joe")
   * // Match all node titles that start with 'Joe' case sensitive:
   * nodeList = node.findAll(/^Joe/)
   * // Match all node titles that contain 'oe', case insensitive:
   * nodeList = node.findAll(/oe/i)
   * // Match all nodes with `data.price` >= 99:
   * nodeList = node.findAll((n) => {
   *   return n.data.price >= 99;
   * })
   * ```
   */
  findAll(match: string | RegExp | MatcherCallback): WunderbaumNode[] {
    const matcher =
      typeof match === "function" ? match : makeNodeTitleMatcher(match);
    const res: WunderbaumNode[] = [];
    this.visit((n) => {
      if (matcher(n)) {
        res.push(n);
      }
    });
    return res;
  }

  /** Return the direct child with a given key, index or null. */
  findDirectChild(
    ptr: number | string | WunderbaumNode
  ): WunderbaumNode | null {
    const cl = this.children;

    if (!cl) {
      return null;
    }
    if (typeof ptr === "string") {
      for (let i = 0, l = cl.length; i < l; i++) {
        if (cl[i].key === ptr) {
          return cl[i];
        }
      }
    } else if (typeof ptr === "number") {
      return cl[ptr];
    } else if (ptr.parent === this) {
      // Return null if `ptr` is not a direct child
      return ptr;
    }
    return null;
  }

  /**
   * Find first descendant node that matches condition (excluding self) or null.
   *
   * @see {@link WunderbaumNode.findAll} for examples.
   */
  findFirst(match: string | RegExp | MatcherCallback): WunderbaumNode | null {
    const matcher =
      typeof match === "function" ? match : makeNodeTitleMatcher(match);
    let res = null;
    this.visit((n) => {
      if (matcher(n)) {
        res = n;
        return false;
      }
    });
    return res;
  }

  /** Find a node relative to self.
   *
   * @see {@link Wunderbaum.findRelatedNode|tree.findRelatedNode()}
   */
  findRelatedNode(where: NavigationType, includeHidden = false) {
    return this.tree.findRelatedNode(this, where, includeHidden);
  }

  /**
   * Iterator version of {@link WunderbaumNode.format}.
   */
  *format_iter(
    name_cb?: NodeStringCallback,
    connectors?: string[]
  ): IterableIterator<string> {
    connectors ??= ["    ", " |  ", " ╰─ ", " ├─ "];
    name_cb ??= (node: WunderbaumNode) => "" + node;

    function _is_last(node: WunderbaumNode): boolean {
      const ca = node.parent.children!;
      return node === ca[ca.length - 1];
    }

    const _format_line = (node: WunderbaumNode) => {
      // https://www.measurethat.net/Benchmarks/Show/12196/0/arr-unshift-vs-push-reverse-small-array
      const parts = [name_cb!(node)];
      parts.unshift(connectors![_is_last(node) ? 2 : 3]);
      let p = node.parent;
      while (p && p !== this) {
        // `this` is the top node
        parts.unshift(connectors![_is_last(p) ? 0 : 1]);
        p = p.parent;
      }
      return parts.join("");
    };

    yield name_cb(this);
    for (const node of this) {
      yield _format_line(node);
    }
  }

  /**
   * Return a multiline string representation of a node/subnode hierarchy.
   * Mostly useful for debugging.
   *
   * Example:
   * ```js
   * console.info(tree.getActiveNode().format((n)=>n.title));
   * ```
   * logs
   * ```
   * Books
   *  ├─ Art of War
   *  ╰─ Don Quixote
   * ```
   * @see {@link WunderbaumNode.format_iter}
   */
  format(name_cb?: NodeStringCallback, connectors?: string[]): string {
    const a = [];
    for (const line of this.format_iter(name_cb, connectors)) {
      a.push(line);
    }
    return a.join("\n");
  }

  /** Return the `<span class='wb-col'>` element with a given index or id.
   * @returns {WunderbaumNode | null}
   */
  getColElem(colIdx: number | string) {
    if (typeof colIdx === "string") {
      colIdx = this.tree.columns.findIndex((value) => value.id === colIdx);
    }
    const colElems = this._rowElem?.querySelectorAll("span.wb-col");
    return colElems ? (colElems[colIdx] as HTMLSpanElement) : null;
  }
  /**
   * Return all nodes with the same refKey.
   *
   * @param includeSelf Include this node itself.
   * @see {@link Wunderbaum.findByRefKey}
   */
  getCloneList(includeSelf = false): WunderbaumNode[] {
    if (!this.refKey) {
      return [];
    }
    const clones = this.tree.findByRefKey(this.refKey);
    if (includeSelf) {
      return clones;
    }
    return [...clones].filter((n) => n !== this);
  }

  /** Return the first child node or null.
   * @returns {WunderbaumNode | null}
   */
  getFirstChild() {
    return this.children ? this.children[0] : null;
  }

  /** Return the last child node or null.
   * @returns {WunderbaumNode | null}
   */
  getLastChild() {
    return this.children ? this.children[this.children.length - 1] : null;
  }

  /** Return node depth (starting with 1 for top level nodes). */
  getLevel(): number {
    let i = 0,
      p = this.parent;

    while (p) {
      i++;
      p = p.parent;
    }
    return i;
  }

  /** Return the successive node (under the same parent) or null. */
  getNextSibling(): WunderbaumNode | null {
    const ac = this.parent.children!;
    const idx = ac.indexOf(this);
    return ac[idx + 1] || null;
  }

  /** Return the parent node (null for the system root node). */
  getParent(): WunderbaumNode | null {
    // TODO: return null for top-level nodes?
    return this.parent;
  }

  /** Return an array of all parent nodes (top-down).
   * @param includeRoot Include the invisible system root node.
   * @param includeSelf Include the node itself.
   */
  getParentList(includeRoot = false, includeSelf = false) {
    const l = [];
    let dtn = includeSelf ? this : this.parent;
    while (dtn) {
      if (includeRoot || dtn.parent) {
        l.unshift(dtn);
      }
      dtn = dtn.parent;
    }
    return l;
  }
  /** Return a string representing the hierarchical node path, e.g. "a/b/c".
   * @param includeSelf
   * @param part property name or callback
   * @param separator
   */
  getPath(
    includeSelf: boolean = true,
    part: keyof WunderbaumNode | NodeAnyCallback = "title",
    separator: string = "/"
  ) {
    let val;
    const path: string[] = [];
    const isFunc = typeof part === "function";

    this.visitParents((n) => {
      if (n.parent) {
        val = isFunc
          ? (<NodeAnyCallback>part)(n)
          : n[<keyof WunderbaumNode>part];
        path.unshift(val);
      }
      return undefined; // TODO remove this line
    }, includeSelf);
    return path.join(separator);
  }

  /** Return the preceding node (under the same parent) or null. */
  getPrevSibling(): WunderbaumNode | null {
    const ac = this.parent.children!;
    const idx = ac.indexOf(this);
    return ac[idx - 1] || null;
  }

  /** Return true if node has children.
   * Return undefined if not sure, i.e. the node is lazy and not yet loaded.
   */
  hasChildren() {
    if (this.lazy) {
      if (this.children == null) {
        return undefined; // null or undefined: Not yet loaded
      } else if (this.children.length === 0) {
        return false; // Loaded, but response was empty
      } else if (
        this.children.length === 1 &&
        this.children[0].isStatusNode()
      ) {
        return undefined; // Currently loading or load error
      }
      return true; // One or more child nodes
    }
    return !!(this.children && this.children.length);
  }

  /** Return true if node has className set. */
  hasClass(className: string): boolean {
    return this.classes ? this.classes.has(className) : false;
  }

  /** Return true if node is the currently focused node. @since 0.9.0 */
  hasFocus(): boolean {
    return this.tree.focusNode === this;
  }

  /** Return true if this node is the currently active tree node. */
  isActive() {
    return this.tree.activeNode === this;
  }

  /** Return true if this node is a direct or indirect parent of `other`.
   * @see {@link WunderbaumNode.isParentOf}
   */
  isAncestorOf(other: WunderbaumNode) {
    return other && other.isDescendantOf(this);
  }

  /** Return true if this node is a **direct** subnode of `other`.
   * @see {@link WunderbaumNode.isDescendantOf}
   */
  isChildOf(other: WunderbaumNode) {
    return other && this.parent === other;
  }

  /** Return true if this node's refKey is used by at least one other node.
   */
  isClone() {
    return !!this.refKey && this.tree.findByRefKey(this.refKey).length > 1;
  }
  /** Return true if this node's title spans all columns, i.e. the node has no
   * grid cells.
   */
  isColspan() {
    return !!this.getOption("colspan");
  }

  /** Return true if this node is a direct or indirect subnode of `other`.
   * @see {@link WunderbaumNode.isChildOf}
   */
  isDescendantOf(other: WunderbaumNode) {
    if (!other || other.tree !== this.tree) {
      return false;
    }
    let p = this.parent;
    while (p) {
      if (p === other) {
        return true;
      }
      if (p === p.parent) {
        util.error(`Recursive parent link: ${p}`);
      }
      p = p.parent;
    }
    return false;
  }

  /** Return true if this node has children, i.e. the node is generally expandable.
   * If `andCollapsed` is set, we also check if this node is collapsed, i.e.
   * an expand operation is currently possible.
   */
  isExpandable(andCollapsed = false): boolean {
    // `false` is never expandable (unofficial)
    if ((andCollapsed && this.expanded) || <any>this.children === false) {
      return false;
    }
    if (this.children == null) {
      return !!this.lazy; // null or undefined can trigger lazy load
    }
    if (this.children.length === 0) {
      return !!this.tree.options.emptyChildListExpandable;
    }
    return true;
  }

  /** Return true if _this_ node is currently in edit-title mode.
   *
   * See {@link WunderbaumNode.startEditTitle}.
   */
  isEditingTitle(): boolean {
    return this.tree._callMethod("edit.isEditingTitle", this);
  }

  /** Return true if this node is currently expanded. */
  isExpanded(): boolean {
    return !!this.expanded;
  }

  /** Return true if this node is the first node of its parent's children. */
  isFirstSibling(): boolean {
    const p = this.parent;
    return !p || p.children![0] === this;
  }

  /** Return true if this node is the last node of its parent's children. */
  isLastSibling(): boolean {
    const p = this.parent;
    return !p || p.children![p.children!.length - 1] === this;
  }

  /** Return true if this node is lazy (even if data was already loaded) */
  isLazy(): boolean {
    return !!this.lazy;
  }

  /** Return true if node is lazy and loaded. For non-lazy nodes always return true. */
  isLoaded(): boolean {
    return !this.lazy || this.hasChildren() !== undefined; // Also checks if the only child is a status node
  }

  /** Return true if node is currently loading, i.e. a GET request is pending. */
  isLoading(): boolean {
    return this._isLoading;
  }

  /** Return true if this node is a temporarily generated status node of type 'paging'. */
  isPagingNode(): boolean {
    return this.statusNodeType === "paging";
  }

  /** Return true if this node is a **direct** parent of `other`.
   * @see {@link WunderbaumNode.isAncestorOf}
   */
  isParentOf(other: WunderbaumNode) {
    return other && other.parent === this;
  }

  /** Return true if this node is partially loaded. @experimental  */
  isPartload(): boolean {
    return !!this._partload;
  }

  /** Return true if this node is partially selected (tri-state). */
  isPartsel(): boolean {
    return !this.selected && !!this._partsel;
  }

  /** Return true if this node has DOM representation, i.e. is displayed in the viewport. */
  isRadio(): boolean {
    return !!this.parent.radiogroup || this.getOption("checkbox") === "radio";
  }

  /** Return true if this node has DOM representation, i.e. is displayed in the viewport. */
  isRendered(): boolean {
    return !!this._rowElem;
  }

  /** Return true if this node is the (invisible) system root node.
   * @see {@link WunderbaumNode.isTopLevel}
   */
  isRootNode(): boolean {
    return this.tree.root === this;
  }

  /** Return true if this node is selected, i.e. the checkbox is set.
   * `undefined` if partly selected (tri-state), false otherwise.
   */
  isSelected(): TristateType {
    return this.selected ? true : this._partsel ? undefined : false;
  }

  /** Return true if this node is a temporarily generated system node like
   * 'loading', 'paging', or 'error' (node.statusNodeType contains the type).
   */
  isStatusNode(): boolean {
    return !!this.statusNodeType;
  }

  /** Return true if this a top level node, i.e. a direct child of the (invisible) system root node. */
  isTopLevel(): boolean {
    return this.tree.root === this.parent;
  }

  /** Return true if node is marked lazy but not yet loaded.
   * For non-lazy nodes always return false.
   */
  isUnloaded(): boolean {
    // Also checks if the only child is a status node:
    return this.hasChildren() === undefined;
  }

  /** Return true if all parent nodes are expanded. Note: this does not check
   * whether the node is scrolled into the visible part of the screen or viewport.
   */
  isVisible(): boolean {
    const hasFilter = this.tree.filterMode === "hide";
    const parents = this.getParentList(false, false);

    // TODO: check $(n.span).is(":visible")
    // i.e. return false for nodes (but not parents) that are hidden
    // by a filter
    if (hasFilter && !this.match && !this.subMatchCount) {
      // this.debug( "isVisible: HIDDEN (" + hasFilter + ", " + this.match + ", " + this.match + ")" );
      return false;
    }

    for (let i = 0, l = parents.length; i < l; i++) {
      const n = parents[i];

      if (!n.expanded) {
        // this.debug("isVisible: HIDDEN (parent collapsed)");
        return false;
      }
      // if (hasFilter && !n.match && !n.subMatchCount) {
      // 	this.debug("isVisible: HIDDEN (" + hasFilter + ", " + this.match + ", " + this.match + ")");
      // 	return false;
      // }
    }
    // this.debug("isVisible: VISIBLE");
    return true;
  }

  protected _loadSourceObject(source: any, level?: number) {
    const tree = this.tree;

    level ??= this.getLevel();

    // Let caller modify the parsed JSON response:
    const res = this._callEvent("receive", { response: source });
    if (res != null) {
      source = res;
    }

    if (util.isArray(source)) {
      source = { children: source };
    }
    util.assert(
      util.isPlainObject(source),
      `Expected an array or plain object: ${source}`
    );

    const format: string = source.format ?? "nested";
    util.assert(
      format === "nested" || format === "flat",
      `Expected source.format = 'nested' or 'flat': ${format}`
    );

    // Pre-rocess for 'nested' or 'flat' format
    decompressSourceData(source);

    util.assert(
      source.children,
      "If `source` is an object, it must have a `children` property"
    );
    if (source.types) {
      tree.logInfo("Redefine types", source.columns);
      tree.setTypes(source.types, false);
      delete source.types;
    }
    if (source.columns) {
      tree.logInfo("Redefine columns", source.columns);
      tree.columns = source.columns;
      delete source.columns;
      tree.update(ChangeType.colStructure);
    }

    this.addChildren(source.children);

    // Add extra data to `tree.data`
    for (const [key, value] of Object.entries(source)) {
      if (!RESERVED_TREE_SOURCE_KEYS.has(key)) {
        tree.data[key] = value;
        // tree.logDebug(`Add source.${key} to tree.data.${key}`);
      }
    }
    if (tree.options.selectMode === "hier") {
      this.fixSelection3FromEndNodes();
    }
    // Allow to un-sort nodes after sorting
    this.resetNativeChildOrder();

    this._callEvent("load");
  }

  async _fetchWithOptions(source: any) {
    // Either a URL string or an object with a `.url` property.
    let url: string, params, body, options, rest;
    let fetchOpts: RequestInit = {};
    if (typeof source === "string") {
      // source is a plain URL string: assume GET request
      url = source;
      fetchOpts.method = "GET";
    } else if (util.isPlainObject(source)) {
      // source is a plain object with `.url` property.
      ({ url, params, body, options, ...rest } = source);
      util.assert(
        !rest || Object.keys(rest).length === 0,
        `Unexpected source properties: ${Object.keys(
          rest
        )}. Use 'options' instead.`
      );
      util.assert(typeof url === "string", `expected source.url as string`);
      if (util.isPlainObject(options)) {
        fetchOpts = options;
      }
      if (util.isPlainObject(body)) {
        // we also accept 'body' as object...
        util.assert(
          !fetchOpts.body,
          "options.body should be passed as source.body"
        );
        fetchOpts.body = JSON.stringify(fetchOpts.body);
        fetchOpts.method ??= "POST"; // set default
      }
      if (util.isPlainObject(params)) {
        url += "?" + new URLSearchParams(params);
        fetchOpts.method ??= "GET"; // set default
      }
    } else {
      url = ""; // keep linter happy
      util.error(`Unsupported source format: ${source}`);
    }
    this.setStatus(NodeStatusType.loading);
    const response = await fetch(url, fetchOpts);
    if (!response.ok) {
      util.error(`GET ${url} returned ${response.status}, ${response}`);
    }
    return await response.json();
  }
  /** Download  data from the cloud, then call `.update()`. */
  async load(source: SourceType) {
    const tree = this.tree;
    const requestId = Date.now();
    const prevParent = this.parent;
    const start = Date.now();
    let elap = 0,
      elapLoad = 0,
      elapProcess = 0;

    // Check for overlapping requests
    if (this._requestId) {
      this.logWarn(
        `Recursive load request #${requestId} while #${this._requestId} is pending. ` +
          "The previous request will be ignored."
      );
    }
    this._requestId = requestId;

    // const timerLabel = tree.logTime(this + ".load()");

    try {
      const url: string =
        typeof source === "string" ? source : (<any>source).url;
      if (!url) {
        // An array or a plain object (that does NOT contain a `.url` property)
        // will be treated as native Wunderbaum data
        if (typeof (<any>source).then === "function") {
          const msg = tree.logTime(`Resolve thenable ${source}`);
          source = await Promise.resolve(source);
          tree.logTimeEnd(msg);
        }
        this._loadSourceObject(source);
        elapProcess = Date.now() - start;
      } else {
        // Either a URL string or an object with a `.url` property.
        const data = await this._fetchWithOptions(source);

        elapLoad = Date.now() - start;

        if (this._requestId && this._requestId > requestId) {
          this.logWarn(
            `Ignored load response #${requestId} because #${this._requestId} is pending.`
          );
          return;
        } else {
          this.logDebug(`Received response for load request #${requestId}`);
        }
        if (this.parent === null && prevParent !== null) {
          this.logWarn(
            "Lazy parent node was removed while loading: discarding response."
          );
          return;
        }
        this.setStatus(NodeStatusType.ok);
        // if (data.columns) {
        //   tree.logInfo("Re-define columns", data.columns);
        //   util.assert(!this.parent);
        //   tree.columns = data.columns;
        //   delete data.columns;
        //   tree.updateColumns({ calculateCols: false });
        // }
        const startProcess = Date.now();
        this._loadSourceObject(data);
        elapProcess = Date.now() - startProcess;
      }
    } catch (error) {
      this.logError("Error during load()", source, error);
      this._callEvent("error", { error: error });
      this.setStatus(NodeStatusType.error, { message: "" + error });
      throw error;
    } finally {
      this._requestId = 0;
      elap = Date.now() - start;
      if (tree.options.debugLevel! >= 3) {
        tree.logInfo(
          `Load source took ${elap / 1000} seconds ` +
            `(transfer: ${elapLoad / 1000}s, ` +
            `processing: ${elapProcess / 1000}s)`
        );
      }
    }
  }

  /**
   * Load content of a lazy node.
   * If the node is already loaded, nothing happens.
   * @param [forceReload=false] If true, reload even if already loaded.
   */
  async loadLazy(forceReload: boolean = false) {
    const wasExpanded = this.expanded;

    util.assert(this.lazy, "load() requires a lazy node");

    if (!forceReload && !this.isUnloaded()) {
      return; // Already loaded: nothing to do
    }
    if (this.isLoading()) {
      this.logWarn("loadLazy() called while already loading: ignored.");
      return; // Already loading: prevent duplicate requests
    }
    if (this.isLoaded()) {
      this.resetLazy(); // Also collapses if currently expanded
    }
    // `lazyLoad` may be long-running, so mark node as loading now. `this.load()`
    // will reset the status later.
    this.setStatus(NodeStatusType.loading);
    try {
      const source = await this._callEvent("lazyLoad");
      if (source === false) {
        this.setStatus(NodeStatusType.ok);
        return;
      }
      util.assert(
        util.isArray(source) || (source && source.url),
        "The lazyLoad event must return a node list, `{url: ...}`, or false."
      );

      await this.load(source);

      this.setStatus(NodeStatusType.ok); // Also resets `this._isLoading`

      if (wasExpanded) {
        this.expanded = true;
        this.tree.update(ChangeType.structure);
      } else {
        this.update(); // Fix expander icon to 'loaded'
      }
    } catch (e) {
      this.logError("Error during loadLazy()", e);
      this._callEvent("error", { error: e });
      // Also resets `this._isLoading`:
      this.setStatus(NodeStatusType.error, { message: "" + e });
    }
    return;
  }

  /** Write to `console.log` with node name as prefix if opts.debugLevel >= 4.
   * @see {@link WunderbaumNode.logDebug}
   */
  log(...args: any[]) {
    if (this.tree.options.debugLevel! >= 4) {
      console.log(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Write to `console.debug` with node name as prefix if opts.debugLevel >= 4
   * and browser console level includes debug/verbose messages.
   * @see {@link WunderbaumNode.log}
   */
  logDebug(...args: any[]) {
    if (this.tree.options.debugLevel! >= 4) {
      console.debug(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Write to `console.error` with node name as prefix if opts.debugLevel >= 1. */
  logError(...args: any[]) {
    if (this.tree.options.debugLevel! >= 1) {
      console.error(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Write to `console.info` with node name as prefix if opts.debugLevel >= 3. */
  logInfo(...args: any[]) {
    if (this.tree.options.debugLevel! >= 3) {
      console.info(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Write to `console.warn` with node name as prefix if opts.debugLevel >= 2. */
  logWarn(...args: any[]) {
    if (this.tree.options.debugLevel! >= 2) {
      console.warn(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Expand all parents and optionally scroll into visible area as neccessary.
   * Promise is resolved, when lazy loading and animations are done.
   * @param {object} [options] passed to `setExpanded()`.
   *     Defaults to {noAnimation: false, noEvents: false, scrollIntoView: true}
   */
  async makeVisible(options?: MakeVisibleOptions) {
    let i;
    const dfd = new Deferred();
    const deferreds = [];
    const parents = this.getParentList(false, false);
    const len = parents.length;
    const noAnimation = util.getOption(options, "noAnimation", false);
    const scroll = util.getOption(options, "scrollIntoView", true);

    // Expand bottom-up, so only the top node is animated
    for (i = len - 1; i >= 0; i--) {
      // self.debug("pushexpand" + parents[i]);
      const seOpts = { noAnimation: noAnimation };
      deferreds.push(parents[i].setExpanded(true, seOpts));
    }
    Promise.all(deferreds).then(() => {
      // All expands have finished
      // self.debug("expand DONE", scroll);
      // Note: this.tree may be none when switching demo trees
      if (scroll && this.tree) {
        // Make sure markup and _rowIdx is updated before we do the scroll calculations
        this.tree.updatePendingModifications();
        this.scrollIntoView().then(() => {
          // self.debug("scroll DONE");
          dfd.resolve();
        });
      } else {
        dfd.resolve();
      }
    });
    return dfd.promise();
  }

  /** Move this node to targetNode. */
  moveTo(
    targetNode: WunderbaumNode,
    mode: InsertNodeType = "appendChild",
    map?: NodeAnyCallback
  ) {
    if (<string>mode === "over") {
      mode = "appendChild"; // compatible with drop region
    }
    if (mode === "prependChild") {
      if (targetNode.children && targetNode.children.length) {
        mode = "before";
        targetNode = targetNode.children[0];
      } else {
        mode = "appendChild";
      }
    }
    let pos;
    const tree = this.tree;
    const prevParent = this.parent;
    const targetParent =
      mode === "appendChild" ? targetNode : targetNode.parent;

    if (this === targetNode) {
      return;
    } else if (!this.parent) {
      util.error("Cannot move system root");
    } else if (targetParent.isDescendantOf(this)) {
      util.error("Cannot move a node to its own descendant");
    }
    if (targetParent !== prevParent) {
      prevParent.triggerModifyChild("remove", this);
    }
    // Unlink this node from current parent
    if (this.parent.children!.length === 1) {
      if (this.parent === targetParent) {
        return; // #258
      }
      this.parent.children = this.parent.lazy ? [] : null;
      this.parent.expanded = false;
    } else {
      pos = this.parent.children!.indexOf(this);
      util.assert(pos >= 0, "invalid source parent");
      this.parent.children!.splice(pos, 1);
    }

    // Insert this node to target parent's child list
    this.parent = targetParent;
    if (targetParent.hasChildren()) {
      switch (mode) {
        case "appendChild":
          // Append to existing target children
          targetParent.children!.push(this);
          break;
        case "before":
          // Insert this node before target node
          pos = targetParent.children!.indexOf(targetNode);
          util.assert(pos >= 0, "invalid target parent");
          targetParent.children!.splice(pos, 0, this);
          break;
        case "after":
          // Insert this node after target node
          pos = targetParent.children!.indexOf(targetNode);
          util.assert(pos >= 0, "invalid target parent");
          targetParent.children!.splice(pos + 1, 0, this);
          break;
        default:
          util.error(`Invalid mode '${mode}'.`);
      }
    } else {
      targetParent.children = [this];
    }

    // Let caller modify the nodes
    if (map) {
      targetNode.visit(map, true);
    }
    if (targetParent === prevParent) {
      targetParent.triggerModifyChild("move", this);
    } else {
      // prevParent.triggerModifyChild("remove", this);
      targetParent.triggerModifyChild("add", this);
    }
    // Handle cross-tree moves
    if (tree !== targetNode.tree) {
      // Fix node.tree for all source nodes
      // 	util.assert(false, "Cross-tree move is not yet implemented.");
      this.logWarn("Cross-tree moveTo is experimental!");
      this.visit((n) => {
        // TODO: fix selection state and activation, ...
        n.tree = targetNode.tree;
      }, true);
    }
    // Make sure we update async, because discarding the markup would prevent
    // DragAndDrop to generate a dragend event on the source node
    setTimeout(() => {
      // Even indentation may have changed:
      tree.update(ChangeType.any);
    }, 0);
    // TODO: fix selection state
    // TODO: fix active state
  }

  /** Set focus relative to this node and optionally activate.
   *
   * 'left' collapses the node if it is expanded, or move to the parent
   * otherwise.
   * 'right' expands the node if it is collapsed, or move to the first
   * child otherwise.
   *
   * @param where 'down', 'first', 'last', 'left', 'parent', 'right', or 'up'.
   *   (Alternatively the `event.key` that would normally trigger this move,
   *   e.g. `ArrowLeft` = 'left'.
   * @param options
   */
  async navigate(where: NavigationType | string, options?: NavigateOptions) {
    // Allow to pass 'ArrowLeft' instead of 'left'
    const navType = (KEY_TO_NAVIGATION_MAP[where] ?? where) as NavigationType;

    // Otherwise activate or focus the related node
    const node = this.findRelatedNode(navType);
    if (!node) {
      this.logWarn(`Could not find related node '${where}'.`);
      return Promise.resolve(this);
    }
    // setFocus/setActive will scroll later (if autoScroll is specified)
    try {
      node.makeVisible({ scrollIntoView: false });
    } catch (e) {
      // ignore
    }
    node.setFocus();
    if (options?.activate === false) {
      return Promise.resolve(this);
    }
    return node.setActive(true, { event: options?.event });
  }

  /** Delete this node and all descendants. */
  remove() {
    const tree = this.tree;
    const pos = this.parent.children!.indexOf(this);
    // Like removeChildren(): don't keep a reference to a removed node (e.g. a
    // paging node that was clicked and is replaced by the next page)
    const isRemoved = (n: WunderbaumNode | null) =>
      !!n && (n === this || n.isDescendantOf(this));
    if (isRemoved(tree.activeNode)) {
      tree._setActiveNode(null);
    }
    if (isRemoved(tree.focusNode)) {
      tree._setFocusNode(null);
    }
    this.triggerModify("remove");
    this.parent.children!.splice(pos, 1);
    this.visit((n) => {
      n.removeMarkup();
      tree._unregisterNode(n);
    }, true);
    tree.update(ChangeType.structure);
  }

  /** Remove all descendants of this node. */
  removeChildren() {
    const tree = this.tree;

    if (!this.children) {
      return;
    }
    if (tree.activeNode?.isDescendantOf(this)) {
      tree.activeNode.setActive(false); // TODO: don't fire events
    }
    if (tree.focusNode?.isDescendantOf(this)) {
      tree._setFocusNode(null);
    }
    // TODO: persist must take care to clear select and expand cookies
    // Unlink children to support GC
    // TODO: also delete this.children (not possible using visit())
    this.triggerModifyChild("remove", null);
    this.visit((n) => {
      tree._unregisterNode(n);
    });
    if (this.lazy) {
      // 'undefined' would be interpreted as 'not yet loaded' for lazy nodes
      this.children = [];
    } else {
      this.children = null;
    }
    // util.assert(this.parent); // don't call this for root node
    if (!this.isRootNode()) {
      this.expanded = false;
    }
    this.tree.update(ChangeType.structure);
  }

  /** Remove all HTML markup from the DOM. */
  removeMarkup() {
    if (this._rowElem) {
      // Don't leave `aria-activedescendant` pointing to a removed row.
      // (`this.tree` may already be reset, see `_unregisterNode()`.)
      const container = this._rowElem.closest("[aria-activedescendant]");
      if (
        container?.getAttribute("aria-activedescendant") === this._rowElem.id
      ) {
        container.removeAttribute("aria-activedescendant");
      }
      delete (<any>this._rowElem)._wb_node;
      this._rowElem.remove();
      this._rowElem = undefined;
    }
  }

  protected _getRenderInfo(): any {
    const allColInfosById: ColumnEventInfoMap = {};
    const renderColInfosById: ColumnEventInfoMap = {};
    const isColspan = this.isColspan();
    const colElems = this._rowElem
      ? ((<unknown>(
          this._rowElem.querySelectorAll("span.wb-col")
        )) as HTMLSpanElement[])
      : null;

    let idx = 0;
    for (const col of this.tree.columns) {
      allColInfosById[col.id] = {
        id: col.id,
        idx: idx,
        elem: colElems ? colElems[idx] : null,
        info: col,
      };
      // renderColInfosById only contains columns that need rendering:
      if (!isColspan && col.id !== "*") {
        renderColInfosById[col.id] = allColInfosById[col.id];
      }
      idx++;
    }
    return {
      allColInfosById: allColInfosById,
      renderColInfosById: renderColInfosById,
    };
  }

  protected _createIcon(
    parentElem: HTMLElement,
    replaceChild: HTMLElement | null,
    showLoading: boolean
  ): HTMLElement | null {
    const iconElem = this.tree._createNodeIcon(this, showLoading, true);
    if (iconElem) {
      if (replaceChild) {
        parentElem.replaceChild(iconElem, replaceChild);
      } else {
        parentElem.appendChild(iconElem);
      }
    }
    return iconElem;
  }

  /**
   * Create a whole new `<div class="wb-row">` element.
   * @see {@link WunderbaumNode._render}
   */
  protected _render_markup(opts: RenderOptions) {
    const tree = this.tree;
    const treeOptions = tree.options;
    const rowHeight = treeOptions.rowHeightPx;
    const checkbox = this.getOption("checkbox");
    const columns = tree.columns;
    const level = this.getLevel();
    const activeColIdx = tree.isRowNav() ? null : tree.activeColIdx;

    let elem: HTMLElement;
    let rowDiv = this._rowElem;
    let checkboxSpan: HTMLElement | null = null;
    let expanderSpan: HTMLElement | null = null;

    const isNew = !rowDiv;
    util.assert(isNew, "Expected unrendered node");
    util.assert(
      !isNew || (opts && opts.after),
      "opts.after expected, unless updating"
    );
    util.assert(!this.isRootNode(), "Root node not allowed");

    rowDiv = document.createElement("div");
    rowDiv.classList.add("wb-row");
    rowDiv.setAttribute("role", "row");
    // Referenced by the container's `aria-activedescendant` (no whitespace)
    rowDiv.id = `${tree.id}-row-${encodeURIComponent(this.key)}`;

    rowDiv.style.top = this._rowIdx! * rowHeight + "px";

    this._rowElem = rowDiv;

    // Attach a node reference to the DOM Element:
    (<any>rowDiv)._wb_node = this;

    const nodeElem: HTMLSpanElement = document.createElement("span");
    nodeElem.classList.add("wb-node", "wb-col");
    nodeElem.setAttribute("role", "gridcell");
    nodeElem.setAttribute("aria-colindex", "1");
    if (this.isColspan() && columns.length > 1) {
      nodeElem.setAttribute("aria-colspan", "" + columns.length);
    }
    rowDiv.appendChild(nodeElem);

    let ofsTitlePx = 0;

    if (checkbox) {
      checkboxSpan = document.createElement("i");
      checkboxSpan.classList.add("wb-checkbox");
      checkboxSpan.setAttribute("aria-hidden", "true");
      if (checkbox === "radio" || this.parent.radiogroup) {
        checkboxSpan.classList.add("wb-radio");
      }
      nodeElem.appendChild(checkboxSpan);
      ofsTitlePx += ICON_WIDTH;
    }

    for (let i = level - 1; i > 0; i--) {
      elem = document.createElement("i");
      elem.classList.add("wb-indent");
      elem.setAttribute("aria-hidden", "true");
      nodeElem.appendChild(elem);
      ofsTitlePx += ICON_WIDTH;
    }

    if (!treeOptions.minExpandLevel || level > treeOptions.minExpandLevel) {
      expanderSpan = document.createElement("i");
      expanderSpan.classList.add("wb-expander");
      expanderSpan.setAttribute("aria-hidden", "true");
      nodeElem.appendChild(expanderSpan);
      ofsTitlePx += ICON_WIDTH;
    }

    // Render the icon (show a 'loading' icon if we do not have an expander that
    // we would prefer).
    const iconSpan = this._createIcon(nodeElem, null, !expanderSpan);
    if (iconSpan) {
      ofsTitlePx += ICON_WIDTH;
    }

    const titleSpan = document.createElement("span");
    titleSpan.classList.add("wb-title");
    nodeElem.appendChild(titleSpan);

    // this._callEvent("enhanceTitle", { titleSpan: titleSpan });

    // Store the width of leading icons with the node, so we can calculate
    // the width of the embedded title span later
    (<any>nodeElem)._ofsTitlePx = ofsTitlePx;

    // Support HTML5 drag-n-drop
    if (tree.options.dnd!.dragStart) {
      nodeElem.draggable = true;
    }

    // Render columns
    const isColspan = this.isColspan();

    if (!isColspan && columns.length > 1) {
      let colIdx = 0;
      for (const col of columns) {
        colIdx++;

        let colElem;
        if (col.id === "*") {
          colElem = nodeElem;
        } else {
          colElem = document.createElement("span");
          colElem.classList.add("wb-col");
          colElem.setAttribute("role", "gridcell");
          colElem.setAttribute("aria-colindex", "" + colIdx);
          rowDiv.appendChild(colElem);
        }
        if (colIdx === activeColIdx) {
          colElem.classList.add("wb-active");
        }
        // Add classes from `columns` definition to `<div.wb-col>` cells
        col.classes ? colElem.classList.add(...col.classes.split(" ")) : 0;

        colElem.style.left = col._ofsPx + "px";
        colElem.style.width = col._widthPx + "px";
        if (isNew && col.html) {
          if (typeof col.html === "string") {
            colElem.innerHTML = col.html;
          }
        }
      }
    }
    // Attach to DOM as late as possible
    const after = opts ? opts.after : "last";
    switch (after) {
      case "first":
        tree.nodeListElement.prepend(rowDiv);
        break;
      case "last":
        tree.nodeListElement.appendChild(rowDiv);
        break;
      default:
        opts.after.after(rowDiv);
    }
    // Now go on and fill in data and update classes
    opts.isNew = true;
    this._render_data(opts);
  }

  /**
   * Render `node.title`, `.icon` into an existing row.
   *
   * @see {@link WunderbaumNode._render}
   */
  protected _render_data(opts: RenderOptions) {
    util.assert(this._rowElem, "No _rowElem");

    const tree = this.tree;
    const treeOptions = tree.options;
    const rowDiv = this._rowElem!;
    const isNew = !!opts.isNew; // Called by _render_markup()?
    const preventScroll = !!opts.preventScroll;
    const columns = tree.columns;
    const isColspan = this.isColspan();

    // Row markup already exists
    const nodeElem = rowDiv.querySelector("span.wb-node") as HTMLSpanElement;
    const titleSpan = nodeElem.querySelector(
      "span.wb-title"
    ) as HTMLSpanElement;

    // Reading scrollTop forces a synchronous layout. Only do it when we need
    // to restore the position, otherwise rendering N rows costs N layouts.
    const scrollTop = preventScroll ? tree.element.scrollTop : 0;
    if (this.titleWithHighlight) {
      titleSpan.innerHTML = this.titleWithHighlight;
    } else {
      titleSpan.textContent = this.title; // TODO: this triggers scroll events
    }
    const tooltip = this.getOption("tooltip", false);
    if (tooltip) {
      titleSpan.title = tooltip === true ? this.title : tooltip;
    }

    // NOTE: At least on Safari, this render call triggers a scroll event
    // probably when a focused input is replaced.
    if (preventScroll) {
      tree.element.scrollTop = scrollTop;
    }

    // Set the width of the title span, so overflow ellipsis work
    if (!treeOptions.skeleton) {
      if (isColspan) {
        const vpWidth = tree.element.clientWidth;
        titleSpan.style.width =
          vpWidth - (<any>nodeElem)._ofsTitlePx - TITLE_SPAN_PAD_Y + "px";
      } else {
        titleSpan.style.width =
          columns[0]._widthPx! -
          (<any>nodeElem)._ofsTitlePx -
          TITLE_SPAN_PAD_Y +
          "px";
      }
    }

    // Update row classes
    opts.isDataChange = true;
    this._render_status(opts);

    // Let user modify the result
    if (this.statusNodeType) {
      this._callEvent("renderStatusNode", {
        isNew: isNew,
        nodeElem: nodeElem,
        isColspan: isColspan,
      });
    } else if (this.parent) {
      // Skip root node
      const renderInfo = this._getRenderInfo();

      this._callEvent("render", {
        isNew: isNew,
        nodeElem: nodeElem,
        isColspan: isColspan,
        allColInfosById: renderInfo.allColInfosById,
        renderColInfosById: renderInfo.renderColInfosById,
      });
    }
  }

  /**
   * Update the ARIA tree grid state of a rendered row: level, position in the
   * sibling set, expansion, selection, busy state and virtual row index.
   * @internal
   */
  protected _render_aria(rowDiv: HTMLElement) {
    const tree = this.tree;
    const setOrRemove = (name: string, value: string | null) =>
      value == null
        ? rowDiv.removeAttribute(name)
        : rowDiv.setAttribute(name, value);

    rowDiv.setAttribute("aria-level", "" + this.getLevel());
    rowDiv.setAttribute(
      "aria-rowindex",
      "" + (this._rowIdx! + 1 + tree._ariaHeaderRows)
    );
    if (this.statusNodeType) {
      setOrRemove("aria-setsize", null);
      setOrRemove("aria-posinset", null);
      setOrRemove("aria-expanded", null);
      setOrRemove("aria-selected", null);
    } else {
      // Compute the sibling positions once per parent and render pass.
      // Status nodes (loading, paging, ...) are not part of the sibling set.
      const parent = this.parent;
      if (
        parent._ariaSetPass !== tree._ariaRenderPass ||
        this._ariaPosInSet == null
      ) {
        let size = 0;
        for (const n of parent.children!) {
          n._ariaPosInSet = n.statusNodeType ? undefined : ++size;
        }
        parent._ariaSetSize = size;
        parent._ariaSetPass = tree._ariaRenderPass;
      }
      rowDiv.setAttribute("aria-setsize", "" + parent._ariaSetSize);
      rowDiv.setAttribute("aria-posinset", "" + this._ariaPosInSet);
      setOrRemove(
        "aria-expanded",
        this.isExpandable() ? "" + !!this.expanded : null
      );
      // Only expose the selection state if nodes can be selected
      setOrRemove(
        "aria-selected",
        this.selected || this.getOption("checkbox")
          ? "" + !!this.selected
          : null
      );
    }
    setOrRemove("aria-busy", this._isLoading ? "true" : null);
    if (this === tree.focusNode) {
      tree._updateActiveDescendant();
    }
  }

  /**
   * Update row classes to reflect active, focuses, etc.
   * @see {@link WunderbaumNode._render}
   */
  protected _render_status(opts: RenderOptions) {
    // this.log("_render_status", opts);
    const tree = this.tree;
    const iconMap = tree.iconMap;
    const treeOptions = tree.options;
    const typeInfo = this.type ? tree.types[this.type] : null;
    const rowDiv = this._rowElem!;

    // Row markup already exists
    const nodeSpan = rowDiv.querySelector("span.wb-node") as HTMLSpanElement;
    const expanderElem = nodeSpan.querySelector(
      "i.wb-expander"
    ) as HTMLLIElement;
    const checkboxElem = nodeSpan.querySelector(
      "i.wb-checkbox"
    ) as HTMLLIElement;

    const rowClasses = ["wb-row"];
    this.expanded ? rowClasses.push("wb-expanded") : 0;
    this.lazy ? rowClasses.push("wb-lazy") : 0;
    this.selected ? rowClasses.push("wb-selected") : 0;
    this._partsel ? rowClasses.push("wb-partsel") : 0;
    this === tree.activeNode ? rowClasses.push("wb-active") : 0;
    this === tree.focusNode ? rowClasses.push("wb-focus") : 0;
    this._errorInfo ? rowClasses.push("wb-error") : 0;
    this._isLoading ? rowClasses.push("wb-loading") : 0;
    this.isColspan() ? rowClasses.push("wb-colspan") : 0;
    this.statusNodeType
      ? rowClasses.push("wb-status-" + this.statusNodeType)
      : 0;

    this.match ? rowClasses.push("wb-match") : 0;
    this.subMatchCount ? rowClasses.push("wb-submatch") : 0;
    treeOptions.skeleton ? rowClasses.push("wb-skeleton") : 0;

    // Replace previous classes:
    rowDiv.className = rowClasses.join(" ");

    this._render_aria(rowDiv);

    // Add classes from `node.classes`
    this.classes ? rowDiv.classList.add(...this.classes) : 0;

    // Add classes from `tree.types[node.type]`
    if (typeInfo && typeInfo.classes) {
      rowDiv.classList.add(...typeInfo.classes);
    }

    if (expanderElem) {
      let image = null;
      if (this._isLoading) {
        image = iconMap.loading;
      } else if (this.isExpandable(false)) {
        if (this.expanded) {
          image = iconMap.expanderExpanded;
        } else {
          image = iconMap.expanderCollapsed;
        }
      } else if (this.lazy && this.children == null) {
        image = iconMap.expanderLazy;
      }

      if (image == null) {
        expanderElem.className = "wb-expander";
        expanderElem.classList.add("wb-indent");
      } else if (TEST_HTML.test(image)) {
        expanderElem.replaceWith(util.elemFromHtml(image));
      } else if (TEST_FILE_PATH.test(image)) {
        expanderElem.style.backgroundImage = `url('${image}')`;
      } else {
        expanderElem.className = "wb-expander " + image;
      }
    }
    if (checkboxElem) {
      let cbclass = "wb-checkbox ";
      if (this.isRadio()) {
        cbclass += "wb-radio ";
        if (this.selected) {
          cbclass += iconMap.radioChecked;
          // } else if (this._partsel) {
          //   cbclass += iconMap.radioUnknown;
        } else {
          cbclass += iconMap.radioUnchecked;
        }
      } else {
        if (this.selected) {
          cbclass += iconMap.checkChecked;
        } else if (this._partsel) {
          cbclass += iconMap.checkUnknown;
        } else {
          cbclass += iconMap.checkUnchecked;
        }
      }
      checkboxElem.className = cbclass;
    }
    // Fix active cell in cell-nav mode
    if (!opts.isNew) {
      let i = 0;
      for (const colSpan of rowDiv.children) {
        colSpan.classList.toggle("wb-active", i++ === tree.activeColIdx);
        colSpan.classList.remove("wb-error", "wb-invalid");
      }
      // Update icon (if not opts.isNew, which would rebuild markup anyway)
      const iconSpan = nodeSpan.querySelector("i.wb-icon") as HTMLElement;
      if (iconSpan) {
        this._createIcon(nodeSpan, iconSpan, !expanderElem);
      }
    }
    // Adjust column width
    if (opts.resizeCols !== false && !this.isColspan()) {
      const colElems = rowDiv.querySelectorAll("span.wb-col");
      let idx = 0;
      let ofs = 0;
      for (const colDef of this.tree.columns) {
        const colElem = colElems[idx] as HTMLSpanElement;
        colElem.style.left = `${ofs}px`;
        colElem.style.width = `${colDef._widthPx}px`;
        idx++;
        ofs += colDef._widthPx!;
      }
    }
  }

  /*
   * Create or update node's markup.
   *
   * `options.change` defaults to ChangeType.data, which updates the title,
   * icon, and status. It also triggers the `render` event, that lets the user
   * create or update the content of embeded cell elements.
   *
   * If only the status or other class-only modifications have changed,
   * `options.change` should be set to ChangeType.status instead for best
   * efficiency.
   *
   * Calling `update()` is almost always a better alternative.
   * @see {@link WunderbaumNode.update}
   */
  _render(options?: RenderOptions) {
    // this.log("render", options);
    const opts = Object.assign({ change: ChangeType.data }, options);
    if (!this._rowElem) {
      opts.change = ChangeType.row;
    }
    switch (opts.change) {
      case "status":
        this._render_status(opts);
        break;
      case "data":
        this._render_data(opts);
        break;
      case "row":
        // _rowElem is not yet created (asserted in _render_markup)
        this._render_markup(opts);
        break;
      default:
        util.error(`Invalid change type '${opts.change}'.`);
    }
  }

  /**
   * Remove all children, collapse, and set the lazy-flag, so that the lazyLoad
   * event is triggered on next expand.
   */
  resetLazy() {
    this.removeChildren();
    this.expanded = false;
    this.lazy = true;
    this.children = null;
    this.tree.update(ChangeType.structure);
  }

  /** Convert node (or whole branch) into a plain object.
   *
   * The result is compatible with node.addChildren().
   *
   * @param recursive include child nodes
   * @param callback is called for every node, in order to allow
   *     modifications.
   *     Return `false` to ignore this node or `"skip"` to include this node
   *     without its children.
   * @see {@link Wunderbaum.toDictArray}.
   */
  toDict(recursive = false, callback?: NodeToDictCallback): WbNodeData {
    const dict: any = {};

    NODE_DICT_PROPS.forEach((propName: string) => {
      const val = (<any>this)[propName];

      if (val instanceof Set) {
        // Convert Set to string (or skip if set is empty)
        val.size
          ? (dict[propName] = Array.prototype.join.call(val.keys(), " "))
          : 0;
      } else if (val || val === false || val === 0) {
        dict[propName] = val;
      }
    });
    if (!util.isEmptyObject(this.data)) {
      dict.data = util.extend({}, this.data);
      if (util.isEmptyObject(dict.data)) {
        delete dict.data;
      }
    }
    if (callback) {
      const res = callback(dict, this);
      if (res === false) {
        // Note: a return value of `false` is only used internally
        return <any>false; // Don't include this node nor its children
      }
      if (res === "skip") {
        recursive = false; // Include this node, but not the children
      }
    }
    if (recursive) {
      if (util.isArray(this.children)) {
        dict.children = [];
        for (let i = 0, l = this.children!.length; i < l; i++) {
          const node = this.children![i];
          if (!node.isStatusNode()) {
            // Note: a return value of `false` is only used internally
            const res = <any>node.toDict(true, callback);
            if (res !== false) {
              dict.children.push(res);
            }
          }
        }
      }
    }
    return dict;
  }

  /** Return an option value that has a default, but may be overridden by a
   * callback or a node instance attribute.
   *
   * Evaluation sequence:
   *
   * - If `tree.options.<name>` is a callback that returns something, use that.
   * - Else if `node.<name>` is defined, use that.
   * - Else if `tree.types[<node.type>]` is a value, use that.
   * - Else if `tree.options.<name>` is a value, use that.
   * - Else use `defaultValue`.
   *
   * @param name name of the option property (on node and tree)
   * @param defaultValue return this if nothing else matched
   * {@link Wunderbaum.getOption|Wunderbaum.getOption}
   */
  getOption(name: string, defaultValue?: any) {
    const tree = this.tree;
    let opts: any = tree.options;

    // Lookup `name` in options dict
    if (name.indexOf(".") >= 0) {
      [opts, name] = name.split(".");
    }
    const value = opts[name]; // ?? defaultValue;

    // A callback resolver always takes precedence
    if (typeof value === "function") {
      const res = value.call(tree, {
        type: "resolve",
        tree: tree,
        node: this,
        // typeInfo: this.type ? tree.types[this.type] : {},
      });
      if (res !== undefined) {
        return res;
      }
    }
    // If this node has an explicit local setting, use it:
    if ((<any>this)[name] !== undefined) {
      return (<any>this)[name];
    }
    // Use value from type definition if defined
    const typeInfo = this.type ? tree.types[this.type] : undefined;
    const res = typeInfo ? typeInfo[name] : undefined;
    if (res !== undefined) {
      return res;
    }
    // Use value from value options dict, fallback do default
    return value ?? defaultValue;
  }

  /** Make sure that this node is visible in the viewport.
   * @see {@link Wunderbaum.scrollTo|Wunderbaum.scrollTo}
   */
  async scrollIntoView(options?: ScrollIntoViewOptions) {
    const opts = Object.assign({ node: this }, options);
    return this.tree.scrollTo(opts);
  }

  /**
   * Activate this node, deactivate previous, send events, activate column and
   * scroll into viewport.
   */
  async setActive(flag: boolean = true, options?: SetActiveOptions) {
    const tree = this.tree;
    const prev = tree.getActiveNode();
    const retrigger = options?.retrigger; // Default: false
    const focusTree = options?.focusTree; // Default: false
    // const focusNode = options?.focusNode !== false; // Default: true
    const noEvents = options?.noEvents; // Default: false
    const orgEvent = options?.event; // Default: null
    const colIdx = options?.colIdx; // Default: null
    const edit = options?.edit; // Default: false

    // util.assert(!colIdx || tree.isCellNav(), "colIdx requires cellNav");
    util.assert(!edit || colIdx != null, "edit requires colIdx");

    if (!noEvents) {
      if (flag) {
        if (prev !== this || retrigger) {
          if (
            prev?._callEvent("deactivate", {
              nextNode: this,
              event: orgEvent,
            }) === false ||
            this._callEvent("beforeActivate", {
              prevNode: prev,
              event: orgEvent,
            }) === false
          ) {
            return;
          }
          tree._setActiveNode(null);
          prev?.update(ChangeType.status);
        }
      } else if (prev === this || retrigger) {
        this._callEvent("deactivate", { nextNode: null, event: orgEvent });
      }
    }

    if (prev !== this) {
      if (flag) {
        tree._setActiveNode(this);
      }
      prev?.update(ChangeType.status);
      this.update(ChangeType.status);
    }
    return this.makeVisible().then(() => {
      if (flag) {
        if (focusTree || edit) {
          tree.setFocus();
          tree._setFocusNode(this);
          tree.focusNode!.setFocus();
        }
        // if (focusNode || edit) {
        //   tree.focusNode = this;
        //   tree.focusNode.setFocus();
        // }
        if (colIdx != null && tree.isCellNav()) {
          tree.setColumn(colIdx, { edit: edit });
        }
        if (!noEvents) {
          this._callEvent("activate", { prevNode: prev, event: orgEvent });
        }
      }
    });
  }

  /**
   * Expand or collapse this node.
   */
  async setExpanded(flag: boolean = true, options?: SetExpandedOptions) {
    const { force, scrollIntoView, immediate, resetLazy } = options ?? {};
    const sendEvents = !options?.noEvents; // Default: send events

    if (
      !flag &&
      this.isExpanded() &&
      this.getLevel() <= this.tree.getOption("minExpandLevel") &&
      !force
    ) {
      this.logDebug("Ignored collapse request below minExpandLevel.");
      return;
    }
    if (!flag === !this.expanded) {
      return; // Nothing to do
    }
    if (
      sendEvents &&
      this._callEvent("beforeExpand", { flag: flag }) === false
    ) {
      return;
    }

    // this.log("setExpanded()");
    if (flag && this.getOption("autoCollapse")) {
      this.collapseSiblings(options);
    }
    if (flag && this.lazy && this.children == null) {
      await this.loadLazy();
    } else if (!flag && resetLazy && this.lazy && this.children) {
      this.resetLazy();
    }
    this.expanded = flag;
    const updateOpts = { immediate: immediate };
    // const updateOpts = { immediate: !!util.getOption(options, "immediate") };
    this.tree.update(ChangeType.structure, updateOpts);
    if (flag && scrollIntoView) {
      const lastChild = this.getLastChild();
      if (lastChild) {
        this.tree.updatePendingModifications();
        lastChild.scrollIntoView({ topNode: this });
      }
    }
    if (sendEvents) {
      this._callEvent("expand", { flag: flag });
    }
  }

  /**
   * Set keyboard focus here.
   * @see {@link setActive}
   */
  setFocus(flag: boolean = true) {
    util.assert(!!flag, "Blur is not yet implemented");
    const prev = this.tree.focusNode;
    this.tree._setFocusNode(this);
    // Only the row classes change, so don't re-render the row content (which
    // would also discard an embedded title editor).
    prev?.update(ChangeType.status);
    this.update(ChangeType.status);
  }

  /** Set a new icon path or class. */
  setIcon(icon: string) {
    this.icon = icon;
    this.update();
  }

  /** Change node's {@link key} and/or {@link refKey}.  */
  setKey(key: string | null, refKey: string | null) {
    throw new Error("Not yet implemented");
  }
  // /**
  //  * Calculate a *stable*, unique key for this node from its refKey (or title).
  //  * We also add information from the parent, because a refKey may occur multiple
  //  * times in a tree.
  //  */
  // calcUniqueKey() {
  //   // Assuming that the parent's key was calculated the same way, we implicitly
  //   // involve the whole refKey-path:
  //   const s = this.key + (this.refKey || this.title);
  //   // 32-bit has a high probability of collisions, so we pump up to 64-bit
  //   // https://security.stackexchange.com/q/209882/207588
  //   const h1 = util.murmurHash3(s, true);
  //   return "id_" + h1 + util.murmurHash3(h1 + s, true);
  //   // const l = [];
  //   // // eslint-disable-next-line  @typescript-eslint/no-this-alias
  //   // let node: WunderbaumNode = this;
  //   // while (node.parent) {
  //   //   l.unshift(node.refKey || node.key);
  //   //   node = node.parent;
  //   // }
  //   // const path = l.join("/");
  //   // 32-bit has a high probability of collisions, so we pump up to 64-bit
  //   // https://security.stackexchange.com/q/209882/207588
  //   // const h1 = util.murmurHash3(path, true);
  //   // return "id_" + h1 + util.murmurHash3(h1 + path, true);
  // }

  /**
   * Trigger a repaint, typically after a status or data change.
   *
   * `change` defaults to 'data', which handles modifcations of title, icon,
   * and column content. It can be reduced to 'ChangeType.status' if only
   * active/focus/selected state has changed.
   *
   * This method will eventually call  {@link WunderbaumNode._render} with
   * default options, but may be more consistent with the tree's
   * {@link Wunderbaum.update} API.
   */
  update(change: ChangeType = ChangeType.data) {
    util.assert(
      change === ChangeType.status || change === ChangeType.data,
      `Invalid change type ${change}`
    );
    this.tree.update(change, this);
  }

  /**
   * Return an array of selected nodes.
   * @param stopOnParents only return the topmost selected node (useful with selectMode 'hier')
   */
  getSelectedNodes(stopOnParents: boolean = false): WunderbaumNode[] {
    const nodeList: WunderbaumNode[] = [];
    this.visit((node) => {
      if (node.selected) {
        nodeList.push(node);
        if (stopOnParents === true) {
          return "skip"; // stop processing this branch
        }
      }
    });
    return nodeList;
  }

  /**
   * Return an array of refKey values.
   *
   * RefKeys are unique identifiers for a node data, and are used to identify
   * clones.
   * If more than one node has the same refKey, it is only returned once.
   * @param selected if true, only return refKeys of selected nodes.
   */
  getRefKeys(selected = false): string[] {
    const refKeys = new Set<string>();
    this.visit((node) => {
      if (node.refKey != null && (!selected || node.selected)) {
        refKeys.add(node.refKey);
      }
    });
    return Array.from(refKeys);
  }

  /** Toggle the check/uncheck state. */
  toggleSelected(options?: SetSelectedOptions): TristateType {
    let flag = this.isSelected();
    if (flag === undefined && !this.isRadio()) {
      flag = this._anySelectable();
    } else {
      flag = !flag;
    }
    return this.setSelected(flag, options);
  }

  /** Return true if at least on selectable descendant end-node is unselected. @internal */
  _anySelectable(): boolean {
    let found = false;
    this.visit((node) => {
      if (
        node.selected === false &&
        !node.unselectable &&
        !node.hasChildren() &&
        !node.parent.radiogroup
      ) {
        found = true;
        return false; // Stop iteration
      }
    });
    return found;
  }

  /* Apply selection state to a single node. */
  protected _changeSelectStatusProps(state: TristateType): boolean {
    let changed = false;
    switch (state) {
      case false:
        changed = this.selected || this._partsel;
        this.selected = false;
        this._partsel = false;
        break;
      case true:
        changed = !this.selected || !this._partsel;
        this.selected = true;
        this._partsel = true;
        break;
      case undefined:
        changed = this.selected || !this._partsel;
        this.selected = false;
        // #110: end nodess cannot have a `_partsel` flag
        this._partsel = this.hasChildren() ? true : false;
        break;
      default:
        util.error(`Invalid state: ${state}`);
    }
    if (changed) {
      this.update();
    }
    return changed;
  }
  /**
   * Fix selection status, after this node was (de)selected in `selectMode: 'hier'`.
   * This includes (de)selecting all descendants.
   */
  fixSelection3AfterClick(opts?: SetSelectedOptions): void {
    const force = !!opts?.force;
    const flag = this.isSelected();

    this.visit((node) => {
      if (node.radiogroup) {
        return "skip"; // Don't (de)select this branch
      }
      if (force || !node.getOption("unselectable")) {
        node._changeSelectStatusProps(flag);
      }
    });
    this.fixSelection3FromEndNodes();
  }

  /**
   * Fix selection status for multi-hier mode.
   * Only end-nodes are considered to update the descendants branch and parents.
   * Should be called after this node has loaded new children or after
   * children have been modified using the API.
   */
  fixSelection3FromEndNodes(opts?: SetSelectedOptions): void {
    const force = !!opts?.force;
    util.assert(
      this.tree.options.selectMode === "hier",
      "expected selectMode 'hier'"
    );

    // Visit all end nodes and adjust their parent's `selected` and `_partsel`
    // attributes. Return selection state true, false, or undefined.
    const _walk = (node: WunderbaumNode) => {
      let state;
      const children = node.children;

      if (children && children.length) {
        // check all children recursively
        let allSelected = true;
        let someSelected = false;

        for (let i = 0, l = children.length; i < l; i++) {
          const child = children[i];
          // the selection state of a node is not relevant; we need the end-nodes
          const s = _walk(child);
          if (s !== false) {
            someSelected = true;
          }
          if (s !== true) {
            allSelected = false;
          }
        }
        state = allSelected ? true : someSelected ? undefined : false;
      } else {
        // This is an end-node: simply report the status
        state = !!node.selected;
      }
      // #939: Keep a `_partsel` flag that was explicitly set on a lazy node
      if (
        node._partsel &&
        !node.selected &&
        node.lazy &&
        node.children == null
      ) {
        state = undefined;
      }
      if (force || !node.getOption("unselectable")) {
        node._changeSelectStatusProps(state);
      }
      return state;
    };
    _walk(this);

    // Update parent's state
    this.visitParents((node) => {
      let state;
      const children = node.children!;
      let allSelected = true;
      let someSelected = false;

      for (let i = 0, l = children.length; i < l; i++) {
        const child = children[i];

        state = !!child.selected;
        // When fixing the parents, we trust the sibling status (i.e. we don't recurse)
        if (state || child._partsel) {
          someSelected = true;
        }
        if (!state) {
          allSelected = false;
        }
      }
      state = allSelected ? true : someSelected ? undefined : false;
      node._changeSelectStatusProps(state);
    });
  }

  /** Modify the check/uncheck state. */
  setSelected(
    flag: boolean = true,
    options?: SetSelectedOptions
  ): TristateType {
    const tree = this.tree;
    const sendEvents = !options?.noEvents; // Default: send events
    const prev = this.isSelected();
    const isRadio = this.parent && this.parent.radiogroup;
    const selectMode = tree.options.selectMode;
    const canSelect = options?.force || !this.getOption("unselectable");

    flag = !!flag;
    // this.logDebug(`setSelected(${flag})`, this);
    if (!canSelect) {
      return prev;
    }
    if (options?.propagateDown && selectMode === "multi") {
      tree.runWithDeferredUpdate(() => {
        this.visit((node) => {
          node.setSelected(flag);
        });
      });
      return prev;
    }

    if (
      flag === prev ||
      (sendEvents && this._callEvent("beforeSelect", { flag: flag }) === false)
    ) {
      return prev;
    }

    tree.runWithDeferredUpdate(() => {
      if (isRadio) {
        // Radiobutton Group
        if (!flag && !options?.force) {
          return prev; // don't uncheck radio buttons
        }
        for (const sibling of this.parent.children!) {
          sibling.selected = sibling === this;
        }
      } else {
        this.selected = flag;
        if (selectMode === "hier") {
          this.fixSelection3AfterClick();
        } else if (selectMode === "single" && flag) {
          tree.visit((n) => {
            if (n !== this) {
              n.selected = false;
            }
          });
        }
      }
    });

    if (sendEvents) {
      this._callEvent("select", { flag: flag });
    }
    return prev;
  }

  /** Display node status (ok, loading, error, noData) using styles and a dummy child node. */
  setStatus(
    status: NodeStatusType,
    options?: SetStatusOptions
  ): WunderbaumNode | null {
    const tree = this.tree;
    const message = options?.message;
    const details = options?.details;

    let statusNode: WunderbaumNode | null = null;

    const _clearStatusNode = () => {
      // Remove dedicated dummy node, if any
      const children = this.children;

      if (children && children.length && children[0].isStatusNode()) {
        children[0].remove();
      }
    };

    const _setStatusNode = (data: any) => {
      // Create/modify the dedicated dummy node for 'loading...' or
      // 'error!' status. (only called for direct child of the invisible
      // system root)
      const children = this.children;
      const firstChild = children ? children[0] : null;

      util.assert(data.statusNodeType, "Not a status node");
      util.assert(
        !firstChild || !firstChild.isStatusNode(),
        "Child must not be a status node"
      );

      statusNode = this.addNode(data, "prependChild");
      statusNode.match = -1; // Mark as 'match' to avoid hiding
      tree.update(ChangeType.structure);

      return statusNode;
    };

    _clearStatusNode();

    switch (status) {
      case "ok":
        this._isLoading = false;
        this._errorInfo = null;
        break;
      case "loading":
        this._isLoading = true;
        this._errorInfo = null;
        if (this.parent) {
          this.update(ChangeType.status);
        } else {
          // If this is the invisible root, add a visible top-level node
          _setStatusNode({
            statusNodeType: status,
            title:
              tree.options.strings.loading +
              (message ? " (" + message + ")" : ""),
            checkbox: false,
            colspan: true,
            tooltip: details,
          });
        }
        // this.update();
        break;
      case "error":
        _setStatusNode({
          statusNodeType: status,
          title:
            tree.options.strings.loadError +
            (message ? " (" + message + ")" : ""),
          checkbox: false,
          colspan: true,
          // classes: "wb-center",
          tooltip: details,
        });
        this._isLoading = false;
        this._errorInfo = { message: message, details: details };
        break;
      case "noData":
        _setStatusNode({
          statusNodeType: status,
          title: message || tree.options.strings.noData,
          checkbox: false,
          colspan: true,
          tooltip: details,
        });
        this._isLoading = false;
        this._errorInfo = null;
        break;
      default:
        util.error("invalid node status " + status);
    }
    tree.update(ChangeType.structure);
    return statusNode;
  }

  /** Rename this node. */
  setTitle(title: string): void {
    this.title = title;
    this.update();
    // this.triggerModify("rename"); // TODO
  }

  /** Set the node tooltip. */
  setTooltip(tooltip: TooltipOption): void {
    this.tooltip = tooltip;
    this.update();
  }

  /**
   * Sort child list by title or custom criteria.
   * @param {function} cmp custom compare function(a, b) that returns -1, 0, or 1
   *    (defaults to sorting by title).
   * @param {boolean} deep pass true to sort all descendant nodes recursively
   * @deprecated use {@link sort}
   */
  sortChildren(
    cmp: SortCallback | null = nodeTitleSorter,
    deep: boolean = false
  ): void {
    this.tree.logDeprecate("node.sortChildren()", { since: "0.14.0" });
    return this.sort({ cmp: cmp ? cmp : undefined, deep: deep });
  }

  /**
   * Renumber nodes `_nativeIndex`. This is useful to allow to restore the
   * order after sorting a column.
   * This method is automatically called after loading new child nodes.
   * @since 0.11.0
   */
  resetNativeChildOrder(options?: ResetOrderOptions) {
    const { recursive = true, propName = "_nativeIndex" } = options ?? {};

    if (this.children) {
      this.children.forEach((child, i) => {
        child.data[propName] = i;
        if (recursive && child.children) {
          child.resetNativeChildOrder(options);
        }
      });
    }
  }

  /**
   * Convenience method to implement column sorting.
   * @since 0.11.0
   * @deprecated use {@link sort}
   */
  sortByProperty(options: SortByPropertyOptions) {
    this.tree.logDeprecate("node.sortByProperty()", { since: "0.14.0" });
    return this.sort(options);
  }

  /**
   * Implement column sorting.
   * @since 0.14.0
   */
  sort(options: SortOptions) {
    const tree = this.tree;
    let {
      propName = undefined,
      deep = true,
      key = undefined,
      order = undefined,
      caseInsensitive = true,
      cmp = undefined,
      // Support click on column sort header:
      updateColInfo = false,
      nativeOrderPropName = "_nativeIndex",
      colId = undefined,
    } = options;

    propName ??= colId;
    if (propName === "*") {
      propName = "title";
    }

    const isFolder =
      tree.options.sortFoldersFirst === true
        ? (node: WunderbaumNode) =>
            node.hasChildren() !== false || node.type === NODE_TYPE_FOLDER
        : tree.options.sortFoldersFirst;

    if (updateColInfo) {
      const colDef = this.tree["_columnsById"][options.colId!];
      util.assert(colDef, `Invalid colId specified: ${options.colId}`);
      order ??= util.rotate(colDef.sortOrder, ["asc", "desc", undefined]);

      for (const col of this.tree.columns) {
        col.sortOrder = col === colDef ? order : undefined;
      }
      if (order === undefined) {
        propName = nativeOrderPropName;
        order = "asc";
      }
      this.tree.update(ChangeType.colStructure);
    } else {
      propName ??= "title";
      order ??= "asc";
    }

    this.logDebug(`sort(), propName=${propName}, ${order}`, options);
    util.assert(propName || cmp || key, "No `propName` or `key` specified");

    // Define a key callback from the parameters we have
    if (key == null && cmp == null) {
      key = (node) => {
        let val;
        if (NODE_DICT_PROPS.has(<string>propName)) {
          val = node[propName as keyof WunderbaumNode];
        } else {
          val = node.data[propName!];
        }
        if (caseInsensitive && typeof val === "string") {
          val = val.toLowerCase();
        }
        return val;
      };
    }
    // Define a compare callback that uses the key callback
    if (cmp) {
      util.assert(!key, "`key` and `cmp` are mutually exclusive");
      tree.logDeprecate("SortOptions.cmp", {
        since: "0.14.0",
        hint: "use the `key` callback instead",
      });
    } else {
      if (options.propName || options.caseInsensitive) {
        tree.logWarn("sort(): ignoring propName, caseInsensitive");
      }

      cmp = (a, b) => {
        if (isFolder) {
          const isFolderA = isFolder(a);
          if (isFolderA !== isFolder(b)) {
            return isFolderA ? -1 : 1;
          }
        }
        let x = key!(a);
        let y = key!(b);
        // Assure we have reasonable comparisons with null values:
        if (x == null) {
          x = typeof y === "string" ? "" : 0;
        } else if (typeof x === "boolean") {
          x = x ? 1 : 0;
        }
        if (y == null) {
          y = typeof x === "string" ? "" : 0;
        } else if (typeof y === "boolean") {
          y = y ? 1 : 0;
        }

        if (order === "desc") {
          return x === y ? 0 : x > y ? -1 : 1;
        }
        return x === y ? 0 : x > y ? 1 : -1;
      };
    }

    function _sortChildren(cl: WunderbaumNode[]): void {
      if (!cl) {
        return;
      }
      // Paging nodes ("More...") stay at the end
      cl.sort(
        (a, b) =>
          Number(a.isPagingNode()) - Number(b.isPagingNode()) || cmp!(a, b)
      );
      if (deep) {
        for (let i = 0, l = cl.length; i < l; i++) {
          if (cl[i].children) {
            _sortChildren(cl[i].children!);
          }
        }
      }
    }
    if (this.children) {
      _sortChildren(this.children);
    }
    this.tree.update(ChangeType.structure);
    // this.triggerModify("sort"); // TODO
  }

  /**
   * Re-apply current sorting if any (use after lazy load).
   * Example:
   * ```js
   * load: function (e) {
   *   // Whe loading a lazy branch, apply current sort order if any
   *   e.node.resort();
   * },
   * ```
   * @since 0.14.0
   */
  resort(options: SortOptions = {}): void {
    for (const colDef of this.tree.columns) {
      if (colDef.sortOrder) {
        options.colId = colDef.id;
        options.order = colDef.sortOrder;
        this.sort(options);
        break;
      }
    }
  }

  /**
   * Trigger `modifyChild` event on a parent to signal that a child was modified.
   * @param {string} operation Type of change: 'add', 'remove', 'rename', 'move', 'data', ...
   */
  triggerModifyChild(
    operation: string,
    child: WunderbaumNode | null,
    extra?: any
  ) {
    this.logDebug(`modifyChild(${operation})`, extra, child);
    if (!this.tree.options.modifyChild) {
      return;
    }
    if (child && child.parent !== this) {
      util.error("child " + child + " is not a child of " + this);
    }
    this._callEvent(
      "modifyChild",
      util.extend({ operation: operation, child: child }, extra)
    );
  }

  /**
   * Trigger `modifyChild` event on node.parent(!).
   * @param {string} operation Type of change: 'add', 'remove', 'rename', 'move', 'data', ...
   * @param {object} [extra]
   */
  triggerModify(operation: string, extra?: any) {
    // if (!this.parent) {
    //   return;
    // }
    this.parent.triggerModifyChild(operation, this, extra);
  }

  /**
   * Call `callback(node)` for all descendant nodes in hierarchical order (depth-first, pre-order).
   *
   * Stop iteration, if fn() returns false. Skip current branch, if fn()
   * returns "skip".<br>
   * Return false if iteration was stopped.
   *
   * @param {function} callback the callback function.
   *     Return false to stop iteration, return "skip" to skip this node and
   *     its children only.
   * @see `wb_node.WunderbaumNode.IterableIterator<WunderbaumNode>`
   * @see {@link Wunderbaum.visit}.
   */
  visit(
    callback: NodeVisitCallback,
    includeSelf: boolean = false
  ): NodeVisitResponse {
    let res: any = true;
    const children = this.children;

    if (includeSelf === true) {
      res = callback(this);
      if (res === false || res === "skip") {
        return res;
      }
    }
    if (children) {
      for (let i = 0, l = children.length; i < l; i++) {
        res = children[i].visit(callback, true);
        if (res === false) {
          break;
        }
      }
    }
    return res;
  }

  /** Call fn(node) for all parent nodes, bottom-up, including invisible system root.<br>
   * Stop iteration, if callback() returns false.<br>
   * Return false if iteration was stopped.
   *
   * @param callback the callback function. Return false to stop iteration
   */
  visitParents(
    callback: (node: WunderbaumNode) => boolean | void,
    includeSelf: boolean = false
  ): boolean {
    if (includeSelf && callback(this) === false) {
      return false;
    }
    let p = this.parent;
    while (p) {
      if (callback(p) === false) {
        return false;
      }
      p = p.parent;
    }
    return true;
  }

  /**
   * Call fn(node) for all sibling nodes.<br>
   * Stop iteration, if fn() returns false.<br>
   * Return false if iteration was stopped.
   *
   * @param callback the callback function.
   *     Return false to stop iteration.
   * @param includeSelf include this node in the iteration.
   */
  visitSiblings(
    callback: (node: WunderbaumNode) => boolean | void,
    includeSelf: boolean = false
  ): boolean {
    const ac = this.parent.children!;

    for (let i = 0, l = ac.length; i < l; i++) {
      const n = ac[i];
      if (includeSelf || n !== this) {
        if (callback(n) === false) {
          return false;
        }
      }
    }
    return true;
  }
  /**
   * [ext-filter] Return true if this node is matched by current filter (or no filter is active).
   */
  isMatched() {
    return !(this.tree.filterMode && !this.match);
  }
}
