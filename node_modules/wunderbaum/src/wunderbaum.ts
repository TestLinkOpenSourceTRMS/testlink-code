/*!
 * wunderbaum.ts
 *
 * A treegrid control.
 *
 * Copyright (c) 2021-2025, Martin Wendt (https://wwWendt.de).
 * https://github.com/mar10/wunderbaum
 *
 * Released under the MIT license.
 * @version @VERSION
 * @date @DATE
 */

// import "./wunderbaum.scss";
import * as util from "./util";
import { FilterExtension } from "./wb_ext_filter";
import { KeynavExtension } from "./wb_ext_keynav";
import { LoggerExtension } from "./wb_ext_logger";
import { DndExtension } from "./wb_ext_dnd";
import { GridExtension } from "./wb_ext_grid";
import { ExtensionsDict, WunderbaumExtension } from "./wb_extension_base";
import {
  AddChildrenOptions,
  ApplyCommandOptions,
  ApplyCommandType,
  ChangeType,
  ColumnDefinitionList,
  ExpandAllOptions,
  FilterModeType,
  FilterNodesOptions,
  IconMapType,
  GetStateOptions,
  MatcherCallback,
  NavigationType,
  NavModeEnum,
  NodeFilterCallback,
  NodeRegion,
  NodeStatusType,
  NodeStringCallback,
  NodeToDictCallback,
  NodeTypeDefinitionMap,
  NodeVisitCallback,
  RenderFlag,
  ScrollToOptions,
  SetActiveOptions,
  SetColumnOptions,
  SetStateOptions,
  SetStatusOptions,
  SortCallback,
  SourceType,
  TreeStateDefinition,
  UpdateOptions,
  VisitRowsOptions,
  WbEventInfo,
  WbNodeData,
  FilterOptionsType,
  EditOptionsType,
  DndOptionsType,
  SortOptions,
  DeprecationOptions,
  SortByPropertyOptions,
  ReloadOptions,
  LoadLazyNodesOptions,
} from "./types";
import {
  DEFAULT_DEBUGLEVEL,
  defaultIconMaps,
  makeNodeTitleStartMatcher,
  nodeTitleSorter,
  RENDER_MAX_PREFETCH,
  DEFAULT_ROW_HEIGHT,
  TEST_FILE_PATH,
  TEST_HTML,
} from "./common";
import { WunderbaumNode } from "./wb_node";
import { Deferred } from "./deferred";
import { EditExtension } from "./wb_ext_edit";
import { InitWunderbaumOptions, WunderbaumOptions } from "./wb_options";
import { DebouncedFunction } from "./debounce";

class WbSystemRoot extends WunderbaumNode {
  constructor(tree: Wunderbaum) {
    super(tree, <WunderbaumNode>(<unknown>null), {
      key: "__root__",
      title: tree.id,
    });
  }
  toString() {
    return `WbSystemRoot@${this.key}<'${this.tree.id}'>`;
  }
}

/**
 * A persistent plain object or array.
 *
 * See also {@link WunderbaumOptions}.
 */
export class Wunderbaum {
  protected static sequence = 0;
  protected enabled = true;

  /** Wunderbaum release version number "MAJOR.MINOR.PATCH". */
  public static version: string = "@VERSION"; // Set to semver by 'grunt release'

  /** The invisible root node, that holds all visible top level nodes. */
  public readonly root: WunderbaumNode;
  /** Unique tree ID as passed to constructor. Defaults to `"wb_SEQUENCE"`. */
  public readonly id: string;
  /** The `div` container element that was passed to the constructor. */
  public readonly element: HTMLDivElement;
  /** The `div.wb-header` element if any. */
  public readonly headerElement: HTMLDivElement;
  /** The `div.wb-list-container` element that contains the `nodeListElement`. */
  public readonly listContainerElement: HTMLDivElement;
  /** The `div.wb-node-list` element that contains all visible div.wb-row child elements. */
  public readonly nodeListElement: HTMLDivElement;
  /** Contains additional data that was sent as response to an Ajax source load request. */
  public readonly data: { [key: string]: any } = {};

  protected readonly _updateViewportThrottled: DebouncedFunction<() => void>;
  protected extensionList: WunderbaumExtension<any>[] = [];
  protected extensions: ExtensionsDict = <ExtensionsDict>{};

  /** Merged options from constructor args and tree- and extension defaults. */
  public options: WunderbaumOptions;

  protected keyMap = new Map<string, WunderbaumNode>();
  protected refKeyMap = new Map<string, Set<WunderbaumNode>>();
  protected treeRowCount = 0;
  /** Number of visible header rows (0 or 1), used for `aria-rowindex`. @internal */
  _ariaHeaderRows = 1;
  /** Incremented for every `_updateRows()` pass (caches ARIA set sizes). @internal */
  _ariaRenderPass = 0;
  protected _disableUpdateCount = 0;
  protected _disableUpdateIgnoreCount = 0;

  protected _activeNode: WunderbaumNode | null = null;
  protected _focusNode: WunderbaumNode | null = null;
  protected _initialSource: SourceType | null = null;

  /** Currently active node if any.
   * Use {@link WunderbaumNode.setActive|setActive} to modify.
   */
  public get activeNode() {
    // Check for deleted node, i.e. node.tree === null
    return this._activeNode?.tree ? this._activeNode : null;
  }
  /** Current node hat has keyboard focus if any.
   * Use {@link WunderbaumNode.setFocus|setFocus()} to modify.
   */
  public get focusNode() {
    // Check for deleted node, i.e. node.tree === null
    return this._focusNode?.tree ? this._focusNode : null;
  }

  /** Shared properties, referenced by `node.type`. */
  public types: NodeTypeDefinitionMap = {};
  /** List of column definitions. */
  public columns: ColumnDefinitionList = [];
  protected _columnsById: { [key: string]: any } = {};
  protected resizeObserver: ResizeObserver;

  // Modification Status
  protected pendingChangeTypes: Set<RenderFlag> = new Set();

  /** A Promise that is resolved when the tree was initialized (similar to `init(e)` event). */
  public readonly ready: Promise<any>;
  /** Expose some useful methods of the util.ts module as `Wunderbaum.util`. */
  public static util = util;
  /** A map of default iconMaps.
   * May be used as default, when passing partial icon definition maps:
   * ```js
   * const tree = new mar10.Wunderbaum({
   *   ...
   *   iconMap: Object.assign(Wunderbaum.iconMaps.bootstrap, {
   *     folder: "bi bi-archive",
   *   }),
   * });
   * ```
   */
  public static iconMaps = defaultIconMaps;
  /** Expose some useful methods of the util.ts module as `tree._util`. */
  public _util = util;

  // --- SELECT ---
  // public selectRangeAnchor: WunderbaumNode | null = null;

  // --- BREADCRUMB ---
  /** Filter options (used as defaults for calls to {@link Wunderbaum.filterNodes} ) */
  public breadcrumb: HTMLElement | null = null;

  // --- FILTER ---
  /** Filter options (used as defaults for calls to {@link Wunderbaum.filterNodes} ) */
  public filterMode: FilterModeType = null;

  // --- KEYNAV ---
  /** @internal Use `setColumn()`/`getActiveColElem()` to access. */
  public activeColIdx = 0;
  /** @internal */
  public _cellNavMode = false;
  /** @internal */
  public lastQuicksearchTime = 0;
  /** @internal */
  public lastQuicksearchTerm = "";

  // --- EDIT ---
  protected lastClickTime = 0;

  constructor(options: InitWunderbaumOptions) {
    // Set default options and merge with user options
    const initOptions = Object.assign<
      InitWunderbaumOptions,
      InitWunderbaumOptions
    >(
      {
        id: undefined,
        source: [], // URL for GET/PUT, Ajax options, or callback
        element: util.unsafeCast<string>(null),
        debugLevel: DEFAULT_DEBUGLEVEL, // 0:quiet, 1:errors, 2:warnings, 3:info, 4:verbose
        header: null, // Show/hide header (pass bool or string)
        rowHeightPx: DEFAULT_ROW_HEIGHT,
        iconMap: "bootstrap",
        columns: [], //util.unsafeCast<ColumnDefinitionList>(null),
        types: {},
        enabled: true,
        fixedCol: false,
        showSpinner: false,
        checkbox: false,
        minExpandLevel: 0,
        emptyChildListExpandable: false,
        skeleton: false,
        autoCollapse: false,
        adjustHeight: true,
        connectTopBreadcrumb: null,
        columnsFilterable: false,
        columnsMenu: false,
        columnsResizable: false,
        columnsSortable: false,
        selectMode: "multi", // SelectModeType
        scrollIntoViewOnExpandClick: true,
        // --- Extensions (actually set by exensions on init)
        dnd: util.unsafeCast<DndOptionsType>(null),
        edit: util.unsafeCast<EditOptionsType>(null),
        filter: util.unsafeCast<FilterOptionsType>(null),
        // --- KeyNav ---
        navigationModeOption: util.unsafeCast<NavModeEnum>(null),
        quicksearch: true,
        // --- Events ---
        // iconBadge: null,
        // change: null,
        // ...

        // --- Strings ---
        strings: {
          loadError: "Error",
          loading: "Loading...",
          noData: "No data",
          breadcrumbDelimiter: " » ",
          queryResult: "Found ${matches} of ${count}",
          noMatch: "No results",
          matchIndex: "${match} of ${matches}",
          moreItems: "More...",
        },
      },
      options
    );
    const opts = initOptions as WunderbaumOptions;
    this.options = opts;

    const readyDeferred = new Deferred();
    this.ready = readyDeferred.promise();
    let readyOk = false;
    this.ready
      .then(() => {
        readyOk = true;
        try {
          this._callEvent("init");
        } catch (error) {
          // We re-raise in the reject handler, but Chrome resets the stack
          // frame then, so we log it here:
          this.logError("Exception inside `init(e)` event:", error);
        }
      })
      .catch((err) => {
        if (readyOk) {
          // Error occurred in `init` handler. We can re-raise, but Chrome
          // resets the stack frame.
          throw err;
        } else {
          // Error in load process
          this._callEvent("init", { error: err });
        }
      });

    this.id = initOptions.id || "wb_" + ++Wunderbaum.sequence;
    delete initOptions.id;
    this.root = new WbSystemRoot(this);

    this._registerExtension(new KeynavExtension(this));
    this._registerExtension(new EditExtension(this));
    this._registerExtension(new FilterExtension(this));
    this._registerExtension(new DndExtension(this));
    this._registerExtension(new GridExtension(this));
    this._registerExtension(new LoggerExtension(this));

    this._updateViewportThrottled = util.adaptiveThrottle(
      this._updateViewportImmediately.bind(this),
      {}
    );

    // --- Evaluate options
    this.columns = initOptions.columns || [];
    delete initOptions.columns;
    if (!this.columns || !this.columns.length) {
      const title = typeof opts.header === "string" ? opts.header : this.id;
      this.columns = [{ id: "*", title: title, width: "*" }];
    }

    if (initOptions.types) {
      this.setTypes(initOptions.types, true);
    }
    delete initOptions.types;

    // --- Create Markup
    this.element = util.elemFromSelector<HTMLDivElement>(initOptions.element)!;
    util.assert(
      !!this.element,
      `Invalid 'element' option: ${initOptions.element}`
    );
    delete (<any>initOptions).element;

    this.element.classList.add("wunderbaum");
    if (!this.element.getAttribute("tabindex")) {
      this.element.tabIndex = 0;
    }
    // ARIA: expose the control as a tree grid. DOM focus stays on this element
    // and `aria-activedescendant` points at the focused row (see _setFocusNode).
    this.element.setAttribute("role", "treegrid");
    if (
      opts.checkbox &&
      opts.checkbox !== "radio" &&
      (opts.selectMode === "multi" || opts.selectMode === "hier")
    ) {
      this.element.setAttribute("aria-multiselectable", "true");
    } else {
      this.element.removeAttribute("aria-multiselectable");
    }

    if (opts.rowHeightPx !== DEFAULT_ROW_HEIGHT) {
      this.element.style.setProperty(
        "--wb-row-outer-height",
        opts.rowHeightPx + "px"
      );
      this.element.style.setProperty(
        "--wb-row-inner-height",
        opts.rowHeightPx - 2 + "px"
      );
    }
    // Attach tree instance to <div>
    (<any>this.element)._wb_tree = this;

    // Create header markup, or take it from the existing html

    this.headerElement =
      this.element.querySelector<HTMLDivElement>("div.wb-header")!;

    const wantHeader =
      opts.header == null ? this.columns.length > 1 : !!opts.header;

    if (this.headerElement) {
      // User existing header markup to define `this.columns`
      util.assert(
        !this.columns,
        "`opts.columns` must not be set if table markup already contains a header"
      );
      this.columns = [];
      const rowElement =
        this.headerElement.querySelector<HTMLDivElement>("div.wb-row")!;
      for (const colDiv of rowElement.querySelectorAll("div")) {
        this.columns.push({
          id: colDiv.dataset.id || `col_${this.columns.length}`,
          // id: colDiv.dataset.id || null,
          title: "" + colDiv.textContent,
          // text: "" + colDiv.textContent,
          width: "*", // TODO: read from header span
        });
      }
    } else {
      // We need a row div, the rest will be computed from `this.columns`
      const coldivs = "<span class='wb-col'></span>".repeat(
        this.columns.length
      );
      this.element.innerHTML = `
        <div class='wb-header'>
          <div class='wb-row'>
            ${coldivs}
          </div>
        </div>`;

      if (!wantHeader) {
        const he = this.element.querySelector<HTMLDivElement>("div.wb-header")!;
        he.style.display = "none";
      }
    }

    //
    this.element.innerHTML += `
      <div class="wb-list-container">
        <div class="wb-node-list"></div>
      </div>`;
    this.listContainerElement = this.element.querySelector<HTMLDivElement>(
      "div.wb-list-container"
    )!;
    this.nodeListElement =
      this.listContainerElement.querySelector<HTMLDivElement>(
        "div.wb-node-list"
      )!;
    this.headerElement =
      this.element.querySelector<HTMLDivElement>("div.wb-header")!;

    // ARIA: header row and node list are row groups of the tree grid
    this.headerElement.setAttribute("role", "rowgroup");
    const headerRowElem = this.headerElement.querySelector("div.wb-row");
    headerRowElem?.setAttribute("role", "row");
    headerRowElem?.setAttribute("aria-rowindex", "1");
    this.nodeListElement.setAttribute("role", "rowgroup");

    this.element.classList.toggle("wb-grid", this.columns.length > 1);

    if (this.options.connectTopBreadcrumb) {
      this.breadcrumb = util.elemFromSelector(
        this.options.connectTopBreadcrumb
      )!;
      util.assert(
        !this.breadcrumb || this.breadcrumb.innerHTML != null,
        `Invalid 'connectTopBreadcrumb' option: ${this.breadcrumb}.`
      );
      this.breadcrumb.addEventListener("click", (e) => {
        // const node = Wunderbaum.getNode(e)!;
        const elem = e.target as HTMLElement;
        if (elem && elem.matches("a.wb-breadcrumb")) {
          const node = this.keyMap.get(elem.dataset.key!);
          node?.setActive();
          e.preventDefault();
        }
      });
    }
    this._initExtensions();

    // --- apply initial options
    ["enabled", "fixedCol"].forEach((optName) => {
      if ((opts as any)[optName] != null) {
        this.setOption(optName, (opts as any)[optName]);
      }
    });

    // --- Load initial data
    if (initOptions.source) {
      if (opts.showSpinner) {
        this.nodeListElement.innerHTML = `<progress class='spinner'>${opts.strings.loading}</progress>`;
      }
      this.load(initOptions.source)
        .then(() => {
          // The source may have defined columns, so we may adjust the nav mode
          if (opts.navigationModeOption == null) {
            if (this.isGrid()) {
              this.setNavigationOption(NavModeEnum.cell);
            } else {
              this.setNavigationOption(NavModeEnum.row);
            }
          } else {
            this.setNavigationOption(opts.navigationModeOption);
          }
          this.update(ChangeType.structure, { immediate: true });
          readyDeferred.resolve();
        })
        .catch((error) => {
          readyDeferred.reject(error);
        })
        .finally(() => {
          this.element.querySelector("progress.spinner")?.remove();
          this.element.classList.remove("wb-initializing");
        });
    } else {
      readyDeferred.resolve();
    }

    // Async mode is sometimes required, because this.element.clientWidth
    // has a wrong value at start???
    this.update(ChangeType.any);

    // --- Bind listeners
    this._registerEventHandlers();

    this.resizeObserver = new ResizeObserver((entries) => {
      // this.log("ResizeObserver: Size changed", entries);
      this.update(ChangeType.resize);
    });
    this.resizeObserver.observe(this.element);
  }

  private _registerEventHandlers() {
    this.element.addEventListener("scroll", (e: Event) => {
      // this.log(`scroll, scrollTop:${e.target.scrollTop}`, e);
      this.update(ChangeType.scroll);
    });

    util.onEvent(this.element, "click", ".wb-button,.wb-col-icon", (e) => {
      const info = Wunderbaum.getEventInfo(e);
      const command = (<HTMLElement>e.target)?.dataset?.command;

      this._callEvent("buttonClick", {
        event: e,
        info: info,
        command: command,
      });
    });

    util.onEvent(this.nodeListElement, "click", "div.wb-row", (e) => {
      const info = Wunderbaum.getEventInfo(e);
      const node = info.node;
      const mouseEvent = e as MouseEvent;

      // this.log("click", info);

      if (
        this._callEvent("click", { event: e, node: node, info: info }) === false
      ) {
        this.lastClickTime = Date.now();
        return false;
      }
      if (node && node.isPagingNode()) {
        node.setFocus();
        node._clickPaging(e);
        this.lastClickTime = Date.now();
        return;
      }
      if (node) {
        if (mouseEvent.ctrlKey) {
          node.toggleSelected();
          return;
        }
        // Edit title if 'clickActive' is triggered:
        const trigger = this.getOption("edit.trigger");
        const slowClickDelay = this.getOption("edit.slowClickDelay");
        if (
          trigger.indexOf("clickActive") >= 0 &&
          info.region === "title" &&
          node.isActive() &&
          (!slowClickDelay || Date.now() - this.lastClickTime < slowClickDelay)
        ) {
          node.startEditTitle();
        }

        if (info.region === NodeRegion.expander) {
          node.setExpanded(!node.isExpanded(), {
            scrollIntoView: this.options.scrollIntoViewOnExpandClick !== false,
          });
        } else if (info.region === NodeRegion.checkbox) {
          node.toggleSelected();
        } else {
          if (info.colIdx >= 0) {
            node.setActive(true, { colIdx: info.colIdx, event: e });
          } else {
            node.setActive(true, { event: e });
          }
          // Keyboard navigation continues from the clicked node (#129).
          // `setFocus()` only updates the row status, so a title edit that
          // was just started by 'clickActive' is kept (#141).
          node.setFocus();
        }
      }
      this.lastClickTime = Date.now();
    });

    util.onEvent(this.nodeListElement, "dblclick", "div.wb-row", (e) => {
      const info = Wunderbaum.getEventInfo(e);
      const node = info.node;
      // this.log("dblclick", info, e);

      if (
        this._callEvent("dblclick", { event: e, node: node, info: info }) ===
        false
      ) {
        return false;
      }
      if (
        node &&
        info.colIdx === 0 &&
        node.isExpandable() &&
        info.region !== NodeRegion.expander
      ) {
        this._callMethod("edit._stopEditTitle");
        node.setExpanded(!node.isExpanded());
      }
    });

    util.onEvent(this.element, "keydown", (e) => {
      const info = Wunderbaum.getEventInfo(e);
      const eventName = util.eventToString(e);
      const node = info.node || this.getFocusNode();

      this._callHook("onKeyEvent", {
        event: e,
        node: node,
        info: info,
        eventName: eventName,
      });
    });

    util.onEvent(this.element, "focusin focusout", (e) => {
      const flag = e.type === "focusin";
      const targetNode = Wunderbaum.getNode(e)!;

      this._callEvent("focus", { flag: flag, event: e });

      if (flag && this.isRowNav() && !this.isEditingTitle()) {
        if (this.options.navigationModeOption === NavModeEnum.row) {
          targetNode?.setActive();
        } else {
          this.setCellNav();
        }
      }
      if (!flag) {
        this._callMethod("edit._stopEditTitle", true, {
          event: e,
          forceClose: true,
        });
      }
    });
  }
  /**
   * Return a Wunderbaum instance, from element, id, index, or event.
   *
   * ```js
   * getTree();         // Get first Wunderbaum instance on page
   * getTree(1);        // Get second Wunderbaum instance on page
   * getTree(event);    // Get tree for this mouse- or keyboard event
   * getTree("foo");    // Get tree for this `tree.options.id`
   * getTree("#tree");  // Get tree for first matching element selector
   * ```
   */
  public static getTree(
    el?: Element | Event | number | string | WunderbaumNode
  ): Wunderbaum | null {
    if (el instanceof Wunderbaum) {
      return el;
    } else if (el instanceof WunderbaumNode) {
      return el.tree;
    }
    if (el === undefined) {
      el = 0; // get first tree
    }
    if (typeof el === "number") {
      el = document.querySelectorAll(".wunderbaum")[el]; // el was an integer: return nth element
    } else if (typeof el === "string") {
      // Search all trees for matching ID
      for (const treeElem of document.querySelectorAll(".wunderbaum")) {
        const tree = (<any>treeElem)._wb_tree;
        if (tree && tree.id === el) {
          return tree;
        }
      }
      // Search by selector
      el = document.querySelector(el)!;
      if (!el) {
        return null;
      }
    } else if ((<Event>el).target) {
      el = (<Event>el).target as Element;
    }
    util.assert(el instanceof Element, `Invalid el type: ${el}`);
    if (!(<HTMLElement>el).matches(".wunderbaum")) {
      el = (<HTMLElement>el).closest(".wunderbaum")!;
    }

    if (el && (<any>el)._wb_tree) {
      return (<any>el)._wb_tree;
    }
    return null;
  }

  /**
   * Return the icon-function -> icon-definition mapping.
   * @deprecated Use {@link Wunderbaum.iconMaps}
   */
  get iconMap(): IconMapType {
    const map = this.options.iconMap;
    if (typeof map === "string") {
      return defaultIconMaps[map as keyof typeof defaultIconMaps];
    }
    return map;
  }

  /**
   * Return a WunderbaumNode instance from element or event.
   */
  public static getNode(el: Element | Event): WunderbaumNode | null {
    if (!el) {
      return null;
    } else if (el instanceof WunderbaumNode) {
      return el;
    } else if ((<Event>el).target !== undefined) {
      el = (<Event>el).target! as Element; // el was an Event
    }
    // `el` is a DOM element
    // let nodeElem = obj.closest("div.wb-row");
    while (el) {
      if ((<any>el)._wb_node) {
        return (<any>el)._wb_node as WunderbaumNode;
      }
      el = (<Element>el).parentElement!; //.parentNode;
    }
    return null;
  }

  /**
   * Iterate all descendant nodes depth-first, pre-order using `for ... of ...` syntax.
   * More concise, but slightly slower than {@link Wunderbaum.visit}.
   *
   * Example:
   * ```js
   * for(const node of tree) {
   *   ...
   * }
   * ```
   */

  *[Symbol.iterator](): IterableIterator<WunderbaumNode> {
    yield* this.root;
  }

  /** @internal */
  protected _registerExtension(extension: WunderbaumExtension<any>): void {
    this.extensionList.push(extension);
    this.extensions[extension.id] = extension;
    // this.extensionMap.set(extension.id, extension);
  }

  /** Called on tree (re)init after markup is created, before loading. */
  protected _initExtensions(): void {
    for (const ext of this.extensionList) {
      ext.init();
    }
  }

  /**
   * Calculate a *stable*, unique key for a node from its refKey (or title).
   * We also add information from the parent, because a refKey may occur multiple
   * times in a tree (but not as child of the same parent).
   * @internal
   */
  _calculateKey(data: WbNodeData, parent?: WunderbaumNode): string {
    if (data.key) {
      // Always use an explicitly passed key
      return data.key;
    }
    // Auto-keys are optional, use a monotonic counter by default:
    if (!this.options.autoKeys) {
      return "" + ++WunderbaumNode.sequence;
    }
    // Add the parent's key to the hash. Assuming this was generated by the
    // same algorithm, this should incorporate the whole path:
    const s = (parent ? parent.key : "") + (data.refKey || data.title);
    // 32-bit has a high probability of collisions, so we pump up to 64-bit
    // https://security.stackexchange.com/q/209882/207588
    const h1 = util.murmurHash3(s, true);
    let key = "id_" + h1 + util.murmurHash3(h1 + s, true);
    // Check for collisions
    // (Most likely if the same title occurs multiple in the same parent).
    const existingNode = this.keyMap.get(key);
    if (existingNode) {
      key += "." + ++Wunderbaum.sequence;
      this.logWarn(
        `Node with existing key: '${existingNode}', using ${key}.`,
        data
      );
    }
    return key;
  }

  /** Add node to tree's bookkeeping data structures. @internal */
  _registerNode(node: WunderbaumNode): void {
    const key = node.key;
    util.assert(key != null, `Missing key: '${node}'.`);
    util.assert(!this.keyMap.has(key), `Duplicate key: '${key}': ${node}.`);
    this.keyMap.set(key, node);
    const rk = node.refKey;
    if (rk != null) {
      const rks = this.refKeyMap.get(rk); // Set of nodes with this refKey
      if (rks) {
        rks.add(node);
      } else {
        this.refKeyMap.set(rk, new Set([node]));
      }
    }
  }

  /** Remove node from tree's bookkeeping data structures. @internal  */
  _unregisterNode(node: WunderbaumNode): void {
    // Remove refKey reference from map (if any)
    const rk = node.refKey;
    if (rk != null) {
      const rks = this.refKeyMap.get(rk);
      if (rks && rks.delete(node) && !rks.size) {
        // We just removed the last element
        this.refKeyMap.delete(rk);
      }
    }
    // Remove key reference from map
    this.keyMap.delete(node.key);
    // Mark as disposed
    (node.tree as any) = null;
    (node.parent as any) = null;
    // Remove HTML markup
    node.removeMarkup();
  }

  /** Call all hook methods of all registered extensions.*/
  protected _callHook(
    hook: keyof WunderbaumExtension<any>,
    data: any = {}
  ): any {
    let res;
    const d = util.extend(
      {},
      { tree: this, options: this.options, result: undefined },
      data
    );

    for (const ext of this.extensionList) {
      res = (<any>ext[hook]).call(ext, d);
      if (res === false) {
        break;
      }
      if (d.result !== undefined) {
        res = d.result;
      }
    }
    return res;
  }

  /**
   * Call tree method or extension method if defined.
   *
   * Example:
   * ```js
   * tree._callMethod("edit.startEdit", "arg1", "arg2")
   * ```
   */
  _callMethod(name: string, ...args: any[]): any {
    const [p, n] = name.split(".");
    const obj = n ? this.extensions[p] : this;
    const func = (<any>obj)[n];
    if (func) {
      return func.apply(obj, args);
    } else {
      this.logError(`Calling undefined method '${name}()'.`);
    }
  }

  /**
   * Call event handler if defined in tree or tree.EXTENSION options.
   *
   * Example:
   * ```js
   * tree._callEvent("edit.beforeEdit", {foo: 42})
   * ```
   */
  _callEvent(type: string, extra?: any): any {
    const [p, n] = type.split(".");
    const opts = this.options as any;
    const func = n ? opts[p][n] : opts[p];
    if (func) {
      return func.call(
        this,
        util.extend({ type: type, tree: this, util: this._util }, extra)
      );
      // } else {
      //   this.logError(`Triggering undefined event '${type}'.`)
    }
  }

  /** Return the node for  given row index. */
  protected _getNodeByRowIdx(idx: number): WunderbaumNode | null {
    // TODO: start searching from active node (reverse)
    let node: WunderbaumNode | null = null;
    this.visitRows((n) => {
      if (n._rowIdx === idx) {
        node = n;
        return false;
      }
    });
    return <WunderbaumNode>node!;
  }

  /** Return the topmost visible node in the viewport.
   * @param complete If `false`, the node is considered visible if at least one
   * pixel is visible.
   */
  getTopmostVpNode(complete = true) {
    const rowHeight = this.options.rowHeightPx;
    const gracePx = 1; // ignore subpixel scrolling
    const scrollParent = this.element;
    // const headerHeight = this.headerElement.clientHeight;  // May be 0
    const scrollTop = scrollParent.scrollTop; // + headerHeight;
    let topIdx: number;

    if (complete) {
      topIdx = Math.ceil((scrollTop - gracePx) / rowHeight);
    } else {
      topIdx = Math.floor(scrollTop / rowHeight);
    }
    return this._getNodeByRowIdx(topIdx)!;
  }

  /** Return the lowest visible node in the viewport. */
  getLowestVpNode(complete = true) {
    const rowHeight = this.options.rowHeightPx;
    const scrollParent = this.element;
    const headerHeight = this.headerElement.clientHeight; // May be 0
    const scrollTop = scrollParent.scrollTop;
    const clientHeight = scrollParent.clientHeight - headerHeight;
    let bottomIdx: number;

    if (complete) {
      bottomIdx = Math.floor((scrollTop + clientHeight) / rowHeight) - 1;
    } else {
      bottomIdx = Math.ceil((scrollTop + clientHeight) / rowHeight) - 1;
    }
    bottomIdx = Math.min(bottomIdx, this.count(true) - 1);
    return this._getNodeByRowIdx(bottomIdx)!;
  }

  /** Return preceding visible node in the viewport. */
  protected _getPrevNodeInView(node?: WunderbaumNode, ofs = 1) {
    this.visitRows(
      (n) => {
        node = n;
        if (ofs-- <= 0) {
          return false;
        }
      },
      { reverse: true, start: node || this.getActiveNode() }
    );
    return node;
  }

  /** Return following visible node in the viewport. */
  protected _getNextNodeInView(
    node?: WunderbaumNode,
    options?: {
      ofs?: number;
      reverse?: boolean;
      cb?: (n: WunderbaumNode) => boolean;
    }
  ) {
    let ofs = options?.ofs || 1;
    const reverse = !!options?.reverse;

    this.visitRows(
      (n) => {
        node = n;
        if (options?.cb && options.cb(n)) {
          return false;
        }
        if (ofs-- <= 0) {
          return false;
        }
      },
      { reverse: reverse, start: node || this.getActiveNode() }
    );
    return node;
  }

  /**
   * Append (or insert) a list of toplevel nodes.
   *
   * @see {@link WunderbaumNode.addChildren}
   */
  addChildren(nodeData: any, options?: AddChildrenOptions): WunderbaumNode {
    return this.root.addChildren(nodeData, options);
  }

  /**
   * Apply a modification (or navigation) operation on the **tree or active node**.
   */
  applyCommand(cmd: ApplyCommandType, options?: ApplyCommandOptions): any;

  /**
   * Apply a modification (or navigation) operation on a **node**.
   * @see {@link WunderbaumNode.applyCommand}
   */
  applyCommand(
    cmd: ApplyCommandType,
    node: WunderbaumNode,
    options?: ApplyCommandOptions
  ): any;

  /**
   * Apply a modification or navigation operation.
   *
   * Most of these commands simply map to a node or tree method.
   * This method is especially useful when implementing keyboard mapping,
   * context menus, or external buttons.
   *
   * Valid commands:
   *   - 'moveUp', 'moveDown'
   *   - 'indent', 'outdent'
   *   - 'remove'
   *   - 'edit', 'addChild', 'addSibling': (reqires ext-edit extension)
   *   - 'cut', 'copy', 'paste': (use an internal singleton 'clipboard')
   *   - 'down', 'first', 'last', 'left', 'parent', 'right', 'up': navigate
   *
   */
  applyCommand(
    cmd: ApplyCommandType,
    nodeOrOpts?: WunderbaumNode | any,
    options?: ApplyCommandOptions
  ): any {
    let // clipboard,
      node,
      refNode;
    // options = $.extend(
    // 	{ setActive: true, clipboard: CLIPBOARD },
    // 	options_
    // );
    if (nodeOrOpts instanceof WunderbaumNode) {
      node = nodeOrOpts;
    } else {
      node = this.getActiveNode()!;
      util.assert(options === undefined, `Unexpected options: ${options}`);
      options = nodeOrOpts;
    }
    // clipboard = options.clipboard;

    switch (cmd) {
      // Sorting and indentation:
      case "moveUp":
        refNode = node.getPrevSibling();
        if (refNode) {
          node.moveTo(refNode, "before");
          node.setActive();
        }
        break;
      case "moveDown":
        refNode = node.getNextSibling();
        if (refNode) {
          node.moveTo(refNode, "after");
          node.setActive();
        }
        break;
      case "indent":
        refNode = node.getPrevSibling();
        if (refNode) {
          node.moveTo(refNode, "appendChild");
          refNode.setExpanded();
          node.setActive();
        }
        break;
      case "outdent":
        if (!node.isTopLevel()) {
          node.moveTo(node.getParent()!, "after");
          node.setActive();
        }
        break;
      // Remove:
      case "remove":
        refNode = node.getPrevSibling() || node.getParent();
        node.remove();
        if (refNode) {
          refNode.setActive();
        }
        break;
      // Add, edit (requires ext-edit):
      case "addChild":
        this._callMethod("edit.createNode", "prependChild");
        break;
      case "addSibling":
        this._callMethod("edit.createNode", "after");
        break;
      case "rename":
        node.startEditTitle();
        break;
      // Simple clipboard simulation:
      // case "cut":
      // 	clipboard = { mode: cmd, data: node };
      // 	break;
      // case "copy":
      // 	clipboard = {
      // 		mode: cmd,
      // 		data: node.toDict(function(d, n) {
      // 			delete d.key;
      // 		}),
      // 	};
      // 	break;
      // case "clear":
      // 	clipboard = null;
      // 	break;
      // case "paste":
      // 	if (clipboard.mode === "cut") {
      // 		// refNode = node.getPrevSibling();
      // 		clipboard.data.moveTo(node, "child");
      // 		clipboard.data.setActive();
      // 	} else if (clipboard.mode === "copy") {
      // 		node.addChildren(clipboard.data).setActive();
      // 	}
      // 	break;
      // Navigation commands:
      case "down":
      case "first":
      case "last":
      case "left":
      case "nextMatch":
      case "pageDown":
      case "pageUp":
      case "parent":
      case "prevMatch":
      case "right":
      case "up":
        return node.navigate(cmd);
      default:
        util.error(`Unhandled command: '${cmd}'`);
    }
  }

  /** Delete all nodes. */
  clear() {
    this.root.removeChildren();
    this.root.children = null;
    this.keyMap.clear();
    this.refKeyMap.clear();
    this.treeRowCount = 0;
    this._activeNode = null;
    this._focusNode = null;

    // this.types = {};
    // this. columns =[];
    // this._columnsById = {};

    // Modification Status
    // this.changedSince = 0;
    // this.changes.clear();
    // this.changedNodes.clear();

    // // --- FILTER ---
    // public filterMode: FilterModeType = null;

    // // --- KEYNAV ---
    // public activeColIdx = 0;
    // public cellNavMode = false;
    // public lastQuicksearchTime = 0;
    // public lastQuicksearchTerm = "";
    this.update(ChangeType.structure);
  }

  /**
   * Clear nodes and markup and detach events and observers.
   *
   * This method may be useful to free up resources before re-creating a tree
   * on an existing div, for example in unittest suites.
   * Note that this Wunderbaum instance becomes unusable afterwards.
   */
  public destroy() {
    this.logInfo("destroy()...");
    this.clear();
    this.resizeObserver.disconnect();
    this.element.innerHTML = "";

    // Remove all event handlers
    this.element.outerHTML = this.element.outerHTML; // eslint-disable-line
  }

  /**
   * Return `tree.option.NAME` (also resolving if this is a callback).
   *
   * See also {@link WunderbaumNode.getOption|WunderbaumNode.getOption()}
   * to evaluate `node.NAME` setting and `tree.types[node.type].NAME`.
   *
   * @param name option name (use dot notation to access extension option, e.g.
   * `filter.mode`)
   */
  getOption(name: string, defaultValue?: any): any {
    let ext;
    let opts = this.options as any;

    // Lookup `name` in options dict
    if (name.indexOf(".") >= 0) {
      [ext, name] = name.split(".");
      opts = opts[ext];
    }
    let value = opts[name];

    // A callback resolver always takes precedence
    if (typeof value === "function") {
      value = value({ type: "resolve", tree: this });
    }
    // Use value from value options dict, fallback do default
    // console.info(name, value, opts)
    return value ?? defaultValue;
  }

  /**
   * Set tree option.
   * Use dot notation to set plugin option, e.g. "filter.mode".
   */
  setOption(name: string, value: any): void {
    // this.log(`setOption(${name}, ${value})`);
    if (name.indexOf(".") >= 0) {
      const parts = name.split(".");
      const ext = this.extensions[parts[0]];
      ext!.setPluginOption(parts[1], value);
      return;
    }
    (this.options as any)[name] = value;
    switch (name) {
      case "checkbox":
        this.update(ChangeType.any);
        break;
      case "enabled":
        this.setEnabled(!!value);
        break;
      case "fixedCol":
        this.element.classList.toggle("wb-fixed-col", !!value);
        break;
    }
  }

  /** Return true if the tree (or one of its nodes) has the input focus. */
  hasFocus() {
    return this.element.contains(document.activeElement);
  }

  /**
   * Return true if the tree displays a header. Grids have a header unless the
   * `header` option is set to `false`. Plain trees have a header if the `header`
   * option is a string or `true`.
   */
  hasHeader() {
    const header = this.options.header;
    return this.isGrid() ? header !== false : !!header;
  }

  /** Run code, but defer rendering of viewport until done.
   *
   * ```js
   * const res = tree.runWithDeferredUpdate(() => {
   *   return someFunctionThatWouldUpdateManyNodes();
   * });
   * ```
   */
  runWithDeferredUpdate<T>(func: () => util.NotPromise<T>): T {
    try {
      this.enableUpdate(false);
      const res = func();
      util.assert(
        !(res instanceof Promise),
        `Promise return not allowed (see 'runWithDeferredUpdateAsync()'): ${res}`
      );
      return res;
    } finally {
      this.enableUpdate(true);
    }
  }
  /** Run code, but defer rendering of viewport until done.
   *
   * ```js
   * const res = await tree.runWithDeferredUpdate(async () => {
   *   return someAsyncFunctionThatWouldUpdateManyNodes();
   * });
   * ```
   */
  async runWithDeferredUpdateAsync<T>(func: () => Promise<T>): Promise<T> {
    try {
      this.enableUpdate(false);
      return await func();
    } finally {
      this.enableUpdate(true);
    }
  }

  /** Recursively expand all expandable nodes (triggers lazy load if needed). */
  async expandAll(flag: boolean = true, options?: ExpandAllOptions) {
    await this.root.expandAll(flag, options);
  }

  /** Recursively select all nodes. */
  selectAll(flag: boolean = true) {
    return this.root.setSelected(flag, { propagateDown: true });
  }

  /** Toggle select all nodes. */
  toggleSelect() {
    this.selectAll(this.root._anySelectable());
  }

  /**
   * Return an array of selected nodes.
   * @param stopOnParents only return the topmost selected node (useful with selectMode 'hier')
   */
  getSelectedNodes(stopOnParents: boolean = false): WunderbaumNode[] {
    return this.root.getSelectedNodes(stopOnParents);
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
    return this.root.getRefKeys(selected);
  }

  /*
   * Return an array of selected nodes.
   */
  protected _selectRange(eventInfo: WbEventInfo): false | void {
    this.logDebug("_selectRange", eventInfo);
    util.error("Not yet implemented.");
    // const mode = this.options.selectMode!;
    // if (mode !== "multi") {
    //   this.logDebug(`Range selection only available for selectMode 'multi'`);
    //   return;
    // }

    // if (eventInfo.canonicalName === "Meta+click") {
    //   eventInfo.node?.toggleSelected();
    //   return false; // don't
    // } else if (eventInfo.canonicalName === "Shift+click") {
    //   let from = this.activeNode;
    //   let to = eventInfo.node;
    //   if (!from || !to || from === to) {
    //     return;
    //   }
    //   this.runWithDeferredUpdate(() => {
    //     this.visitRows(
    //       (node) => {
    //         node.setSelected();
    //       },
    //       {
    //         includeHidden: true,
    //         includeSelf: false,
    //         start: from,
    //         reverse: from!._rowIdx! > to!._rowIdx!,
    //       }
    //     );
    //   });
    //   return false;
    // }
  }

  /** Return the number of nodes in the data model.
   * @param visible if true, nodes that are hidden due to collapsed parents are ignored.
   */
  count(visible = false): number {
    return visible ? this.treeRowCount : this.keyMap.size;
  }

  /** Return the number of *unique* nodes in the data model, i.e. unique `node.refKey`.
   */
  countUnique(): number {
    return this.refKeyMap.size;
  }

  /** @internal sanity check. */
  _check() {
    let i = 0;
    this.visit((n) => {
      i++;
    });
    if (this.keyMap.size !== i) {
      this.logWarn(`_check failed: ${this.keyMap.size} !== ${i}`);
    }
    // util.assert(this.keyMap.size === i);
  }

  /**
   * Find all nodes that match condition.
   *
   * @param match title string to search for, or a
   *     callback function that returns `true` if a node is matched.
   * @see {@link WunderbaumNode.findAll}
   */
  findAll(match: string | RegExp | MatcherCallback) {
    return this.root.findAll(match);
  }

  /**
   * Find all nodes with a given _refKey_ (aka a list of clones).
   *
   * @param refKey a `node.refKey` value to search for.
   * @returns an array of matching nodes with at least two element or `[]`
   * if nothing found.
   *
   * @see {@link WunderbaumNode.getCloneList}
   */
  findByRefKey(refKey: string): WunderbaumNode[] {
    const clones = this.refKeyMap.get(refKey);
    return clones ? Array.from(clones) : [];
  }

  /**
   * Find first node that matches condition.
   *
   * @param match title string to search for, or a
   *     callback function that returns `true` if a node is matched.
   * @see {@link WunderbaumNode.findFirst}
   */
  findFirst(match: string | RegExp | MatcherCallback) {
    return this.root.findFirst(match);
  }

  /**
   * Find first node that matches condition.
   *
   * @see {@link WunderbaumNode.findFirst}
   *
   */
  findKey(key: string): WunderbaumNode | null {
    return this.keyMap.get(key) || null;
  }

  /**
   * Find the next visible node that starts with `match`, starting at `startNode`
   * and wrap-around at the end.
   * Used by quicksearch and keyboard navigation.
   */
  findNextNode(
    match: string | MatcherCallback,
    startNode?: WunderbaumNode | null,
    reverse = false
  ): WunderbaumNode | null {
    //, visibleOnly) {
    let res: WunderbaumNode | null = null;
    const firstNode = this.getFirstChild()!;
    // Last visible node (calculation is expensive, so do only if we need it):
    const lastNode = reverse ? this.findRelatedNode(firstNode, "last")! : null;

    const matcher =
      typeof match === "string" ? makeNodeTitleStartMatcher(match) : match;
    startNode = startNode || (reverse ? lastNode : firstNode);

    function _checkNode(n: WunderbaumNode) {
      // console.log("_check " + n)
      if (matcher(n)) {
        res = n;
      }
      if (res || n === startNode) {
        return false;
      }
    }
    this.visitRows(_checkNode, {
      start: startNode,
      includeSelf: false,
      reverse: reverse,
    });
    // Wrap around search
    if (!res && startNode !== firstNode) {
      this.visitRows(_checkNode, {
        start: reverse ? lastNode : firstNode,
        includeSelf: true,
        reverse: reverse,
      });
    }
    return res;
  }

  /**
   * Find a node relative to another node.
   *
   * @param node
   * @param where 'down', 'first', 'last', 'left', 'parent', 'right', or 'up'.
   *   (Alternatively the keyCode that would normally trigger this move,
   *   e.g. `$.ui.keyCode.LEFT` = 'left'.
   * @param includeHidden Not yet implemented
   */
  findRelatedNode(
    node: WunderbaumNode,
    where: NavigationType,
    includeHidden = false
  ) {
    const rowHeight = this.options.rowHeightPx;
    let res = null;
    const pageSize = Math.floor(
      this.listContainerElement.clientHeight / rowHeight
    );

    switch (where) {
      case "parent":
        if (node.parent && node.parent.parent) {
          res = node.parent;
        }
        break;
      case "first":
        // First visible node
        this.visit((n) => {
          if (n.isVisible()) {
            res = n;
            return false;
          }
        });
        break;
      case "last":
        this.visit((n) => {
          // last visible node
          if (n.isVisible()) {
            res = n;
          }
        });
        break;
      case "left":
        if (node.parent && node.parent.parent) {
          res = node.parent;
        }
        // if (node.expanded) {
        //   node.setExpanded(false);
        // } else if (node.parent && node.parent.parent) {
        //   res = node.parent;
        // }
        break;
      case "right":
        if (node.children && node.children.length) {
          res = node.children[0];
        }
        // if (this.cellNavMode) {
        //   throw new Error("Not implemented");
        // } else {
        //   if (!node.expanded && (node.children || node.lazy)) {
        //     node.setExpanded();
        //     res = node;
        //   } else if (node.children && node.children.length) {
        //     res = node.children[0];
        //   }
        // }
        break;
      case "up":
        res = this._getNextNodeInView(node, { reverse: true });
        break;
      case "down":
        res = this._getNextNodeInView(node);
        break;
      case "pageDown":
        {
          const bottomNode = this.getLowestVpNode();
          // this.logDebug(`${where}(${node}) -> ${bottomNode}`);

          if (node._rowIdx! < bottomNode._rowIdx!) {
            res = bottomNode;
          } else {
            res = this._getNextNodeInView(node, {
              reverse: false,
              ofs: pageSize,
            });
          }
        }
        break;
      case "pageUp":
        if (node._rowIdx === 0) {
          res = node;
        } else {
          const topNode = this.getTopmostVpNode();
          // this.logDebug(`${where}(${node}) -> ${topNode}`);

          if (node._rowIdx! > topNode._rowIdx!) {
            res = topNode;
          } else {
            res = this._getNextNodeInView(node, {
              reverse: true,
              ofs: pageSize,
            });
          }
        }
        break;

      case "prevMatch":
      // fallthrough
      case "nextMatch":
        if (!this.isFilterActive) {
          this.logWarn(`${where}: Filter is not active.`);
          break;
        }
        res = this.findNextNode(
          (n) => n.isMatched(),
          node,
          where === "prevMatch"
        );
        res?.setActive();
        break;
      default:
        this.logWarn("Unknown relation '" + where + "'.");
    }
    return res;
  }

  /**
   * Iterator version of {@link Wunderbaum.format}.
   */
  *format_iter(
    name_cb?: NodeStringCallback,
    connectors?: string[]
  ): IterableIterator<string> {
    yield* this.root.format_iter(name_cb, connectors);
  }

  /**
   * Return multiline string representation of the node hierarchy.
   * Mostly useful for debugging.
   *
   * Example:
   * ```js
   * console.info(tree.format((n)=>n.title));
   * ```
   * logs
   * ```
   * Playground
   * ├─ Books
   * |   ├─ Art of War
   * |   ╰─ Don Quixote
   * ├─ Music
   * ...
   * ```
   *
   * @see {@link Wunderbaum.format_iter} and {@link WunderbaumNode.format}.
   */
  format(name_cb?: NodeStringCallback, connectors?: string[]): string {
    return this.root.format(name_cb, connectors);
  }

  /**
   * Always returns null (so a tree instance behaves as `tree.root`).
   */
  get parent(): null {
    return null;
  }

  /**
   * Return a list of top-level nodes.
   */
  get children(): WunderbaumNode[] {
    return this.root.children || [];
  }

  /**
   * Return the active cell (`span.wb-col`) of the currently active node or null.
   */
  getActiveColElem() {
    if (this.activeNode && this.activeColIdx >= 0) {
      return this.activeNode.getColElem(this.activeColIdx);
    }
    return null;
  }

  /**
   * Return the currently active node or null (alias for `tree.activeNode`).
   * Alias for {@link Wunderbaum.activeNode}.
   *
   * @see {@link WunderbaumNode.setActive}
   * @see {@link WunderbaumNode.isActive}
   * @see {@link Wunderbaum.activeNode}
   * @see {@link Wunderbaum.focusNode}
   */
  getActiveNode() {
    return this.activeNode;
  }

  /**
   * Return the first top level node if any (not the invisible root node).
   */
  getFirstChild() {
    return this.root.getFirstChild();
  }

  /**
   * Return the last top level node if any (not the invisible root node).
   */
  getLastChild() {
    return this.root.getLastChild();
  }

  /**
   * Return the node that currently has keyboard focus or null.
   * Alias for {@link Wunderbaum.focusNode}.
   * @see {@link WunderbaumNode.setFocus}
   * @see {@link WunderbaumNode.hasFocus}
   * @see {@link Wunderbaum.activeNode}
   * @see {@link Wunderbaum.focusNode}
   */
  getFocusNode() {
    return this.focusNode;
  }

  /** Return a {node: WunderbaumNode, region: TYPE} object for a mouse event.
   *
   * @param {Event} event Mouse event, e.g. click, ...
   * @returns {object} Return a {node: WunderbaumNode, region: TYPE} object
   *     TYPE: 'title' | 'prefix' | 'expander' | 'checkbox' | 'icon' | undefined
   */
  static getEventInfo(event: Event): WbEventInfo {
    const target = <Element>event.target;
    const cl = target.classList;
    const parentCol = target.closest("span.wb-col") as HTMLSpanElement;
    const node = Wunderbaum.getNode(target);
    const tree = node ? node.tree : Wunderbaum.getTree(event);
    const res: WbEventInfo = {
      event: <MouseEvent>event,
      canonicalName: util.eventToString(event),
      tree: tree!,
      node: node,
      region: NodeRegion.unknown,
      colDef: undefined,
      colIdx: -1,
      colId: undefined,
      colElem: parentCol,
    };

    if (cl.contains("wb-title")) {
      res.region = NodeRegion.title;
    } else if (cl.contains("wb-expander")) {
      res.region = node!.isExpandable()
        ? NodeRegion.expander
        : NodeRegion.prefix;
    } else if (cl.contains("wb-checkbox")) {
      res.region = NodeRegion.checkbox;
    } else if (cl.contains("wb-icon")) {
      //|| cl.contains("wb-custom-icon")) {
      res.region = NodeRegion.icon;
    } else if (cl.contains("wb-node")) {
      res.region = NodeRegion.title;
    } else if (parentCol) {
      res.region = NodeRegion.column;
      const idx = Array.prototype.indexOf.call(
        parentCol.parentNode!.children,
        parentCol
      );
      res.colIdx = idx;
    } else if (cl.contains("wb-row")) {
      // Plain tree
      res.region = NodeRegion.title;
    } else {
      // Somewhere near the title
      if (event.type !== "mousemove" && !(event instanceof KeyboardEvent)) {
        tree?.logWarn("getEventInfo(): not found", event, res);
      }
      return res;
    }
    if (res.colIdx === -1) {
      res.colIdx = 0;
    }
    res.colDef = <any>tree?.columns[res.colIdx];
    res.colDef != null ? (res.colId = (<any>res.colDef).id) : 0;
    // this.log("Event", event, res);
    return res;
  }

  /**
   * Return readable string representation for this instance.
   * @internal
   */
  toString() {
    return `Wunderbaum<'${this.id}'>`;
  }

  /** Return true if any node title or grid cell is currently beeing edited.
   *
   * See also {@link isEditingTitle}.
   */
  isEditing(): boolean {
    const focusElem = this.nodeListElement.querySelector(
      "input:focus,select:focus"
    );
    return !!focusElem;
  }

  /** Return true if any node is currently in edit-title mode.
   *
   * See also {@link WunderbaumNode.isEditingTitle} and {@link isEditing}.
   */
  isEditingTitle(): boolean {
    return this._callMethod("edit.isEditingTitle");
  }

  /**
   * Return true if any node is currently beeing loaded, i.e. a Ajax request is pending.
   */
  isLoading(): boolean {
    let res = false;

    this.root.visit((n) => {
      // also visit rootNode
      if (n._isLoading || n._requestId) {
        res = true;
        return false;
      }
    }, true);
    return res;
  }

  /** Write to `console.log` with tree name as prefix if opts.debugLevel >= 4.
   * @see {@link logDebug}
   */
  log(...args: any[]) {
    if (this.options.debugLevel! >= 4) {
      console.log(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Write to `console.debug`  with tree name as prefix if opts.debugLevel >= 4.
   * and browser console level includes debug/verbose messages.
   * @see {@link log}
   */
  logDebug(...args: any[]) {
    if (this.options.debugLevel! >= 4) {
      console.debug(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Write to `console.error` with tree name as prefix. */
  logError(...args: any[]) {
    if (this.options.debugLevel! >= 1) {
      console.error(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Write to `console.info`  with tree name as prefix if opts.debugLevel >= 3. */
  logInfo(...args: any[]) {
    if (this.options.debugLevel! >= 3) {
      console.info(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** @internal */
  logTime(label: string): string {
    if (this.options.debugLevel! >= 4) {
      console.time(this + ": " + label); // eslint-disable-line no-console
    }
    return label;
  }

  /** @internal */
  logTimeEnd(label: string): void {
    if (this.options.debugLevel! >= 4) {
      console.timeEnd(this + ": " + label); // eslint-disable-line no-console
    }
  }

  /** Write to `console.warn` with tree name as prefix with if opts.debugLevel >= 2. */
  logWarn(...args: any[]) {
    if (this.options.debugLevel! >= 2) {
      console.warn(this.toString(), ...args); // eslint-disable-line no-console
    }
  }

  /** Emit a warning for deprecated methods. @internal */
  logDeprecate(method: string, options?: DeprecationOptions) {
    if (this.options.debugLevel! >= 2) {
      let msg = `${this}: ${method} is deprecated`;
      if (options?.since) {
        msg += ` since ${options.since}`;
      }
      if (options?.hint) {
        msg += ` (${options.since})`;
      }
      console.warn(msg + "."); // eslint-disable-line no-console
    }
  }

  /** Reset column widths to default. @since 0.10.0 */
  resetColumns() {
    this.columns.forEach((col) => {
      delete col.customWidthPx;
    });
    this.update(ChangeType.colStructure);
  }

  // /** Renumber nodes `_nativeIndex`. @see {@link WunderbaumNode.resetNativeChildOrder} */
  // resetNativeChildOrder(options?: ResetOrderOptions) {
  //   this.root.resetNativeChildOrder(options);
  // }

  /**
   * Make sure that this node is vertically scrolled into the viewport.
   *
   * Nodes that are above the visible area become the top row, nodes that are
   * below the viewport become the bottom row.
   */
  scrollTo(nodeOrOpts: ScrollToOptions | WunderbaumNode) {
    const PADDING = 2; // leave some pixels between viewport bounds

    let node;
    // WunderbaumNode;
    let options: ScrollToOptions | undefined;

    if (nodeOrOpts instanceof WunderbaumNode) {
      node = nodeOrOpts;
    } else {
      options = nodeOrOpts;
      node = options.node;
    }
    util.assert(node && node._rowIdx != null, `Invalid node: ${node}`);

    const rowHeight = this.options.rowHeightPx;
    const scrollParent = this.element;
    const headerHeight = this.headerElement.clientHeight; // May be 0
    const scrollTop = scrollParent.scrollTop;
    const vpHeight = scrollParent.clientHeight;
    const rowTop = node._rowIdx! * rowHeight + headerHeight;
    const vpTop = headerHeight;
    const vpRowTop = rowTop - scrollTop;
    const vpRowBottom = vpRowTop + rowHeight;
    const topNode = options?.topNode;

    // this.log( `scrollTo(${node.title}), vpTop:${vpTop}px, scrollTop:${scrollTop}, vpHeight:${vpHeight}, rowTop:${rowTop}, vpRowTop:${vpRowTop}`, nodeOrOpts , options);

    let newScrollTop: number | null = null;
    if (vpRowTop >= vpTop) {
      if (vpRowBottom <= vpHeight) {
        // Already in view
        // this.log("Already in view");
      } else {
        // Node is below viewport
        // this.log("Below viewport");
        newScrollTop = rowTop + rowHeight - vpHeight + PADDING; // leave some pixels between viewport bounds
      }
    } else {
      // Node is above viewport
      // this.log("Above viewport");
      newScrollTop = rowTop - vpTop - PADDING; // leave some pixels between viewport bounds
    }
    if (newScrollTop != null) {
      this.log(`scrollTo(${rowTop}): ${scrollTop} => ${newScrollTop}`);
      scrollParent.scrollTop = newScrollTop;
      if (topNode) {
        // Make sure the topNode is always visible
        this.scrollTo(topNode);
      }
      // this.update(ChangeType.scroll);
    }
  }

  /**
   * Make sure that this node is horizontally scrolled into the viewport.
   * Called by {@link setColumn}.
   */
  protected scrollToHorz() {
    // const PADDING = 1;
    const fixedWidth = this.columns[0]._widthPx!;
    const vpWidth = this.element.clientWidth;
    const scrollLeft = this.element.scrollLeft;
    const colElem = this.getActiveColElem()!;
    const colLeft = Number.parseInt(colElem?.style.left, 10);
    const colRight = colLeft + Number.parseInt(colElem?.style.width, 10);
    let newLeft = scrollLeft;

    if (colLeft - scrollLeft < fixedWidth) {
      // The current column is scrolled behind the left fixed column
      newLeft = colLeft - fixedWidth;
    } else if (colRight - scrollLeft > vpWidth) {
      // The current column is scrolled outside the right side
      newLeft = colRight - vpWidth;
    }
    newLeft = Math.max(0, newLeft);
    // util.assert(node._rowIdx != null);
    this.log(
      `scrollToHorz(${this.activeColIdx}): ${colLeft}..${colRight}, fixedOfs=${fixedWidth}, vpWidth=${vpWidth}, curLeft=${scrollLeft} -> ${newLeft}`
    );
    this.element.scrollLeft = newLeft;
    // this.update(ChangeType.scroll);
  }
  /**
   * Set column #colIdx to 'active'.
   *
   * This higlights the column header and -cells by adding the `wb-active`
   * class to all grid cells of the active column. <br>
   * Available in cell-nav mode only.
   *
   * If _options.edit_ is true, the embedded input element is focused, or if
   * colIdx is 0, the node title is put into edit mode.
   */
  setColumn(colIdx: number | string, options?: SetColumnOptions) {
    const edit = options?.edit;
    const scroll = options?.scrollIntoView !== false;

    util.assert(this.isCellNav(), "Expected cellNav mode");

    if (typeof colIdx === "string") {
      const cid = colIdx;
      colIdx = this.columns.findIndex((c) => c.id === colIdx);
      util.assert(colIdx >= 0, `Invalid colId: ${cid}`);
    }
    util.assert(
      0 <= colIdx && colIdx < this.columns.length,
      `Invalid colIdx: ${colIdx}`
    );
    this.activeColIdx = colIdx;

    // Update `wb-active` class for all headers
    if (this.hasHeader()) {
      for (const rowDiv of this.headerElement.children) {
        let i = 0;
        for (const colDiv of rowDiv.children) {
          (colDiv as HTMLElement).classList.toggle("wb-active", i++ === colIdx);
        }
      }
    }

    this.activeNode?.update(ChangeType.status);

    // Update `wb-active` class for all cell spans
    for (const rowDiv of this.nodeListElement.children) {
      let i = 0;
      for (const colDiv of rowDiv.children) {
        (colDiv as HTMLElement).classList.toggle("wb-active", i++ === colIdx);
      }
    }
    // Horizontically scroll into view
    if (scroll || edit) {
      this.scrollToHorz();
    }

    if (edit && this.activeNode) {
      // this.activeNode.setFocus(); // Blur prev. input if any
      if (colIdx === 0) {
        this.activeNode.startEditTitle();
      } else {
        this.getActiveColElem()
          ?.querySelector<HTMLInputElement>("input,select")
          ?.focus();
      }
    }
  }

  /* Set or remove keyboard focus to the tree container. @internal */
  _setActiveNode(node: WunderbaumNode | null) {
    this._activeNode = node;
  }

  /** Set or remove keyboard focus to the tree container. */
  setActiveNode(key: string, flag: boolean = true, options?: SetActiveOptions) {
    this.findKey(key)?.setActive(flag, options);
  }

  /** Set or remove keyboard focus to the tree container. */
  setFocus(flag = true) {
    if (flag) {
      this.element.focus();
    } else {
      this.element.blur();
    }
  }

  /* Set or remove keyboard focus to the tree container. @internal */
  _setFocusNode(node: WunderbaumNode | null) {
    this._focusNode = node;
    this._updateActiveDescendant();
  }

  /**
   * Point `aria-activedescendant` at the focused row, if it is rendered.
   * (Rows outside the viewport are not in the DOM; the attribute is restored
   * when the row is rendered again.)
   * @internal
   */
  _updateActiveDescendant() {
    const rowElem = this._focusNode?._rowElem;
    if (rowElem && rowElem.id) {
      this.element.setAttribute("aria-activedescendant", rowElem.id);
    } else {
      this.element.removeAttribute("aria-activedescendant");
    }
  }

  /** Return the current selection/expansion/activation status. @experimental */
  getState(options: GetStateOptions = {}): TreeStateDefinition {
    const {
      activeKey = true,
      expandedKeys = false,
      selectedKeys = false,
    } = options;

    const expandSet = new Set<string>();

    if (expandedKeys) {
      for (const node of this) {
        if (node.isExpanded() && node.hasChildren()) {
          expandSet.add(node.key);
        }
      }
    }
    // Parents of active node are always expanded
    if (activeKey && this.activeNode) {
      this.activeNode.visitParents((n) => {
        if (n.parent) {
          expandSet.add(n.key);
        }
      }, false);
    }

    const state: TreeStateDefinition = {
      expandedKeys: expandSet.size ? Array.from(expandSet) : undefined,
      activeKey: this.activeNode?.key ?? null,
      activeColIdx: this.activeColIdx,
      selectedKeys: selectedKeys
        ? this.getSelectedNodes().flatMap((n) => n.key)
        : undefined,
    };
    return state;
  }

  /** Apply selection/expansion/activation status. @experimental */
  async setState(state: TreeStateDefinition, options: SetStateOptions = {}) {
    const { expandLazy = true } = options;
    return this.runWithDeferredUpdateAsync(async () => {
      if (state.expandedKeys && state.expandedKeys.length) {
        if (expandLazy) {
          // Expand all keys recursively, even if they are not in the tree yet
          await this._loadLazyNodes(state.expandedKeys, {
            expand: true,
            noEvents: true,
          });
        } else {
          for (const key of state.expandedKeys) {
            this.findKey(key)?.setExpanded(true);
          }
        }
      }
      if (state.activeKey) {
        this.setActiveNode(state.activeKey);
      }
      if (state.selectedKeys) {
        this.selectAll(false);
        for (const key of state.selectedKeys) {
          this.findKey(key)?.setSelected(true);
        }
      }
      if (this.isCellNav() && state.activeColIdx != null) {
        this.setColumn(state.activeColIdx);
      }
    });
  }

  /**
   * Schedule an update request to reflect a tree change.
   * The render operation is async and debounced unless the `immediate` option
   * is set.
   *
   * Use {@link WunderbaumNode.update} if only a single node has changed,
   * or {@link WunderbaumNode._render}) to pass special options.
   */
  update(change: ChangeType, options?: UpdateOptions): void;

  /**
   * Update a row to reflect a single node's modification.
   *
   * @see {@link WunderbaumNode.update}, {@link WunderbaumNode._render}
   */
  update(
    change: ChangeType,
    node: WunderbaumNode,
    options?: UpdateOptions
  ): void;

  update(
    change: ChangeType,
    node?: WunderbaumNode | UpdateOptions,
    options?: UpdateOptions
  ): void {
    // this.log(`update(${change}) node=${node}`);
    if (!(node instanceof WunderbaumNode)) {
      options = node;
      node = undefined;
    }

    const immediate = !!util.getOption(options, "immediate");
    const RF = RenderFlag;
    const pending = this.pendingChangeTypes;

    if (this._disableUpdateCount) {
      // Assuming that we redraw all when enableUpdate() is re-enabled.
      // this.log(
      //   `IGNORED update(${change}) node=${node} (disable level ${this._disableUpdateCount})`
      // );
      this._disableUpdateIgnoreCount++;
      return;
    }
    switch (change) {
      case ChangeType.any:
      case ChangeType.colStructure:
        pending.add(RF.header);
        pending.add(RF.clearMarkup);
        pending.add(RF.redraw);
        pending.add(RF.scroll);
        break;
      case ChangeType.resize:
        // case ChangeType.colWidth:
        pending.add(RF.header);
        pending.add(RF.redraw);
        break;
      case ChangeType.structure:
        pending.add(RF.redraw);
        break;
      case ChangeType.scroll:
        pending.add(RF.scroll);
        break;
      case ChangeType.row:
      case ChangeType.data:
      case ChangeType.status:
        util.assert(node, `Option '${change}' requires a node.`);
        // Single nodes are immediately updated if already inside the viewport
        // (otherwise we can ignore)
        if ((<WunderbaumNode>node)!._rowElem) {
          (<WunderbaumNode>node)!._render({ change: change });
        }
        break;
      default:
        util.error(`Invalid change type '${change}'.`);
    }

    if (change === ChangeType.colStructure) {
      const isGrid = this.isGrid();
      this.element.classList.toggle("wb-grid", isGrid);
      if (!isGrid && this.isCellNav()) {
        this.setCellNav(false);
      }
    }

    if (pending.size > 0) {
      if (immediate) {
        this._updateViewportImmediately();
      } else {
        this._updateViewportThrottled();
      }
    }
  }

  /** Disable mouse and keyboard interaction (return prev. state). */
  setEnabled(flag: boolean = true): boolean {
    const prev = this.enabled;
    this.enabled = !!flag;
    this.element.classList.toggle("wb-disabled", !flag);
    return prev;
  }

  /** Return false if tree is disabled. */
  isEnabled(): boolean {
    return this.enabled;
  }

  /** Return true if tree has more than one column, i.e. has additional data columns. */
  isGrid(): boolean {
    return this.columns && this.columns.length > 1;
  }

  /** Return true if cell-navigation mode is active. */
  isCellNav(): boolean {
    return !!this._cellNavMode;
  }
  /** Return true if row-navigation mode is active. */
  isRowNav(): boolean {
    return !this._cellNavMode;
  }

  /** Set the tree's navigation mode. */
  setCellNav(flag: boolean = true) {
    const prev = this._cellNavMode;
    // if (flag === prev) {
    //   return;
    // }
    this._cellNavMode = !!flag;
    if (flag && !prev) {
      // switch from row to cell mode
      this.setColumn(0);
    }
    this.element.classList.toggle("wb-cell-mode", flag);
    this.activeNode?.update(ChangeType.status);
  }

  /** Set the tree's navigation mode option. */
  setNavigationOption(mode: NavModeEnum, reset = false) {
    if (!this.isGrid() && mode !== NavModeEnum.row) {
      this.logWarn("Plain trees only support row navigation mode.");
      return;
    }
    this.options.navigationModeOption = mode;

    switch (mode) {
      case NavModeEnum.cell:
        this.setCellNav(true);
        break;
      case NavModeEnum.row:
        this.setCellNav(false);
        break;
      case NavModeEnum.startCell:
        if (reset) {
          this.setCellNav(true);
        }
        break;
      case NavModeEnum.startRow:
        if (reset) {
          this.setCellNav(false);
        }
        break;
      default:
        util.error(`Invalid mode '${mode}'.`);
    }
  }

  /** Display tree status (ok, loading, error, noData) using styles and a dummy root node. */
  setStatus(
    status: NodeStatusType,
    options?: SetStatusOptions
  ): WunderbaumNode | null {
    return this.root.setStatus(status, options);
  }

  /** Add or redefine node type definitions. */
  setTypes(types: any, replace = true) {
    util.assert(util.isPlainObject(types), `Expected plain objext: ${types}`);
    if (replace) {
      this.types = types;
    } else {
      util.extend(this.types, types);
    }
    // Convert `TYPE.classes` to a Set
    for (const t of Object.values(this.types) as any) {
      if (t.classes) {
        t.classes = util.toSet(t.classes);
      }
    }
  }

  /**
   * Sort nodes list by title or custom criteria.
   * @param {function} cmp custom compare function(a, b) that returns -1, 0, or 1
   *    (defaults to sorting by title).
   * @param {boolean} deep pass true to sort all descendant nodes recursively
   * @deprecated use {@link sort}
   */
  sortChildren(
    cmp: SortCallback | null = nodeTitleSorter,
    deep: boolean = false
  ): void {
    this.logDeprecate("sortChildren()", { since: "0.14.0" });
    return this.sort({
      cmp: cmp ? cmp : undefined,
      deep: deep,
      propName: "title",
    });
  }

  /**
   * Convenience method to implement column sorting.
   * @see {@link WunderbaumNode.sortByProperty}.
   * @since 0.11.0
   * @deprecated use {@link sort}
   */
  sortByProperty(options: SortByPropertyOptions) {
    this.logDeprecate("sortByProperty()", { since: "0.14.0" });
    this.root.sortByProperty(options);
  }

  /**
   * Sort nodes list by title or custom criteria.
   * @since 0.14.0
   */
  sort(options: SortOptions): void {
    this.root.sort(options);
  }

  /** Convert tree to an array of plain objects.
   *
   * @param callback is called for every node, in order to allow
   *     modifications.
   *     Return `false` to ignore this node or `"skip"` to include this node
   *     without its children.
   * @see {@link WunderbaumNode.toDict}.
   */
  toDictArray(callback?: NodeToDictCallback): Array<WbNodeData> {
    const res = this.root.toDict(true, callback);
    return res.children ?? [];
  }

  /**
   * Update column headers and column width.
   * Return true if at least one column width changed.
   */
  // _updateColumnWidths(options?: UpdateColumnsOptions): boolean {
  _updateColumnWidths(): boolean {
    // options = Object.assign({ updateRows: true, renderMarkup: false }, options);
    const defaultMinWidth = 4;
    const vpWidth = this.element.clientWidth;
    // Shorten last column width to avoid h-scrollbar
    // (otherwise resizbing the demo would display a void scrollbar?)
    const FIX_ADJUST_LAST_COL = 1;
    const columns = this.columns;
    const col0 = columns[0];

    let totalWidth = 0;
    let totalWeight = 0;
    let fixedWidth = 0;
    let modified = false;

    // this.element.classList.toggle("wb-grid", isGrid);
    // if (!isGrid && this.isCellNav()) {
    //   this.setCellNav(false);
    // }

    // if (options.calculateCols) {
    if (col0.id !== "*") {
      throw new Error(`First column must have  id '*': got '${col0.id}'.`);
    }
    // Gather width definitions
    this._columnsById = {};
    for (const col of columns) {
      this._columnsById[<string>col.id] = col;
      const cw = col.customWidthPx ? `${col.customWidthPx}px` : col.width;
      if (col.id === "*" && col !== col0) {
        throw new Error(
          `Column id '*' must be defined only once: '${col.title}'.`
        );
      }

      if (!cw || cw === "*") {
        col._weight = 1.0;
        totalWeight += 1.0;
      } else if (typeof cw === "number") {
        col._weight = cw;
        totalWeight += cw;
      } else if (typeof cw === "string" && cw.endsWith("px")) {
        col._weight = 0;
        const px = parseFloat(cw.slice(0, -2));
        if (col._widthPx != px) {
          modified = true;
          col._widthPx = px;
        }
        fixedWidth += px;
      } else {
        util.error(
          `Invalid column width: ${cw} (expected string ending with 'px' or number, e.g. "<num>px" or <int>).`
        );
      }
    }
    // Share remaining space between non-fixed columns
    const restPx = Math.max(0, vpWidth - fixedWidth);
    let ofsPx = 0;

    for (const col of columns) {
      let minWidth: number;

      if (col._weight) {
        const cmw = col.minWidth;
        if (typeof cmw === "number") {
          minWidth = cmw;
        } else if (typeof cmw === "string" && cmw.endsWith("px")) {
          minWidth = parseFloat(cmw.slice(0, -2));
        } else {
          minWidth = defaultMinWidth;
        }
        const px = Math.max(minWidth, (restPx * col._weight) / totalWeight);
        if (col._widthPx != px) {
          modified = true;
          col._widthPx = px;
        }
      }
      col._ofsPx = ofsPx;
      ofsPx += col._widthPx!;
    }
    columns[columns.length - 1]._widthPx! -= FIX_ADJUST_LAST_COL;
    totalWidth = ofsPx - FIX_ADJUST_LAST_COL;

    const tw = `${totalWidth}px`;
    this.headerElement.style.width = tw;
    this.listContainerElement!.style.width = tw;
    // }

    // Every column has now a calculated `_ofsPx` and `_widthPx`
    // this.logInfo("UC", this.columns, vpWidth, this.element.clientWidth, this.element);
    // console.trace();
    // util.error("BREAK");
    // if (modified) {
    //   this._renderHeaderMarkup();
    //   if (options.renderMarkup) {
    //     this.update(ChangeType.header, { removeMarkup: true });
    //   } else if (options.updateRows) {
    //     this._updateRows();
    //   }
    // }
    return modified;
  }

  // protected _insertIcon(icon: string, elem: HTMLElement) {
  //   const iconElem = document.createElement("i");
  //   iconElem.className = icon;
  //   elem.appendChild(iconElem);
  // }

  /** Create/update header markup from `this.columns` definition.
   * @internal
   */
  protected _renderHeaderMarkup() {
    util.assert(this.headerElement, "Expected a headerElement");
    const wantHeader = this.hasHeader();
    util.setElemDisplay(this.headerElement, wantHeader);
    this._ariaHeaderRows = wantHeader ? 1 : 0;
    if (!wantHeader) {
      return;
    }
    const iconMap = this.iconMap;
    const colCount = this.columns.length;
    const headerRow = this.headerElement.querySelector(".wb-row")!;
    util.assert(headerRow, "Expected a row in header element");
    headerRow.innerHTML = "<span class='wb-col'></span>".repeat(colCount);

    for (let i = 0; i < colCount; i++) {
      const col = this.columns[i];
      const colElem = <HTMLElement>headerRow.children[i];

      colElem.style.left = col._ofsPx + "px";
      colElem.style.width = col._widthPx + "px";
      colElem.setAttribute("role", "columnheader");
      colElem.setAttribute("aria-colindex", "" + (i + 1));
      if (util.toBool(col.sortable, this.options.columnsSortable, false)) {
        colElem.setAttribute(
          "aria-sort",
          col.sortOrder === "asc"
            ? "ascending"
            : col.sortOrder === "desc"
              ? "descending"
              : "none"
        );
      }
      // Add classes from `columns` definition to `<div.wb-col>` cells
      if (typeof col.headerClasses === "string") {
        col.headerClasses
          ? colElem.classList.add(...col.headerClasses.split(" "))
          : 0;
      } else {
        col.classes ? colElem.classList.add(...col.classes.split(" ")) : 0;
      }

      // Add tooltip to column title
      let tooltip = "";
      if (col.tooltip) {
        tooltip = util.escapeTooltip(col.tooltip);
        tooltip = ` title="${tooltip}"`;
      }
      // Add column header icons
      let addMarkup = "";
      // NOTE: we use CSS float: right to align icons, so they must be added in
      // reverse order
      if (util.toBool(col.menu, this.options.columnsMenu, false)) {
        const iconClass = "wb-col-icon-menu " + iconMap.colMenu;
        const icon = `<i data-command=menu class="wb-col-icon ${iconClass}" aria-hidden="true"></i>`;
        addMarkup += icon;
      }
      if (util.toBool(col.sortable, this.options.columnsSortable, false)) {
        let iconClass = "wb-col-icon-sort " + iconMap.colSortable;
        if (col.sortOrder) {
          iconClass += `wb-col-sort-${col.sortOrder}`;
          iconClass +=
            col.sortOrder === "asc" ? iconMap.colSortAsc : iconMap.colSortDesc;
        }
        const icon = `<i data-command=sort class="wb-col-icon ${iconClass}" aria-hidden="true"></i>`;
        addMarkup += icon;
      }
      if (util.toBool(col.filterable, this.options.columnsFilterable, false)) {
        colElem.classList.toggle("wb-col-filter", !!col.filterActive);
        let iconClass = "wb-col-icon-filter " + iconMap.colFilter;
        if (col.filterActive) {
          iconClass += iconMap.colFilterActive;
        }
        const icon = `<i data-command=filter class="wb-col-icon ${iconClass}" aria-hidden="true"></i>`;
        addMarkup += icon;
      }
      // Add resizer to all but the last column
      if (i < colCount - 1) {
        if (util.toBool(col.resizable, this.options.columnsResizable, false)) {
          addMarkup +=
            '<span class="wb-col-resizer wb-col-resizer-active" aria-hidden="true"></span>';
        } else {
          addMarkup +=
            '<span class="wb-col-resizer" aria-hidden="true"></span>';
        }
      }

      // Create column header
      const title = util.escapeHtml(col.title || col.id);
      colElem.innerHTML = `<span class="wb-col-title"${tooltip}>${title}</span>${addMarkup}`;

      // Highlight active column
      if (this.isCellNav()) {
        colElem.classList.toggle("wb-active", i === this.activeColIdx);
      }
    }
  }

  /**
   * Render pending changes that were scheduled using {@link WunderbaumNode.update} if any.
   *
   * This is hardly ever neccessary, since we normally either
   * - call `update(ChangeType.TYPE)` (async, throttled), or
   * - call `update(ChangeType.TYPE, {immediate: true})` (synchronous)
   *
   * `updatePendingModifications()` will only force immediate execution of
   * pending async changes if any.
   */
  updatePendingModifications() {
    if (this.pendingChangeTypes.size > 0) {
      this._updateViewportImmediately();
    }
  }

  /** @internal */
  public _createNodeIcon(
    node: WunderbaumNode,
    showLoading: boolean,
    showBadge: boolean
  ): HTMLElement | null {
    const iconMap = this.iconMap;
    let iconElem;
    let icon = node.getOption("icon");
    if (node._errorInfo) {
      icon = iconMap.error;
    } else if (node._isLoading && showLoading) {
      // Status nodes, or nodes without expander (< minExpandLevel) should
      // display the 'loading' status with the i.wb-icon span
      icon = iconMap.loading;
    }
    if (icon === false) {
      return null; // explicitly disabled: don't try default icons
    }
    if (typeof icon === "string") {
      // Callback returned an icon definition
      // icon = icon.trim()
    } else if (node.statusNodeType) {
      icon = (<any>iconMap)[node.statusNodeType];
    } else if (node.expanded) {
      icon = iconMap.folderOpen;
    } else if (node.children) {
      icon = iconMap.folder;
    } else if (node.lazy) {
      icon = iconMap.folderLazy;
    } else {
      icon = iconMap.doc;
    }

    if (!icon) {
      iconElem = document.createElement("i");
      iconElem.className = "wb-icon";
    } else if (TEST_HTML.test(icon)) {
      iconElem = util.elemFromHtml(icon);
    } else if (TEST_FILE_PATH.test(icon)) {
      iconElem = util.elemFromHtml(
        `<i class="wb-icon" style="background-image: url('${icon}');">`
      );
    } else {
      // Class name
      iconElem = document.createElement("i");
      iconElem.className = "wb-icon " + icon;
    }

    // Event handler `tree.iconBadge` can return a badge text or HTMLSpanElement
    const cbRes =
      showBadge && node._callEvent("iconBadge", { iconSpan: iconElem });

    let badge = null;
    if (cbRes != null && cbRes !== false) {
      let classes = "";
      let tooltip = "";
      if (util.isPlainObject(cbRes)) {
        badge = "" + cbRes.badge;
        classes = cbRes.badgeClass ? " " + cbRes.badgeClass : "";
        tooltip = cbRes.badgeTooltip ? ` title="${cbRes.badgeTooltip}"` : "";
      } else if (typeof cbRes === "number") {
        badge = "" + cbRes;
      } else {
        badge = cbRes; // string or HTMLSpanElement
      }
      if (typeof badge === "string") {
        badge = util.elemFromHtml(
          `<span class="wb-badge${classes}"${tooltip}>${util.escapeHtml(
            badge
          )}</span>`
        );
      }
      if (badge) {
        iconElem.append(<HTMLSpanElement>badge);
      }
    }
    return iconElem;
  }

  private _updateTopBreadcrumb() {
    const breadcrumb = this.breadcrumb!;
    const topmost = this.getTopmostVpNode(true);
    const parentList = topmost?.getParentList(false, false);
    if (parentList?.length) {
      breadcrumb.innerHTML = "";
      for (const n of topmost.getParentList(false, false)) {
        const icon = this._createNodeIcon(n, false, false);
        if (icon) {
          breadcrumb.append(icon, " ");
        }
        const part = document.createElement("a");
        part.textContent = n.title;
        part.href = "#";
        part.classList.add("wb-breadcrumb");
        part.dataset.key = n.key;
        breadcrumb.append(part, this.options.strings.breadcrumbDelimiter);
      }
    } else {
      breadcrumb.innerHTML = "&nbsp;";
    }
  }

  /**
   * This is the actual update method, which is wrapped inside a throttle method.
   * It calls `updateColumns()` and `_updateRows()`.
   *
   * This protected method should not be called directly but via
   * {@link WunderbaumNode.update}`, {@link Wunderbaum.update},
   * or {@link Wunderbaum.updatePendingModifications}.
   * @internal
   */
  protected _updateViewportImmediately() {
    if (this._disableUpdateCount) {
      this.log(
        `_updateViewportImmediately() IGNORED (disable level: ${this._disableUpdateCount}).`
      );
      this._disableUpdateIgnoreCount++;
      return;
    }
    if (this._updateViewportThrottled.pending()) {
      // this.logWarn(`_updateViewportImmediately() cancel pending timer.`);
      this._updateViewportThrottled.cancel();
    }
    // Shorten container height to avoid v-scrollbar
    const FIX_ADJUST_HEIGHT = 1;
    const RF = RenderFlag;

    const pending = new Set(this.pendingChangeTypes);
    this.pendingChangeTypes.clear();

    const scrollOnly = pending.has(RF.scroll) && pending.size === 1;
    if (scrollOnly) {
      this._updateRows({ newNodesOnly: true });
      // this.log("_updateViewportImmediately(): scroll only.");
    } else {
      this.log("_updateViewportImmediately():", pending);
      if (this.options.adjustHeight !== false) {
        let height = this.listContainerElement.clientHeight;
        const headerHeight = this.headerElement.clientHeight; // May be 0
        const wantHeight =
          this.element.clientHeight - headerHeight - FIX_ADJUST_HEIGHT;

        if (Math.abs(height - wantHeight) > 1.0) {
          // this.log("resize", height, wantHeight);
          this.listContainerElement.style.height = wantHeight + "px";
          height = wantHeight;
        }
      }
      // console.profile(`_updateViewportImmediately()`)

      if (pending.has(RF.clearMarkup)) {
        this.visit((n) => {
          n.removeMarkup();
        });
      }

      // let widthModified = false;
      if (pending.has(RF.header)) {
        // widthModified = this._updateColumnWidths();
        this._updateColumnWidths();
        this._renderHeaderMarkup();
      }
      this._updateRows();
      // console.profileEnd(`_updateViewportImmediately()`)
    }

    if (this.breadcrumb) {
      this._updateTopBreadcrumb();
    }
    this._callEvent("update");
  }

  // /**
  //  * Assert that TR order matches the natural node order
  //  * @internal
  //  */
  // protected _validateRows(): boolean {
  //   let trs = this.nodeListElement.childNodes;
  //   let i = 0;
  //   let prev = -1;
  //   let ok = true;
  //   trs.forEach((element) => {
  //     const tr = element as HTMLTableRowElement;
  //     const top = Number.parseInt(tr.style.top);
  //     const n = (<any>tr)._wb_node;
  //     // if (i < 4) {
  //     //   console.info(
  //     //     `TR#${i}, rowIdx=${n._rowIdx} , top=${top}px: '${n.title}'`
  //     //   );
  //     // }
  //     if (prev >= 0 && top !== prev + ROW_HEIGHT) {
  //       n.logWarn(
  //         `TR order mismatch at index ${i}: top=${top}px != ${
  //           prev + ROW_HEIGHT
  //         }`
  //       );
  //       // throw new Error("fault");
  //       ok = false;
  //     }
  //     prev = top;
  //     i++;
  //   });
  //   return ok;
  // }

  /*
   * - Traverse all *visible* nodes of the whole tree, i.e. skip collapsed nodes.
   * - Store count of rows to `tree.treeRowCount`.
   * - Renumber `node._rowIdx` for all visible nodes.
   * - Calculate the index range that must be rendered to fill the viewport
   *   (including upper and lower prefetch)
   * -
   */
  protected _updateRows(options?: any): boolean {
    // const label = this.logTime("_updateRows");
    // this.log("_updateRows", opts)
    options = Object.assign({ newNodesOnly: false }, options);
    const newNodesOnly = !!options.newNodesOnly;
    this._ariaRenderPass++;

    const rowHeight = this.options.rowHeightPx;
    const vpHeight = this.element.clientHeight;
    const prefetch = RENDER_MAX_PREFETCH;
    // const grace_prefetch = RENDER_MAX_PREFETCH - RENDER_MIN_PREFETCH;
    const ofs = this.element.scrollTop;

    let startIdx = Math.max(0, ofs / rowHeight - prefetch);
    startIdx = Math.floor(startIdx);
    // Make sure start is always even, so the alternating row colors don't
    // change when scrolling:
    if (startIdx % 2) {
      startIdx--;
    }
    let endIdx = Math.max(0, (ofs + vpHeight) / rowHeight + prefetch);
    endIdx = Math.ceil(endIdx);

    // this.debug("render", opts);
    const obsoleteNodes = new Set<WunderbaumNode>();
    this.nodeListElement.childNodes.forEach((elem) => {
      if ((<any>elem)._wb_node) {
        obsoleteNodes.add((<any>elem)._wb_node);
      }
    });

    let idx = 0;
    let top = 0;
    let modified = false;
    let prevElem: HTMLDivElement | "first" | "last" = "first";

    this.visitRows(function (node) {
      // node.log("visit")
      const rowDiv = node._rowElem;

      // Renumber all expanded nodes
      if (node._rowIdx !== idx) {
        node._rowIdx = idx;
        modified = true;
      }

      if (idx < startIdx || idx > endIdx) {
        // row is outside viewport bounds
        if (rowDiv) {
          prevElem = rowDiv;
        }
      } else if (rowDiv && newNodesOnly) {
        obsoleteNodes.delete(node);
        // no need to update existing node markup
        rowDiv.style.top = idx * rowHeight + "px";
        prevElem = rowDiv;
      } else {
        obsoleteNodes.delete(node);
        // Create new markup
        if (rowDiv) {
          rowDiv.style.top = idx * rowHeight + "px";
        }
        node._render({ top: top, after: prevElem });
        // node.log("render", top, prevElem, "=>", node._rowElem);
        prevElem = node._rowElem!;
      }
      idx++;
      top += rowHeight;
    });
    this.treeRowCount = idx;
    // ARIA: rows are virtualized, so announce the full count (incl. header)
    this.element.setAttribute(
      "aria-rowcount",
      "" + (idx + this._ariaHeaderRows)
    );
    for (const n of obsoleteNodes) {
      n._callEvent("discard");
      n.removeMarkup();
    }
    // Resize tree container
    this.nodeListElement.style.height = `${top}px`;
    // this.log(
    //   `_updateRows(scrollOfs:${ofs}, ${startIdx}..${endIdx})`,
    //   this.nodeListElement.style.height
    // );
    // this.logTimeEnd(label);
    // this._validateRows();
    return modified;
  }

  /**
   * Call `callback(node)` for all nodes in hierarchical order (depth-first, pre-order).
   * @see `wb_node.WunderbaumNode.IterableIterator<WunderbaumNode>`
   * @see {@link WunderbaumNode.visit}.
   *
   * @param {function} callback the callback function.
   *     Return false to stop iteration, return "skip" to skip this node and
   *     children only.
   * @returns {boolean} false, if the iterator was stopped.
   */
  visit(callback: (node: WunderbaumNode) => any) {
    return this.root.visit(callback, false);
  }

  /**
   * Call callback(node) for all nodes in vertical order, top down (or bottom up).
   *
   * Note that this considers expansion state, i.e. filtered nodes and children
   * of collapsed nodes are skipped, unless `includeHidden` is set.
   *
   * Stop iteration if callback() returns false.<br>
   * Return false if iteration was stopped.
   *
   * @returns {boolean} false if iteration was canceled
   */
  visitRows(callback: NodeVisitCallback, options?: VisitRowsOptions): boolean {
    if (!this.root.hasChildren()) {
      return false;
    }
    if (options && options.reverse) {
      delete options.reverse;
      return this._visitRowsUp(callback, options);
    }
    options = options || {};
    let i,
      nextIdx,
      parent,
      res,
      siblings,
      stopNode: WunderbaumNode,
      siblingOfs = 0,
      skipFirstNode = options.includeSelf === false,
      node: WunderbaumNode = options.start || this.root.children![0];
    const includeHidden = !!options.includeHidden;
    const checkFilter = !includeHidden && this.filterMode === "hide";
    parent = node.parent;
    while (parent) {
      // visit siblings
      siblings = parent.children!;
      nextIdx = siblings.indexOf(node) + siblingOfs;
      util.assert(
        nextIdx >= 0,
        `Could not find ${node} in parent's children: ${parent}`
      );

      for (i = nextIdx; i < siblings.length; i++) {
        node = siblings[i];
        if (node === stopNode!) {
          return false;
        }
        if (
          checkFilter &&
          !node.statusNodeType &&
          !node.match &&
          !node.subMatchCount
        ) {
          continue;
        }
        if (!skipFirstNode && callback(node) === false) {
          return false;
        }
        skipFirstNode = false;
        // Dive into node's child nodes
        if (
          node.children &&
          node.children.length &&
          (includeHidden || node.expanded)
        ) {
          res = node.visit((n: WunderbaumNode) => {
            if (n === stopNode) {
              return false;
            }
            if (checkFilter && !n.match && !n.subMatchCount) {
              return "skip";
            }
            if (callback(n) === false) {
              return false;
            }
            if (!includeHidden && n.children && !n.expanded) {
              return "skip";
            }
          }, false);
          if (res === false) {
            return false;
          }
        }
      }
      // Visit parent nodes (bottom up)
      node = parent;
      parent = parent.parent;
      siblingOfs = 1; //

      if (!parent && options.wrap) {
        this.logDebug("visitRows(): wrap around");
        util.assert(options.start, "`wrap` option requires `start`");
        stopNode = options.start!;
        options.wrap = false;
        parent = this.root;
        siblingOfs = 0;
      }
    }
    return true;
  }

  /**
   * Call fn(node) for all nodes in vertical order, bottom up.
   * @internal
   */
  protected _visitRowsUp(
    callback: NodeVisitCallback,
    options: VisitRowsOptions
  ): boolean {
    let children,
      idx,
      parent,
      node = options.start || this.root.children![0];
    const includeHidden = !!options.includeHidden;

    if (options.includeSelf !== false) {
      if (callback(node) === false) {
        return false;
      }
    }
    while (true) {
      parent = node.parent;
      children = parent.children!;

      if (children[0] === node) {
        // If this is already the first sibling, goto parent
        node = parent;
        if (!node.parent) {
          break; // first node of the tree
        }
        children = parent.children;
      } else {
        // Otherwise, goto prev. sibling
        idx = children.indexOf(node);
        node = children[idx - 1];
        // If the prev. sibling has children, follow down to last descendant
        while (
          (includeHidden || node.expanded) &&
          node.children &&
          node.children.length
        ) {
          children = node.children;
          parent = node;
          node = children[children.length - 1];
        }
      }
      // Skip invisible
      if (!includeHidden && !node.isVisible()) {
        continue;
      }
      if (callback(node) === false) {
        return false;
      }
    }
    return true;
  }

  /**
   * Reload the tree with a new source.
   *
   * Previous data is cleared. Note that also column- and type defintions may
   * be passed with the `source` object.
   * @see {@link Wunderbaum.reload} for a shortcut to reload the last ajax request
   * and restore the previous state.
   */
  async load(source: SourceType) {
    this.clear();
    this._initialSource = source;
    return this.root.load(source);
  }

  /** Reload the tree and optionally restore state.
   * Source defaults to last ajax url if any.
   * Restoring the active node requires stable keys
   * @see {@link WunderbaumOptions.autoKeys}
   * @see {@link Wunderbaum.load}
   * @experimental
   */
  async reload(options: ReloadOptions = {}) {
    const { source = this._initialSource, reactivate = true } = options;
    if (!source) {
      this.logWarn("No previous ajax source to reload.");
      return;
    }
    if (!reactivate) {
      return this.load(source);
    }
    const state = this.getState();
    await this.load(source);
    return this.setState(state);
  }

  /**
   * Make sure that all nodes in the given keyList are accessible.
   * This may include loading lazy parent nodes.
   * Recursively load (and optionally expand) all requested node paths.
   */
  protected async _loadLazyNodes(
    keyList: string[],
    options: LoadLazyNodesOptions = {}
  ) {
    const { expand = true } = options;
    const keySet = new Set<string>(keyList);

    // Make sure that all parent nodes are loaded (and expand if requested)
    while (keySet.size > 0) {
      const pendingNodes: Promise<void>[] = [];
      const curSet = new Set(keySet);
      for (const key of curSet) {
        const node = this.findKey(key);
        if (!node) {
          continue; // key not yet found (need to load lazy parent?)
        }
        keySet.delete(key);
        if (expand) {
          pendingNodes.push(node.setExpanded(true));
        } else if (node.isUnloaded()) {
          pendingNodes.push(node.loadLazy());
        }
        if (node._rowElem) {
          node._render(); // show spinner even is update is suppressed
        }
      }
      if (pendingNodes.length === 0) {
        // will not load any more nodes, so if if there are still keys
        // left in the set, we will never find them
        this.logWarn(`Could not expand ${keySet.size} nodes:`, keySet);
        break;
      }
      await Promise.allSettled(pendingNodes);
    }
  }

  /**
   * Disable render requests during operations that would trigger many updates.
   *
   * ```js
   * try {
   *   tree.enableUpdate(false);
   *   // ... (long running operation that would trigger many updates)
   *   foo();
   *   // ... NOTE: make sure that async operations have finished, e.g.
   *   await foo();
   * } finally {
   *   tree.enableUpdate(true);
   * }
   * ```
   */
  public enableUpdate(flag: boolean): void {
    /*
        5  7  9                20       25   30
    1   >-------------------------------------<
    2      >--------------------<
    3         >--------------------------<
    */
    if (flag) {
      util.assert(
        this._disableUpdateCount > 0,
        "enableUpdate(true) was called too often"
      );
      this._disableUpdateCount--;
      // this.logDebug(
      //   `enableUpdate(${flag}): count -> ${this._disableUpdateCount}...`
      // );
      if (this._disableUpdateCount === 0) {
        this.logDebug(
          `enableUpdate(): active again. Re-painting to catch up with ${this._disableUpdateIgnoreCount} ignored update requests...`
        );
        this._disableUpdateIgnoreCount = 0;
        this.update(ChangeType.any, { immediate: true });
      }
    } else {
      this._disableUpdateCount++;
      // this.logDebug(
      //   `enableUpdate(${flag}): count -> ${this._disableUpdateCount}...`
      // );
      // this._disableUpdate = Date.now();
    }
    // return !flag; // return previous value
  }

  /* ---------------------------------------------------------------------------
   * FILTER
   * -------------------------------------------------------------------------*/
  /**
   * Dim or hide unmatched nodes.
   * @param filter a string to match against node titles, or a callback function.
   * @param options filter options. Defaults to the `tree.options.filter` settings.
   * @returns the number of nodes that match the filter.
   * @example
   * ```ts
   * tree.filterNodes("foo", {mode: 'dim', fuzzy: true});
   * // or pass a callback
   * tree.filterNodes((node) => { return node.data.foo === true }, {mode: 'hide'});
   * ```
   */
  filterNodes(
    filter: string | RegExp | NodeFilterCallback,
    options: FilterNodesOptions
  ): number {
    return this.extensions.filter.filterNodes(filter, options);
  }

  /**
   * Return the number of nodes that match the current filter.
   * @see {@link Wunderbaum.filterNodes}
   * @since 0.9.0
   */
  countMatches(): number {
    return this.extensions.filter.countMatches();
  }

  /**
   * Dim or hide whole branches.
   * @deprecated Use {@link filterNodes} instead and set `options.matchBranch: true`.
   */
  filterBranches(
    filter: string | NodeFilterCallback,
    options: FilterNodesOptions
  ) {
    return this.extensions.filter.filterBranches(filter, options);
  }

  /**
   * Reset the filter.
   */
  clearFilter() {
    return this.extensions.filter.clearFilter();
  }
  /**
   * Return true if a filter is currently applied.
   */
  isFilterActive() {
    return !!this.filterMode;
  }
  /**
   * Re-apply current filter.
   */
  updateFilter() {
    return this.extensions.filter.updateFilter();
  }
}
